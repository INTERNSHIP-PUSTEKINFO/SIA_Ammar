<?php
// admin/kelas/list.php
$page_title = "Data Kelas";
$active_page = "kelas";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Kelas', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Query untuk mendapatkan data kelas dengan nama jurusan
$kelas_list = [];
$stmt = $conn->prepare("SELECT k.id, k.nama_kelas, k.tingkat, j.nama_jurusan 
                        FROM kelas k 
                        LEFT JOIN jurusan j ON k.jurusan_id = j.id 
                        ORDER BY k.tingkat, k.nama_kelas");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $kelas_list[] = $row;
}
$stmt->close();
$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-school me-2"></i>Data Kelas</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Kelas
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-table me-2"></i>Daftar Kelas</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kelas</th>
                        <th>Tingkat</th>
                        <th>Jurusan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kelas_list)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data kelas</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($kelas_list as $kelas): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($kelas['id']); ?></td>
                                <td><?php echo htmlspecialchars($kelas['nama_kelas']); ?></td>
                                <td><?php echo htmlspecialchars($kelas['tingkat']); ?></td>
                                <td><?php echo htmlspecialchars($kelas['nama_jurusan'] ?? '-'); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo urlencode($kelas['id']); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo urlencode($kelas['id']); ?>" class="btn btn-sm btn-outline-danger btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus kelas ini? Ini akan menghapus data terkait.')">
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