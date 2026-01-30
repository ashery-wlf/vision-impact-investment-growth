<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    
    // Validate inputs
    if (empty($title)) {
        $error_message = "Kichwa cha taarifa kinahitajika.";
    } elseif (strlen($title) > 255) {
        $error_message = "Kichwa cha taarifa ni kirefu mno.";
    } elseif (empty($content)) {
        $error_message = "Maelezo ya taarifa hayawezi kuwa tupu.";
    } elseif (strlen($content) > 10000) {
        $error_message = "Maelezo ya taarifa ni marefu mno.";
    } else {
        // Handle file upload
        $file_path = '';
        
        if (!empty($_FILES['file']['name'])) {
            $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            // Check file size
            if ($_FILES['file']['size'] > $max_size) {
                $error_message = "Faili ni kubwa mno. Upeo: 5MB.";
            } else {
                $file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                
                // Check file extension
                if (!in_array($file_ext, $allowed_types)) {
                    $error_message = "Aina ya faili haiaruhiwi. Ruhusi: " . implode(', ', $allowed_types);
                } else {
                    // Check MIME type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $_FILES['file']['tmp_name']);
                    finfo_close($finfo);
                    
                    $allowed_mimes = [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'image/jpeg',
                        'image/png',
                        'image/gif'
                    ];
                    
                    if (!in_array($mime_type, $allowed_mimes)) {
                        $error_message = "Aina ya MIME haiaruhiwi.";
                    } else {
                        // Generate safe filename
                        $target_dir = "uploads/";
                        if (!is_dir($target_dir)) {
                            mkdir($target_dir, 0755, true);
                        }
                        
                        $file_name = time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
                        $target_file = $target_dir . $file_name;
                        
                        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                            $file_path = $target_file;
                        } else {
                            $error_message = "Kosa lilotokea wakati wa kuupload faili.";
                        }
                    }
                }
            }
        }
        
        // If no errors, proceed with database insertion
        if (empty($error_message)) {
            try {
                // Determine status based on user role
                $status = ($_SESSION['role'] == 'leader') ? 'approved' : 'pending';
                
                // Use prepared statement to prevent SQL injection
                $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, file_path, status) 
                                        VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $title, $content, $file_path, $status]);
                
                $success_message = "Taarifa imetumwa kwa mafanikio. " . 
                                 ($status == 'pending' ? "Inasubiri idhini ya kiongozi." : "Imetangazwa mara moja.");
            } catch (PDOException $e) {
                $error_message = "Kosa lilotokea: Taarifa haiwezi kusaliwa.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tuma Taarifa - VIIG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .post-container {
            padding: 30px 0;
        }

        .post-header {
            background: linear-gradient(135deg, #1a3a52 0%, #0d1f2d 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .post-header h1 {
            color: white;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .post-form-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: block;
        }

        .form-control, .form-control-file {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .btn-submit {
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

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
            color: white;
            text-decoration: none;
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

        .file-help {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-top: 8px;
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

    <div class="container post-container">
        <a href="view_posts.php" class="back-link">
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

        <div class="post-header">
            <h1><i class="fas fa-pen me-2"></i>Tuma Taarifa Mpya</h1>
            <p>Jaza maelezo ya taarifa utakayotaka kujaza</p>
        </div>

        <div class="post-form-section">
            <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                <div class="form-group mb-3">
                    <label for="title">Kichwa cha Taarifa <span style="color: #e74c3c;">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" required maxlength="255" 
                           placeholder="Ingiza kichwa cha taarifa">
                </div>

                <div class="form-group mb-3">
                    <label for="content">Maelezo ya Taarifa <span style="color: #e74c3c;">*</span></label>
                    <textarea class="form-control" id="content" name="content" rows="8" required maxlength="10000"
                              placeholder="Ingiza maelezo kamili ya taarifa..."></textarea>
                    <small class="text-muted">Upeo: 10,000 herufi</small>
                </div>

                <div class="form-group mb-3">
                    <label for="file">Ambatanisha Faili (Hiari)</label>
                    <input type="file" class="form-control" id="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                    <div class="file-help">
                        <i class="fas fa-info-circle me-1"></i>
                        Ruhusi: PDF, Word, Picha (JPG, PNG, GIF) • Upeo: 5MB
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Tuma Taarifa
                </button>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function validateForm() {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            const file = document.getElementById('file');

            if (title.length < 3) {
                alert('Kichwa cha taarifa lazima kiwe na herufi 3 au zaidi.');
                return false;
            }

            if (content.length < 10) {
                alert('Maelezo ya taarifa lazima yawe na herufi 10 au zaidi.');
                return false;
            }

            if (file.files.length > 0) {
                const fileSize = file.files[0].size;
                const maxSize = 5 * 1024 * 1024; // 5MB

                if (fileSize > maxSize) {
                    alert('Faili ni kubwa mno. Upeo: 5MB');
                    return false;
                }
            }

            return true;
        }
    </script>
</body>
</html>