<?php
// admin/siswa/list.php
$page_title = "Data Siswa";
$active_page = "siswa";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Siswa', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Query untuk mendapatkan data siswa
$siswa_list = [];
$stmt = $conn->prepare("SELECT s.nis, s.tempat_lahir, s.tanggal_lahir, s.jenis_kelamin, s.alamat, s.tahun_masuk, s.status, u.nama, u.email, j.nama_jurusan, k.nama_kelas 
                        FROM siswa s 
                        JOIN users u ON s.user_id = u.id 
                        LEFT JOIN jurusan j ON s.jurusan_id = j.id 
                        LEFT JOIN kelas k ON s.kelas_id = k.id 
                        ORDER BY s.nis");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $siswa_list[] = $row;
}
$stmt->close();
$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users me-2"></i>Data Siswa</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Siswa
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-table me-2"></i>Daftar Siswa</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($siswa_list)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data siswa</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($siswa_list as $siswa): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($siswa['nis']); ?></td>
                                <td><?php echo htmlspecialchars($siswa['nama']); ?></td>
                                <td><?php echo htmlspecialchars($siswa['email']); ?></td>
                                <td><?php echo htmlspecialchars($siswa['nama_kelas'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($siswa['nama_jurusan'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($siswa['status'] == 'aktif'): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php elseif ($siswa['status'] == 'lulus'): ?>
                                        <span class="badge bg-primary">Lulus</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Keluar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit.php?nis=<?php echo urlencode($siswa['nis']); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?nis=<?php echo urlencode($siswa['nis']); ?>" class="btn btn-sm btn-outline-danger btn-delete">
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