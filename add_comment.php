<?php
// =====================================================
// WEEK 14: BLOG APPLICATION - ADD COMMENT
// =====================================================
// This file handles adding comments to blog posts
// Only logged-in users can add comments
// This is a processing file (no HTML output)

require_once 'config.php';

// =====================================================
// AUTHENTICATION CHECK
// =====================================================
if (!isLoggedIn()) {
    redirect('login.php', 'Please login to comment.', 'error');
}

// =====================================================
// PROCESS COMMENT FORM
// =====================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get form data
    $blog_id = isset($_POST['blog_id']) ? intval($_POST['blog_id']) : 0;
    $comment_text = sanitize($_POST['comment_text']);
    $user_id = getCurrentUserId();

    // Validation
    if ($blog_id <= 0) {
        redirect('index.php', 'Invalid blog.', 'error');
    }

    if (empty($comment_text)) {
        redirect('blog_details.php?id=' . $blog_id, 'Comment cannot be empty.', 'error');
    }

    // Check if blog exists
    $check_blog = "SELECT id FROM blogs WHERE id = $blog_id";
    $result = mysqli_query($conn, $check_blog);

    if (mysqli_num_rows($result) == 0) {
        redirect('index.php', 'Blog not found.', 'error');
    }

    // =====================================================
    // INSERT COMMENT INTO DATABASE
    // =====================================================
    $insert_query = "INSERT INTO comments (user_id, blog_id, comment_text)
                     VALUES ($user_id, $blog_id, '$comment_text')";

    if (mysqli_query($conn, $insert_query)) {
        redirect('blog_details.php?id=' . $blog_id, 'Comment added successfully!');
    } else {
        redirect('blog_details.php?id=' . $blog_id, 'Failed to add comment.', 'error');
    }

} else {
    // If accessed directly without POST, redirect to home
    redirect('index.php');
}
?>
