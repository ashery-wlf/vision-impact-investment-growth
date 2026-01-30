<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

include 'header.php';

// Get statistics
try {
    $statsStmt = $conn->prepare("SELECT COUNT(*) as total_posts FROM posts");
    $statsStmt->execute();
    $postsCount = $statsStmt->fetch()['total_posts'] ?? 0;
    
    $approvedStmt = $conn->prepare("SELECT COUNT(*) as approved FROM posts WHERE status = 'approved'");
    $approvedStmt->execute();
    $approvedCount = $approvedStmt->fetch()['approved'] ?? 0;
    
    $pendingStmt = $conn->prepare("SELECT COUNT(*) as pending FROM posts WHERE status = 'pending'");
    $pendingStmt->execute();
    $pendingCount = $pendingStmt->fetch()['pending'] ?? 0;
    
    $usersStmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users");
    $usersStmt->execute();
    $usersCount = $usersStmt->fetch()['total_users'] ?? 0;
} catch (PDOException $e) {
    $postsCount = $approvedCount = $pendingCount = $usersCount = 0;
}

// Get recent posts
try {
    $recentStmt = $conn->prepare("SELECT posts.*, users.full_name FROM posts 
        JOIN users ON posts.user_id = users.id 
        WHERE posts.status = 'approved'
        ORDER BY posts.created_at DESC LIMIT 5");
    $recentStmt->execute();
    $recentPosts = $recentStmt->fetchAll() ?? [];
} catch (PDOException $e) {
    $recentPosts = [];
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - VIIG System</title>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </h1>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>Jumla ya Taarifa</h3>
                    <p class="stat-number"><?php echo $postsCount; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>Taarifa Zilizoidhinishwa</h3>
                    <p class="stat-number"><?php echo $approvedCount; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-info">
                    <h3>Taarifa Zinazosubiri</h3>
                    <p class="stat-number"><?php echo $pendingCount; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>Jumla ya Watumiaji</h3>
                    <p class="stat-number"><?php echo $usersCount; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Recent Posts -->
        <div class="mt-5">
            <h2 class="mb-4">
                <i class="fas fa-newspaper me-2"></i>Taarifa za Karibuni
            </h2>
            
            <?php if (!empty($recentPosts)): ?>
                <div class="posts-container">
                    <?php foreach ($recentPosts as $post): ?>
                        <div class="post-card">
                            <div class="post-header">
                                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($post['full_name']); ?> - 
                                    <?php echo date('d M Y H:i', strtotime($post['created_at'])); ?>
                                </small>
                            </div>
                            <div class="post-content">
                                <?php echo htmlspecialchars(substr($post['content'], 0, 200)); ?>...
                            </div>
                            <a href="view_posts.php" class="btn btn-sm btn-primary mt-3">
                                <i class="fas fa-eye me-1"></i>Soma Zaidi
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Hakuna taarifa zilizoidhinishwa.
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="mt-5 mb-5">
            <h2 class="mb-4">
                <i class="fas fa-lightning-bolt me-2"></i>Vitendo Haraka
            </h2>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <a href="post.php" class="btn btn-lg btn-primary w-100">
                        <i class="fas fa-plus-circle me-2"></i>Unda Taarifa Mpya
                    </a>
                </div>
                <div class="col-md-6 mb-3">
                    <a href="view_posts.php" class="btn btn-lg btn-info w-100">
                        <i class="fas fa-newspaper me-2"></i>Angalia Taarifa Zote
                    </a>
                </div>
                
                <?php if ($_SESSION['role'] == 'leader'): ?>
                    <div class="col-md-6 mb-3">
                        <a href="approve_posts.php" class="btn btn-lg btn-warning w-100">
                            <i class="fas fa-check-square me-2"></i>Idhinisha Taarifa
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="manage_users.php" class="btn btn-lg btn-danger w-100">
                            <i class="fas fa-users-cog me-2"></i>Dhibiti Watumiaji
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            margin-right: 20px;
        }

        .stat-info h3 {
            margin: 0;
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0 0 0;
        }

        .post-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid #3498db;
        }

        .post-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .post-header h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }

        .post-header small {
            display: block;
            color: #999;
        }

        .post-content {
            color: #666;
            line-height: 1.6;
            margin-top: 15px;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .stat-card {
                flex-direction: column;
                text-align: center;
            }

            .stat-icon {
                margin-right: 0;
                margin-bottom: 10px;
                width: 60px;
                height: 60px;
            }
        }
    </style>
</body>
</html>

<?php include 'footer.php'; ?>