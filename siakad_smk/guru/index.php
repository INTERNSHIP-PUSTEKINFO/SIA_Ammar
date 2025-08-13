<?php
// guru/index.php
$page_title = "Dashboard Guru";
$active_page = "dashboard";
include_once '../includes/guru_header.php';

include_once '../config/database.php';
$conn = getConnection();

// Ambil informasi guru yang sedang login
$nip = $_SESSION['nip'];
$guru_info = null;
$stmt = $conn->prepare("SELECT g.nip, g.tempat_lahir, g.tanggal_lahir, g.jenis_kelamin, g.alamat, u.nama, u.email, m.nama_mapel 
                        FROM guru g 
                        JOIN users u ON g.user_id = u.id 
                        LEFT JOIN mapel m ON g.mapel_id = m.id 
                        WHERE g.nip = ?");
$stmt->bind_param("s", $nip);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $guru_info = $result->fetch_assoc();
}
$stmt->close();

// Hitung jumlah nilai yang sudah diinput
$total_nilai = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM nilai n 
                        JOIN jadwal_pelajaran jp ON n.mapel_id = jp.mapel_id 
                        WHERE jp.guru_id = ?");
$stmt->bind_param("s", $nip);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $total_nilai = $row['total'];
}
$stmt->close();

// Hitung jumlah absensi yang sudah diinput
$total_absensi = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM absensi a 
                        JOIN jadwal_pelajaran jp ON a.mapel_id = jp.mapel_id 
                        WHERE jp.guru_id = ?");
$stmt->bind_param("s", $nip);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $total_absensi = $row['total'];
}
$stmt->close();

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-chalkboard-teacher me-2"></i>Dashboard Guru</h2>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user me-2"></i>Profil Guru</h5>
            </div>
            <div class="card-body">
                <?php if ($guru_info): ?>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Nama</strong></td>
                            <td><?php echo htmlspecialchars($guru_info['nama']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>NIP</strong></td>
                            <td><?php echo htmlspecialchars($guru_info['nip']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email</strong></td>
                            <td><?php echo htmlspecialchars($guru_info['email']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Mata Pelajaran</strong></td>
                            <td><?php echo htmlspecialchars($guru_info['nama_mapel'] ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Jenis Kelamin</strong></td>
                            <td><?php echo ($guru_info['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></td>
                        </tr>
                    </table>
                <?php else: ?>
                    <p>Data guru tidak ditemukan.</p>
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
                    <p class="mb-0">Nilai Diinput</p>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="stat-card bg-success-light rounded-3 p-3 text-center">
                    <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                    <h4><?php echo $total_absensi; ?></h4>
                    <p class="mb-0">Absensi Diinput</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-calendar-alt me-2"></i>Jadwal Mengajar</h5>
            </div>
            <div class="card-body">
                <p>Jadwal mengajar akan ditampilkan di sini.</p>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>