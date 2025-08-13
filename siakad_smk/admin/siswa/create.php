<?php
// admin/siswa/create.php
$page_title = "Tambah Siswa";
$active_page = "siswa";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Siswa', 'url' => 'list.php'],
    ['name' => 'Tambah Siswa', 'active' => true]
];
include_once '../../includes/admin_header.php';
include_once '../../config/database.php';
$conn = getConnection();

// Ambil data untuk dropdown
$jurusan_list = [];
$stmt = $conn->prepare("SELECT id, kode_jurusan, nama_jurusan FROM jurusan ORDER BY nama_jurusan");
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

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis = trim($_POST['nis']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $tempat_lahir = trim($_POST['tempat_lahir']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = trim($_POST['alamat']);
    $jurusan_id = !empty($_POST['jurusan_id']) ? $_POST['jurusan_id'] : NULL;
    $kelas_id = !empty($_POST['kelas_id']) ? $_POST['kelas_id'] : NULL;
    $tahun_masuk = $_POST['tahun_masuk'];
    $status = $_POST['status'];

    // Validasi
    if (empty($nis) || empty($nama) || empty($email) || empty($password) || 
        empty($tempat_lahir) || empty($tanggal_lahir) || empty($jenis_kelamin) || 
        empty($alamat) || empty($tahun_masuk) || empty($status)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Cek apakah NIS sudah ada
        $stmt = $conn->prepare("SELECT nis FROM siswa WHERE nis = ?");
        $stmt->bind_param("s", $nis);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = "NIS sudah terdaftar!";
        } else {
            $stmt->close();

            // Cek apakah email sudah ada
            $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $error = "Email sudah terdaftar!";
            } else {
                $stmt->close();

                // Proses simpan data
                $conn->begin_transaction();
                try {
                    // ✅ 1. Simpan ke tabel users
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $role = 'siswa';

                    $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->bind_param("ssss", $nama, $email, $hashed_password, $role);
                    if (!$stmt->execute()) {
                        throw new Exception("Gagal menyimpan data user: " . $stmt->error);
                    }
                    $user_id = $conn->insert_id; // Ambil user_id dari users
                    $stmt->close();

                    // ✅ 2. Simpan ke tabel siswa (TANPA kolom nama!)
                    $stmt = $conn->prepare("INSERT INTO siswa (nis, user_id, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, jurusan_id, kelas_id, tahun_masuk, status) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sisssssiss", $nis, $user_id, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $jurusan_id, $kelas_id, $tahun_masuk, $status);
                    if (!$stmt->execute()) {
                        throw new Exception("Gagal menyimpan data siswa: " . $stmt->error);
                    }
                    $stmt->close();
                    $conn->commit();
                    $message = "Data siswa berhasil ditambahkan!";
                    $_POST = []; // Reset form
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Terjadi kesalahan: " . $e->getMessage();
                    error_log("Error creating student: " . $e->getMessage());
                }
            }
        }
    }
}
$conn->close();
?>
<!-- HTML Form tetap sama -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-plus me-2"></i>Tambah Siswa</h2>
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
        <h5><i class="fas fa-user me-2"></i>Form Data Siswa</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <!-- Data Akun -->
                <div class="col-md-6">
                    <h5>Data Akun</h5>
                    <hr>
                    <div class="mb-3">
                        <label for="nis" class="form-label">NIS *</label>
                        <input type="text" class="form-control" id="nis" name="nis" value="<?php echo isset($_POST['nis']) ? htmlspecialchars($_POST['nis']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <!-- Data Pribadi -->
                <div class="col-md-6">
                    <h5>Data Pribadi</h5>
                    <hr>
                    <div class="mb-3">
                        <label for="tempat_lahir" class="form-label">Tempat Lahir *</label>
                        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" value="<?php echo isset($_POST['tempat_lahir']) ? htmlspecialchars($_POST['tempat_lahir']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir *</label>
                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo isset($_POST['tanggal_lahir']) ? htmlspecialchars($_POST['tanggal_lahir']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin *</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" <?php echo (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="P" <?php echo (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat *</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
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
                                <option value="<?php echo $jurusan['id']; ?>" <?php echo (isset($_POST['jurusan_id']) && $_POST['jurusan_id'] == $jurusan['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($jurusan['kode_jurusan'] . ' - ' . $jurusan['nama_jurusan']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="kelas_id" class="form-label">Kelas</label>
                        <select class="form-select" id="kelas_id" name="kelas_id">
                            <option value="">Pilih Kelas</option>
                            <?php foreach ($kelas_list as $kelas): ?>
                                <option value="<?php echo $kelas['id']; ?>" <?php echo (isset($_POST['kelas_id']) && $_POST['kelas_id'] == $kelas['id']) ? 'selected' : ''; ?>>
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
                                <option value="<?php echo $year; ?>" <?php echo (isset($_POST['tahun_masuk']) && $_POST['tahun_masuk'] == $year) ? 'selected' : ''; ?>>
                                    <?php echo $year; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Pilih Status</option>
                            <option value="aktif" <?php echo (isset($_POST['status']) && $_POST['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                            <option value="lulus" <?php echo (isset($_POST['status']) && $_POST['status'] == 'lulus') ? 'selected' : ''; ?>>Lulus</option>
                            <option value="keluar" <?php echo (isset($_POST['status']) && $_POST['status'] == 'keluar') ? 'selected' : ''; ?>>Keluar</option>
                        </select>
                    </div>
                </div>
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