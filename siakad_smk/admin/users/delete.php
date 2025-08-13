<?php
// admin/users/delete.php
session_start();
include_once '../../includes/auth_check.php';
checkRole('admin'); // Hanya admin yang bisa menghapus

include_once '../../config/database.php';

// Ambil ID dari parameter GET
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (empty($id)) {
    header("Location: list.php?error=invalid_id");
    exit();
}

$conn = getConnection();

// Cek apakah user dengan ID tersebut ada
$stmt = $conn->prepare("SELECT id, role FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Cek apakah user yang dihapus adalah admin terakhir
    if ($user['role'] == 'admin') {
        $stmt = $conn->prepare("SELECT COUNT(*) as total_admin FROM users WHERE role = 'admin' AND id != ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total_admin = $row['total_admin'];
        $stmt->close();
        
        if ($total_admin < 1) {
            header("Location: list.php?error=cannot_delete_last_admin");
            exit();
        }
    }
    
    // Mulai transaksi
    $conn->begin_transaction();
    
    try {
        // Hapus data terkait user dari tabel siswa (jika ada)
        $stmt = $conn->prepare("DELETE FROM siswa WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Hapus data terkait user dari tabel guru (jika ada)
        $stmt = $conn->prepare("DELETE FROM guru WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Hapus data user dari tabel users
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaksi
        $conn->commit();
        
        header("Location: list.php?success=deleted");
        exit();
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        $conn->rollback();
        error_log("Error deleting user: " . $e->getMessage()); // Log error untuk debugging
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