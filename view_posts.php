<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
}
include 'header.php';

// Show only approved posts to members, leaders see all
$sql = "SELECT posts.*, users.full_name FROM posts 
        JOIN users ON posts.user_id = users.id 
        WHERE status='approved' OR ? = 'leader' 
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$_SESSION['role']]);
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-newspaper me-2"></i>Taarifa Zote</h2>
            <a href="post.php" class="btn btn-primary-custom">
                <i class="fas fa-plus me-2"></i>Tuma Taarifa Mpya
            </a>
        </div>

        <!-- Search and Filter -->
        <div class="card-custom card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-custom" placeholder="Tafuta taarifa...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="showPending" <?php if($_SESSION['role']=='leader') echo 'checked'; ?>>
                            <label class="form-check-label" for="showPending">Onyesha zinazosubiri idhini</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php while ($post = $stmt->fetch()): ?>
        <div class="post-box" id="post-<?php echo $post['id']; ?>">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="mb-1"><?php echo $post['title']; ?></h4>
                    <p class="text-muted mb-2">
                        <i class="fas fa-user me-1"></i><?php echo $post['full_name']; ?>
                        <span class="mx-2">•</span>
                        <i class="fas fa-calendar me-1"></i><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
                        <?php if($post['status'] != 'approved'): ?>
                            <span class="ms-2 badge-status badge-pending">Inasubiri Idhini</span>
                        <?php endif; ?>
                    </p>
                </div>
                <?php if($_SESSION['role'] == 'leader' && $post['status'] == 'pending'): ?>
                    <a href="approve_posts.php?approve=<?php echo $post['id']; ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-check me-1"></i>Idhinisha
                    </a>
                <?php endif; ?>
            </div>

            <div class="mt-3 mb-3">
                <?php echo nl2br($post['content']); ?>
            </div>

            <?php if (!empty($post['file_path'])): ?>
            <div class="mt-3">
                <div class="d-flex align-items-center p-3" style="background-color: #f8f9fa; border-radius: 8px;">
                    <i class="fas fa-paperclip fa-2x me-3"></i>
                    <div>
                        <p class="mb-0">Faili Imesakinishwa</p>
                        <a href="<?php echo $post['file_path']; ?>" target="_blank" class="btn btn-outline-primary btn-sm mt-1">
                            <i class="fas fa-download me-1"></i>Pakua Faili
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Comments Section -->
            <div class="mt-4">
                <h5><i class="fas fa-comments me-2"></i>Maoni (<?php
                    $post_id = $post['id'];
                    $comment_stmt = $conn->prepare("SELECT COUNT(*) as count FROM comments WHERE post_id=?");
                    $comment_stmt->execute([$post_id]);
                    $comment_count = $comment_stmt->fetch()['count'];
                    echo $comment_count;
                ?>)</h5>

                <!-- Display Comments -->
                <?php
                $comments_sql = "SELECT comments.*, users.full_name FROM comments 
                                 JOIN users ON comments.user_id = users.id 
                                 WHERE post_id=? ORDER BY created_at";
                $comments_stmt = $conn->prepare($comments_sql);
                $comments_stmt->execute([$post_id]);
                while ($comment = $comments_stmt->fetch()):
                ?>
                <div class="comment-box mt-3">
                    <div class="d-flex justify-content-between">
                        <strong><?php echo $comment['full_name']; ?></strong>
                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></small>
                    </div>
                    <p class="mb-0 mt-2"><?php echo nl2br($comment['comment']); ?></p>
                </div>
                <?php endwhile; ?>

                <!-- Add Comment Form -->
                <form method="POST" action="add_comment.php" class="mt-4">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Ongeza Maoni Yako</label>
                        <textarea name="comment" class="form-control form-control-custom" rows="3" placeholder="Andika maoni yako hapa..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fas fa-paper-plane me-2"></i>Tuma Maoni
                    </button>
                </form>
            </div>
        </div>
        <?php endwhile; ?>

        <?php if($stmt->rowCount() == 0): ?>
        <div class="text-center py-5">
            <i class="fas fa-newspaper fa-5x mb-3" style="color: #ddd;"></i>
            <h4 class="text-muted">Hakuna taarifa zilizopo</h4>
            <p class="text-muted">Kuwa wa kwanza kutuma taarifa kwa kikundi!</p>
            <a href="post.php" class="btn btn-primary-custom mt-2">Tuma Taarifa ya Kwanza</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>