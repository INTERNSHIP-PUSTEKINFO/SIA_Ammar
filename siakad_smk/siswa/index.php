<?php
// siswa/index.php
$page_title = "Dashboard Siswa";
$active_page = "dashboard";
include_once '../includes/siswa_header.php';

include_once '../config/database.php';
$conn = getConnection();

// Ambil informasi siswa yang sedang login
$nis = $_SESSION['nis'];
$siswa_info = null;
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
    $siswa_info = $result->fetch_assoc();
}
$stmt->close();

// Hitung jumlah nilai yang sudah ada
$total_nilai = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM nilai WHERE siswa_nis = ?");
$stmt->bind_param("s", $nis);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $total_nilai = $row['total'];
}
$stmt->close();

// Hitung jumlah absensi
$total_absensi = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM absensi WHERE siswa_nis = ?");
$stmt->bind_param("s", $nis);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $total_absensi = $row['total'];
}
$stmt->close();

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-graduate me-2"></i>Dashboard Siswa</h2>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user me-2"></i>Profil Siswa</h5>
            </div>
            <div class="card-body">
                <?php if ($siswa_info): ?>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Nama</strong></td>
                            <td><?php echo htmlspecialchars($siswa_info['nama']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>NIS</strong></td>
                            <td><?php echo htmlspecialchars($siswa_info['nis']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email</strong></td>
                            <td><?php echo htmlspecialchars($siswa_info['email']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Kelas</strong></td>
                            <td><?php echo htmlspecialchars($siswa_info['nama_kelas'] ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Jurusan</strong></td>
                            <td><?php echo htmlspecialchars($siswa_info['nama_jurusan'] ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Jenis Kelamin</strong></td>
                            <td><?php echo ($siswa_info['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td>
                                <?php if ($siswa_info['status'] == 'aktif'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php elseif ($siswa_info['status'] == 'lulus'): ?>
                                    <span class="badge bg-primary">Lulus</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Keluar</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                <?php else: ?>
                    <p>Data siswa tidak ditemukan.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="stat-card bg-primary-light rounded-3 p-3 text-center">
                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                    <h4><?php echo $total_nilai; ?></h4>
                    <p class="mb-0">Mata Pelajaran</p>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="stat-card bg-success-light rounded-3 p-3 text-center">
                    <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                    <h4><?php echo $total_absensi; ?></h4>
                    <p class="mb-0">Data Absensi</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-calendar-alt me-2"></i>Jadwal Pelajaran</h5>
            </div>
            <div class="card-body">
                <p>Jadwal pelajaran akan ditampilkan di sini.</p>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>