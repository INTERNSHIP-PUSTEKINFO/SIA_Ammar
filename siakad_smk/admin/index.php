<?php
// admin/index.php
$page_title = "Dashboard Admin";
$active_page = "dashboard";
include_once '../includes/admin_header.php';

include_once '../config/database.php';
$conn = getConnection();

// Query untuk mendapatkan statistik
$total_siswa = 0;
$total_guru = 0;
$total_kelas = 0;
$total_jurusan = 0;

// Hitung total siswa
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM siswa WHERE status = 'aktif'");
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $total_siswa = $row['total'];
}
$stmt->close();

// Hitung total guru
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM guru");
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $total_guru = $row['total'];
}
$stmt->close();

// Hitung total kelas
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM kelas");
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $total_kelas = $row['total'];
}
$stmt->close();

// Hitung total jurusan
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM jurusan");
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $total_jurusan = $row['total'];
}
$stmt->close();

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin</h2>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card bg-primary-light rounded-3 p-3">
            <i class="fas fa-users"></i>
            <h4><?php echo $total_siswa; ?></h4>
            <p class="mb-0">Total Siswa Aktif</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-success-light rounded-3 p-3">
            <i class="fas fa-chalkboard-teacher"></i>
            <h4><?php echo $total_guru; ?></h4>
            <p class="mb-0">Total Guru</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-warning-light rounded-3 p-3">
            <i class="fas fa-school"></i>
            <h4><?php echo $total_kelas; ?></h4>
            <p class="mb-0">Total Kelas</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-info-light rounded-3 p-3">
            <i class="fas fa-graduation-cap"></i>
            <h4><?php echo $total_jurusan; ?></h4>
            <p class="mb-0">Total Jurusan</p>
        </div>
    </div>
</div>

<!-- Aktivitas Terbaru -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-bell me-2"></i>Aktivitas Terbaru</h5>
    </div>
    <div class="card-body">
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <small class="text-muted">Hari ini</small>
                <p class="mb-0">Sistem diperbarui dengan fitur baru</p>
            </li>
            <li class="list-group-item">
                <small class="text-muted">Kemarin</small>
                <p class="mb-0">5 akun siswa baru telah didaftarkan</p>
            </li>
            <li class="list-group-item">
                <small class="text-muted">2 hari yang lalu</small>
                <p class="mb-0">Jadwal pelajaran semester ganjil telah diperbarui</p>
            </li>
        </ul>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>