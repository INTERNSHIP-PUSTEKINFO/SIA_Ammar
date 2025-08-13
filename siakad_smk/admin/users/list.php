<?php
// admin/users/list.php
$page_title = "Data Users";
$active_page = "users";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Users', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Query untuk mendapatkan data users
$users_list = [];
$stmt = $conn->prepare("SELECT id, nama, email, role, created_at, update_at FROM users ORDER BY role, nama");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $users_list[] = $row;
}
$stmt->close();
$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users me-2"></i>Data Users</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah User
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-table me-2"></i>Daftar Users</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users_list)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data users</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users_list as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['nama']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['role'] == 'admin'): ?>
                                        <span class="badge bg-primary">Admin</span>
                                    <?php elseif ($user['role'] == 'guru'): ?>
                                        <span class="badge bg-success">Guru</span>
                                    <?php elseif ($user['role'] == 'siswa'): ?>
                                        <span class="badge bg-info">Siswa</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($user['updated_at'] ?? $user['created_at']); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo urlencode($user['id']); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['role'] != 'admin' || count(array_filter($users_list, function($u) { return $u['role'] == 'admin'; })) > 1): ?>
                                    <a href="delete.php?id=<?php echo urlencode($user['id']); ?>" class="btn btn-sm btn-outline-danger btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini? Ini akan mempengaruhi data terkait.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
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