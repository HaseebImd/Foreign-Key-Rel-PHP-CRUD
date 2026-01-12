<?php
// =====================================================
// WEEK 14: BLOG APPLICATION - DELETE COMMENT
// =====================================================
// This file handles deleting comments
// IMPORTANT: Users can ONLY delete their OWN comments
// This demonstrates authorization (not just authentication)

require_once 'config.php';

// =====================================================
// AUTHENTICATION CHECK
// =====================================================
if (!isLoggedIn()) {
    redirect('login.php', 'Please login first.', 'error');
}

// =====================================================
// GET PARAMETERS FROM URL
// =====================================================
// $_GET['id'] is the comment ID
// $_GET['blog_id'] is used to redirect back to the blog

$comment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$blog_id = isset($_GET['blog_id']) ? intval($_GET['blog_id']) : 0;
$user_id = getCurrentUserId();

// Validate IDs
if ($comment_id <= 0 || $blog_id <= 0) {
    redirect('index.php', 'Invalid request.', 'error');
}

// =====================================================
// AUTHORIZATION CHECK
// =====================================================
// First, check if this comment exists AND belongs to current user
// This is AUTHORIZATION - checking if user has permission to perform action

$check_query = "SELECT id, user_id FROM comments WHERE id = $comment_id";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) == 0) {
    // Comment doesn't exist
    redirect('blog_details.php?id=' . $blog_id, 'Comment not found.', 'error');
}

$comment = mysqli_fetch_assoc($result);

// Check if current user is the owner of this comment
if ($comment['user_id'] != $user_id) {
    // User is trying to delete someone else's comment - NOT ALLOWED!
    redirect('blog_details.php?id=' . $blog_id, 'You can only delete your own comments.', 'error');
}

// =====================================================
// DELETE COMMENT FROM DATABASE
// =====================================================
$delete_query = "DELETE FROM comments WHERE id = $comment_id AND user_id = $user_id";

if (mysqli_query($conn, $delete_query)) {
    redirect('blog_details.php?id=' . $blog_id, 'Comment deleted successfully.');
} else {
    redirect('blog_details.php?id=' . $blog_id, 'Failed to delete comment.', 'error');
}
?>
