<?php
// admin/nilai/create.php
$page_title = "Tambah Nilai";
$active_page = "nilai";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Nilai', 'url' => 'list.php'],
    ['name' => 'Tambah Nilai', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Ambil data untuk dropdown siswa, mapel, semester, tahun ajaran
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
    $semester = $_POST['semester'];
    $tahun_ajaran = trim($_POST['tahun_ajaran']);
    $nilai_tugas = (float)$_POST['nilai_tugas'];
    $nilai_uts = (float)$_POST['nilai_uts'];
    $nilai_uas = (float)$_POST['nilai_uas'];
    
    // Validasi
    if (empty($siswa_nis) || empty($mapel_id) || empty($semester) || empty($tahun_ajaran) || 
        empty($nilai_tugas) || empty($nilai_uts) || empty($nilai_uas)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Cek apakah kombinasi siswa, mapel, semester, dan tahun ajaran sudah ada
        $stmt = $conn->prepare("SELECT id FROM nilai WHERE siswa_nis = ? AND mapel_id = ? AND semester = ? AND tahun_ajaran = ?");
        $stmt->bind_param("siss", $siswa_nis, $mapel_id, $semester, $tahun_ajaran);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Data nilai untuk siswa, mata pelajaran, semester, dan tahun ajaran ini sudah ada!";
        } else {
            $stmt->close();
            
            // Proses simpan data
            $nilai_akhir = round(($nilai_tugas + $nilai_uts + $nilai_uas) / 3, 2); // Hitung nilai akhir
            
            $stmt = $conn->prepare("INSERT INTO nilai (siswa_nis, mapel_id, semester, tahun_ajaran, nilai_tugas, nilai_uts, nilai_uas, nilai_akhir) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sissddd", $siswa_nis, $mapel_id, $semester, $tahun_ajaran, $nilai_tugas, $nilai_uts, $nilai_uas, $nilai_akhir);
            
            if ($stmt->execute()) {
                $message = "Data nilai berhasil ditambahkan!";
                // Reset form
                $_POST = [];
            } else {
                $error = "Gagal menyimpan data nilai: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle me-2"></i>Tambah Nilai</h2>
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
        <h5><i class="fas fa-chart-line me-2"></i>Form Data Nilai</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="siswa_nis" class="form-label">Siswa *</label>
                <select class="form-select" id="siswa_nis" name="siswa_nis" required>
                    <option value="">Pilih Siswa</option>
                    <?php foreach ($siswa_list as $siswa): ?>
                        <option value="<?php echo $siswa['nis']; ?>" <?php echo (isset($_POST['siswa_nis']) && $_POST['siswa_nis'] == $siswa['nis']) ? 'selected' : ''; ?>>
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
                <label for="semester" class="form-label">Semester *</label>
                <select class="form-select" id="semester" name="semester" required>
                    <option value="">Pilih Semester</option>
                    <option value="Ganjil" <?php echo (isset($_POST['semester']) && $_POST['semester'] == 'Ganjil') ? 'selected' : ''; ?>>Ganjil</option>
                    <option value="Genap" <?php echo (isset($_POST['semester']) && $_POST['semester'] == 'Genap') ? 'selected' : ''; ?>>Genap</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="tahun_ajaran" class="form-label">Tahun Ajaran *</label>
                <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" value="<?php echo isset($_POST['tahun_ajaran']) ? htmlspecialchars($_POST['tahun_ajaran']) : ''; ?>" placeholder="Contoh: 2023/2024" required>
            </div>
            
            <div class="mb-3">
                <label for="nilai_tugas" class="form-label">Nilai Tugas *</label>
                <input type="number" step="0.1" class="form-control" id="nilai_tugas" name="nilai_tugas" value="<?php echo isset($_POST['nilai_tugas']) ? htmlspecialchars($_POST['nilai_tugas']) : ''; ?>" min="0" max="100" required>
            </div>
            
            <div class="mb-3">
                <label for="nilai_uts" class="form-label">Nilai UTS *</label>
                <input type="number" step="0.1" class="form-control" id="nilai_uts" name="nilai_uts" value="<?php echo isset($_POST['nilai_uts']) ? htmlspecialchars($_POST['nilai_uts']) : ''; ?>" min="0" max="100" required>
            </div>
            
            <div class="mb-3">
                <label for="nilai_uas" class="form-label">Nilai UAS *</label>
                <input type="number" step="0.1" class="form-control" id="nilai_uas" name="nilai_uas" value="<?php echo isset($_POST['nilai_uas']) ? htmlspecialchars($_POST['nilai_uas']) : ''; ?>" min="0" max="100" required>
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