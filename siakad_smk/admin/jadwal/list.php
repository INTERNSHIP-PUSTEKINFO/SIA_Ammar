<?php
// admin/jadwal/list.php
$page_title = "Data Jadwal Pelajaran";
$active_page = "jadwal";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Jadwal Pelajaran', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Query untuk mendapatkan data jadwal dengan nama kelas, mapel, dan guru
$jadwal_list = [];
// Perhatikan JOIN ke guru menggunakan nip
$stmt = $conn->prepare("SELECT jp.id, k.nama_kelas, m.kode_mapel, m.nama_mapel, g.nip, u.nama as nama_guru, jp.hari, jp.jam_ke
                        FROM jadwal_pelajaran jp
                        JOIN kelas k ON jp.kelas_id = k.id
                        JOIN mapel m ON jp.mapel_id = m.id
                        JOIN guru g ON jp.guru_id = g.nip
                        JOIN users u ON g.user_id = u.id
                        ORDER BY k.nama_kelas, jp.hari, jp.jam_ke");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $jadwal_list[] = $row;
}
$stmt->close();
$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-alt me-2"></i>Data Jadwal Pelajaran</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Jadwal
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-table me-2"></i>Daftar Jadwal Pelajaran</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru</th>
                        <th>Hari</th>
                        <th>Jam Ke</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jadwal_list)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data jadwal pelajaran</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jadwal_list as $jadwal): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($jadwal['id']); ?></td>
                                <td><?php echo htmlspecialchars($jadwal['nama_kelas']); ?></td>
                                <td><?php echo htmlspecialchars($jadwal['kode_mapel'] . ' - ' . $jadwal['nama_mapel']); ?></td>
                                <td><?php echo htmlspecialchars($jadwal['nama_guru'] . ' (' . $jadwal['nip'] . ')'); ?></td>
                                <td><?php echo htmlspecialchars($jadwal['hari']); ?></td>
                                <td><?php echo htmlspecialchars($jadwal['jam_ke']); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo urlencode($jadwal['id']); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo urlencode($jadwal['id']); ?>" class="btn btn-sm btn-outline-danger btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">
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