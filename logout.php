<?php
// =====================================================
// WEEK 14: BLOG APPLICATION - LOGOUT
// =====================================================
// This file handles user logout
// Clears the session and redirects to login page

require_once 'config.php';

// Clear user session
// This calls session_unset() and session_destroy()
clearUserSession();

// Redirect to login page with message
// We need to start a new session for the flash message
session_start();
redirect('login.php', 'You have been logged out successfully.');
?>
