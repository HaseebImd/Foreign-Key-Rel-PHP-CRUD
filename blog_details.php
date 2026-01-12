<?php
// =====================================================
// WEEK 14: BLOG APPLICATION - BLOG DETAILS PAGE
// =====================================================
// This page shows full blog content with comments
// Comments are only visible to logged-in users
// Users can only delete their own comments

require_once 'config.php';

// =====================================================
// GET BLOG ID FROM URL
// =====================================================
// $_GET contains URL parameters (e.g., ?id=1)
// intval() converts to integer for security

$blog_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validate blog ID
if ($blog_id <= 0) {
    redirect('index.php', 'Invalid blog ID.', 'error');
}

// =====================================================
// FETCH BLOG WITH AUTHOR INFORMATION
// =====================================================
// JOIN query to get author details along with blog
$blog_query = "SELECT blogs.*, users.full_name as author_name, users.profile_image as author_image, users.bio as author_bio
               FROM blogs
               JOIN users ON blogs.user_id = users.id
               WHERE blogs.id = $blog_id";

$blog_result = mysqli_query($conn, $blog_query);

// Check if blog exists
if (mysqli_num_rows($blog_result) == 0) {
    redirect('index.php', 'Blog not found.', 'error');
}

// Fetch blog data as associative array
$blog = mysqli_fetch_assoc($blog_result);

// =====================================================
// FETCH COMMENTS FOR THIS BLOG
// =====================================================
// Only fetch if user is logged in (comments are hidden for guests)
$comments = [];
$comment_count = 0;

if (isLoggedIn()) {
    // JOIN with users to get commenter's name and image
    $comments_query = "SELECT comments.*, users.full_name as commenter_name, users.profile_image as commenter_image
                       FROM comments
                       JOIN users ON comments.user_id = users.id
                       WHERE comments.blog_id = $blog_id
                       ORDER BY comments.created_at DESC";

    $comments_result = mysqli_query($conn, $comments_query);
    $comment_count = mysqli_num_rows($comments_result);

    // Store comments in array for later use
    while ($row = mysqli_fetch_assoc($comments_result)) {
        $comments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['title']); ?> - BlogApp</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .blog-header-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 15px;
        }
        .blog-content {
            line-height: 1.8;
            font-size: 1.1rem;
        }
        .author-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .author-img-large {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
        .comment-card {
            border-left: 4px solid #667eea;
            background: white;
        }
        .comment-img {
            width: 45px;
            height: 45px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<?php require_once 'navbar.php'; ?>

<!-- Main Content -->
<div class="container py-5">

    <!-- Flash Message -->
    <?php echo showFlashMessage(); ?>

    <div class="row">
        <!-- Main Blog Content -->
        <div class="col-lg-8">

            <!-- Back Button -->
            <a href="index.php" class="btn btn-outline-primary mb-4">
                <i class="bi bi-arrow-left me-2"></i>Back to Blogs
            </a>

            <!-- Blog Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                <!-- Header Image -->
                <?php
                $headerImg = 'uploads/' . $blog['header_image'];
                if (empty($blog['header_image']) || !file_exists($headerImg)) {
                    $headerImg = 'https://via.placeholder.com/800x400/667eea/ffffff?text=' . urlencode($blog['title']);
                }
                ?>
                <img src="<?php echo $headerImg; ?>" class="blog-header-image" alt="<?php echo htmlspecialchars($blog['title']); ?>">

                <div class="card-body p-4 p-md-5">

                    <!-- Blog Title -->
                    <h1 class="fw-bold mb-4"><?php echo htmlspecialchars($blog['title']); ?></h1>

                    <!-- Meta Information -->
                    <div class="d-flex flex-wrap align-items-center mb-4 pb-4 border-bottom">
                        <?php
                        $authorImg = 'uploads/' . $blog['author_image'];
                        if (empty($blog['author_image']) || !file_exists($authorImg)) {
                            $authorImg = 'https://via.placeholder.com/45x45/667eea/ffffff?text=' . substr($blog['author_name'], 0, 1);
                        }
                        ?>
                        <img src="<?php echo $authorImg; ?>" class="rounded-circle me-3" width="45" height="45" style="object-fit: cover;" alt="Author">
                        <div>
                            <span class="fw-bold"><?php echo htmlspecialchars($blog['author_name']); ?></span>
                            <br>
                            <small class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i>
                                <?php echo formatDate($blog['created_at']); ?>
                            </small>
                        </div>
                    </div>

                    <!-- Blog Content -->
                    <!-- nl2br() converts newlines to <br> tags -->
                    <div class="blog-content">
                        <?php echo nl2br(htmlspecialchars($blog['content'])); ?>
                    </div>

                </div>
            </div>

            <!-- =====================================================
                 COMMENTS SECTION
                 =====================================================
                 Only visible to logged-in users
            -->
            <div class="mt-5">
                <h3 class="mb-4">
                    <i class="bi bi-chat-dots me-2"></i>Comments
                    <?php if (isLoggedIn()): ?>
                        <span class="badge bg-primary"><?php echo $comment_count; ?></span>
                    <?php endif; ?>
                </h3>

                <?php if (isLoggedIn()): ?>
                    <!-- Comment Form - Only for logged-in users -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Leave a Comment</h5>
                            <form action="add_comment.php" method="POST">
                                <!-- Hidden input to pass blog ID -->
                                <input type="hidden" name="blog_id" value="<?php echo $blog_id; ?>">

                                <div class="mb-3">
                                    <textarea class="form-control" name="comment_text" rows="3"
                                              placeholder="Write your comment here..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>Post Comment
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Display Comments -->
                    <?php if (count($comments) > 0): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="card comment-card mb-3 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <!-- Commenter Image -->
                                        <?php
                                        $commenterImg = 'uploads/' . $comment['commenter_image'];
                                        if (empty($comment['commenter_image']) || !file_exists($commenterImg)) {
                                            $commenterImg = 'https://via.placeholder.com/45x45/667eea/ffffff?text=' . substr($comment['commenter_name'], 0, 1);
                                        }
                                        ?>
                                        <img src="<?php echo $commenterImg; ?>" class="rounded-circle comment-img me-3" alt="Commenter">

                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($comment['commenter_name']); ?></h6>
                                                    <small class="text-muted"><?php echo timeAgo($comment['created_at']); ?></small>
                                                </div>

                                                <!-- Delete Button - Only for own comments -->
                                                <?php if ($comment['user_id'] == getCurrentUserId()): ?>
                                                    <a href="delete_comment.php?id=<?php echo $comment['id']; ?>&blog_id=<?php echo $blog_id; ?>"
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Are you sure you want to delete this comment?');">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Comment Text -->
                                            <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No comments yet. Be the first to comment!
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Message for non-logged-in users -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-lock text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Login to See Comments</h5>
                            <p class="text-muted">You need to be logged in to view and post comments.</p>
                            <a href="login.php" class="btn btn-primary me-2">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </a>
                            <a href="signup.php" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus me-2"></i>Sign Up
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">

            <!-- Author Card -->
            <div class="card author-card border-0 mb-4">
                <div class="card-body text-center p-4">
                    <h5 class="mb-3">About the Author</h5>
                    <?php
                    $authorImgLarge = 'uploads/' . $blog['author_image'];
                    if (empty($blog['author_image']) || !file_exists($authorImgLarge)) {
                        $authorImgLarge = 'https://via.placeholder.com/80x80/ffffff/667eea?text=' . substr($blog['author_name'], 0, 1);
                    }
                    ?>
                    <img src="<?php echo $authorImgLarge; ?>" class="rounded-circle author-img-large mb-3 border border-3 border-white" alt="Author">
                    <h5 class="mb-2"><?php echo htmlspecialchars($blog['author_name']); ?></h5>
                    <?php if (!empty($blog['author_bio'])): ?>
                        <p class="small opacity-75"><?php echo htmlspecialchars(truncateText($blog['author_bio'], 100)); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Blog Stats -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-bar-chart me-2"></i>Blog Stats
                    </h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span><i class="bi bi-calendar3 me-2 text-primary"></i>Published</span>
                            <span><?php echo formatDate($blog['created_at']); ?></span>
                        </li>
                        <?php if (isLoggedIn()): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><i class="bi bi-chat-dots me-2 text-primary"></i>Comments</span>
                            <span><?php echo $comment_count; ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Share Section -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-share me-2"></i>Share This Blog
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary flex-fill">
                            <i class="bi bi-facebook"></i>
                        </button>
                        <button class="btn btn-outline-info flex-fill">
                            <i class="bi bi-twitter"></i>
                        </button>
                        <button class="btn btn-outline-success flex-fill">
                            <i class="bi bi-whatsapp"></i>
                        </button>
                        <button class="btn btn-outline-secondary flex-fill">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Footer -->
<footer class="bg-dark text-white py-4 mt-5">
    <div class="container text-center">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> BlogApp - Week 14 Project</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
