<?php
// admin/nilai/edit.php
$page_title = "Edit Nilai";
$active_page = "nilai";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Nilai', 'url' => 'list.php'],
    ['name' => 'Edit Nilai', 'active' => true]
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

// Ambil data untuk dropdown siswa, mapel
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

// Ambil data nilai berdasarkan ID
$nilai = null;
$stmt = $conn->prepare("SELECT id, siswa_nis, mapel_id, semester, tahun_ajaran, nilai_tugas, nilai_uts, nilai_uas, nilai_akhir FROM nilai WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $nilai = $result->fetch_assoc();
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
        // Perbarui nilai akhir
        $nilai_akhir = round(($nilai_tugas + $nilai_uts + $nilai_uas) / 3, 2);
        
        // Proses update data
        $stmt = $conn->prepare("UPDATE nilai SET siswa_nis = ?, mapel_id = ?, semester = ?, tahun_ajaran = ?, nilai_tugas = ?, nilai_uts = ?, nilai_uas = ?, nilai_akhir = ? WHERE id = ?");
        $stmt->bind_param("sissdddi", $siswa_nis, $mapel_id, $semester, $tahun_ajaran, $nilai_tugas, $nilai_uts, $nilai_uas, $nilai_akhir, $id);
        
        if ($stmt->execute()) {
            $message = "Data nilai berhasil diupdate!";
            
            // Refresh data nilai setelah update
            $stmt2 = $conn->prepare("SELECT id, siswa_nis, mapel_id, semester, tahun_ajaran, nilai_tugas, nilai_uts, nilai_uas, nilai_akhir FROM nilai WHERE id = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $nilai = $result2->fetch_assoc();
            $stmt2->close();
        } else {
            $error = "Gagal mengupdate data nilai: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Edit Nilai</h2>
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
        <h5><i class="fas fa-chart-line me-2"></i>Form Edit Data Nilai</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($nilai['id']); ?>">
            
            <div class="mb-3">
                <label for="siswa_nis" class="form-label">Siswa *</label>
                <select class="form-select" id="siswa_nis" name="siswa_nis" required>
                    <option value="">Pilih Siswa</option>
                    <?php foreach ($siswa_list as $siswa): ?>
                        <option value="<?php echo $siswa['nis']; ?>" <?php echo ($nilai['siswa_nis'] == $siswa['nis']) ? 'selected' : ''; ?>>
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
                        <option value="<?php echo $mapel['id']; ?>" <?php echo ($nilai['mapel_id'] == $mapel['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($mapel['kode_mapel'] . ' - ' . $mapel['nama_mapel']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="semester" class="form-label">Semester *</label>
                <select class="form-select" id="semester" name="semester" required>
                    <option value="">Pilih Semester</option>
                    <option value="Ganjil" <?php echo ($nilai['semester'] == 'Ganjil') ? 'selected' : ''; ?>>Ganjil</option>
                    <option value="Genap" <?php echo ($nilai['semester'] == 'Genap') ? 'selected' : ''; ?>>Genap</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="tahun_ajaran" class="form-label">Tahun Ajaran *</label>
                <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" value="<?php echo htmlspecialchars($nilai['tahun_ajaran']); ?>" placeholder="Contoh: 2023/2024" required>
            </div>
            
            <div class="mb-3">
                <label for="nilai_tugas" class="form-label">Nilai Tugas *</label>
                <input type="number" step="0.1" class="form-control" id="nilai_tugas" name="nilai_tugas" value="<?php echo htmlspecialchars($nilai['nilai_tugas']); ?>" min="0" max="100" required>
            </div>
            
            <div class="mb-3">
                <label for="nilai_uts" class="form-label">Nilai UTS *</label>
                <input type="number" step="0.1" class="form-control" id="nilai_uts" name="nilai_uts" value="<?php echo htmlspecialchars($nilai['nilai_uts']); ?>" min="0" max="100" required>
            </div>
            
            <div class="mb-3">
                <label for="nilai_uas" class="form-label">Nilai UAS *</label>
                <input type="number" step="0.1" class="form-control" id="nilai_uas" name="nilai_uas" value="<?php echo htmlspecialchars($nilai['nilai_uas']); ?>" min="0" max="100" required>
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