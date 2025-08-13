<?php
// auth/login.php
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
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!empty($email) && !empty($password) && !empty($role)) {
        $conn = getConnection();

        // Cari user (sesuaikan dengan nama kolom yang benar)
        $stmt = $conn->prepare("SELECT id, nama, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Cek role
                if ($user['role'] == $role) {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    // Untuk siswa, ambil NIS
                    if ($role == 'siswa') {
                        $stmt2 = $conn->prepare("SELECT nis FROM siswa WHERE user_id = ?");
                        $stmt2->bind_param("i", $user['id']);
                        $stmt2->execute();
                        $result2 = $stmt2->get_result();
                        if ($result2->num_rows == 1) {
                            $siswa = $result2->fetch_assoc();
                            $_SESSION['nis'] = $siswa['nis'];
                        }
                        $stmt2->close();
                    }

                    // Untuk guru, ambil NIP
                    if ($role == 'guru') {
                        $stmt3 = $conn->prepare("SELECT nip FROM guru WHERE user_id = ?");
                        $stmt3->bind_param("i", $user['id']);
                        $stmt3->execute();
                        $result3 = $stmt3->get_result();
                        if ($result3->num_rows == 1) {
                            $guru = $result3->fetch_assoc();
                            $_SESSION['nip'] = $guru['nip'];
                        }
                        $stmt3->close();
                    }

                    // Redirect ke dashboard
                    switch ($role) {
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
                } else {
                    $error_message = "Role tidak sesuai dengan akun Anda.";
                }
            } else {
                $error_message = "Email atau password salah.";
            }
        } else {
            $error_message = "Email atau password salah.";
        }

        $stmt->close();
        $conn->close();
    } else {
        $error_message = "Semua field harus diisi.";
    }
}

$page_title = "Login";
include_once '../includes/public_header.php';
?>

<div class="auth-container">
    <div class="card auth-card">
        <div class="card-header auth-header">
            <h3><i class="fas fa-graduation-cap me-2"></i>SIAKAD SMK</h3>
            <p class="mb-0">Sistem Informasi Akademik</p>
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

            <form method="POST" action="login.php">
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
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                    </div>
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
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
                <div class="text-center">
                    <small>Belum punya akun? <a href="register.php" class="text-decoration-none">Register</a></small>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../includes/public_footer.php'; ?>