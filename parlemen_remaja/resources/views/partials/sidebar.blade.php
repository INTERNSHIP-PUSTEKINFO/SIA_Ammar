<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIAKAD SMK - <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard Admin'; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS - Path relatif terhadap file yang memanggil (admin/*/) -->
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <!-- Navbar Atas -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../admin/index.php">
                <i class="fas fa-graduation-cap me-2"></i>SIAKAD SMK
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($userName); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Kiri -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center p-2 border-bottom">
                        <img src="https://placehold.co/60x60" alt="Profile" class="rounded-circle mb-2">
                        <h6><?php echo htmlspecialchars($userName); ?></h6>
                        <small class="text-muted text-capitalize"><?php echo htmlspecialchars($userRole); ?></small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'dashboard') ? 'active' : ''; ?>" href="../admin/index.php">
                                <i class="fas fa-home me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'siswa') ? 'active' : ''; ?>" href="../admin/siswa/list.php">
                                <i class="fas fa-users me-2"></i> Data Siswa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'guru') ? 'active' : ''; ?>" href="../admin/guru/list.php">
                                 <i class="fas fa-chalkboard-teacher me-2"></i> Data Guru
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'kelas') ? 'active' : ''; ?>" href="../admin/kelas/list.php">
                                <i class="fas fa-school me-2"></i> Kelas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'jurusan') ? 'active' : ''; ?>" href="../admin/jurusan/list.php">
                                <i class="fas fa-graduation-cap me-2"></i> Jurusan
                            </a>
                        </li>
                         <li class="nav-item">
                            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'mapel') ? 'active' : ''; ?>" href="../admin/mapel/list.php">
                                <i class="fas fa-book me-2"></i> Mata Pelajaran
                            </a>
                        </li>
                         <li class="nav-item">
                            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'jadwal') ? 'active' : ''; ?>" href="../admin/jadwal/list.php">
                                <i class="fas fa-calendar-alt me-2"></i> Jadwal Pelajaran
                            </a>
                        </li>
                         <li class="nav-item">
                            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'users') ? 'active' : ''; ?>" href="../admin/users/list.php">
                                <i class="fas fa-users-cog me-2"></i> Users
                            </a>
                        </li>
                        <!-- Tambahkan menu lainnya sesuai kebutuhan -->
                    </ul>
                </div>
            </nav>

            <!-- Konten Utama -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Breadcrumb -->
                <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                <nav aria-label="breadcrumb" class="mt-3">
                    <ol class="breadcrumb">
                        <?php foreach ($breadcrumbs as $breadcrumb): ?>
                            <?php if (isset($breadcrumb['active']) && $breadcrumb['active']): ?>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($breadcrumb['name']); ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars($breadcrumb['url']); ?>"><?php echo htmlspecialchars($breadcrumb['name']); ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
                <?php endif; ?>
