<?php
// admin/jadwal/create.php
$page_title = "Tambah Jadwal Pelajaran";
$active_page = "jadwal";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Jadwal Pelajaran', 'url' => 'list.php'],
    ['name' => 'Tambah Jadwal', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

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

// Perhatikan query untuk guru: kita ambil nip dan nama
$guru_list = [];
$stmt = $conn->prepare("SELECT g.nip, u.nama FROM guru g JOIN users u ON g.user_id = u.id ORDER BY u.nama");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $guru_list[] = $row;
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
        // Cek apakah sudah ada jadwal di kelas, hari, dan jam yang sama
        $stmt = $conn->prepare("SELECT id FROM jadwal_pelajaran WHERE kelas_id = ? AND hari = ? AND jam_ke = ?");
        $stmt->bind_param("isi", $kelas_id, $hari, $jam_ke);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Sudah ada jadwal di kelas, hari, dan jam tersebut!";
        } else {
            $stmt->close();
            
            // Proses simpan data
            // Perhatikan tipe data guru_id (VARCHAR)
            $stmt = $conn->prepare("INSERT INTO jadwal_pelajaran (kelas_id, mapel_id, guru_id, hari, jam_ke) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iissi", $kelas_id, $mapel_id, $guru_id, $hari, $jam_ke);
            
            if ($stmt->execute()) {
                $message = "Data jadwal pelajaran berhasil ditambahkan!";
                // Reset form
                $_POST = [];
            } else {
                $error = "Gagal menyimpan data jadwal: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle me-2"></i>Tambah Jadwal Pelajaran</h2>
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
        <h5><i class="fas fa-calendar-alt me-2"></i>Form Data Jadwal Pelajaran</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="kelas_id" class="form-label">Kelas *</label>
                <select class="form-select" id="kelas_id" name="kelas_id" required>
                    <option value="">Pilih Kelas</option>
                    <?php foreach ($kelas_list as $kelas): ?>
                        <option value="<?php echo $kelas['id']; ?>" <?php echo (isset($_POST['kelas_id']) && $_POST['kelas_id'] == $kelas['id']) ? 'selected' : ''; ?>>
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
                        <option value="<?php echo $mapel['id']; ?>" <?php echo (isset($_POST['mapel_id']) && $_POST['mapel_id'] == $mapel['id']) ? 'selected' : ''; ?>>
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
                        <option value="<?php echo $guru['nip']; ?>" <?php echo (isset($_POST['guru_id']) && $_POST['guru_id'] == $guru['nip']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($guru['nama'] . ' (' . $guru['nip'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="hari" class="form-label">Hari *</label>
                <select class="form-select" id="hari" name="hari" required>
                    <option value="">Pilih Hari</option>
                    <option value="Senin" <?php echo (isset($_POST['hari']) && $_POST['hari'] == 'Senin') ? 'selected' : ''; ?>>Senin</option>
                    <option value="Selasa" <?php echo (isset($_POST['hari']) && $_POST['hari'] == 'Selasa') ? 'selected' : ''; ?>>Selasa</option>
                    <option value="Rabu" <?php echo (isset($_POST['hari']) && $_POST['hari'] == 'Rabu') ? 'selected' : ''; ?>>Rabu</option>
                    <option value="Kamis" <?php echo (isset($_POST['hari']) && $_POST['hari'] == 'Kamis') ? 'selected' : ''; ?>>Kamis</option>
                    <option value="Jumat" <?php echo (isset($_POST['hari']) && $_POST['hari'] == 'Jumat') ? 'selected' : ''; ?>>Jumat</option>
                    <option value="Sabtu" <?php echo (isset($_POST['hari']) && $_POST['hari'] == 'Sabtu') ? 'selected' : ''; ?>>Sabtu</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="jam_ke" class="form-label">Jam Ke *</label>
                <input type="number" class="form-control" id="jam_ke" name="jam_ke" min="1" max="15" value="<?php echo isset($_POST['jam_ke']) ? htmlspecialchars($_POST['jam_ke']) : ''; ?>" required>
                <div class="form-text">Masukkan nomor jam pelajaran (contoh: 1, 2, 3).</div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>