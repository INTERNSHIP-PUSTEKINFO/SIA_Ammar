<?php
// admin/absensi/edit.php
$page_title = "Edit Absensi";
$active_page = "absensi";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Absensi', 'url' => 'list.php'],
    ['name' => 'Edit Absensi', 'active' => true]
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

// Ambil data absensi berdasarkan ID
$absensi = null;
$stmt = $conn->prepare("SELECT id, siswa_nis, mapel_id, tanggal, keterangan FROM absensi WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $absensi = $result->fetch_assoc();
} else {
    header("Location: list.php?error=not_found");
    exit();
}
$stmt->close();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $siswa_nis = trim($_POST['siswa_nis']);
    $mapel_id = (int)$_POST['mapel_id'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];
    
    // Validasi
    if (empty($siswa_nis) || empty($mapel_id) || empty($tanggal) || empty($keterangan)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Perbarui data absensi
        $stmt = $conn->prepare("UPDATE absensi SET siswa_nis = ?, mapel_id = ?, tanggal = ?, keterangan = ? WHERE id = ?");
        $stmt->bind_param("sissi", $siswa_nis, $mapel_id, $tanggal, $keterangan, $id);
        
        if ($stmt->execute()) {
            $message = "Data absensi berhasil diperbarui!";
            
            // Refresh data absensi setelah update
            $stmt2 = $conn->prepare("SELECT id, siswa_nis, mapel_id, tanggal, keterangan FROM absensi WHERE id = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $absensi = $result2->fetch_assoc();
            $stmt2->close();
        } else {
            $error = "Gagal memperbarui data absensi: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Edit Absensi</h2>
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
        <h5><i class="fas fa-calendar-check me-2"></i>Form Edit Data Absensi</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($absensi['id']); ?>">
            
            <div class="mb-3">
                <label for="siswa_nis" class="form-label">Siswa *</label>
                <select class="form-select" id="siswa_nis" name="siswa_nis" required>
                    <option value="">Pilih Siswa</option>
                    <?php foreach ($siswa_list as $siswa): ?>
                        <option value="<?php echo htmlspecialchars($siswa['nis']); ?>" <?php echo ($absensi['siswa_nis'] == $siswa['nis']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($siswa['nis'] . ' - ' . $siswa['nama']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="mapel_id" class="form-label">Mata Pelajaran *</label>
                <select class="form-select" id="mapel_id" name="mapel_id" required>
                    <option value="">Pilih Mata Pelajaran</option>
                    <?php foreach ($mapel_list as $mapel): ?>
                        <option value="<?php echo $mapel['id']; ?>" <?php echo ($absensi['mapel_id'] == $mapel['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($mapel['kode_mapel'] . ' - ' . $mapel['nama_mapel']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal *</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($absensi['tanggal']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="keterangan" class="form-label">Keterangan *</label>
                <select class="form-select" id="keterangan" name="keterangan" required>
                    <option value="">Pilih Keterangan</option>
                    <option value="Hadir" <?php echo ($absensi['keterangan'] == 'Hadir') ? 'selected' : ''; ?>>Hadir</option>
                    <option value="Sakit" <?php echo ($absensi['keterangan'] == 'Sakit') ? 'selected' : ''; ?>>Sakit</option>
                    <option value="Izin" <?php echo ($absensi['keterangan'] == 'Izin') ? 'selected' : ''; ?>>Izin</option>
                    <option value="Alpa" <?php echo ($absensi['keterangan'] == 'Alpa') ? 'selected' : ''; ?>>Alpa</option>
                </select>
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