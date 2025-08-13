<?php
// guru/absensi/detail.php
$page_title = "Detail Absensi Siswa";
$active_page = "absensi";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Absensi Siswa', 'url' => 'list.php'],
    ['name' => 'Detail Absensi', 'active' => true]
];

include_once '../../includes/guru_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Ambil parameter dari URL
$nis = isset($_GET['nis']) ? trim($_GET['nis']) : '';
$mapel_id = isset($_GET['mapel_id']) ? (int)$_GET['mapel_id'] : 0;
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

if (empty($nis) || empty($mapel_id) || empty($tanggal)) {
    header("Location: list.php?error=invalid_parameters");
    exit();
}

// Ambil data siswa berdasarkan NIS
$siswa = null;
$stmt = $conn->prepare("SELECT s.nis, u.nama FROM siswa s JOIN users u ON s.user_id = u.id WHERE s.nis = ?");
$stmt->bind_param("s", $nis);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $siswa = $result->fetch_assoc();
} else {
    header("Location: list.php?error=student_not_found");
    exit();
}
$stmt->close();

// Ambil data absensi siswa berdasarkan NIS, mapel_id, dan tanggal
$absensi = null;
$stmt = $conn->prepare("SELECT keterangan FROM absensi WHERE siswa_nis = ? AND mapel_id = ? AND tanggal = ?");
$stmt->bind_param("sis", $nis, $mapel_id, $tanggal);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $absensi = $result->fetch_assoc();
} else {
    header("Location: list.php?error=no_attendance_found");
    exit();
}
$stmt->close();

// Ambil nama mata pelajaran
$mapel = null;
$stmt = $conn->prepare("SELECT kode_mapel, nama_mapel FROM mapel WHERE id = ?");
$stmt->bind_param("i", $mapel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $mapel = $result->fetch_assoc();
}
$stmt->close();

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-clipboard-list me-2"></i>Detail Absensi Siswa</h2>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-user me-2"></i>Data Siswa</h5>
    </div>
    <div class="card-body">
        <table class="table table-borderless">
            <tr>
                <td><strong>NIS</strong></td>
                <td><?php echo htmlspecialchars($siswa['nis']); ?></td>
            </tr>
            <tr>
                <td><strong>Nama Siswa</strong></td>
                <td><?php echo htmlspecialchars($siswa['nama']); ?></td>
            </tr>
            <tr>
                <td><strong>Mata Pelajaran</strong></td>
                <td><?php echo htmlspecialchars($mapel['kode_mapel'] . ' - ' . $mapel['nama_mapel']); ?></td>
            </tr>
            <tr>
                <td><strong>Tanggal</strong></td>
                <td><?php echo htmlspecialchars(date('d M Y', strtotime($tanggal))); ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5><i class="fas fa-clipboard-list me-2"></i>Detail Absensi</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php if ($absensi['keterangan'] == 'Hadir'): ?>
                            <span class="badge bg-success">Hadir</span>
                        <?php elseif ($absensi['keterangan'] == 'Sakit'): ?>
                            <span class="badge bg-warning">Sakit</span>
                        <?php elseif ($absensi['keterangan'] == 'Izin'): ?>
                            <span class="badge bg-info">Izin</span>
                        <?php elseif ($absensi['keterangan'] == 'Alpa'): ?>
                            <span class="badge bg-danger">Alpa</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>