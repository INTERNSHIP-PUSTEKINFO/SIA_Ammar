<?php
// index.php - Halaman utama project
session_start();

// Fungsi untuk memeriksa apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Fungsi untuk mendapatkan role user
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

// Jika user sudah login, arahkan ke dashboard sesuai role
if (isLoggedIn()) {
    switch (getUserRole()) {
        case 'admin':
            header("Location: admin/index.php");
            exit();
        case 'guru':
            header("Location: guru/index.php");
            exit();
        case 'siswa':
            header("Location: siswa/index.php");
            exit();
        default:
            // Jika role tidak dikenali, logout dan arahkan ke login
            session_destroy();
            header("Location: auth/login.php");
            exit();
    }
} else {
    // Jika user belum login, arahkan ke halaman login
    header("Location: auth/login.php");
    exit();
}
?>