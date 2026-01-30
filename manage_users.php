<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'leader') {
    header("Location: index.php");
    exit();
}

$success_message = "";
$error_message = "";

// Add new user/leader
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    try {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $full_name = trim($_POST['full_name']);
        $role = $_POST['role'];

        // Validate input
        if (strlen($username) < 3) {
            $error_message = "Jina la watumiaji lazima liwe na herufi 3 au zaidi.";
        } elseif (strlen($password) < 6) {
            $error_message = "Neno la siri lazima liwe na herufi 6 au zaidi.";
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $password_hash, $full_name, $role]);
            $success_message = "Mtumiaji mpya ameongezesha kwa mafanikio!";
        }
    } catch (PDOException $e) {
        $error_message = "Jina la watumiaji linalopo tayari!";
    }
}

// Change role
if (isset($_GET['make_leader'])) {
    $user_id = $_GET['make_leader'];
    $stmt = $conn->prepare("UPDATE users SET role='leader' WHERE id=?");
    $stmt->execute([$user_id]);
    $success_message = "Mtumiaji amefanya kiongozi!";
    header("Refresh: 1; url=manage_users.php");
}
if (isset($_GET['make_member'])) {
    $user_id = $_GET['make_member'];
    $stmt = $conn->prepare("UPDATE users SET role='member' WHERE id=?");
    $stmt->execute([$user_id]);
    $success_message = "Mtumiaji amefanya mwanachama!";
    header("Refresh: 1; url=manage_users.php");
}

// Delete user
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    $success_message = "Mtumiaji amefutwa!";
    header("Refresh: 1; url=manage_users.php");
}

$stmt = $conn->prepare("SELECT * FROM users ORDER BY role DESC, full_name");
$stmt->execute();
$all_users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simamia Watumiaji - VIIG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .users-container {
            padding: 30px 0;
        }

        .users-header {
            background: linear-gradient(135deg, #1a3a52 0%, #0d1f2d 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .users-header h1 {
            color: white;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .users-header p {
            margin: 0;
            opacity: 0.9;
        }

        .users-count {
            background: #3498db;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
            margin-left: 10px;
        }

        .add-user-section {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .add-user-section h3 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .add-user-section h3 i {
            color: #3498db;
            font-size: 1.3rem;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .btn-add {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            justify-content: center;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
            color: white;
            text-decoration: none;
        }

        .users-list-section {
            margin-top: 40px;
        }

        .users-list-header {
            margin-bottom: 20px;
        }

        .users-list-header h3 {
            color: #2c3e50;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0;
        }

        .users-list-header h3 i {
            color: #3498db;
            font-size: 1.3rem;
        }

        .user-card {
            background: white;
            border: none;
            border-left: 5px solid #3498db;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .user-card:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }

        .user-info {
            flex: 1;
            min-width: 200px;
        }

        .user-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .user-username {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .user-role {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .user-role.leader {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        .user-role.member {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }

        .user-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn-action {
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: white;
        }

        .btn-promote {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        }

        .btn-promote:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-demote {
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
        }

        .btn-demote:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(155, 89, 182, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-remove {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }

        .btn-remove:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
            color: white;
            text-decoration: none;
        }

        .no-users {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .no-users i {
            font-size: 4rem;
            color: #bdc3c7;
            margin-bottom: 20px;
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

        @media (max-width: 768px) {
            .user-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .user-actions {
                justify-content: flex-start;
                width: 100%;
            }

            .btn-action {
                flex: 1;
                min-width: 100px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container users-container">
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

        <div class="users-header">
            <h1><i class="fas fa-users-cog me-2"></i>Simamia Watumiaji</h1>
            <p>Dhibiti watumiaji, majina, na hali zao</p>
            <span class="users-count">
                <i class="fas fa-users me-1"></i><?php echo count($all_users); ?> Watumiaji
            </span>
        </div>

        <!-- Add User Form -->
        <div class="add-user-section">
            <h3><i class="fas fa-user-plus"></i>Ongeza Mtu Mpya</h3>
            <form method="POST" onsubmit="return validateForm()">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="username">Jina la Watumiaji</label>
                            <input type="text" class="form-control" id="username" name="username" required minlength="3" placeholder="Ingiza jina la watumiaji">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="full_name">Jina Kamili</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required placeholder="Ingiza jina kamili">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Neno la Siri</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6" placeholder="Ingiza neno la siri (kiwango cha 6 herufi)">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="role">Cheo</label>
                            <select class="form-select" id="role" name="role">
                                <option value="member">Mwanachama</option>
                                <option value="leader">Kiongozi</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" name="add_user" class="btn-add">
                    <i class="fas fa-plus"></i> Ongeza Mtumiaji
                </button>
            </form>
        </div>

        <!-- Users List -->
        <div class="users-list-section">
            <div class="users-list-header">
                <h3><i class="fas fa-list"></i>Orodha ya Watumiaji</h3>
            </div>

            <?php if (count($all_users) > 0): ?>
                <div class="users-list">
                    <?php foreach ($all_users as $user): ?>
                        <div class="user-card">
                            <div class="user-info">
                                <div class="user-name">
                                    <i class="fas fa-user-circle me-2" style="color: #3498db;"></i><?php echo htmlspecialchars($user['full_name']); ?>
                                </div>
                                <div class="user-username">
                                    @<?php echo htmlspecialchars($user['username']); ?>
                                </div>
                                <span class="user-role <?php echo strtolower($user['role']); ?>">
                                    <i class="fas fa-<?php echo $user['role'] == 'leader' ? 'crown' : 'user'; ?> me-1"></i><?php echo ucfirst($user['role']); ?>
                                </span>
                            </div>

                            <div class="user-actions">
                                <?php if ($user['role'] == 'member'): ?>
                                    <a href="manage_users.php?make_leader=<?php echo $user['id']; ?>" class="btn-action btn-promote" title="Fanya Kiongozi">
                                        <i class="fas fa-arrow-up"></i> Fanya Kiongozi
                                    </a>
                                <?php else: ?>
                                    <a href="manage_users.php?make_member=<?php echo $user['id']; ?>" class="btn-action btn-demote" title="Fanya Mwanachama">
                                        <i class="fas fa-arrow-down"></i> Fanya Mwanachama
                                    </a>
                                <?php endif; ?>
                                <a href="manage_users.php?delete_user=<?php echo $user['id']; ?>" class="btn-action btn-remove" onclick="return confirm('Una uhakika kuwa unataka kufuta mtumiaji huyu?')" title="Futa">
                                    <i class="fas fa-trash"></i> Futa
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-users">
                    <i class="fas fa-users"></i>
                    <h3>Hakuna Watumiaji</h3>
                    <p>Ongeza watumiaji wa kwanza kwa kutumia fomu hapo juu.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function validateForm() {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const fullName = document.getElementById('full_name').value.trim();

            if (username.length < 3) {
                alert('Jina la watumiaji lazima liwe na herufi 3 au zaidi.');
                return false;
            }

            if (password.length < 6) {
                alert('Neno la siri lazima liwe na herufi 6 au zaidi.');
                return false;
            }

            if (fullName.length < 2) {
                alert('Jina kamili lazima liwe na herufi 2 au zaidi.');
                return false;
            }

            return true;
        }
    </script>
</body>
</html>