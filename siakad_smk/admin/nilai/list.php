<?php
// admin/nilai/list.php
$page_title = "Data Nilai";
$active_page = "nilai";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Nilai', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Query untuk mendapatkan data nilai dengan nama siswa, mapel, semester, tahun ajaran
$nilai_list = [];
$stmt = $conn->prepare("SELECT n.id, s.nis, s.nama as nama_siswa, m.kode_mapel, m.nama_mapel, n.semester, n.tahun_ajaran, 
                       n.nilai_tugas, n.nilai_uts, n.nilai_uas, n.nilai_akhir 
                       FROM nilai n 
                       JOIN siswa s ON n.siswa_nis = s.nis 
                       JOIN mapel m ON n.mapel_id = m.id 
                       ORDER BY s.nama, m.nama_mapel");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $nilai_list[] = $row;
}
$stmt->close();
$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-chart-line me-2"></i>Data Nilai</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Nilai
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-table me-2"></i>Daftar Nilai</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Siswa</th>
                        <th>Mata Pelajaran</th>
                        <th>Semester</th>
                        <th>Tahun Ajaran</th>
                        <th>Nilai Tugas</th>
                        <th>Nilai UTS</th>
                        <th>Nilai UAS</th>
                        <th>Nilai Akhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($nilai_list)): ?>
                        <tr>
                            <td colspan="10" class="text-center">Tidak ada data nilai</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($nilai_list as $nilai): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($nilai['id']); ?></td>
                                <td><?php echo htmlspecialchars($nilai['nis'] . ' - ' . $nilai['nama_siswa']); ?></td>
                                <td><?php echo htmlspecialchars($nilai['kode_mapel'] . ' - ' . $nilai['nama_mapel']); ?></td>
                                <td><?php echo htmlspecialchars($nilai['semester']); ?></td>
                                <td><?php echo htmlspecialchars($nilai['tahun_ajaran']); ?></td>
                                <td><?php echo htmlspecialchars($nilai['nilai_tugas']); ?></td>
                                <td><?php echo htmlspecialchars($nilai['nilai_uts']); ?></td>
                                <td><?php echo htmlspecialchars($nilai['nilai_uas']); ?></td>
                                <td><?php echo htmlspecialchars($nilai['nilai_akhir']); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo urlencode($nilai['id']); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo urlencode($nilai['id']); ?>" class="btn btn-sm btn-outline-danger btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data nilai ini?')">
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