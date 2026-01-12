<?php
// =====================================================
// WEEK 14: BLOG APPLICATION - DELETE BLOG
// =====================================================
// Allows users to delete their own blogs
// Due to CASCADE, all comments on the blog are also deleted

require_once 'config.php';

// Authentication check
if (!isLoggedIn()) {
    redirect('login.php', 'Please login first.', 'error');
}

$blog_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = getCurrentUserId();

if ($blog_id <= 0) {
    redirect('my_blogs.php', 'Invalid blog.', 'error');
}

// Check if blog belongs to current user
$check_query = "SELECT id, user_id, header_image FROM blogs WHERE id = $blog_id";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) == 0) {
    redirect('my_blogs.php', 'Blog not found.', 'error');
}

$blog = mysqli_fetch_assoc($result);

if ($blog['user_id'] != $user_id) {
    redirect('my_blogs.php', 'You can only delete your own blogs.', 'error');
}

// Delete blog image if exists
if (!empty($blog['header_image'])) {
    $image_path = 'uploads/' . $blog['header_image'];
    if (file_exists($image_path)) {
        unlink($image_path);  // unlink() deletes a file
    }
}

// Delete blog (CASCADE will delete related comments automatically)
$delete_query = "DELETE FROM blogs WHERE id = $blog_id AND user_id = $user_id";

if (mysqli_query($conn, $delete_query)) {
    redirect('my_blogs.php', 'Blog deleted successfully.');
} else {
    redirect('my_blogs.php', 'Failed to delete blog.', 'error');
}
?>
