<?php
// guru/absensi/list.php
$page_title = "Data Absensi Siswa";
$active_page = "absensi";
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '../index.php'],
    ['name' => 'Data Absensi Siswa', 'active' => true]
];

include_once '../../includes/guru_header.php';
include_once '../../config/database.php';

$conn = getConnection();
$nip = $_SESSION['nip'];

// Ambil daftar mapel yang diajarkan oleh guru ini
$mapel_list = [];
$stmt = $conn->prepare("SELECT DISTINCT m.id, m.kode_mapel, m.nama_mapel 
                        FROM jadwal_pelajaran jp 
                        JOIN mapel m ON jp.mapel_id = m.id 
                        WHERE jp.guru_id = ?");
$stmt->bind_param("s", $nip);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $mapel_list[] = $row;
}
$stmt->close();

// Filter berdasarkan mapel jika ada parameter
$selected_mapel_id = isset($_GET['mapel_id']) ? (int)$_GET['mapel_id'] : (isset($mapel_list[0]['id']) ? $mapel_list[0]['id'] : 0);

// Ambil daftar kelas yang mengambil mapel ini
$kelas_list = [];
if ($selected_mapel_id) {
    $stmt = $conn->prepare("SELECT DISTINCT k.id, k.nama_kelas 
                            FROM jadwal_pelajaran jp 
                            JOIN kelas k ON jp.kelas_id = k.id 
                            WHERE jp.mapel_id = ? AND jp.guru_id = ?");
    $stmt->bind_param("is", $selected_mapel_id, $nip);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $kelas_list[] = $row;
    }
    $stmt->close();
}

// Filter berdasarkan kelas jika ada parameter
$selected_kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : (isset($kelas_list[0]['id']) ? $kelas_list[0]['id'] : 0);

// Filter berdasarkan tanggal
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Ambil daftar absensi siswa
$absensi_list = [];
if ($selected_mapel_id && $selected_kelas_id) {
    $stmt = $conn->prepare("SELECT a.id, s.nis, u.nama as nama_siswa, a.tanggal, a.keterangan
                           FROM absensi a
                           JOIN siswa s ON a.siswa_nis = s.nis
                           JOIN users u ON s.user_id = u.id
                           JOIN kelas k ON s.kelas_id = k.id
                           WHERE a.mapel_id = ? AND k.id = ? AND a.tanggal = ?
                           ORDER BY u.nama");
    $stmt->bind_param("iis", $selected_mapel_id, $selected_kelas_id, $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $absensi_list[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-clipboard-list me-2"></i>Data Absensi Siswa</h2>
    <a href="input.php?mapel_id=<?php echo $selected_mapel_id; ?>&kelas_id=<?php echo $selected_kelas_id; ?>&date=<?php echo $selected_date; ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Input Absensi
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5><i class="fas fa-filter me-2"></i>Filter Data</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-3 mb-3">
                <label for="mapel_id" class="form-label">Mata Pelajaran</label>
                <select class="form-select" id="mapel_id" name="mapel_id" onchange="this.form.submit()">
                    <option value="">Pilih Mata Pelajaran</option>
                    <?php foreach ($mapel_list as $mapel): ?>
                        <option value="<?php echo $mapel['id']; ?>" <?php echo ($selected_mapel_id == $mapel['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($mapel['kode_mapel'] . ' - ' . $mapel['nama_mapel']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if (!empty($kelas_list)): ?>
            <div class="col-md-3 mb-3">
                <label for="kelas_id" class="form-label">Kelas</label>
                <select class="form-select" id="kelas_id" name="kelas_id" onchange="this.form.submit()">
                    <option value="">Pilih Kelas</option>
                    <?php foreach ($kelas_list as $kelas): ?>
                        <option value="<?php echo $kelas['id']; ?>" <?php echo ($selected_kelas_id == $kelas['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div class="col-md-3 mb-3">
                <label for="date" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo $selected_date; ?>" onchange="this.form.submit()">
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-table me-2"></i>Daftar Absensi Siswa</h5>
    </div>
    <div class="card-body">
        <?php if (empty($selected_mapel_id) || empty($selected_kelas_id)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Silakan pilih mata pelajaran dan kelas untuk menampilkan data absensi.
            </div>
        <?php elseif (empty($absensi_list)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>Belum ada data absensi untuk mata pelajaran, kelas, dan tanggal yang dipilih.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($absensi_list as $absensi): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($absensi['nis']); ?></td>
                                <td><?php echo htmlspecialchars($absensi['nama_siswa']); ?></td>
                                <td><?php echo htmlspecialchars(date('d M Y', strtotime($absensi['tanggal']))); ?></td>
                                <td>
                                    <?php if ($absensi['keterangan'] == 'Hadir'): ?>
                                        <span class="badge bg-success">Hadir</span>
                                    <?php elseif ($absensi['keterangan'] == 'Sakit'): ?>
                                        <span class="badge bg-warning">Sakit</span>
                                    <?php elseif ($absensi['keterangan'] == 'Izin'): ?>
                                        <span class="badge bg-info">Izin</span>
                                    <?php elseif ($absensi['keterangan'] == 'Alpa'): ?>
                                        <span class="badge bg-danger">Alpa</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="input.php?id=<?php echo $absensi['id']; ?>&mapel_id=<?php echo $selected_mapel_id; ?>&kelas_id=<?php echo $selected_kelas_id; ?>&date=<?php echo $selected_date; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>