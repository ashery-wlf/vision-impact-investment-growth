<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'leader') {
    header("Location: index.php");
    exit();
}

$success_message = "";
$error_message = "";

// Approve post
if (isset($_GET['approve'])) {
    $post_id = $_GET['approve'];
    $stmt = $conn->prepare("UPDATE posts SET status='approved' WHERE id=?");
    if ($stmt->execute([$post_id])) {
        $success_message = "Taarifa imeidhinishwa kwa mafanikio!";
        header("Refresh: 2; url=approve_posts.php");
    } else {
        $error_message = "Kosa lilotokea wakati wa idhini.";
    }
}

// Delete post
if (isset($_GET['delete'])) {
    $post_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM posts WHERE id=?");
    if ($stmt->execute([$post_id])) {
        $success_message = "Taarifa imefutwa kwa mafanikio!";
        header("Refresh: 2; url=approve_posts.php");
    } else {
        $error_message = "Kosa lilotokea wakati wa kufuta.";
    }
}

$stmt = $conn->prepare("SELECT posts.*, users.full_name FROM posts 
                        JOIN users ON posts.user_id = users.id 
                        WHERE status='pending' ORDER BY created_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Idhinisha Taarifa - VIIG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .approval-container {
            padding: 30px 0;
        }

        .approval-header {
            background: linear-gradient(135deg, #1a3a52 0%, #0d1f2d 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .approval-header h1 {
            color: white;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .approval-header p {
            margin: 0;
            opacity: 0.9;
        }

        .pending-badge {
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
            margin-left: 10px;
        }

        .post-card {
            background: white;
            border: none;
            border-left: 5px solid #3498db;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .post-card:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }

        .post-card-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .post-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .post-meta {
            display: flex;
            gap: 20px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .post-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .post-meta-item i {
            color: #3498db;
        }

        .post-card-body {
            padding: 20px;
        }

        .post-content {
            color: #555;
            line-height: 1.7;
            margin-bottom: 15px;
        }

        .post-card-footer {
            padding: 15px 20px;
            background: #f8f9fa;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-approve {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            justify-content: center;
            min-width: 150px;
        }

        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-delete {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            justify-content: center;
            min-width: 150px;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
            color: white;
            text-decoration: none;
        }

        .no-posts {
            text-align: center;
            padding: 60px 20px;
        }

        .no-posts i {
            font-size: 4rem;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .no-posts h3 {
            color: #7f8c8d;
            margin-bottom: 10px;
        }

        .no-posts p {
            color: #95a5a6;
            margin-bottom: 0;
        }

        .alert-success, .alert-danger {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #3498db;
            font-weight: 600;
            margin-bottom: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #2980b9;
            transform: translateX(-3px);
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container approval-container">
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Rudi Nyuma
        </a>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="approval-header">
            <h1><i class="fas fa-check-double me-2"></i>Idhinisha Taarifa</h1>
            <p>Kazi yako: Kuchambua na idhini taarifa mbalimbali</p>
            <span class="pending-badge">
                <i class="fas fa-hourglass-half me-1"></i><?php echo count($posts); ?> Zinasubiri
            </span>
        </div>

        <?php if (count($posts) > 0): ?>
            <div class="posts-list">
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <div class="post-card-header">
                            <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                            <div class="post-meta">
                                <div class="post-meta-item">
                                    <i class="fas fa-user"></i>
                                    <span><?php echo htmlspecialchars($post['full_name']); ?></span>
                                </div>
                                <div class="post-meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></span>
                                </div>
                                <div class="post-meta-item">
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-hourglass-half"></i> Inasubiri
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="post-card-body">
                            <div class="post-content">
                                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                            </div>

                            <?php if (!empty($post['file_path'])): ?>
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-paperclip me-2"></i>
                                    <strong>Faili Imeambatanishwa:</strong>
                                    <a href="<?php echo htmlspecialchars($post['file_path']); ?>" target="_blank" class="ms-2">
                                        <i class="fas fa-download"></i> Pakua Faili
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="post-card-footer">
                            <a href="approve_posts.php?approve=<?php echo $post['id']; ?>" class="btn-approve">
                                <i class="fas fa-check"></i> Idhinisha
                            </a>
                            <a href="approve_posts.php?delete=<?php echo $post['id']; ?>" class="btn-delete" onclick="return confirm('Una uhakika kuwa unataka kufuta taarifa hii?')">
                                <i class="fas fa-trash"></i> Futa
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-posts">
                <i class="fas fa-check-circle"></i>
                <h3>Hakuna Taarifa Zinazosubiri Idhini</h3>
                <p>Kazi yako imekamilika! Taarifa zote zimeidhinishwa.</p>
                <a href="dashboard.php" class="btn btn-primary mt-3">
                    <i class="fas fa-home me-2"></i>Rudi Nyuma
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>