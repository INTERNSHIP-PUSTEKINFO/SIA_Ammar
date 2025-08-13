<?php
// includes/header.php
// Pastikan session sudah dimulai

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk memeriksa apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Fungsi untuk mendapatkan nama user
// function getUserName() {
//     return isset($_SESSION['nama']) ? $_SESSION['nama'] : 'User';
// }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIAKAD SMK - <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Sistem Informasi Akademik'; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>