<?php
// admin/kelas/create.php
$page_title = "Tambah Kelas";
$active_page = "kelas";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Kelas', 'url' => 'list.php'],
    ['name' => 'Tambah Kelas', 'active' => true]
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
    // --- PERBAIKAN: Normalisasi dan Validasi Data ---
    $nama_kelas = trim($_POST['nama_kelas'] ?? '');
    
    // 1. Normalisasi nilai tingkat ke huruf kapital
    $tingkat_input = trim($_POST['tingkat'] ?? '');
    $tingkat = strtoupper($tingkat_input); // Konversi ke huruf kapital
    
    // 2. Pastikan jurusan_id adalah integer atau null
    $jurusan_id_input = $_POST['jurusan_id'] ?? null;
    $jurusan_id = !empty($jurusan_id_input) ? (int)$jurusan_id_input : null;
    // --- AKHIR PERBAIKAN ---

    // Validasi
    if (empty($nama_kelas) || empty($tingkat)) {
        $error = "Nama kelas dan tingkat wajib diisi!";
    } else {
        // --- PERBAIKAN: Validasi nilai tingkat terhadap ENUM ---
        // 3. Validasi nilai tingkat terhadap ENUM yang diperbolehkan
        $allowed_tingkat_values = ['X', 'XI', 'XII']; // Sesuai definisi ENUM di PDF
        if (!in_array($tingkat, $allowed_tingkat_values)) {
             $error = "Nilai tingkat tidak valid. Harus 'X', 'XI', atau 'XII'.";
        } else {
            // --- AKHIR PERBAIKAN ---
            
            // Cek apakah nama kelas sudah ada untuk tingkat dan jurusan yang sama
            // (Perhatikan: Query ini juga perlu menggunakan $tingkat yang sudah dinormalisasi)
            $stmt = $conn->prepare("SELECT id FROM kelas WHERE nama_kelas = ? AND tingkat = ? AND (jurusan_id = ? OR (jurusan_id IS NULL AND ? IS NULL))");
            $stmt->bind_param("ssss", $nama_kelas, $tingkat, $jurusan_id, $jurusan_id); // Gunakan $tingkat
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Kelas dengan nama, tingkat, dan jurusan yang sama sudah terdaftar!";
            } else {
                $stmt->close();
                
                // Proses simpan data
                // (Perhatikan: Query ini juga perlu menggunakan $tingkat yang sudah dinormalisasi)
                $stmt = $conn->prepare("INSERT INTO kelas (nama_kelas, tingkat, jurusan_id) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $nama_kelas, $tingkat, $jurusan_id); // Gunakan $tingkat
                
                if ($stmt->execute()) {
                    $message = "Data kelas berhasil ditambahkan!";
                    // Reset form
                    $_POST = [];
                } else {
                    $error = "Gagal menyimpan data kelas: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle me-2"></i>Tambah Kelas</h2>
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
        <h5><i class="fas fa-school me-2"></i>Form Data Kelas</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="nama_kelas" class="form-label">Nama Kelas *</label>
                <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" value="<?php echo isset($_POST['nama_kelas']) ? htmlspecialchars($_POST['nama_kelas']) : ''; ?>" placeholder="Contoh: X RPL 1" required>
                <div class="form-text">Gunakan format seperti: X RPL 1, XI TKJ 2, dll.</div>
            </div>
            
            <div class="mb-3">
                <label for="tingkat" class="form-label">Tingkat *</label>
                <select class="form-select" id="tingkat" name="tingkat" required>
                    <option value="">Pilih Tingkat</option>
                    <!-- PASTIKAN value menggunakan HURUF KAPITAL -->
                    <option value="X" <?php echo (isset($_POST['tingkat']) && $_POST['tingkat'] == 'X') ? 'selected' : ''; ?>>X (Sepuluh)</option>
                    <option value="XI" <?php echo (isset($_POST['tingkat']) && $_POST['tingkat'] == 'XI') ? 'selected' : ''; ?>>XI (Sebelas)</option>
                    <option value="XII" <?php echo (isset($_POST['tingkat']) && $_POST['tingkat'] == 'XII') ? 'selected' : ''; ?>>XII (Dua Belas)</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="jurusan_id" class="form-label">Jurusan</label>
                <select class="form-select" id="jurusan_id" name="jurusan_id">
                    <option value="">Pilih Jurusan (Opsional)</option>
                    <?php foreach ($jurusan_list as $jurusan): ?>
                        <option value="<?php echo $jurusan['id']; ?>" <?php echo (isset($_POST['jurusan_id']) && $_POST['jurusan_id'] == $jurusan['id']) ? 'selected' : ''; ?>>
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
                    <i class="fas fa-save me-1"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>