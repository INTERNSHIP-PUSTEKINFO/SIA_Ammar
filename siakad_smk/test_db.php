<?php
// test_db.php
include_once 'config/database.php';

$conn = getConnection();

// Test query
$stmt = $conn->prepare("SELECT id, nama, email, password, role FROM users LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Daftar User dalam Database:</h2>";
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . "<br>";
    echo "Nama: " . $row['nama'] . "<br>";
    echo "Email: " . $row['email'] . "<br>";
    echo "Role: " . $row['role'] . "<br>";
    echo "Password (hashed): " . substr($row['password'], 0, 20) . "...<br><br>";
}

$stmt->close();
$conn->close();
?>