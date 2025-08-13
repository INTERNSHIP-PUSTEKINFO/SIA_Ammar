<?php
// admin/kelas/edit.php
$page_title = "Edit Kelas";
$active_page = "kelas";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Kelas', 'url' => 'list.php'],
    ['name' => 'Edit Kelas', 'active' => true]
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

// Ambil data untuk dropdown jurusan
$jurusan_list = [];
$stmt = $conn->prepare("SELECT id, kode_jurusan, nama_jurusan FROM jurusan ORDER BY nama_jurusan");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $jurusan_list[] = $row;
}
$stmt->close();

// Ambil data kelas berdasarkan ID
$kelas = null;
$stmt = $conn->prepare("SELECT id, nama_kelas, tingkat, jurusan_id FROM kelas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $kelas = $result->fetch_assoc();
} else {
    header("Location: list.php?error=not_found");
    exit();
}
$stmt->close();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kelas = trim($_POST['nama_kelas']);
    $tingkat = $_POST['tingkat'];
    $jurusan_id = !empty($_POST['jurusan_id']) ? $_POST['jurusan_id'] : null;
    
    // Validasi
    if (empty($nama_kelas) || empty($tingkat)) {
        $error = "Nama kelas dan tingkat wajib diisi!";
    } else {
        // Cek apakah nama kelas sudah ada untuk tingkat dan jurusan yang sama (kecuali kelas ini sendiri)
        $stmt = $conn->prepare("SELECT id FROM kelas WHERE nama_kelas = ? AND tingkat = ? AND (jurusan_id = ? OR (jurusan_id IS NULL AND ? IS NULL)) AND id != ?");
        $stmt->bind_param("ssssi", $nama_kelas, $tingkat, $jurusan_id, $jurusan_id, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Kelas dengan nama, tingkat, dan jurusan yang sama sudah terdaftar!";
        } else {
            $stmt->close();
            
            // Proses update data
            $stmt = $conn->prepare("UPDATE kelas SET nama_kelas = ?, tingkat = ?, jurusan_id = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nama_kelas, $tingkat, $jurusan_id, $id);
            
            if ($stmt->execute()) {
                $message = "Data kelas berhasil diupdate!";
                
                // Refresh data kelas setelah update
                $stmt2 = $conn->prepare("SELECT id, nama_kelas, tingkat, jurusan_id FROM kelas WHERE id = ?");
                $stmt2->bind_param("i", $id);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                $kelas = $result2->fetch_assoc();
                $stmt2->close();
            } else {
                $error = "Gagal mengupdate data kelas: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Edit Kelas</h2>
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
        <h5><i class="fas fa-school me-2"></i>Form Edit Data Kelas</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($kelas['id']); ?>">
            
            <div class="mb-3">
                <label for="nama_kelas" class="form-label">Nama Kelas *</label>
                <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" value="<?php echo htmlspecialchars($kelas['nama_kelas']); ?>" placeholder="Contoh: X RPL 1" required>
                <div class="form-text">Gunakan format seperti: X RPL 1, XI TKJ 2, dll.</div>
            </div>
            
            <div class="mb-3">
                <label for="tingkat" class="form-label">Tingkat *</label>
                <select class="form-select" id="tingkat" name="tingkat" required>
                    <option value="">Pilih Tingkat</option>
                    <option value="X" <?php echo ($kelas['tingkat'] == 'X') ? 'selected' : ''; ?>>X (Sepuluh)</option>
                    <option value="XI" <?php echo ($kelas['tingkat'] == 'XI') ? 'selected' : ''; ?>>XI (Sebelas)</option>
                    <option value="XII" <?php echo ($kelas['tingkat'] == 'XII') ? 'selected' : ''; ?>>XII (Dua Belas)</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="jurusan_id" class="form-label">Jurusan</label>
                <select class="form-select" id="jurusan_id" name="jurusan_id">
                    <option value="">Pilih Jurusan (Opsional)</option>
                    <?php foreach ($jurusan_list as $jurusan): ?>
                        <option value="<?php echo $jurusan['id']; ?>" <?php echo ($kelas['jurusan_id'] == $jurusan['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($jurusan['kode_jurusan'] . ' - ' . $jurusan['nama_jurusan']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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