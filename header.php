<?php
// header.php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIIG - Mfumo wa Taarifa</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="logo.png">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <img src="logo.png" alt="VIIG Logo" class="logo me-2">
                <span class="fw-bold">VIIG System</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <span class="nav-link">
                                <i class="fas fa-user me-1"></i>
                                <?php echo $_SESSION['username']; ?>
                                <span class="user-role ms-2"><?php echo $_SESSION['role']; ?></span>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-1"></i>Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="view_posts.php"><i class="fas fa-newspaper me-1"></i>Taarifa</a>
                        </li>
                        <?php if($_SESSION['role'] == 'leader'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="approve_posts.php">
                                    <i class="fas fa-check-circle me-1"></i>Idhinisha
                                    <?php
                                    include 'config.php';
                                    $pending = $conn->query("SELECT COUNT(*) as count FROM posts WHERE status='pending'")->fetch()['count'];
                                    if($pending > 0): ?>
                                        <span class="badge bg-danger rounded-pill"><?php echo $pending; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="manage_users.php"><i class="fas fa-users-cog me-1"></i>Watumiaji</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Toka</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Content will go here -->