<?php
// admin/guru/delete.php
session_start();
include_once '../../includes/auth_check.php';
checkRole('admin'); // Hanya admin yang bisa menghapus

include_once '../../config/database.php';

// Ambil NIP dari parameter GET
$nip = isset($_GET['nip']) ? trim($_GET['nip']) : '';

if (empty($nip)) {
    header("Location: list.php?error=invalid_nip");
    exit();
}

$conn = getConnection();

// Cek apakah guru dengan NIP tersebut ada
$stmt = $conn->prepare("SELECT g.nip, u.id as user_id FROM guru g JOIN users u ON g.user_id = u.id WHERE g.nip = ?");
$stmt->bind_param("s", $nip);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $guru = $result->fetch_assoc();
    $user_id = $guru['user_id'];
    $stmt->close();
    
    // Mulai transaksi
    $conn->begin_transaction();
    
    try {
        // Hapus data terkait guru dari tabel jadwal_pelajaran
        // Catatan: Kita hapus jadwal yang diampu guru ini.
        $stmt = $conn->prepare("DELETE FROM jadwal_pelajaran WHERE guru_id = ?");
        $stmt->bind_param("s", $nip); // guru_id di jadwal_pelajaran merujuk ke nip di guru
        $stmt->execute();
        $stmt->close();
        
        // Hapus data guru dari tabel guru
        $stmt = $conn->prepare("DELETE FROM guru WHERE nip = ?");
        $stmt->bind_param("s", $nip);
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
        error_log("Error deleting guru: " . $e->getMessage()); // Log error untuk debugging
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