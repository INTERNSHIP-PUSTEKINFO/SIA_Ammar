<?php
// admin/jurusan/create.php
$page_title = "Tambah Jurusan";
$active_page = "jurusan";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Jurusan', 'url' => 'list.php'],
    ['name' => 'Tambah Jurusan', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_jurusan = trim($_POST['kode_jurusan']);
    $nama_jurusan = trim($_POST['nama_jurusan']);
    
    // Validasi
    if (empty($kode_jurusan) || empty($nama_jurusan)) {
        $error = "Kode jurusan dan nama jurusan wajib diisi!";
    } else {
        // Cek apakah kode jurusan sudah ada
        $stmt = $conn->prepare("SELECT id FROM jurusan WHERE kode_jurusan = ?");
        $stmt->bind_param("s", $kode_jurusan);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Kode jurusan sudah terdaftar!";
        } else {
            $stmt->close();
            
            // Cek apakah nama jurusan sudah ada
            $stmt = $conn->prepare("SELECT id FROM jurusan WHERE nama_jurusan = ?");
            $stmt->bind_param("s", $nama_jurusan);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Nama jurusan sudah terdaftar!";
            } else {
                $stmt->close();
                
                // Proses simpan data
                $stmt = $conn->prepare("INSERT INTO jurusan (kode_jurusan, nama_jurusan) VALUES (?, ?)");
                $stmt->bind_param("ss", $kode_jurusan, $nama_jurusan);
                
                if ($stmt->execute()) {
                    $message = "Data jurusan berhasil ditambahkan!";
                    // Reset form
                    $_POST = [];
                } else {
                    $error = "Gagal menyimpan data jurusan: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle me-2"></i>Tambah Jurusan</h2>
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
        <h5><i class="fas fa-graduation-cap me-2"></i>Form Data Jurusan</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="kode_jurusan" class="form-label">Kode Jurusan *</label>
                <input type="text" class="form-control" id="kode_jurusan" name="kode_jurusan" value="<?php echo isset($_POST['kode_jurusan']) ? htmlspecialchars($_POST['kode_jurusan']) : ''; ?>" placeholder="Contoh: RPL, TKJ" required>
                <div class="form-text">Gunakan singkatan yang umum digunakan.</div>
            </div>
            
            <div class="mb-3">
                <label for="nama_jurusan" class="form-label">Nama Jurusan *</label>
                <input type="text" class="form-control" id="nama_jurusan" name="nama_jurusan" value="<?php echo isset($_POST['nama_jurusan']) ? htmlspecialchars($_POST['nama_jurusan']) : ''; ?>" placeholder="Contoh: Rekayasa Perangkat Lunak" required>
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