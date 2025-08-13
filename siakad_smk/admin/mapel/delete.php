<?php
// admin/mapel/delete.php
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

// Cek apakah mata pelajaran dengan ID tersebut ada
$stmt = $conn->prepare("SELECT id FROM mapel WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $stmt->close();
    
    // Mulai transaksi
    $conn->begin_transaction();
    
    try {
        // Hapus data terkait mapel dari tabel jadwal_pelajaran
        $stmt = $conn->prepare("DELETE FROM jadwal_pelajaran WHERE mapel_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Hapus data terkait mapel dari tabel nilai
        $stmt = $conn->prepare("DELETE FROM nilai WHERE mapel_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Hapus data terkait mapel dari tabel absensi
        $stmt = $conn->prepare("DELETE FROM absensi WHERE mapel_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Hapus data terkait mapel dari tabel guru (set mapel_id ke NULL)
        $stmt = $conn->prepare("UPDATE guru SET mapel_id = NULL WHERE mapel_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Hapus data mata pelajaran dari tabel mapel
        $stmt = $conn->prepare("DELETE FROM mapel WHERE id = ?");
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
        error_log("Error deleting mapel: " . $e->getMessage()); // Log error untuk debugging
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