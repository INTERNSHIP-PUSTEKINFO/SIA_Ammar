<?php
// guru/nilai/detail.php
$page_title = "Detail Nilai Siswa";
$active_page = "nilai";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Nilai Siswa', 'url' => 'list.php'],
    ['name' => 'Detail Nilai', 'active' => true]
];

include_once '../../includes/guru_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Ambil parameter dari URL
$nis = isset($_GET['nis']) ? trim($_GET['nis']) : '';
$mapel_id = isset($_GET['mapel_id']) ? (int)$_GET['mapel_id'] : 0;

if (empty($nis) || empty($mapel_id)) {
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

// Ambil data nilai siswa berdasarkan NIS dan mapel_id
$nilai = null;
$stmt = $conn->prepare("SELECT semester, tahun_ajaran, nilai_tugas, nilai_uts, nilai_uas, nilai_akhir 
                       FROM nilai 
                       WHERE siswa_nis = ? AND mapel_id = ?");
$stmt->bind_param("si", $nis, $mapel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $nilai = $result->fetch_assoc();
} else {
    header("Location: list.php?error=no_grades_found");
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
    <h2><i class="fas fa-chart-line me-2"></i>Detail Nilai Siswa</h2>
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
        </table>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5><i class="fas fa-chart-line me-2"></i>Detail Nilai</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Semester</th>
                    <th>Tahun Ajaran</th>
                    <th>Nilai Tugas</th>
                    <th>Nilai UTS</th>
                    <th>Nilai UAS</th>
                    <th>Nilai Akhir</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($nilai['semester']); ?></td>
                    <td><?php echo htmlspecialchars($nilai['tahun_ajaran']); ?></td>
                    <td><?php echo htmlspecialchars($nilai['nilai_tugas']); ?></td>
                    <td><?php echo htmlspecialchars($nilai['nilai_uts']); ?></td>
                    <td><?php echo htmlspecialchars($nilai['nilai_uas']); ?></td>
                    <td><?php echo htmlspecialchars($nilai['nilai_akhir']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>