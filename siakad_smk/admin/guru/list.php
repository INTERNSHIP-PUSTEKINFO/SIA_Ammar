<?php
// admin/guru/list.php
$page_title = "Data Guru";
$active_page = "guru";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Guru', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Query untuk mendapatkan data guru
$guru_list = [];
$stmt = $conn->prepare("SELECT g.nip, g.tempat_lahir, g.tanggal_lahir, g.jenis_kelamin, g.alamat, g.mapel_id, u.nama, u.email, m.kode_mapel, m.nama_mapel 
                        FROM guru g 
                        JOIN users u ON g.user_id = u.id 
                        LEFT JOIN mapel m ON g.mapel_id = m.id 
                        ORDER BY g.nip");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $guru_list[] = $row;
}
$stmt->close();
$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-chalkboard-teacher me-2"></i>Data Guru</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Guru
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-table me-2"></i>Daftar Guru</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Mata Pelajaran</th>
                        <th>Jenis Kelamin</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($guru_list)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data guru</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($guru_list as $guru): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($guru['nip']); ?></td>
                                <td><?php echo htmlspecialchars($guru['nama']); ?></td>
                                <td><?php echo htmlspecialchars($guru['email']); ?></td>
                                <td><?php echo htmlspecialchars($guru['kode_mapel'] . ' - ' . $guru['nama_mapel'] ?? '-'); ?></td>
                                <td><?php echo ($guru['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></td>
                                <td>
                                    <a href="edit.php?nip=<?php echo urlencode($guru['nip']); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?nip=<?php echo urlencode($guru['nip']); ?>" class="btn btn-sm btn-outline-danger btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus guru ini?')">
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