<?php
// =====================================================
// WEEK 14: BLOG APPLICATION - HOME PAGE
// =====================================================
// This is the main page that displays all blog posts
// Uses JOIN query to get blog author information

require_once 'config.php';

// =====================================================
// FETCH ALL BLOGS WITH AUTHOR INFORMATION
// =====================================================
// JOIN query connects blogs table with users table
// This allows us to get author name along with blog data

$query = "SELECT blogs.*, users.full_name as author_name, users.profile_image as author_image
          FROM blogs
          JOIN users ON blogs.user_id = users.id
          ORDER BY blogs.created_at DESC";

// Execute query
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlogApp - Home</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        .blog-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .blog-image {
            height: 200px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }
        .author-img {
            width: 35px;
            height: 35px;
            object-fit: cover;
        }
        .card-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .card-text {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<?php require_once 'navbar.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Welcome to BlogApp</h1>
        <p class="lead mb-4">Discover amazing stories, share your thoughts, and connect with writers</p>

        <?php if (!isLoggedIn()): ?>
            <a href="signup.php" class="btn btn-light btn-lg px-4 me-2">
                <i class="bi bi-person-plus me-2"></i>Get Started
            </a>
            <a href="login.php" class="btn btn-outline-light btn-lg px-4">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </a>
        <?php else: ?>
            <a href="add_blog.php" class="btn btn-light btn-lg px-4">
                <i class="bi bi-pencil-square me-2"></i>Write a Blog
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- Main Content -->
<div class="container pb-5">

    <!-- Flash Message -->
    <?php echo showFlashMessage(); ?>

    <!-- Section Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-journals me-2 text-primary"></i>Latest Blogs
        </h2>
        <span class="badge bg-primary fs-6">
            <?php echo mysqli_num_rows($result); ?> Posts
        </span>
    </div>

    <!-- Blog Cards Grid -->
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="row g-4">
            <?php while ($blog = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card blog-card">

                        <!-- Blog Header Image -->
                        <?php
                        // Check if header image exists
                        $headerImg = 'uploads/' . $blog['header_image'];
                        if (empty($blog['header_image']) || !file_exists($headerImg)) {
                            // Use placeholder if no image
                            $headerImg = 'https://via.placeholder.com/400x200/667eea/ffffff?text=Blog+Post';
                        }
                        ?>
                        <img src="<?php echo $headerImg; ?>" class="blog-image" alt="<?php echo htmlspecialchars($blog['title']); ?>">

                        <div class="card-body d-flex flex-column">

                            <!-- Blog Title -->
                            <h5 class="card-title fw-bold mb-3">
                                <?php echo htmlspecialchars($blog['title']); ?>
                            </h5>

                            <!-- Blog Excerpt -->
                            <!-- truncateText() limits content to 150 characters -->
                            <p class="card-text text-muted flex-grow-1">
                                <?php echo htmlspecialchars(truncateText($blog['content'], 120)); ?>
                            </p>

                            <!-- Author Info and Date -->
                            <div class="d-flex align-items-center mt-3 pt-3 border-top">
                                <?php
                                // Author profile image
                                $authorImg = 'uploads/' . $blog['author_image'];
                                if (empty($blog['author_image']) || !file_exists($authorImg)) {
                                    $authorImg = 'https://via.placeholder.com/35x35/667eea/ffffff?text=' . substr($blog['author_name'], 0, 1);
                                }
                                ?>
                                <img src="<?php echo $authorImg; ?>" class="rounded-circle author-img me-2" alt="Author">
                                <div class="flex-grow-1">
                                    <small class="fw-bold d-block"><?php echo htmlspecialchars($blog['author_name']); ?></small>
                                    <small class="text-muted"><?php echo formatDate($blog['created_at']); ?></small>
                                </div>
                            </div>

                            <!-- Read More Button -->
                            <a href="blog_details.php?id=<?php echo $blog['id']; ?>" class="btn btn-primary mt-3">
                                <i class="bi bi-arrow-right me-1"></i>Read More
                            </a>

                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <!-- No blogs found message -->
        <div class="text-center py-5">
            <i class="bi bi-journal-x text-muted" style="font-size: 5rem;"></i>
            <h3 class="mt-4 text-muted">No Blogs Yet</h3>
            <p class="text-muted">Be the first one to share your thoughts!</p>
            <?php if (isLoggedIn()): ?>
                <a href="add_blog.php" class="btn btn-primary btn-lg mt-3">
                    <i class="bi bi-plus-circle me-2"></i>Write First Blog
                </a>
            <?php else: ?>
                <a href="signup.php" class="btn btn-primary btn-lg mt-3">
                    <i class="bi bi-person-plus me-2"></i>Sign Up to Write
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Footer -->
<footer class="bg-dark text-white py-4">
    <div class="container text-center">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> BlogApp - Week 14 Project</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
