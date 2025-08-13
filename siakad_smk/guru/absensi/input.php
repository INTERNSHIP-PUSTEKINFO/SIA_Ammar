<?php
// guru/absensi/input.php
$page_title = "Input Absensi Siswa";
$active_page = "absensi";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Absensi Siswa', 'url' => 'list.php'],
    ['name' => 'Input Absensi', 'active' => true]
];

include_once '../../includes/guru_header.php';
include_once '../../config/database.php';

$conn = getConnection();
$nip = $_SESSION['nip'];

// Ambil parameter
$absensi_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$selected_mapel_id = isset($_GET['mapel_id']) ? (int)$_GET['mapel_id'] : 0;
$selected_kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Cek apakah guru mengajar mapel ini
if ($selected_mapel_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM jadwal_pelajaran WHERE mapel_id = ? AND guru_id = ?");
    $stmt->bind_param("is", $selected_mapel_id, $nip);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['total'] == 0) {
        header("Location: list.php?error=unauthorized");
        exit();
    }
    $stmt->close();
}

// Ambil data absensi jika sedang mengedit
$absensi_data = null;
if ($absensi_id) {
    $stmt = $conn->prepare("SELECT * FROM absensi WHERE id = ?");
    $stmt->bind_param("i", $absensi_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $absensi_data = $result->fetch_assoc();
        $selected_mapel_id = $absensi_data['mapel_id'];
        $selected_date = $absensi_data['tanggal'];
    } else {
        header("Location: list.php?error=not_found");
        exit();
    }
    $stmt->close();
}

// Ambil daftar siswa berdasarkan kelas
$siswa_list = [];
if ($selected_kelas_id) {
    $stmt = $conn->prepare("SELECT s.nis, u.nama FROM siswa s JOIN users u ON s.user_id = u.id WHERE s.kelas_id = ? ORDER BY u.nama");
    $stmt->bind_param("i", $selected_kelas_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $siswa_list[] = $row;
    }
    $stmt->close();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $siswa_nis = trim($_POST['siswa_nis']);
    $keterangan = $_POST['keterangan'];
    
    // Validasi
    if (empty($siswa_nis) || empty($keterangan)) {
        $error = "Semua field wajib diisi!";
    } else {
        if ($absensi_id) {
            // Update absensi
            $stmt = $conn->prepare("UPDATE absensi SET siswa_nis = ?, mapel_id = ?, tanggal = ?, keterangan = ? WHERE id = ?");
            $stmt->bind_param("sissi", $siswa_nis, $selected_mapel_id, $selected_date, $keterangan, $absensi_id);
            
            if ($stmt->execute()) {
                $message = "Data absensi berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui data absensi: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // Cek apakah absensi sudah ada
            $stmt = $conn->prepare("SELECT id FROM absensi WHERE siswa_nis = ? AND mapel_id = ? AND tanggal = ?");
            $stmt->bind_param("sis", $siswa_nis, $selected_mapel_id, $selected_date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Data absensi untuk siswa ini pada tanggal yang sama sudah ada!";
            } else {
                $stmt->close();
                
                // Insert absensi baru
                $stmt = $conn->prepare("INSERT INTO absensi (siswa_nis, mapel_id, tanggal, keterangan) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("siss", $siswa_nis, $selected_mapel_id, $selected_date, $keterangan);
                
                if ($stmt->execute()) {
                    $message = "Data absensi berhasil disimpan!";
                    // Reset form
                    $_POST = [];
                } else {
                    $error = "Gagal menyimpan data absensi: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i><?php echo $absensi_id ? 'Edit' : 'Input'; ?> Absensi Siswa</h2>
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
        <h5><i class="fas fa-edit me-2"></i>Form <?php echo $absensi_id ? 'Edit' : 'Input'; ?> Absensi</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $absensi_id; ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="siswa_nis" class="form-label">Siswa *</label>
                        <select class="form-select" id="siswa_nis" name="siswa_nis" required <?php echo $absensi_id ? 'disabled' : ''; ?>>
                            <option value="">Pilih Siswa</option>
                            <?php foreach ($siswa_list as $siswa): ?>
                                <option value="<?php echo $siswa['nis']; ?>" <?php echo (isset($_POST['siswa_nis']) && $_POST['siswa_nis'] == $siswa['nis']) || ($absensi_data && $absensi_data['siswa_nis'] == $siswa['nis']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($siswa['nis'] . ' - ' . $siswa['nama']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($absensi_id): ?>
                            <input type="hidden" name="siswa_nis" value="<?php echo htmlspecialchars($absensi_data['siswa_nis']); ?>">
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <input type="text" class="form-control" id="tanggal" value="<?php echo htmlspecialchars(date('d M Y', strtotime($selected_date))); ?>" disabled>
                        <input type="hidden" name="tanggal" value="<?php echo $selected_date; ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan *</label>
                        <select class="form-select" id="keterangan" name="keterangan" required>
                            <option value="">Pilih Keterangan</option>
                            <option value="Hadir" <?php echo (isset($_POST['keterangan']) && $_POST['keterangan'] == 'Hadir') || ($absensi_data && $absensi_data['keterangan'] == 'Hadir') ? 'selected' : ''; ?>>Hadir</option>
                            <option value="Sakit" <?php echo (isset($_POST['keterangan']) && $_POST['keterangan'] == 'Sakit') || ($absensi_data && $absensi_data['keterangan'] == 'Sakit') ? 'selected' : ''; ?>>Sakit</option>
                            <option value="Izin" <?php echo (isset($_POST['keterangan']) && $_POST['keterangan'] == 'Izin') || ($absensi_data && $absensi_data['keterangan'] == 'Izin') ? 'selected' : ''; ?>>Izin</option>
                            <option value="Alpa" <?php echo (isset($_POST['keterangan']) && $_POST['keterangan'] == 'Alpa') || ($absensi_data && $absensi_data['keterangan'] == 'Alpa') ? 'selected' : ''; ?>>Alpa</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="list.php?mapel_id=<?php echo $selected_mapel_id; ?>&kelas_id=<?php echo $selected_kelas_id; ?>&date=<?php echo $selected_date; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i><?php echo $absensi_id ? 'Update' : 'Simpan'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>