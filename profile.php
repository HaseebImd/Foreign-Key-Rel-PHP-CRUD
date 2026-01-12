<?php
// =====================================================
// WEEK 14: BLOG APPLICATION - USER PROFILE
// =====================================================
// Displays user profile information
// Protected page - requires authentication

require_once 'config.php';

// Authentication check
if (!isLoggedIn()) {
    redirect('login.php', 'Please login to view profile.', 'error');
}

// Get user ID from session
$user_id = getCurrentUserId();

// Fetch user data from database
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Count user's blogs
$blog_count_query = "SELECT COUNT(*) as count FROM blogs WHERE user_id = $user_id";
$blog_result = mysqli_query($conn, $blog_count_query);
$blog_count = mysqli_fetch_assoc($blog_result)['count'];

// Count user's comments
$comment_count_query = "SELECT COUNT(*) as count FROM comments WHERE user_id = $user_id";
$comment_result = mysqli_query($conn, $comment_count_query);
$comment_count = mysqli_fetch_assoc($comment_result)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - BlogApp</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background-color: #f8f9fa; }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 5px solid white;
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>

<?php require_once 'navbar.php'; ?>

<div class="container py-5">

    <?php echo showFlashMessage(); ?>

    <!-- Profile Header -->
    <div class="card profile-header mb-4">
        <div class="card-body p-5 text-center">
            <?php
            $profileImg = 'uploads/' . $user['profile_image'];
            if (empty($user['profile_image']) || !file_exists($profileImg)) {
                $profileImg = 'https://via.placeholder.com/150x150/ffffff/667eea?text=' . substr($user['full_name'], 0, 1);
            }
            ?>
            <img src="<?php echo $profileImg; ?>" class="rounded-circle profile-img mb-3" alt="Profile">
            <h2 class="mb-2"><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <p class="opacity-75 mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
            <?php if (!empty($user['bio'])): ?>
                <p class="mt-3"><?php echo htmlspecialchars($user['bio']); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card stat-card shadow-sm text-center p-4">
                <i class="bi bi-journal-text text-primary" style="font-size: 2.5rem;"></i>
                <h2 class="mt-2 mb-0"><?php echo $blog_count; ?></h2>
                <p class="text-muted mb-0">Blogs Written</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm text-center p-4">
                <i class="bi bi-chat-dots text-success" style="font-size: 2.5rem;"></i>
                <h2 class="mt-2 mb-0"><?php echo $comment_count; ?></h2>
                <p class="text-muted mb-0">Comments Made</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm text-center p-4">
                <i class="bi bi-calendar3 text-info" style="font-size: 2.5rem;"></i>
                <p class="mt-2 mb-0 fw-bold"><?php echo formatDate($user['created_at']); ?></p>
                <p class="text-muted mb-0">Member Since</p>
            </div>
        </div>
    </div>

    <!-- Profile Details -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Profile Details</h5>
        </div>
        <div class="card-body">
            <table class="table table-borderless">
                <tr>
                    <td class="text-muted" width="200">Full Name</td>
                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Email</td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Phone</td>
                    <td><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not provided'; ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Bio</td>
                    <td><?php echo !empty($user['bio']) ? htmlspecialchars($user['bio']) : 'No bio added'; ?></td>
                </tr>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
