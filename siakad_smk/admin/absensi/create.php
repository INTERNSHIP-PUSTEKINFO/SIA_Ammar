<?php
// admin/absensi/create.php
$page_title = "Tambah Absensi";
$active_page = "absensi";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Absensi', 'url' => 'list.php'],
    ['name' => 'Tambah Absensi', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Ambil data untuk dropdown siswa dan mapel
$siswa_list = [];
$stmt = $conn->prepare("SELECT nis, nama FROM siswa ORDER BY nama");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $siswa_list[] = $row;
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
        // Cek apakah absensi untuk siswa, mapel, dan tanggal sudah ada
        $stmt = $conn->prepare("SELECT id FROM absensi WHERE siswa_nis = ? AND mapel_id = ? AND tanggal = ?");
        $stmt->bind_param("sis", $siswa_nis, $mapel_id, $tanggal);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Absensi untuk siswa, mata pelajaran, dan tanggal ini sudah ada!";
        } else {
            $stmt->close();
            
            // Proses simpan data
            $stmt = $conn->prepare("INSERT INTO absensi (siswa_nis, mapel_id, tanggal, keterangan) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siss", $siswa_nis, $mapel_id, $tanggal, $keterangan);
            
            if ($stmt->execute()) {
                $message = "Data absensi berhasil ditambahkan!";
                // Reset form
                $_POST = [];
            } else {
                $error = "Gagal menyimpan data absensi: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle me-2"></i>Tambah Absensi</h2>
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
        <h5><i class="fas fa-calendar-check me-2"></i>Form Data Absensi</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="siswa_nis" class="form-label">Siswa *</label>
                <select class="form-select" id="siswa_nis" name="siswa_nis" required>
                    <option value="">Pilih Siswa</option>
                    <?php foreach ($siswa_list as $siswa): ?>
                        <option value="<?php echo htmlspecialchars($siswa['nis']); ?>" <?php echo (isset($_POST['siswa_nis']) && $_POST['siswa_nis'] == $siswa['nis']) ? 'selected' : ''; ?>>
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
                        <option value="<?php echo $mapel['id']; ?>" <?php echo (isset($_POST['mapel_id']) && $_POST['mapel_id'] == $mapel['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($mapel['kode_mapel'] . ' - ' . $mapel['nama_mapel']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal *</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo isset($_POST['tanggal']) ? htmlspecialchars($_POST['tanggal']) : ''; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="keterangan" class="form-label">Keterangan *</label>
                <select class="form-select" id="keterangan" name="keterangan" required>
                    <option value="">Pilih Keterangan</option>
                    <option value="Hadir" <?php echo (isset($_POST['keterangan']) && $_POST['keterangan'] == 'Hadir') ? 'selected' : ''; ?>>Hadir</option>
                    <option value="Sakit" <?php echo (isset($_POST['keterangan']) && $_POST['keterangan'] == 'Sakit') ? 'selected' : ''; ?>>Sakit</option>
                    <option value="Izin" <?php echo (isset($_POST['keterangan']) && $_POST['keterangan'] == 'Izin') ? 'selected' : ''; ?>>Izin</option>
                    <option value="Alpa" <?php echo (isset($_POST['keterangan']) && $_POST['keterangan'] == 'Alpa') ? 'selected' : ''; ?>>Alpa</option>
                </select>
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