<?php
// admin/jurusan/edit.php
$page_title = "Edit Jurusan";
$active_page = "jurusan";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Jurusan', 'url' => 'list.php'],
    ['name' => 'Edit Jurusan', 'active' => true]
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

// Ambil data jurusan berdasarkan ID
$jurusan = null;
$stmt = $conn->prepare("SELECT id, kode_jurusan, nama_jurusan FROM jurusan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $jurusan = $result->fetch_assoc();
} else {
    header("Location: list.php?error=not_found");
    exit();
}
$stmt->close();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_jurusan = trim($_POST['kode_jurusan']);
    $nama_jurusan = trim($_POST['nama_jurusan']);
    
    // Validasi
    if (empty($kode_jurusan) || empty($nama_jurusan)) {
        $error = "Kode jurusan dan nama jurusan wajib diisi!";
    } else {
        // Cek apakah kode jurusan sudah ada (kecuali untuk jurusan ini sendiri)
        $stmt = $conn->prepare("SELECT id FROM jurusan WHERE kode_jurusan = ? AND id != ?");
        $stmt->bind_param("si", $kode_jurusan, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Kode jurusan sudah terdaftar oleh jurusan lain!";
        } else {
            $stmt->close();
            
            // Cek apakah nama jurusan sudah ada (kecuali untuk jurusan ini sendiri)
            $stmt = $conn->prepare("SELECT id FROM jurusan WHERE nama_jurusan = ? AND id != ?");
            $stmt->bind_param("si", $nama_jurusan, $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Nama jurusan sudah terdaftar oleh jurusan lain!";
            } else {
                $stmt->close();
                
                // Proses update data
                $stmt = $conn->prepare("UPDATE jurusan SET kode_jurusan = ?, nama_jurusan = ? WHERE id = ?");
                $stmt->bind_param("ssi", $kode_jurusan, $nama_jurusan, $id);
                
                if ($stmt->execute()) {
                    $message = "Data jurusan berhasil diupdate!";
                    
                    // Refresh data jurusan setelah update
                    $stmt2 = $conn->prepare("SELECT id, kode_jurusan, nama_jurusan FROM jurusan WHERE id = ?");
                    $stmt2->bind_param("i", $id);
                    $stmt2->execute();
                    $result2 = $stmt2->get_result();
                    $jurusan = $result2->fetch_assoc();
                    $stmt2->close();
                } else {
                    $error = "Gagal mengupdate data jurusan: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Edit Jurusan</h2>
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
        <h5><i class="fas fa-graduation-cap me-2"></i>Form Edit Data Jurusan</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($jurusan['id']); ?>">
            
            <div class="mb-3">
                <label for="kode_jurusan" class="form-label">Kode Jurusan *</label>
                <input type="text" class="form-control" id="kode_jurusan" name="kode_jurusan" value="<?php echo htmlspecialchars($jurusan['kode_jurusan']); ?>" placeholder="Contoh: RPL, TKJ" required>
                <div class="form-text">Gunakan singkatan yang umum digunakan.</div>
            </div>
            
            <div class="mb-3">
                <label for="nama_jurusan" class="form-label">Nama Jurusan *</label>
                <input type="text" class="form-control" id="nama_jurusan" name="nama_jurusan" value="<?php echo htmlspecialchars($jurusan['nama_jurusan']); ?>" placeholder="Contoh: Rekayasa Perangkat Lunak" required>
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