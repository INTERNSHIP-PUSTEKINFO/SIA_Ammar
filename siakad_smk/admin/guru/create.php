<?php
// admin/guru/create.php
$page_title = "Tambah Guru";
$active_page = "guru";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Guru', 'url' => 'list.php'],
    ['name' => 'Tambah Guru', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Ambil data untuk dropdown mata pelajaran
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
    $nip = trim($_POST['nip']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $tempat_lahir = trim($_POST['tempat_lahir']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = trim($_POST['alamat']);
    $mapel_id = !empty($_POST['mapel_id']) ? $_POST['mapel_id'] : null;
    
    // Validasi
    if (empty($nip) || empty($nama) || empty($email) || empty($password) || empty($tempat_lahir) || 
        empty($tanggal_lahir) || empty($jenis_kelamin) || empty($alamat)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Cek apakah NIP sudah ada
        $stmt = $conn->prepare("SELECT nip FROM guru WHERE nip = ?");
        $stmt->bind_param("s", $nip);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "NIP sudah terdaftar!";
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
                    // Simpan ke tabel users
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $role = 'guru';
                    
                    $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->bind_param("ssss", $nama, $email, $hashed_password, $role);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Gagal menyimpan data user: " . $stmt->error);
                    }
                    
                    $user_id = $conn->insert_id;
                    $stmt->close();
                    
                    // Simpan ke tabel guru
                    $stmt = $conn->prepare("INSERT INTO guru (nip, user_id, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, mapel_id) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sisssss", $nip, $user_id, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $mapel_id);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Gagal menyimpan data guru: " . $stmt->error);
                    }
                    
                    $stmt->close();
                    
                    $conn->commit();
                    $message = "Data guru berhasil ditambahkan!";
                    
                    // Reset form
                    $_POST = [];
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Terjadi kesalahan: " . $e->getMessage();
                }
            }
        }
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-plus me-2"></i>Tambah Guru</h2>
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
        <h5><i class="fas fa-chalkboard-teacher me-2"></i>Form Data Guru</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <!-- Data Akun -->
                <div class="col-md-6">
                    <h5>Data Akun</h5>
                    <hr>
                    <div class="mb-3">
                        <label for="nip" class="form-label">NIP *</label>
                        <input type="text" class="form-control" id="nip" name="nip" value="<?php echo isset($_POST['nip']) ? htmlspecialchars($_POST['nip']) : ''; ?>" required>
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
                        <label for="mapel_id" class="form-label">Mata Pelajaran</label>
                        <select class="form-select" id="mapel_id" name="mapel_id">
                            <option value="">Pilih Mata Pelajaran (Opsional)</option>
                            <?php foreach ($mapel_list as $mapel): ?>
                                <option value="<?php echo $mapel['id']; ?>" <?php echo (isset($_POST['mapel_id']) && $_POST['mapel_id'] == $mapel['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($mapel['kode_mapel'] . ' - ' . $mapel['nama_mapel']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <!-- Spacer untuk tata letak -->
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