<?php
// functions/admin_functions.php

include_once 'config/database.php';

/**
 * Fungsi untuk mendapatkan semua data siswa
 * @return array
 */
function getAllStudents() {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT s.nis, s.tempat_lahir, s.tanggal_lahir, s.jenis_kelamin, s.alamat, s.tahun_masuk, s.status, 
                           u.nama, u.email, j.nama_jurusan, k.nama_kelas 
                           FROM siswa s 
                           JOIN users u ON s.user_id = u.id 
                           LEFT JOIN jurusan j ON s.jurusan_id = j.id 
                           LEFT JOIN kelas k ON s.kelas_id = k.id 
                           ORDER BY s.nis");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $students;
}

/**
 * Fungsi untuk mendapatkan data siswa berdasarkan NIS
 * @param string $nis
 * @return array|bool
 */
function getStudentByNis($nis) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT s.nis, s.tempat_lahir, s.tanggal_lahir, s.jenis_kelamin, s.alamat, s.tahun_masuk, s.status, 
                           s.jurusan_id, s.kelas_id, u.nama, u.email, u.id as user_id
                           FROM siswa s 
                           JOIN users u ON s.user_id = u.id 
                           WHERE s.nis = ?");
    $stmt->bind_param("s", $nis);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $student = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $student;
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

/**
 * Fungsi untuk menambahkan siswa baru
 * @param array $data
 * @return bool
 */
function createStudent($data) {
    $conn = getConnection();
    
    $conn->begin_transaction();
    
    try {
        // Simpan ke tabel users
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $role = 'siswa';
        
        $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $data['nama'], $data['email'], $hashed_password, $role);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan data user");
        }
        
        $user_id = $conn->insert_id;
        $stmt->close();
        
        // Simpan ke tabel siswa
        $stmt = $conn->prepare("INSERT INTO siswa (nis, user_id, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, jurusan_id, kelas_id, tahun_masuk, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisssssiss", 
            $data['nis'], $user_id, $data['tempat_lahir'], $data['tanggal_lahir'], 
            $data['jenis_kelamin'], $data['alamat'], $data['jurusan_id'], $data['kelas_id'], 
            $data['tahun_masuk'], $data['status']);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan data siswa");
        }
        
        $stmt->close();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error creating student: " . $e->getMessage());
        return false;
    }
    
    $conn->close();
}

/**
 * Fungsi untuk memperbarui data siswa
 * @param string $nis
 * @param array $data
 * @return bool
 */
function updateStudent($nis, $data) {
    $conn = getConnection();
    
    $conn->begin_transaction();
    
    try {
        // Update tabel users
        $stmt = $conn->prepare("UPDATE users u JOIN siswa s ON u.id = s.user_id SET u.nama = ?, u.email = ?, u.updated_at = NOW() WHERE s.nis = ?");
        $stmt->bind_param("sss", $data['nama'], $data['email'], $nis);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal mengupdate data user");
        }
        $stmt->close();
        
        // Update tabel siswa
        $stmt = $conn->prepare("UPDATE siswa SET tempat_lahir = ?, tanggal_lahir = ?, jenis_kelamin = ?, alamat = ?, jurusan_id = ?, kelas_id = ?, tahun_masuk = ?, status = ? WHERE nis = ?");
        $stmt->bind_param("ssssssiss", 
            $data['tempat_lahir'], $data['tanggal_lahir'], $data['jenis_kelamin'], 
            $data['alamat'], $data['jurusan_id'], $data['kelas_id'], 
            $data['tahun_masuk'], $data['status'], $nis);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal mengupdate data siswa");
        }
        $stmt->close();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating student: " . $e->getMessage());
        return false;
    }
    
    $conn->close();
}

/**
 * Fungsi untuk menghapus siswa
 * @param string $nis
 * @return bool
 */
function deleteStudent($nis) {
    $conn = getConnection();
    
    $conn->begin_transaction();
    
    try {
        // Dapatkan user_id
        $stmt = $conn->prepare("SELECT user_id FROM siswa WHERE nis = ?");
        $stmt->bind_param("s", $nis);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $siswa = $result->fetch_assoc();
            $user_id = $siswa['user_id'];
        } else {
            throw new Exception("Siswa tidak ditemukan");
        }
        $stmt->close();
        
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
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error deleting student: " . $e->getMessage());
        return false;
    }
    
    $conn->close();
}

/**
 * Fungsi untuk mendapatkan semua data guru
 * @return array
 */
function getAllTeachers() {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT g.nip, g.tempat_lahir, g.tanggal_lahir, g.jenis_kelamin, g.alamat, g.mapel_id, 
                           u.nama, u.email, m.nama_mapel 
                           FROM guru g 
                           JOIN users u ON g.user_id = u.id 
                           LEFT JOIN mapel m ON g.mapel_id = m.id 
                           ORDER BY g.nip");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $teachers = [];
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $teachers;
}

/**
 * Fungsi untuk mendapatkan data guru berdasarkan NIP
 * @param string $nip
 * @return array|bool
 */
function getTeacherByNip($nip) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT g.nip, g.tempat_lahir, g.tanggal_lahir, g.jenis_kelamin, g.alamat, g.mapel_id, 
                           u.nama, u.email, u.id as user_id
                           FROM guru g 
                           JOIN users u ON g.user_id = u.id 
                           WHERE g.nip = ?");
    $stmt->bind_param("s", $nip);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $teacher = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $teacher;
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

/**
 * Fungsi untuk menambahkan guru baru
 * @param array $data
 * @return bool
 */
function createTeacher($data) {
    $conn = getConnection();
    
    $conn->begin_transaction();
    
    try {
        // Simpan ke tabel users
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $role = 'guru';
        
        $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $data['nama'], $data['email'], $hashed_password, $role);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan data user");
        }
        
        $user_id = $conn->insert_id;
        $stmt->close();
        
        // Simpan ke tabel guru
        $stmt = $conn->prepare("INSERT INTO guru (nip, user_id, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, mapel_id) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisssss", 
            $data['nip'], $user_id, $data['tempat_lahir'], $data['tanggal_lahir'], 
            $data['jenis_kelamin'], $data['alamat'], $data['mapel_id']);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan data guru");
        }
        
        $stmt->close();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error creating teacher: " . $e->getMessage());
        return false;
    }
    
    $conn->close();
}

/**
 * Fungsi untuk memperbarui data guru
 * @param string $nip
 * @param array $data
 * @return bool
 */
function updateTeacher($nip, $data) {
    $conn = getConnection();
    
    $conn->begin_transaction();
    
    try {
        // Update tabel users
        $stmt = $conn->prepare("UPDATE users u JOIN guru g ON u.id = g.user_id SET u.nama = ?, u.email = ?, u.updated_at = NOW() WHERE g.nip = ?");
        $stmt->bind_param("sss", $data['nama'], $data['email'], $nip);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal mengupdate data user");
        }
        $stmt->close();
        
        // Update tabel guru
        $stmt = $conn->prepare("UPDATE guru SET tempat_lahir = ?, tanggal_lahir = ?, jenis_kelamin = ?, alamat = ?, mapel_id = ? WHERE nip = ?");
        $stmt->bind_param("ssssss", 
            $data['tempat_lahir'], $data['tanggal_lahir'], $data['jenis_kelamin'], 
            $data['alamat'], $data['mapel_id'], $nip);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal mengupdate data guru");
        }
        $stmt->close();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating teacher: " . $e->getMessage());
        return false;
    }
    
    $conn->close();
}

/**
 * Fungsi untuk menghapus guru
 * @param string $nip
 * @return bool
 */
function deleteTeacher($nip) {
    $conn = getConnection();
    
    $conn->begin_transaction();
    
    try {
        // Dapatkan user_id
        $stmt = $conn->prepare("SELECT user_id FROM guru WHERE nip = ?");
        $stmt->bind_param("s", $nip);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $guru = $result->fetch_assoc();
            $user_id = $guru['user_id'];
        } else {
            throw new Exception("Guru tidak ditemukan");
        }
        $stmt->close();
        
        // Hapus data terkait guru dari tabel jadwal_pelajaran
        $stmt = $conn->prepare("DELETE FROM jadwal_pelajaran WHERE guru_id = ?");
        $stmt->bind_param("s", $nip);
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
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error deleting teacher: " . $e->getMessage());
        return false;
    }
    
    $conn->close();
}

// Fungsi-fungsi CRUD untuk kelas, jurusan, mapel, jadwal, nilai, absensi bisa ditambahkan di sini
// Untuk menjaga ukuran file, saya akan membuat contoh beberapa fungsi tambahan:

/**
 * Fungsi untuk mendapatkan semua kelas
 * @return array
 */
function getAllClasses() {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT k.id, k.nama_kelas, k.tingkat, j.nama_jurusan 
                           FROM kelas k 
                           LEFT JOIN jurusan j ON k.jurusan_id = j.id 
                           ORDER BY k.tingkat, k.nama_kelas");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $classes;
}

/**
 * Fungsi untuk mendapatkan semua jurusan
 * @return array
 */
function getAllMajors() {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT id, kode_jurusan, nama_jurusan FROM jurusan ORDER BY kode_jurusan");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $majors = [];
    while ($row = $result->fetch_assoc()) {
        $majors[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $majors;
}

/**
 * Fungsi untuk mendapatkan semua mata pelajaran
 * @return array
 */
function getAllSubjects() {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT m.id, m.kode_mapel, m.nama_mapel, j.nama_jurusan 
                           FROM mapel m 
                           LEFT JOIN jurusan j ON m.jurusan_id = j.id 
                           ORDER BY m.kode_mapel");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $subjects;
}

?>