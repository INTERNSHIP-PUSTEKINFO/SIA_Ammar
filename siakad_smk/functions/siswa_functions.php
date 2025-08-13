<?php
// functions/siswa_functions.php

include_once 'config/database.php';

/**
 * Fungsi untuk mendapatkan profil siswa
 * @param string $nis
 * @return array|bool
 */
function getStudentProfile($nis) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT s.nis, s.tempat_lahir, s.tanggal_lahir, s.jenis_kelamin, s.alamat, s.tahun_masuk, s.status, 
                           u.nama, u.email, j.nama_jurusan, k.nama_kelas 
                           FROM siswa s 
                           JOIN users u ON s.user_id = u.id 
                           LEFT JOIN jurusan j ON s.jurusan_id = j.id 
                           LEFT JOIN kelas k ON s.kelas_id = k.id 
                           WHERE s.nis = ?");
    $stmt->bind_param("s", $nis);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $profile = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $profile;
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

/**
 * Fungsi untuk mendapatkan daftar nilai siswa
 * @param string $nis
 * @return array
 */
function getStudentGrades($nis) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT n.semester, n.tahun_ajaran, n.nilai_tugas, n.nilai_uts, n.nilai_uas, n.nilai_akhir, 
                           m.kode_mapel, m.nama_mapel
                           FROM nilai n
                           JOIN mapel m ON n.mapel_id = m.id
                           WHERE n.siswa_nis = ?
                           ORDER BY n.tahun_ajaran DESC, n.semester, m.nama_mapel");
    $stmt->bind_param("s", $nis);
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
 * Fungsi untuk mendapatkan daftar absensi siswa
 * @param string $nis
 * @return array
 */
function getStudentAttendance($nis) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT a.tanggal, a.keterangan, m.kode_mapel, m.nama_mapel
                           FROM absensi a
                           JOIN mapel m ON a.mapel_id = m.id
                           WHERE a.siswa_nis = ?
                           ORDER BY a.tanggal DESC, m.nama_mapel");
    $stmt->bind_param("s", $nis);
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
 * Fungsi untuk menghitung statistik nilai siswa
 * @param string $nis
 * @return array
 */
function getStudentGradeStatistics($nis) {
    $conn = getConnection();
    
    // Hitung jumlah nilai
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM nilai WHERE siswa_nis = ?");
    $stmt->bind_param("s", $nis);
    $stmt->execute();
    $result = $stmt->get_result();
    $total = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // Hitung rata-rata nilai akhir
    $stmt = $conn->prepare("SELECT AVG(nilai_akhir) as average FROM nilai WHERE siswa_nis = ?");
    $stmt->bind_param("s", $nis);
    $stmt->execute();
    $result = $stmt->get_result();
    $average = $result->fetch_assoc()['average'];
    $stmt->close();
    
    // Hitung nilai tertinggi
    $stmt = $conn->prepare("SELECT MAX(nilai_akhir) as highest FROM nilai WHERE siswa_nis = ?");
    $stmt->bind_param("s", $nis);
    $stmt->execute();
    $result = $stmt->get_result();
    $highest = $result->fetch_assoc()['highest'];
    $stmt->close();
    
    // Hitung nilai terendah
    $stmt = $conn->prepare("SELECT MIN(nilai_akhir) as lowest FROM nilai WHERE siswa_nis = ?");
    $stmt->bind_param("s", $nis);
    $stmt->execute();
    $result = $stmt->get_result();
    $lowest = $result->fetch_assoc()['lowest'];
    $stmt->close();
    
    $conn->close();
    
    return [
        'total' => $total,
        'average' => $average ? round($average, 2) : 0,
        'highest' => $highest ? round($highest, 2) : 0,
        'lowest' => $lowest ? round($lowest, 2) : 0
    ];
}

/**
 * Fungsi untuk menghitung statistik absensi siswa
 * @param string $nis
 * @return array
 */
function getStudentAttendanceStatistics($nis) {
    $conn = getConnection();
    
    // Hitung jumlah absensi
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM absensi WHERE siswa_nis = ?");
    $stmt->bind_param("s", $nis);
    $stmt->execute();
    $result = $stmt->get_result();
    $total = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // Hitung jumlah hadir
    $stmt = $conn->prepare("SELECT COUNT(*) as present FROM absensi WHERE siswa_nis = ? AND keterangan = 'Hadir'");
    $stmt->bind_param("s", $nis);
    $stmt->execute();
    $result = $stmt->get_result();
    $present = $result->fetch_assoc()['present'];
    $stmt->close();
    
    // Hitung jumlah sakit
    $stmt = $conn->prepare("SELECT COUNT(*) as sick FROM absensi WHERE siswa_nis = ? AND keterangan = 'Sakit'");
    $stmt->bind_param("s", $nis);
    $stmt->execute();
    $result = $stmt->get_result();
    $sick = $result->fetch_assoc()['sick'];
    $stmt->close();
    
    // Hitung jumlah izin
    $stmt = $conn->prepare("SELECT COUNT(*) as permission FROM absensi WHERE siswa_nis = ? AND keterangan = 'Izin'");
    $stmt->bind_param("s", $nis);
    $stmt->execute();
    $result = $stmt->get_result();
    $permission = $result->fetch_assoc()['permission'];
    $stmt->close();
    
    // Hitung jumlah alpa
    $stmt = $conn->prepare("SELECT COUNT(*) as absent FROM absensi WHERE siswa_nis = ? AND keterangan = 'Alpa'");
    $stmt->bind_param("s", $nis);
    $stmt->execute();
    $result = $stmt->get_result();
    $absent = $result->fetch_assoc()['absent'];
    $stmt->close();
    
    $conn->close();
    
    return [
        'total' => $total,
        'present' => $present,
        'sick' => $sick,
        'permission' => $permission,
        'absent' => $absent
    ];
}

?>