<?php
// admin/siswa/delete.php
session_start();
include_once '../../includes/auth_check.php';
checkRole('admin'); // Hanya admin yang bisa menghapus

include_once '../../config/database.php';

// Ambil NIS dari parameter GET
$nis = isset($_GET['nis']) ? trim($_GET['nis']) : '';

if (empty($nis)) {
    header("Location: list.php?error=invalid_nis");
    exit();
}

$conn = getConnection();

// Cek apakah siswa dengan NIS tersebut ada
$stmt = $conn->prepare("SELECT s.nis, u.id as user_id FROM siswa s JOIN users u ON s.user_id = u.id WHERE s.nis = ?");
$stmt->bind_param("s", $nis);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $siswa = $result->fetch_assoc();
    $user_id = $siswa['user_id'];
    $stmt->close();
    
    // Mulai transaksi
    $conn->begin_transaction();
    
    try {
        // Hapus data terkait siswa dari tabel absensi
        $stmt = $conn->prepare("DELETE FROM absensi WHERE siswa_nis = ?");
        $stmt->bind_param("s", $nis);
        $stmt->execute();
        $stmt->close();
        
        // Hapus data terkait siswa dari tabel nilai
        $stmt = $conn->prepare("DELETE FROM nilai WHERE siswa_nis = ?");
        $stmt->bind_param("s", $nis);
        $stmt->execute();
        $stmt->close();
        
        // Hapus data siswa dari tabel siswa
        $stmt = $conn->prepare("DELETE FROM siswa WHERE nis = ?");
        $stmt->bind_param("s", $nis);
        $stmt->execute();
        $stmt->close();
        
        // Hapus data user dari tabel users
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaksi
        $conn->commit();
        
        header("Location: list.php?success=deleted");
        exit();
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        $conn->rollback();
        header("Location: list.php?error=delete_failed");
        exit();
    }
} else {
    $stmt->close();
    header("Location: list.php?error=not_found");
    exit();
}

$conn->close();
?>  