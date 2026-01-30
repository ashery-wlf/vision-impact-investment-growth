<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_id']) && isset($_POST['comment'])) {
    $post_id = intval($_POST['post_id']);
    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'];
    
    // Validate input
    if (empty($comment)) {
        $_SESSION['error'] = "Maoni hayawezi kuwa tupu.";
        header("Location: view_posts.php#post-$post_id");
        exit();
    }
    
    if (strlen($comment) > 5000) {
        $_SESSION['error'] = "Maoni ni marefu mno.";
        header("Location: view_posts.php#post-$post_id");
        exit();
    }
    
    try {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $user_id, $comment]);
        
        $_SESSION['success'] = "Maoni yako yameongezwa kwa mafanikio!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Kosa lilotokea wakati wa kuongeza maoni.";
    }
}

header("Location: view_posts.php");
exit();
?>