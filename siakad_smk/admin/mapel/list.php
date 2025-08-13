<?php
// admin/mapel/list.php
$page_title = "Data Mata Pelajaran";
$active_page = "mapel";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Mata Pelajaran', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Query untuk mendapatkan data mata pelajaran dengan nama jurusan
$mapel_list = [];
$stmt = $conn->prepare("SELECT m.id, m.kode_mapel, m.nama_mapel, j.nama_jurusan 
                        FROM mapel m 
                        LEFT JOIN jurusan j ON m.jurusan_id = j.id 
                        ORDER BY m.kode_mapel");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $mapel_list[] = $row;
}
$stmt->close();
$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-book me-2"></i>Data Mata Pelajaran</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Mata Pelajaran
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-table me-2"></i>Daftar Mata Pelajaran</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kode Mapel</th>
                        <th>Nama Mapel</th>
                        <th>Jurusan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mapel_list)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data mata pelajaran</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($mapel_list as $mapel): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($mapel['id']); ?></td>
                                <td><?php echo htmlspecialchars($mapel['kode_mapel']); ?></td>
                                <td><?php echo htmlspecialchars($mapel['nama_mapel']); ?></td>
                                <td><?php echo htmlspecialchars($mapel['nama_jurusan'] ?? '-'); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo urlencode($mapel['id']); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo urlencode($mapel['id']); ?>" class="btn btn-sm btn-outline-danger btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus mata pelajaran ini? Ini akan mempengaruhi data terkait.')">
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