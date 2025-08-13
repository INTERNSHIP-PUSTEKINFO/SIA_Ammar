<?php
// includes/siswa_header.php
include_once 'auth_check.php';
checkRole('siswa');
include_once 'header.php';
?>

<!-- Navbar untuk user yang sudah login -->
<nav class="navbar navbar-expand-lg navbar-light bg-white">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <i class="fas fa-graduation-cap me-2"></i>SIAKAD SMK
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars(getUserName()); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 d-none d-lg-block p-0">
            <div class="sidebar">
                <div class="profile-section">
                    <img src="https://placehold.co/80x80" class="profile-img">
                    <h6><?php echo htmlspecialchars(getUserName()); ?></h6>
                    <small class="text-capitalize"><?php echo htmlspecialchars(getUserRole()); ?></small>
                </div>
                <div class="p-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'dashboard') ? 'active' : ''; ?>" href="../siswa/index.php">
                                <i class="fas fa-home me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'nilai') ? 'active' : ''; ?>" href="../siswa/nilai/view.php">
                                <i class="fas fa-chart-bar me-2"></i>Lihat Nilai
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-lg-10 ms-auto">
            <div class="main-content">
                <?php if (isset($breadcrumbs)): ?>
                <nav aria-label="breadcrumb" class="mb-4">
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