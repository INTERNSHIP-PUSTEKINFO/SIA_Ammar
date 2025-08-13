<?php
// guru/nilai/input.php
$page_title = "Input Nilai Siswa";
$active_page = "nilai";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Nilai Siswa', 'url' => 'list.php'],
    ['name' => 'Input Nilai', 'active' => true]
];

include_once '../../includes/guru_header.php';
include_once '../../config/database.php';

$conn = getConnection();
$nip = $_SESSION['nip'];

// Ambil parameter
$nilai_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$selected_mapel_id = isset($_GET['mapel_id']) ? (int)$_GET['mapel_id'] : 0;
$selected_kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;

// Cek apakah guru mengajar mapel ini
if ($selected_mapel_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM jadwal_pelajaran WHERE mapel_id = ? AND guru_id = ?");
    $stmt->bind_param("is", $selected_mapel_id, $nip);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['total'] == 0) {
        header("Location: list.php?error=unauthorized");
        exit();
    }
    $stmt->close();
}

// Ambil data nilai jika sedang mengedit
$nilai_data = null;
if ($nilai_id) {
    $stmt = $conn->prepare("SELECT * FROM nilai WHERE id = ?");
    $stmt->bind_param("i", $nilai_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $nilai_data = $result->fetch_assoc();
        $selected_mapel_id = $nilai_data['mapel_id'];
    } else {
        header("Location: list.php?error=not_found");
        exit();
    }
    $stmt->close();
}

// Ambil daftar siswa berdasarkan kelas
$siswa_list = [];
if ($selected_kelas_id) {
    $stmt = $conn->prepare("SELECT s.nis, u.nama FROM siswa s JOIN users u ON s.user_id = u.id WHERE s.kelas_id = ? ORDER BY u.nama");
    $stmt->bind_param("i", $selected_kelas_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $siswa_list[] = $row;
    }
    $stmt->close();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $siswa_nis = trim($_POST['siswa_nis']);
    $semester = $_POST['semester'];
    $tahun_ajaran = trim($_POST['tahun_ajaran']);
    $nilai_tugas = (float)$_POST['nilai_tugas'];
    $nilai_uts = (float)$_POST['nilai_uts'];
    $nilai_uas = (float)$_POST['nilai_uas'];
    
    // Validasi
    if (empty($siswa_nis) || empty($semester) || empty($tahun_ajaran) || 
        !is_numeric($nilai_tugas) || !is_numeric($nilai_uts) || !is_numeric($nilai_uas)) {
        $error = "Semua field wajib diisi dengan benar!";
    } else {
        // Hitung nilai akhir
        $nilai_akhir = round(($nilai_tugas + $nilai_uts + $nilai_uas) / 3, 2);
        
        if ($nilai_id) {
            // Update nilai
            $stmt = $conn->prepare("UPDATE nilai SET siswa_nis = ?, semester = ?, tahun_ajaran = ?, nilai_tugas = ?, nilai_uts = ?, nilai_uas = ?, nilai_akhir = ? WHERE id = ?");
            $stmt->bind_param("ssssdddi", $siswa_nis, $semester, $tahun_ajaran, $nilai_tugas, $nilai_uts, $nilai_uas, $nilai_akhir, $nilai_id);
            
            if ($stmt->execute()) {
                $message = "Data nilai berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui data nilai: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // Cek apakah nilai sudah ada
            $stmt = $conn->prepare("SELECT id FROM nilai WHERE siswa_nis = ? AND mapel_id = ? AND semester = ? AND tahun_ajaran = ?");
            $stmt->bind_param("siss", $siswa_nis, $selected_mapel_id, $semester, $tahun_ajaran);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Data nilai untuk siswa ini pada semester dan tahun ajaran yang sama sudah ada!";
            } else {
                $stmt->close();
                
                // Insert nilai baru
                $stmt = $conn->prepare("INSERT INTO nilai (siswa_nis, mapel_id, semester, tahun_ajaran, nilai_tugas, nilai_uts, nilai_uas, nilai_akhir) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sissdddd", $siswa_nis, $selected_mapel_id, $semester, $tahun_ajaran, $nilai_tugas, $nilai_uts, $nilai_uas, $nilai_akhir);
                
                if ($stmt->execute()) {
                    $message = "Data nilai berhasil disimpan!";
                    // Reset form
                    $_POST = [];
                } else {
                    $error = "Gagal menyimpan data nilai: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i><?php echo $nilai_id ? 'Edit' : 'Input'; ?> Nilai Siswa</h2>
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
        <h5><i class="fas fa-edit me-2"></i>Form <?php echo $nilai_id ? 'Edit' : 'Input'; ?> Nilai</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $nilai_id; ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="siswa_nis" class="form-label">Siswa *</label>
                        <select class="form-select" id="siswa_nis" name="siswa_nis" required <?php echo $nilai_id ? 'disabled' : ''; ?>>
                            <option value="">Pilih Siswa</option>
                            <?php foreach ($siswa_list as $siswa): ?>
                                <option value="<?php echo $siswa['nis']; ?>" <?php echo (isset($_POST['siswa_nis']) && $_POST['siswa_nis'] == $siswa['nis']) || ($nilai_data && $nilai_data['siswa_nis'] == $siswa['nis']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($siswa['nis'] . ' - ' . $siswa['nama']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($nilai_id): ?>
                            <input type="hidden" name="siswa_nis" value="<?php echo htmlspecialchars($nilai_data['siswa_nis']); ?>">
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="semester" class="form-label">Semester *</label>
                        <select class="form-select" id="semester" name="semester" required>
                            <option value="">Pilih Semester</option>
                            <option value="Ganjil" <?php echo (isset($_POST['semester']) && $_POST['semester'] == 'Ganjil') || ($nilai_data && $nilai_data['semester'] == 'Ganjil') ? 'selected' : ''; ?>>Ganjil</option>
                            <option value="Genap" <?php echo (isset($_POST['semester']) && $_POST['semester'] == 'Genap') || ($nilai_data && $nilai_data['semester'] == 'Genap') ? 'selected' : ''; ?>>Genap</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tahun_ajaran" class="form-label">Tahun Ajaran *</label>
                        <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" value="<?php echo isset($_POST['tahun_ajaran']) ? htmlspecialchars($_POST['tahun_ajaran']) : ($nilai_data ? htmlspecialchars($nilai_data['tahun_ajaran']) : ''); ?>" placeholder="Contoh: 2023/2024" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nilai_tugas" class="form-label">Nilai Tugas *</label>
                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="nilai_tugas" name="nilai_tugas" value="<?php echo isset($_POST['nilai_tugas']) ? htmlspecialchars($_POST['nilai_tugas']) : ($nilai_data ? htmlspecialchars($nilai_data['nilai_tugas']) : ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nilai_uts" class="form-label">Nilai UTS *</label>
                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="nilai_uts" name="nilai_uts" value="<?php echo isset($_POST['nilai_uts']) ? htmlspecialchars($_POST['nilai_uts']) : ($nilai_data ? htmlspecialchars($nilai_data['nilai_uts']) : ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nilai_uas" class="form-label">Nilai UAS *</label>
                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="nilai_uas" name="nilai_uas" value="<?php echo isset($_POST['nilai_uas']) ? htmlspecialchars($_POST['nilai_uas']) : ($nilai_data ? htmlspecialchars($nilai_data['nilai_uas']) : ''); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="list.php?mapel_id=<?php echo $selected_mapel_id; ?>&kelas_id=<?php echo $selected_kelas_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i><?php echo $nilai_id ? 'Update' : 'Simpan'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>