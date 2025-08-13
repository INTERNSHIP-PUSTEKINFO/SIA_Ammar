<?php
// create_test_user.php
include_once 'config/database.php';

$conn = getConnection();

// Data user test
$users = [
    [
        'nama' => 'Admin Test',
        'email' => 'admin@test.com',
        'password' => 'admin123',
        'role' => 'admin'
    ],
    [
        'nama' => 'Guru Test',
        'email' => 'guru@test.com',
        'password' => 'guru123',
        'role' => 'guru'
    ],
    [
        'nama' => 'Siswa Test',
        'email' => 'siswa@test.com',
        'password' => 'siswa123',
        'role' => 'siswa'
    ]
];

foreach ($users as $user) {
    // Hash password
    $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);

    // Cek apakah email sudah ada
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $user['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "User {$user['email']} sudah ada.<br>";
    } else {
        $stmt->close();

        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $user['nama'], $user['email'], $hashed_password, $user['role']);

        if ($stmt->execute()) {
            echo "Berhasil membuat user: {$user['email']} ({$user['role']})<br>";

            // Jika siswa, buat data siswa
            if ($user['role'] == 'siswa') {
                $user_id = $conn->insert_id;
                $nis = '1234567890';
                $stmt2 = $conn->prepare("INSERT INTO siswa (nis, user_id, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, tahun_masuk, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param("sisssssi", $nis, $user_id, 'Jakarta', '2000-01-01', 'L', 'Alamat Test', 2020, 'aktif');
                if ($stmt2->execute()) {
                    echo "<p style='color: blue;'>Berhasil membuat data siswa</p>";
                }
                $stmt2->close();
            }

            // Jika guru, buat data guru
            if ($user['role'] == 'guru') {
                $user_id = $conn->insert_id;
                $nip = '987654321';
                $stmt2 = $conn->prepare("INSERT INTO guru (nip, user_id, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param("sissss", $nip, $user_id, 'Bandung', '1980-01-01', 'L', 'Alamat Guru Test');
                if ($stmt2->execute()) {
                    echo "<p style='color: blue;'>Berhasil membuat data guru</p>";
                }
                $stmt2->close();
            }
        } else {
            echo "Gagal membuat user {$user['email']}: " . $stmt->error . "<br>";
        }
        $stmt->close();
    }
}

$conn->close();
?>