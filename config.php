<?php
// =====================================================
// WEEK 14: BLOG APPLICATION - CONFIGURATION FILE
// =====================================================
// This file contains:
// 1. Database connection
// 2. Session configuration
// 3. Helper functions for authentication and utilities

// =====================================================
// SESSION CONFIGURATION
// =====================================================
// session_start() creates or resumes a session
// MUST be called before any HTML output
// Sessions are more secure than cookies for storing user data
session_start();

// =====================================================
// DATABASE CONNECTION
// =====================================================
// mysqli_connect() establishes connection to MySQL database
// Parameters: hostname, username, password, database_name

$host = 'localhost';        // Database server (localhost for local development)
$username = 'root';         // MySQL username
$password = '';             // MySQL password (empty for XAMPP default)
$database = 'blog_db';      // Our blog database name

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check if connection failed
// mysqli_connect_error() returns error message if connection fails
if (mysqli_connect_error()) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// =====================================================
// HELPER FUNCTIONS
// =====================================================

/**
 * Check if user is logged in
 * Uses SESSION instead of cookies (more secure)
 *
 * @return boolean - true if logged in, false otherwise
 */
function isLoggedIn() {
    // isset() checks if a variable exists and is not NULL
    return isset($_SESSION['user_id']);
}

/**
 * Get current logged in user's ID
 *
 * @return int|null - User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current logged in user's name
 *
 * @return string|null - User name or null if not logged in
 */
function getCurrentUserName() {
    return $_SESSION['user_name'] ?? null;
}

/**
 * Get current logged in user's profile image
 *
 * @return string - Profile image filename or default.png
 */
function getCurrentUserImage() {
    return $_SESSION['user_image'] ?? 'default.png';
}

/**
 * Set user session after successful login
 * Stores user data in SESSION for access throughout the application
 *
 * @param array $user - User data from database
 */
function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_image'] = $user['profile_image'];
}

/**
 * Clear user session (logout)
 * session_destroy() removes all session data
 * session_unset() frees all session variables
 */
function clearUserSession() {
    session_unset();    // Remove all session variables
    session_destroy();  // Destroy the session
}

/**
 * Redirect to a page with optional message
 * header() sends a raw HTTP header
 *
 * @param string $url - URL to redirect to
 * @param string $message - Optional success/error message
 * @param string $type - Message type: 'success' or 'error'
 */
function redirect($url, $message = '', $type = 'success') {
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit(); // Always call exit after header redirect
}

/**
 * Display flash message if exists
 * Flash messages are shown once then deleted
 *
 * @return string - HTML for the message or empty string
 */
function showFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';

        // Determine Bootstrap alert class based on type
        $alertClass = ($type == 'error') ? 'alert-danger' : 'alert-success';

        // Clear the message so it only shows once
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);

        return "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
                    " . htmlspecialchars($message) . "
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
    }
    return '';
}

/**
 * Sanitize input data to prevent SQL Injection
 * mysqli_real_escape_string() escapes special characters
 *
 * @param string $data - Input data to sanitize
 * @return string - Sanitized data
 */
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

/**
 * Format date for display
 * strtotime() converts string to timestamp
 * date() formats timestamp to readable date
 *
 * @param string $date - Date string from database
 * @return string - Formatted date
 */
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Format date with time
 *
 * @param string $date - Date string from database
 * @return string - Formatted date with time
 */
function formatDateTime($date) {
    return date('F j, Y \a\t g:i A', strtotime($date));
}

/**
 * Truncate text to specified length
 * Useful for showing blog excerpts on homepage
 *
 * @param string $text - Text to truncate
 * @param int $length - Maximum length
 * @return string - Truncated text with ... if needed
 */
function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Get time ago string (e.g., "2 hours ago")
 * Useful for comments and posts
 *
 * @param string $datetime - Date string from database
 * @return string - Time ago string
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime);
    }
}
?>
