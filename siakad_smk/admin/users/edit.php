<?php
// admin/users/edit.php
$page_title = "Edit User";
$active_page = "users";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Users', 'url' => 'list.php'],
    ['name' => 'Edit User', 'active' => true]
];

include_once '../../includes/admin_header.php';
include_once '../../config/database.php';

$conn = getConnection();

// Ambil ID dari parameter GET
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (empty($id)) {
    header("Location: list.php?error=invalid_id");
    exit();
}

// Ambil data user berdasarkan ID
$user = null;
$stmt = $conn->prepare("SELECT id, nama, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
} else {
    header("Location: list.php?error=not_found");
    exit();
}
$stmt->close();

// Cek apakah user yang diedit adalah admin terakhir
if ($user['role'] == 'admin') {
    $stmt = $conn->prepare("SELECT COUNT(*) as total_admin FROM users WHERE role = 'admin'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_admin = $row['total_admin'];
    $stmt->close();
    
    if ($total_admin <= 1) {
        $error = "Tidak bisa mengedit role admin terakhir. Minimal harus ada satu admin.";
        include_once '../../includes/admin_header.php';
        echo '<div class="container mt-4"><div class="alert alert-danger">' . htmlspecialchars($error) . '</div></div>';
        include_once '../../includes/footer.php';
        exit();
    }
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    
    // Validasi
    if (empty($nama) || empty($email) || empty($role)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Validasi email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid!";
        } else {
            // Cek apakah email sudah ada (kecuali untuk user ini sendiri)
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Email sudah terdaftar oleh user lain!";
            } else {
                $stmt->close();
                
                // Cek apakah user yang diedit adalah admin terakhir
                if ($role != 'admin' && $user['role'] == 'admin') {
                    $stmt = $conn->prepare("SELECT COUNT(*) as total_admin FROM users WHERE role = 'admin' AND id != ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $total_admin = $row['total_admin'];
                    $stmt->close();
                    
                    if ($total_admin < 1) {
                        $error = "Tidak bisa mengubah role admin terakhir. Minimal harus ada satu admin.";
                    }
                }
                
                if (empty($error)) {
                    // Proses update data
                    $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, role = ?, update_at = NOW() WHERE id = ?");
                    $stmt->bind_param("sssi", $nama, $email, $role, $id);
                    
                    if ($stmt->execute()) {
                        $message = "Data user berhasil diupdate!";
                        
                        // Refresh data user setelah update
                        $stmt2 = $conn->prepare("SELECT id, nama, email, role FROM users WHERE id = ?");
                        $stmt2->bind_param("i", $id);
                        $stmt2->execute();
                        $result2 = $stmt2->get_result();
                        $user = $result2->fetch_assoc();
                        $stmt2->close();
                    } else {
                        $error = "Gagal mengupdate data user: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-edit me-2"></i>Edit User</h2>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-user me-2"></i>Form Edit Data User</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
            
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Lengkap *</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" placeholder="Nama lengkap" required>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="email@example.com" required>
            </div>
            
            <div class="mb-3">
                <label for="role" class="form-label">Role *</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="">Pilih Role</option>
                    <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="guru" <?php echo ($user['role'] == 'guru') ? 'selected' : ''; ?>>Guru</option>
                    <option value="siswa" <?php echo ($user['role'] == 'siswa') ? 'selected' : ''; ?>>Siswa</option>
                </select>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Update
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>