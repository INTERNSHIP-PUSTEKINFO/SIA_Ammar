<?php
// siswa/nilai/view.php
$page_title = "Lihat Nilai";
$active_page = "nilai";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Lihat Nilai', 'active' => true]
];

include_once '../../includes/siswa_header.php';
include_once '../../config/database.php';

$conn = getConnection();
$nis = $_SESSION['nis'];

// Ambil daftar nilai siswa
$nilai_list = [];
$stmt = $conn->prepare("SELECT n.semester, n.tahun_ajaran, n.nilai_tugas, n.nilai_uts, n.nilai_uas, n.nilai_akhir, 
                       m.kode_mapel, m.nama_mapel
                       FROM nilai n
                       JOIN mapel m ON n.mapel_id = m.id
                       WHERE n.siswa_nis = ?
                       ORDER BY n.tahun_ajaran DESC, n.semester, m.nama_mapel");
$stmt->bind_param("s", $nis);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $nilai_list[] = $row;
}
$stmt->close();

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-chart-line me-2"></i>Lihat Nilai</h2>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-table me-2"></i>Daftar Nilai</h5>
    </div>
    <div class="card-body">
        <?php if (empty($nilai_list)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Belum ada data nilai.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Mata Pelajaran</th>
                            <th>Semester</th>
                            <th>Tahun Ajaran</th>
                            <th>Nilai Tugas</th>
                            <th>Nilai UTS</th>
                            <th>Nilai UAS</th>
                            <th>Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($nilai_list as $nilai): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($nilai['kode_mapel'] . ' - ' . $nilai['nama_mapel']); ?></td>
                                <td><?php echo htmlspecialchars($nilai['semester']); ?></td>
                                <td><?php echo htmlspecialchars($nilai['tahun_ajaran']); ?></td>
                                <td><?php echo htmlspecialchars($nilai['nilai_tugas']); ?></td>
                                <td><?php echo htmlspecialchars($nilai['nilai_uts']); ?></td>
                                <td><?php echo htmlspecialchars($nilai['nilai_uas']); ?></td>
                                <td><?php echo htmlspecialchars($nilai['nilai_akhir']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>