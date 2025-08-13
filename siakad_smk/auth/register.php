<?php
// auth/register.php
session_start();

// Jika user sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: ../admin/index.php");
            exit();
        case 'guru':
            header("Location: ../guru/index.php");
            exit();
        case 'siswa':
            header("Location: ../siswa/index.php");
            exit();
    }
}

include_once '../config/database.php';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validasi input
    if (empty($nama) || empty($email) || empty($password) || empty($role)) {
        $error_message = "Semua field harus diisi.";
    } else {
        // Validasi email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Format email tidak valid.";
        } else {
            $conn = getConnection();

            // Cek email duplikat
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error_message = "Email sudah terdaftar.";
            } else {
                // Hash password dan simpan
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                // Sesuaikan dengan struktur tabel: created_at -> create_ad
                $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role, create_ad) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssss", $nama, $email, $hashed_password, $role);

                if ($stmt->execute()) {
                    // Redirect langsung ke login dengan pesan sukses
                    header("Location: login.php?registered=success");
                    exit();
                } else {
                    $error_message = "Registrasi gagal. Silakan coba lagi. Error: " . $stmt->error;
                }
            }
            $stmt->close();
            $conn->close();
        }
    }
}

$page_title = "Register";
include_once '../includes/public_header.php';
?>

<div class="auth-container">
    <div class="card auth-card">
        <div class="card-header auth-header">
            <h3><i class="fas fa-user-plus me-2"></i>Register</h3>
            <p class="mb-0">Buat Akun Baru</p>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['registered']) && $_GET['registered'] == 'success'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Registrasi berhasil! Silakan login.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap" value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required minlength="6">
                    </div>
                    <div class="form-text">Password minimal 6 karakter.</div>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="">Pilih Role</option>
                        <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="guru" <?php echo (isset($_POST['role']) && $_POST['role'] == 'guru') ? 'selected' : ''; ?>>Guru</option>
                        <option value="siswa" <?php echo (isset($_POST['role']) && $_POST['role'] == 'siswa') ? 'selected' : ''; ?>>Siswa</option>
                    </select>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
                <div class="text-center">
                    <small>Sudah punya akun? <a href="login.php" class="text-decoration-none">Login</a></small>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../includes/public_footer.php'; ?>