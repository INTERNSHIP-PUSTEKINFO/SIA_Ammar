<?php
// admin/siswa/edit.php
$page_title = "Edit Siswa";
$active_page = "siswa";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Siswa', 'url' => 'list.php'],
    ['name' => 'Edit Siswa', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Ambil NIS dari parameter GET
$nis = isset($_GET['nis']) ? trim($_GET['nis']) : '';

if (empty($nis)) {
    header("Location: list.php");
    exit();
}

// Ambil data untuk dropdown
$jurusan_list = [];
$stmt = $conn->prepare("SELECT id, nama_jurusan FROM jurusan ORDER BY nama_jurusan");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $jurusan_list[] = $row;
}
$stmt->close();

$kelas_list = [];
$stmt = $conn->prepare("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $kelas_list[] = $row;
}
$stmt->close();

// Ambil data siswa berdasarkan NIS
$siswa = null;
$stmt = $conn->prepare("SELECT s.nis, s.tempat_lahir, s.tanggal_lahir, s.jenis_kelamin, s.alamat, s.tahun_masuk, s.status, s.jurusan_id, s.kelas_id, u.nama, u.email 
                        FROM siswa s 
                        JOIN users u ON s.user_id = u.id 
                        WHERE s.nis = ?");
$stmt->bind_param("s", $nis);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $siswa = $result->fetch_assoc();
} else {
    header("Location: list.php");
    exit();
}
$stmt->close();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $tempat_lahir = trim($_POST['tempat_lahir']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = trim($_POST['alamat']);
    $jurusan_id = !empty($_POST['jurusan_id']) ? $_POST['jurusan_id'] : null;
    $kelas_id = !empty($_POST['kelas_id']) ? $_POST['kelas_id'] : null;
    $tahun_masuk = $_POST['tahun_masuk'];
    $status = $_POST['status'];
    
    // Validasi
    if (empty($nama) || empty($email) || empty($tempat_lahir) || 
        empty($tanggal_lahir) || empty($jenis_kelamin) || empty($alamat) || empty($tahun_masuk) || empty($status)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Cek apakah email sudah ada (kecuali untuk user ini)
        $stmt = $conn->prepare("SELECT u.id FROM users u JOIN siswa s ON u.id = s.user_id WHERE u.email = ? AND s.nis != ?");
        $stmt->bind_param("ss", $email, $nis);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email sudah terdaftar oleh siswa lain!";
        } else {
            $stmt->close();
            
            // Proses update data
            $conn->begin_transaction();
            
            try {
                // Update tabel users
                $stmt = $conn->prepare("UPDATE users u JOIN siswa s ON u.id = s.user_id SET u.nama = ?, u.email = ?, u.updated_at = NOW() WHERE s.nis = ?");
                $stmt->bind_param("sss", $nama, $email, $nis);
                
                if (!$stmt->execute()) {
                    throw new Exception("Gagal mengupdate data user");
                }
                $stmt->close();
                
                // Update tabel siswa
                $stmt = $conn->prepare("UPDATE siswa SET tempat_lahir = ?, tanggal_lahir = ?, jenis_kelamin = ?, alamat = ?, jurusan_id = ?, kelas_id = ?, tahun_masuk = ?, status = ? WHERE nis = ?");
                $stmt->bind_param("ssssssiss", $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $jurusan_id, $kelas_id, $tahun_masuk, $status, $nis);
                
                if (!$stmt->execute()) {
                    throw new Exception("Gagal mengupdate data siswa");
                }
                $stmt->close();
                
                $conn->commit();
                $message = "Data siswa berhasil diupdate!";
                
                // Refresh data siswa setelah update
                $stmt = $conn->prepare("SELECT s.nis, s.tempat_lahir, s.tanggal_lahir, s.jenis_kelamin, s.alamat, s.tahun_masuk, s.status, s.jurusan_id, s.kelas_id, u.nama, u.email 
                                        FROM siswa s 
                                        JOIN users u ON s.user_id = u.id 
                                        WHERE s.nis = ?");
                $stmt->bind_param("s", $nis);
                $stmt->execute();
                $result = $stmt->get_result();
                $siswa = $result->fetch_assoc();
                $stmt->close();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-edit me-2"></i>Edit Siswa</h2>
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
        <h5><i class="fas fa-user me-2"></i>Form Edit Data Siswa</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <!-- Data Akun -->
                <div class="col-md-6">
                    <h5>Data Akun</h5>
                    <hr>
                    <div class="mb-3">
                        <label for="nis" class="form-label">NIS</label>
                        <input type="text" class="form-control" id="nis" value="<?php echo htmlspecialchars($siswa['nis']); ?>" disabled>
                        <input type="hidden" name="nis" value="<?php echo htmlspecialchars($siswa['nis']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($siswa['nama']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($siswa['email']); ?>" required>
                    </div>
                </div>
                
                <!-- Data Pribadi -->
                <div class="col-md-6">
                    <h5>Data Pribadi</h5>
                    <hr>
                    <div class="mb-3">
                        <label for="tempat_lahir" class="form-label">Tempat Lahir *</label>
                        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" value="<?php echo htmlspecialchars($siswa['tempat_lahir']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir *</label>
                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo htmlspecialchars($siswa['tanggal_lahir']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin *</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" <?php echo ($siswa['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="P" <?php echo ($siswa['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat *</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($siswa['alamat']); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Data Akademik -->
                <div class="col-md-6">
                    <h5>Data Akademik</h5>
                    <hr>
                    <div class="mb-3">
                        <label for="jurusan_id" class="form-label">Jurusan</label>
                        <select class="form-select" id="jurusan_id" name="jurusan_id">
                            <option value="">Pilih Jurusan</option>
                            <?php foreach ($jurusan_list as $jurusan): ?>
                                <option value="<?php echo $jurusan['id']; ?>" <?php echo ($siswa['jurusan_id'] == $jurusan['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($jurusan['nama_jurusan']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="kelas_id" class="form-label">Kelas</label>
                        <select class="form-select" id="kelas_id" name="kelas_id">
                            <option value="">Pilih Kelas</option>
                            <?php foreach ($kelas_list as $kelas): ?>
                                <option value="<?php echo $kelas['id']; ?>" <?php echo ($siswa['kelas_id'] == $kelas['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Data Tambahan -->
                <div class="col-md-6">
                    <h5>Data Tambahan</h5>
                    <hr>
                    <div class="mb-3">
                        <label for="tahun_masuk" class="form-label">Tahun Masuk *</label>
                        <select class="form-select" id="tahun_masuk" name="tahun_masuk" required>
                            <option value="">Pilih Tahun</option>
                            <?php 
                            $current_year = date('Y');
                            for ($year = $current_year; $year >= $current_year - 10; $year--): 
                            ?>
                                <option value="<?php echo $year; ?>" <?php echo ($siswa['tahun_masuk'] == $year) ? 'selected' : ''; ?>>
                                    <?php echo $year; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Pilih Status</option>
                            <option value="aktif" <?php echo ($siswa['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                            <option value="lulus" <?php echo ($siswa['status'] == 'lulus') ? 'selected' : ''; ?>>Lulus</option>
                            <option value="keluar" <?php echo ($siswa['status'] == 'keluar') ? 'selected' : ''; ?>>Keluar</option>
                        </select>
                    </div>
                </div>
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