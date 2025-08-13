<?php
// admin/mapel/create.php
$page_title = "Tambah Mata Pelajaran";
$active_page = "mapel";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Mata Pelajaran', 'url' => 'list.php'],
    ['name' => 'Tambah Mata Pelajaran', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Ambil data untuk dropdown jurusan
$jurusan_list = [];
$stmt = $conn->prepare("SELECT id, kode_jurusan, nama_jurusan FROM jurusan ORDER BY nama_jurusan");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $jurusan_list[] = $row;
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
        // Cek apakah kode mata pelajaran sudah ada
        $stmt = $conn->prepare("SELECT id FROM mapel WHERE kode_mapel = ?");
        $stmt->bind_param("s", $kode_mapel);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Kode mata pelajaran sudah terdaftar!";
        } else {
            $stmt->close();
            
            // Proses simpan data
            $stmt = $conn->prepare("INSERT INTO mapel (kode_mapel, nama_mapel, jurusan_id) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $kode_mapel, $nama_mapel, $jurusan_id);
            
            if ($stmt->execute()) {
                $message = "Data mata pelajaran berhasil ditambahkan!";
                // Reset form
                $_POST = [];
            } else {
                $error = "Gagal menyimpan data mata pelajaran: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle me-2"></i>Tambah Mata Pelajaran</h2>
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
        <h5><i class="fas fa-book me-2"></i>Form Data Mata Pelajaran</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="kode_mapel" class="form-label">Kode Mata Pelajaran *</label>
                <input type="text" class="form-control" id="kode_mapel" name="kode_mapel" value="<?php echo isset($_POST['kode_mapel']) ? htmlspecialchars($_POST['kode_mapel']) : ''; ?>" placeholder="Contoh: MP001" required>
            </div>
            
            <div class="mb-3">
                <label for="nama_mapel" class="form-label">Nama Mata Pelajaran *</label>
                <input type="text" class="form-control" id="nama_mapel" name="nama_mapel" value="<?php echo isset($_POST['nama_mapel']) ? htmlspecialchars($_POST['nama_mapel']) : ''; ?>" placeholder="Contoh: Pemrograman Dasar" required>
            </div>
            
            <div class="mb-3">
                <label for="jurusan_id" class="form-label">Jurusan (Opsional)</label>
                <select class="form-select" id="jurusan_id" name="jurusan_id">
                    <option value="">Pilih Jurusan (Umum)</option>
                    <?php foreach ($jurusan_list as $jurusan): ?>
                        <option value="<?php echo $jurusan['id']; ?>" <?php echo (isset($_POST['jurusan_id']) && $_POST['jurusan_id'] == $jurusan['id']) ? 'selected' : ''; ?>>
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
                    <i class="fas fa-save me-1"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>