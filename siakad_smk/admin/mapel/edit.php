<?php
// admin/mapel/edit.php
$page_title = "Edit Mata Pelajaran";
$active_page = "mapel";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Mata Pelajaran', 'url' => 'list.php'],
    ['name' => 'Edit Mata Pelajaran', 'active' => true]
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

// Ambil data mata pelajaran berdasarkan ID
$mapel = null;
$stmt = $conn->prepare("SELECT id, kode_mapel, nama_mapel, jurusan_id FROM mapel WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $mapel = $result->fetch_assoc();
} else {
    header("Location: list.php?error=not_found");
    exit();
}
$stmt->close();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_mapel = trim($_POST['kode_mapel']);
    $nama_mapel = trim($_POST['nama_mapel']);
    $jurusan_id = !empty($_POST['jurusan_id']) ? $_POST['jurusan_id'] : null;
    
    // Validasi
    if (empty($kode_mapel) || empty($nama_mapel)) {
        $error = "Kode mata pelajaran dan nama mata pelajaran wajib diisi!";
    } else {
        // Cek apakah kode mata pelajaran sudah ada (kecuali untuk mapel ini sendiri)
        $stmt = $conn->prepare("SELECT id FROM mapel WHERE kode_mapel = ? AND id != ?");
        $stmt->bind_param("si", $kode_mapel, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Kode mata pelajaran sudah terdaftar oleh mata pelajaran lain!";
        } else {
            $stmt->close();
            
            // Proses update data
            $stmt = $conn->prepare("UPDATE mapel SET kode_mapel = ?, nama_mapel = ?, jurusan_id = ? WHERE id = ?");
            $stmt->bind_param("sssi", $kode_mapel, $nama_mapel, $jurusan_id, $id);
            
            if ($stmt->execute()) {
                $message = "Data mata pelajaran berhasil diupdate!";
                
                // Refresh data mapel setelah update
                $stmt2 = $conn->prepare("SELECT id, kode_mapel, nama_mapel, jurusan_id FROM mapel WHERE id = ?");
                $stmt2->bind_param("i", $id);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                $mapel = $result2->fetch_assoc();
                $stmt2->close();
            } else {
                $error = "Gagal mengupdate data mata pelajaran: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Edit Mata Pelajaran</h2>
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
        <h5><i class="fas fa-book me-2"></i>Form Edit Data Mata Pelajaran</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($mapel['id']); ?>">
            
            <div class="mb-3">
                <label for="kode_mapel" class="form-label">Kode Mata Pelajaran *</label>
                <input type="text" class="form-control" id="kode_mapel" name="kode_mapel" value="<?php echo htmlspecialchars($mapel['kode_mapel']); ?>" placeholder="Contoh: MP001" required>
            </div>
            
            <div class="mb-3">
                <label for="nama_mapel" class="form-label">Nama Mata Pelajaran *</label>
                <input type="text" class="form-control" id="nama_mapel" name="nama_mapel" value="<?php echo htmlspecialchars($mapel['nama_mapel']); ?>" placeholder="Contoh: Pemrograman Dasar" required>
            </div>
            
            <div class="mb-3">
                <label for="jurusan_id" class="form-label">Jurusan (Opsional)</label>
                <select class="form-select" id="jurusan_id" name="jurusan_id">
                    <option value="">Pilih Jurusan (Umum)</option>
                    <?php foreach ($jurusan_list as $jurusan): ?>
                        <option value="<?php echo $jurusan['id']; ?>" <?php echo ($mapel['jurusan_id'] == $jurusan['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($jurusan['kode_jurusan'] . ' - ' . $jurusan['nama_jurusan']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Pilih jurusan jika mata pelajaran khusus untuk jurusan tertentu.</div>
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