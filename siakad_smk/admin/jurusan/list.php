<?php
// admin/jurusan/list.php
$page_title = "Data Jurusan";
$active_page = "jurusan";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Jurusan', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Query untuk mendapatkan data jurusan
$jurusan_list = [];
$stmt = $conn->prepare("SELECT id, kode_jurusan, nama_jurusan FROM jurusan ORDER BY kode_jurusan");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $jurusan_list[] = $row;
}
$stmt->close();
$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-graduation-cap me-2"></i>Data Jurusan</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Jurusan
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-table me-2"></i>Daftar Jurusan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kode Jurusan</th>
                        <th>Nama Jurusan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jurusan_list)): ?>
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data jurusan</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jurusan_list as $jurusan): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($jurusan['id']); ?></td>
                                <td><?php echo htmlspecialchars($jurusan['kode_jurusan']); ?></td>
                                <td><?php echo htmlspecialchars($jurusan['nama_jurusan']); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo urlencode($jurusan['id']); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo urlencode($jurusan['id']); ?>" class="btn btn-sm btn-outline-danger btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus jurusan ini? Ini akan mempengaruhi data terkait.')">
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