<?php
// functions/user_functions.php

include_once 'config/database.php';

/**
 * Fungsi untuk memverifikasi login user
 * @param string $email
 * @param string $password
 * @param string $role
 * @return array|bool
 */
function verifyUserLogin($email, $password, $role) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT id, nama, email, password, role FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $stmt->close();
            $conn->close();
            return $user;
        }
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

/**
 * Fungsi untuk mendaftarkan user baru
 * @param string $nama
 * @param string $email
 * @param string $password
 * @param string $role
 * @return bool
 */
function registerUser($nama, $email, $password, $role) {
    $conn = getConnection();
    
    // Cek apakah email sudah terdaftar
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->close();
        return false; // Email sudah terdaftar
    }
    $stmt->close();
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $nama, $email, $hashed_password, $role);
    
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $success;
}

/**
 * Fungsi untuk mendapatkan NIS siswa berdasarkan user_id
 * @param int $user_id
 * @return string|bool
 */
function getStudentNis($user_id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT nis FROM siswa WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $siswa = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $siswa['nis'];
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

/**
 * Fungsi untuk mendapatkan NIP guru berdasarkan user_id
 * @param int $user_id
 * @return string|bool
 */
function getTeacherNip($user_id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT nip FROM guru WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $guru = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $guru['nip'];
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

/**
 * Fungsi untuk memperbarui profil user
 * @param int $user_id
 * @param string $nama
 * @param string $email
 * @return bool
 */
function updateUserProfile($user_id, $nama, $email) {
    $conn = getConnection();
    
    // Cek apakah email sudah digunakan oleh user lain
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->close();
        return false; // Email sudah digunakan
    }
    $stmt->close();
    
    // Update data user
    $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $nama, $email, $user_id);
    
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $success;
}

/**
 * Fungsi untuk mengganti password user
 * @param int $user_id
 * @param string $old_password
 * @param string $new_password
 * @return bool
 */
function changeUserPassword($user_id, $old_password, $new_password) {
    $conn = getConnection();
    
    // Verifikasi password lama
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (!password_verify($old_password, $user['password'])) {
            $stmt->close();
            $conn->close();
            return false; // Password lama salah
        }
    } else {
        $stmt->close();
        $conn->close();
        return false; // User tidak ditemukan
    }
    $stmt->close();
    
    // Hash password baru
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $hashed_new_password, $user_id);
    
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $success;
}

?>