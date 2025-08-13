<?php
// includes/auth_check.php
// Mulai session jika belum
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk memeriksa apakah user sudah login
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        // Jika belum login, redirect ke halaman login
        header("Location: ../auth/login.php");
        exit();
    }
}

// Fungsi untuk memeriksa role user
function checkRole($allowed_roles) {
    // Pastikan user sudah login
    checkLogin();

    // Pastikan parameter adalah array
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }

    // Periksa apakah role user ada dalam daftar role yang diizinkan
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        // Jika role tidak diizinkan, redirect ke halaman yang sesuai
        switch ($_SESSION['role']) {
            case 'admin':
                header("Location: ../admin/index.php");
                break;
            case 'guru':
                header("Location: ../guru/index.php");
                break;
            case 'siswa':
                header("Location: ../siswa/index.php");
                break;
            default:
                header("Location: ../auth/login.php");
                break;
        }
        exit();
    }
}

// Fungsi untuk mendapatkan nama user
// Fungsi untuk mendapatkan nama user
// Fungsi untuk mendapatkan nama user
function getUserName() {
    return isset($_SESSION['nama']) ? $_SESSION['nama'] : 'User';
}
// Fungsi untuk mendapatkan role user
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}
?>