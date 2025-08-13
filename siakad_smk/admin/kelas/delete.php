<?php
// admin/kelas/delete.php
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

// Cek apakah kelas dengan ID tersebut ada
$stmt = $conn->prepare("SELECT id FROM kelas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $stmt->close();
    
    // Mulai transaksi
    $conn->begin_transaction();
    
    try {
        // Hapus data terkait kelas dari tabel jadwal_pelajaran
        $stmt = $conn->prepare("DELETE FROM jadwal_pelajaran WHERE kelas_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Hapus data terkait kelas dari tabel siswa (set kelas_id ke NULL)
        $stmt = $conn->prepare("UPDATE siswa SET kelas_id = NULL WHERE kelas_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Hapus data kelas dari tabel kelas
        $stmt = $conn->prepare("DELETE FROM kelas WHERE id = ?");
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
        error_log("Error deleting kelas: " . $e->getMessage()); // Log error untuk debugging
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