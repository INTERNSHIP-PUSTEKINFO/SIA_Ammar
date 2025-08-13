<?php
// admin/absensi/list.php
$page_title = "Data Absensi";
$active_page = "absensi";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Absensi', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Query untuk mendapatkan data absensi dengan nama siswa, mapel, dan tanggal
$absensi_list = [];
$stmt = $conn->prepare("SELECT a.id, s.nis, s.nama as nama_siswa, m.kode_mapel, m.nama_mapel, a.tanggal, a.keterangan 
                       FROM absensi a 
                       JOIN siswa s ON a.siswa_nis = s.nis 
                       JOIN mapel m ON a.mapel_id = m.id 
                       ORDER BY s.nama, m.nama_mapel, a.tanggal");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $absensi_list[] = $row;
}
$stmt->close();
$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-check me-2"></i>Data Absensi</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Absensi
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-table me-2"></i>Daftar Absensi</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Siswa</th>
                        <th>Mata Pelajaran</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($absensi_list)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data absensi</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($absensi_list as $absensi): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($absensi['id']); ?></td>
                                <td><?php echo htmlspecialchars($absensi['nis'] . ' - ' . $absensi['nama_siswa']); ?></td>
                                <td><?php echo htmlspecialchars($absensi['kode_mapel'] . ' - ' . $absensi['nama_mapel']); ?></td>
                                <td><?php echo htmlspecialchars(date('d M Y', strtotime($absensi['tanggal']))); ?></td>
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
                                <td>
                                    <a href="edit.php?id=<?php echo urlencode($absensi['id']); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo urlencode($absensi['id']); ?>" class="btn btn-sm btn-outline-danger btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data absensi ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>