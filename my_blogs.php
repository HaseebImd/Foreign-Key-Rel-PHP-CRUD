<?php
// =====================================================
// WEEK 14: BLOG APPLICATION - MY BLOGS
// =====================================================
// Shows all blogs written by the logged-in user
// Protected page - requires authentication

require_once 'config.php';

// Authentication check
if (!isLoggedIn()) {
    redirect('login.php', 'Please login to view your blogs.', 'error');
}

$user_id = getCurrentUserId();

// Fetch user's blogs
$query = "SELECT * FROM blogs WHERE user_id = $user_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Blogs - BlogApp</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background-color: #f8f9fa; }
        .blog-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .blog-card:hover { transform: translateY(-5px); }
        .blog-image {
            height: 150px;
            object-fit: cover;
            border-radius: 15px 0 0 15px;
        }
    </style>
</head>
<body>

<?php require_once 'navbar.php'; ?>

<div class="container py-5">

    <?php echo showFlashMessage(); ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-earmark-text text-primary me-2"></i>My Blogs</h2>
        <a href="add_blog.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Write New Blog
        </a>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($blog = mysqli_fetch_assoc($result)): ?>
            <div class="card blog-card mb-3">
                <div class="row g-0">
                    <div class="col-md-3">
                        <?php
                        $headerImg = 'uploads/' . $blog['header_image'];
                        if (empty($blog['header_image']) || !file_exists($headerImg)) {
                            $headerImg = 'https://via.placeholder.com/200x150/667eea/ffffff?text=Blog';
                        }
                        ?>
                        <img src="<?php echo $headerImg; ?>" class="blog-image w-100 h-100" alt="Blog">
                    </div>
                    <div class="col-md-9">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($blog['title']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars(truncateText($blog['content'], 150)); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?php echo formatDate($blog['created_at']); ?>
                                </small>
                                <div>
                                    <a href="blog_details.php?id=<?php echo $blog['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                    <a href="delete_blog.php?id=<?php echo $blog['id']; ?>" class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this blog?');">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-journal-x text-muted" style="font-size: 5rem;"></i>
            <h3 class="mt-4 text-muted">No Blogs Yet</h3>
            <p class="text-muted">Start writing your first blog!</p>
            <a href="add_blog.php" class="btn btn-primary btn-lg">
                <i class="bi bi-plus-circle me-2"></i>Write First Blog
            </a>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
