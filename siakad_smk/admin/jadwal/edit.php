<?php
// admin/jadwal/edit.php
$page_title = "Edit Jadwal Pelajaran";
$active_page = "jadwal";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Jadwal Pelajaran', 'url' => 'list.php'],
    ['name' => 'Edit Jadwal', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Ambil ID dari parameter GET
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (empty($id)) {
    header("Location: list.php?error=invalid_id");
    exit();
}

// Ambil data untuk dropdown
$kelas_list = [];
$stmt = $conn->prepare("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $kelas_list[] = $row;
}
$stmt->close();

$mapel_list = [];
$stmt = $conn->prepare("SELECT id, kode_mapel, nama_mapel FROM mapel ORDER BY nama_mapel");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $mapel_list[] = $row;
}
$stmt->close();

$guru_list = [];
$stmt = $conn->prepare("SELECT g.nip, u.nama FROM guru g JOIN users u ON g.user_id = u.id ORDER BY u.nama");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $guru_list[] = $row;
}
$stmt->close();

// Ambil data jadwal berdasarkan ID
$jadwal = null;
$stmt = $conn->prepare("SELECT id, kelas_id, mapel_id, guru_id, hari, jam_ke FROM jadwal_pelajaran WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $jadwal = $result->fetch_assoc();
} else {
    header("Location: list.php?error=not_found");
    exit();
}
$stmt->close();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kelas_id = (int)$_POST['kelas_id'];
    $mapel_id = (int)$_POST['mapel_id'];
    $guru_id = trim($_POST['guru_id']); // guru_id adalah nip (VARCHAR)
    $hari = $_POST['hari'];
    $jam_ke = (int)$_POST['jam_ke'];
    
    // Validasi
    if (empty($kelas_id) || empty($mapel_id) || empty($guru_id) || empty($hari) || empty($jam_ke)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Cek apakah sudah ada jadwal di kelas, hari, dan jam yang sama (kecuali jadwal ini sendiri)
        $stmt = $conn->prepare("SELECT id FROM jadwal_pelajaran WHERE kelas_id = ? AND hari = ? AND jam_ke = ? AND id != ?");
        $stmt->bind_param("isii", $kelas_id, $hari, $jam_ke, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Sudah ada jadwal di kelas, hari, dan jam tersebut!";
        } else {
            $stmt->close();
            
            // Proses update data
            // Perhatikan tipe data guru_id (VARCHAR)
            $stmt = $conn->prepare("UPDATE jadwal_pelajaran SET kelas_id = ?, mapel_id = ?, guru_id = ?, hari = ?, jam_ke = ? WHERE id = ?");
            $stmt->bind_param("iissii", $kelas_id, $mapel_id, $guru_id, $hari, $jam_ke, $id);
            
            if ($stmt->execute()) {
                $message = "Data jadwal pelajaran berhasil diupdate!";
                
                // Refresh data jadwal setelah update
                $stmt2 = $conn->prepare("SELECT id, kelas_id, mapel_id, guru_id, hari, jam_ke FROM jadwal_pelajaran WHERE id = ?");
                $stmt2->bind_param("i", $id);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                $jadwal = $result2->fetch_assoc();
                $stmt2->close();
            } else {
                $error = "Gagal mengupdate data jadwal: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Edit Jadwal Pelajaran</h2>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-calendar-alt me-2"></i>Form Edit Data Jadwal Pelajaran</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($jadwal['id']); ?>">
            
            <div class="mb-3">
                <label for="kelas_id" class="form-label">Kelas *</label>
                <select class="form-select" id="kelas_id" name="kelas_id" required>
                    <option value="">Pilih Kelas</option>
                    <?php foreach ($kelas_list as $kelas): ?>
                        <option value="<?php echo $kelas['id']; ?>" <?php echo ($jadwal['kelas_id'] == $kelas['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="mapel_id" class="form-label">Mata Pelajaran *</label>
                <select class="form-select" id="mapel_id" name="mapel_id" required>
                    <option value="">Pilih Mata Pelajaran</option>
                    <?php foreach ($mapel_list as $mapel): ?>
                        <option value="<?php echo $mapel['id']; ?>" <?php echo ($jadwal['mapel_id'] == $mapel['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($mapel['kode_mapel'] . ' - ' . $mapel['nama_mapel']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="guru_id" class="form-label">Guru *</label>
                <select class="form-select" id="guru_id" name="guru_id" required>
                    <option value="">Pilih Guru</option>
                    <?php foreach ($guru_list as $guru): ?>
                        <option value="<?php echo $guru['nip']; ?>" <?php echo ($jadwal['guru_id'] == $guru['nip']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($guru['nama'] . ' (' . $guru['nip'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="hari" class="form-label">Hari *</label>
                <select class="form-select" id="hari" name="hari" required>
                    <option value="">Pilih Hari</option>
                    <option value="Senin" <?php echo ($jadwal['hari'] == 'Senin') ? 'selected' : ''; ?>>Senin</option>
                    <option value="Selasa" <?php echo ($jadwal['hari'] == 'Selasa') ? 'selected' : ''; ?>>Selasa</option>
                    <option value="Rabu" <?php echo ($jadwal['hari'] == 'Rabu') ? 'selected' : ''; ?>>Rabu</option>
                    <option value="Kamis" <?php echo ($jadwal['hari'] == 'Kamis') ? 'selected' : ''; ?>>Kamis</option>
                    <option value="Jumat" <?php echo ($jadwal['hari'] == 'Jumat') ? 'selected' : ''; ?>>Jumat</option>
                    <option value="Sabtu" <?php echo ($jadwal['hari'] == 'Sabtu') ? 'selected' : ''; ?>>Sabtu</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="jam_ke" class="form-label">Jam Ke *</label>
                <input type="number" class="form-control" id="jam_ke" name="jam_ke" min="1" max="15" value="<?php echo htmlspecialchars($jadwal['jam_ke']); ?>" required>
                <div class="form-text">Masukkan nomor jam pelajaran (contoh: 1, 2, 3).</div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Update
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>