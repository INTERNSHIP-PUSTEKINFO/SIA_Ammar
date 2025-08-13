<?php
// admin/guru/edit.php
$page_title = "Edit Guru";
$active_page = "guru";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Guru', 'url' => 'list.php'],
    ['name' => 'Edit Guru', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Ambil NIP dari parameter GET
$nip = isset($_GET['nip']) ? trim($_GET['nip']) : '';

if (empty($nip)) {
    header("Location: list.php");
    exit();
}

// Ambil data untuk dropdown mata pelajaran
$mapel_list = [];
$stmt = $conn->prepare("SELECT id, kode_mapel, nama_mapel FROM mapel ORDER BY nama_mapel");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $mapel_list[] = $row;
}
$stmt->close();

// Ambil data guru berdasarkan NIP
$guru = null;
$stmt = $conn->prepare("SELECT g.nip, g.tempat_lahir, g.tanggal_lahir, g.jenis_kelamin, g.alamat, g.mapel_id, u.nama, u.email 
                        FROM guru g 
                        JOIN users u ON g.user_id = u.id 
                        WHERE g.nip = ?");
$stmt->bind_param("s", $nip);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $guru = $result->fetch_assoc();
} else {
    header("Location: list.php?error=not_found");
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
    $mapel_id = !empty($_POST['mapel_id']) ? $_POST['mapel_id'] : null;
    
    // Validasi
    if (empty($nama) || empty($email) || empty($tempat_lahir) || 
        empty($tanggal_lahir) || empty($jenis_kelamin) || empty($alamat)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Cek apakah email sudah ada (kecuali untuk user ini)
        $stmt = $conn->prepare("SELECT u.id FROM users u JOIN guru g ON u.id = g.user_id WHERE u.email = ? AND g.nip != ?");
        $stmt->bind_param("ss", $email, $nip);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email sudah terdaftar oleh guru lain!";
        } else {
            $stmt->close();
            
            // Proses update data
            $conn->begin_transaction();
            
            try {
                // Update tabel users
                $stmt = $conn->prepare("UPDATE users u JOIN guru g ON u.id = g.user_id SET u.nama = ?, u.email = ?, u.updated_at = NOW() WHERE g.nip = ?");
                $stmt->bind_param("sss", $nama, $email, $nip);
                
                if (!$stmt->execute()) {
                    throw new Exception("Gagal mengupdate data user");
                }
                $stmt->close();
                
                // Update tabel guru
                $stmt = $conn->prepare("UPDATE guru SET tempat_lahir = ?, tanggal_lahir = ?, jenis_kelamin = ?, alamat = ?, mapel_id = ? WHERE nip = ?");
                $stmt->bind_param("ssssss", $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $mapel_id, $nip);
                
                if (!$stmt->execute()) {
                    throw new Exception("Gagal mengupdate data guru");
                }
                $stmt->close();
                
                $conn->commit();
                $message = "Data guru berhasil diupdate!";
                
                // Refresh data guru setelah update
                $stmt = $conn->prepare("SELECT g.nip, g.tempat_lahir, g.tanggal_lahir, g.jenis_kelamin, g.alamat, g.mapel_id, u.nama, u.email 
                                        FROM guru g 
                                        JOIN users u ON g.user_id = u.id 
                                        WHERE g.nip = ?");
                $stmt->bind_param("s", $nip);
                $stmt->execute();
                $result = $stmt->get_result();
                $guru = $result->fetch_assoc();
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
    <h2><i class="fas fa-user-edit me-2"></i>Edit Guru</h2>
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
        <h5><i class="fas fa-chalkboard-teacher me-2"></i>Form Edit Data Guru</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <!-- Data Akun -->
                <div class="col-md-6">
                    <h5>Data Akun</h5>
                    <hr>
                    <div class="mb-3">
                        <label for="nip" class="form-label">NIP</label>
                        <input type="text" class="form-control" id="nip" value="<?php echo htmlspecialchars($guru['nip']); ?>" disabled>
                        <input type="hidden" name="nip" value="<?php echo htmlspecialchars($guru['nip']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($guru['nama']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($guru['email']); ?>" required>
                    </div>
                </div>
                
                <!-- Data Pribadi -->
                <div class="col-md-6">
                    <h5>Data Pribadi</h5>
                    <hr>
                    <div class="mb-3">
                        <label for="tempat_lahir" class="form-label">Tempat Lahir *</label>
                        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" value="<?php echo htmlspecialchars($guru['tempat_lahir']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir *</label>
                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo htmlspecialchars($guru['tanggal_lahir']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin *</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" <?php echo ($guru['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="P" <?php echo ($guru['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat *</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($guru['alamat']); ?></textarea>
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
                                <option value="<?php echo $mapel['id']; ?>" <?php echo ($guru['mapel_id'] == $mapel['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($mapel['kode_mapel'] . ' - ' . $mapel['nama_mapel']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <!-- Spacer -->
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