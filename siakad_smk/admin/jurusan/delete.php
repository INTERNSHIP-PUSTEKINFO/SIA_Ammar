<?php
// admin/jurusan/delete.php
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

// Cek apakah jurusan dengan ID tersebut ada
$stmt = $conn->prepare("SELECT id FROM jurusan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $stmt->close();
    
    // Mulai transaksi
    $conn->begin_transaction();
    
    try {
        // Hapus data terkait jurusan dari tabel mapel (set jurusan_id ke NULL)
        $stmt = $conn->prepare("UPDATE mapel SET jurusan_id = NULL WHERE jurusan_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Hapus data terkait jurusan dari tabel kelas (set jurusan_id ke NULL)
        $stmt = $conn->prepare("UPDATE kelas SET jurusan_id = NULL WHERE jurusan_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Hapus data terkait jurusan dari tabel guru (set mapel_id ke NULL jika ada relasi melalui mapel)
        // Note: Ini kompleks, kita hanya set mapel_id ke NULL jika mapel tersebut milik jurusan ini
        // Tapi karena relasi langsung ke mapel, kita lewati dulu
        
        // Hapus data terkait jurusan dari tabel siswa (set jurusan_id ke NULL)
        $stmt = $conn->prepare("UPDATE siswa SET jurusan_id = NULL WHERE jurusan_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Hapus data jurusan dari tabel jurusan
        $stmt = $conn->prepare("DELETE FROM jurusan WHERE id = ?");
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
        error_log("Error deleting jurusan: " . $e->getMessage()); // Log error untuk debugging
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