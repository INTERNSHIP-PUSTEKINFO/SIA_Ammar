<?php
// siswa/absensi/view.php
$page_title = "Lihat Absensi";
$active_page = "absensi";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Lihat Absensi', 'active' => true]
];

include_once '../../includes/siswa_header.php';
include_once '../../config/database.php';

$conn = getConnection();
$nis = $_SESSION['nis'];

// Ambil daftar absensi siswa
$absensi_list = [];
$stmt = $conn->prepare("SELECT a.tanggal, a.keterangan, m.kode_mapel, m.nama_mapel
                       FROM absensi a
                       JOIN mapel m ON a.mapel_id = m.id
                       WHERE a.siswa_nis = ?
                       ORDER BY a.tanggal DESC, m.nama_mapel");
$stmt->bind_param("s", $nis);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $absensi_list[] = $row;
}
$stmt->close();

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-clipboard-list me-2"></i>Lihat Absensi</h2>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-table me-2"></i>Daftar Absensi</h5>
    </div>
    <div class="card-body">
        <?php if (empty($absensi_list)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Belum ada data absensi.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Mata Pelajaran</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($absensi_list as $absensi): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('d M Y', strtotime($absensi['tanggal']))); ?></td>
                                <td><?php echo htmlspecialchars($absensi['kode_mapel'] . ' - ' . $absensi['nama_mapel']); ?></td>
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
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>