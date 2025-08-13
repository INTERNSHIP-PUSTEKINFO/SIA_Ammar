<?php
// functions/guru_functions.php

include_once 'config/database.php';

/**
 * Fungsi untuk mendapatkan daftar mapel yang diajarkan oleh guru
 * @param string $nip
 * @return array
 */
function getTeacherSubjects($nip) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT DISTINCT m.id, m.kode_mapel, m.nama_mapel 
                           FROM jadwal_pelajaran jp 
                           JOIN mapel m ON jp.mapel_id = m.id 
                           WHERE jp.guru_id = ?");
    $stmt->bind_param("s", $nip);
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

/**
 * Fungsi untuk mendapatkan daftar kelas yang mengambil mapel tertentu oleh guru
 * @param string $nip
 * @param int $mapel_id
 * @return array
 */
function getTeacherClassesForSubject($nip, $mapel_id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT DISTINCT k.id, k.nama_kelas 
                           FROM jadwal_pelajaran jp 
                           JOIN kelas k ON jp.kelas_id = k.id 
                           WHERE jp.mapel_id = ? AND jp.guru_id = ?");
    $stmt->bind_param("is", $mapel_id, $nip);
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
 * Fungsi untuk mendapatkan daftar nilai siswa berdasarkan mapel dan kelas
 * @param int $mapel_id
 * @param int $kelas_id
 * @return array
 */
function getStudentGrades($mapel_id, $kelas_id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT n.id, s.nis, u.nama as nama_siswa, n.semester, n.tahun_ajaran, 
                           n.nilai_tugas, n.nilai_uts, n.nilai_uas, n.nilai_akhir
                           FROM nilai n
                           JOIN siswa s ON n.siswa_nis = s.nis
                           JOIN users u ON s.user_id = u.id
                           JOIN kelas k ON s.kelas_id = k.id
                           WHERE n.mapel_id = ? AND k.id = ?
                           ORDER BY u.nama");
    $stmt->bind_param("ii", $mapel_id, $kelas_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $grades = [];
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $grades;
}

/**
 * Fungsi untuk mendapatkan daftar absensi siswa berdasarkan mapel, kelas, dan tanggal
 * @param int $mapel_id
 * @param int $kelas_id
 * @param string $tanggal
 * @return array
 */
function getStudentAttendance($mapel_id, $kelas_id, $tanggal) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT a.id, s.nis, u.nama as nama_siswa, a.tanggal, a.keterangan
                           FROM absensi a
                           JOIN siswa s ON a.siswa_nis = s.nis
                           JOIN users u ON s.user_id = u.id
                           JOIN kelas k ON s.kelas_id = k.id
                           WHERE a.mapel_id = ? AND k.id = ? AND a.tanggal = ?
                           ORDER BY u.nama");
    $stmt->bind_param("iis", $mapel_id, $kelas_id, $tanggal);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $attendance = [];
    while ($row = $result->fetch_assoc()) {
        $attendance[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $attendance;
}

/**
 * Fungsi untuk menyimpan atau memperbarui nilai siswa
 * @param array $data
 * @return bool
 */
function saveStudentGrade($data) {
    $conn = getConnection();
    
    // Hitung nilai akhir
    $nilai_akhir = round(($data['nilai_tugas'] + $data['nilai_uts'] + $data['nilai_uas']) / 3, 2);
    
    if (!empty($data['id'])) {
        // Update nilai
        $stmt = $conn->prepare("UPDATE nilai SET siswa_nis = ?, semester = ?, tahun_ajaran = ?, nilai_tugas = ?, nilai_uts = ?, nilai_uas = ?, nilai_akhir = ? WHERE id = ?");
        $stmt->bind_param("ssssdddi", 
            $data['siswa_nis'], $data['semester'], $data['tahun_ajaran'], 
            $data['nilai_tugas'], $data['nilai_uts'], $data['nilai_uas'], $nilai_akhir, $data['id']);
    } else {
        // Insert nilai baru
        $stmt = $conn->prepare("INSERT INTO nilai (siswa_nis, mapel_id, semester, tahun_ajaran, nilai_tugas, nilai_uts, nilai_uas, nilai_akhir) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissdddd", 
            $data['siswa_nis'], $data['mapel_id'], $data['semester'], $data['tahun_ajaran'], 
            $data['nilai_tugas'], $data['nilai_uts'], $data['nilai_uas'], $nilai_akhir);
    }
    
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $success;
}

/**
 * Fungsi untuk menyimpan atau memperbarui absensi siswa
 * @param array $data
 * @return bool
 */
function saveStudentAttendance($data) {
    $conn = getConnection();
    
    if (!empty($data['id'])) {
        // Update absensi
        $stmt = $conn->prepare("UPDATE absensi SET siswa_nis = ?, mapel_id = ?, tanggal = ?, keterangan = ? WHERE id = ?");
        $stmt->bind_param("sissi", 
            $data['siswa_nis'], $data['mapel_id'], $data['tanggal'], $data['keterangan'], $data['id']);
    } else {
        // Cek apakah absensi sudah ada
        $stmt = $conn->prepare("SELECT id FROM absensi WHERE siswa_nis = ? AND mapel_id = ? AND tanggal = ?");
        $stmt->bind_param("sis", $data['siswa_nis'], $data['mapel_id'], $data['tanggal']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update absensi yang sudah ada
            $existing = $result->fetch_assoc();
            $stmt->close();
            
            $stmt = $conn->prepare("UPDATE absensi SET keterangan = ? WHERE id = ?");
            $stmt->bind_param("si", $data['keterangan'], $existing['id']);
        } else {
            // Insert absensi baru
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO absensi (siswa_nis, mapel_id, tanggal, keterangan) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siss", 
                $data['siswa_nis'], $data['mapel_id'], $data['tanggal'], $data['keterangan']);
        }
    }
    
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $success;
}

/**
 * Fungsi untuk mendapatkan daftar siswa berdasarkan kelas
 * @param int $kelas_id
 * @return array
 */
function getStudentsByClass($kelas_id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT s.nis, u.nama FROM siswa s JOIN users u ON s.user_id = u.id WHERE s.kelas_id = ? ORDER BY u.nama");
    $stmt->bind_param("i", $kelas_id);
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

?>