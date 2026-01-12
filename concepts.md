# Week 14: Blog Application - Complete Documentation

This document explains all the concepts, code patterns, and functionality used in the Blog Application project.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [File Structure](#2-file-structure)
3. [Database Design & Relationships](#3-database-design--relationships)
4. [PHP Concepts Used](#4-php-concepts-used)
5. [Authentication & Authorization](#5-authentication--authorization)
6. [CRUD Operations](#6-crud-operations)
7. [Bootstrap Integration](#7-bootstrap-integration)
8. [Security Measures](#8-security-measures)
9. [Helper Functions](#9-helper-functions)
10. [How Each Page Works](#10-how-each-page-works)

---

## 1. Project Overview

### What This Application Does

This is a **Blog Application** where users can:
- Register with personal information
- Login/Logout securely
- Write and publish blog posts
- View all blogs on the homepage
- Read full blog details
- Comment on blogs (only if logged in)
- Delete their own comments (only their own!)
- View their profile and blogs

### Key Features

| Feature | Description |
|---------|-------------|
| User Authentication | Signup, Login, Logout using Sessions |
| Blog Management | Create, View, Delete blogs |
| Comments System | Add and delete comments (with authorization) |
| Image Uploads | Profile pictures and blog header images |
| Responsive Design | Bootstrap 5 for mobile-friendly UI |

---

## 2. File Structure

```
Week 14/
├── config.php          # Database connection + helper functions
├── navbar.php          # Navigation bar component (included in all pages)
├── index.php           # Homepage - displays all blogs
├── signup.php          # User registration form
├── login.php           # User login form
├── logout.php          # Handles logout (clears session)
├── profile.php         # User profile page
├── add_blog.php        # Create new blog form
├── blog_details.php    # Single blog view with comments
├── my_blogs.php        # User's own blogs list
├── add_comment.php     # Handles comment submission (no HTML)
├── delete_comment.php  # Handles comment deletion (no HTML)
├── delete_blog.php     # Handles blog deletion (no HTML)
├── database.sql        # Database schema with table definitions
├── concepts.md         # This documentation file
└── uploads/            # Folder for uploaded images
```

### File Types Explained

| Type | Files | Purpose |
|------|-------|---------|
| **Config** | config.php | Central configuration, included everywhere |
| **Components** | navbar.php | Reusable UI component |
| **Pages** | index, signup, login, profile, add_blog, blog_details, my_blogs | Full HTML pages |
| **Handlers** | add_comment, delete_comment, delete_blog, logout | Processing only, no HTML output |

---

## 3. Database Design & Relationships

### Tables Overview

#### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,    -- Unique ID
    full_name VARCHAR(100) NOT NULL,      -- User's name
    email VARCHAR(100) NOT NULL UNIQUE,   -- Must be unique
    password VARCHAR(255) NOT NULL,       -- Hashed password
    phone VARCHAR(20),                    -- Optional
    bio TEXT,                             -- Optional
    profile_image VARCHAR(255),           -- Image filename
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Blogs Table
```sql
CREATE TABLE blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                 -- WHO wrote this blog
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    header_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### Comments Table
```sql
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                 -- WHO wrote this comment
    blog_id INT NOT NULL,                 -- WHICH blog it's on
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE
);
```

### Understanding Relationships

```
                    ONE-TO-MANY RELATIONSHIPS

    USERS (1) ────────────────→ (Many) BLOGS
      │                              │
      │                              │
      ↓                              ↓
    (1)                            (1)
      │                              │
      └─────→ COMMENTS ←─────────────┘
             (Many)      (Many)
```

#### What is a FOREIGN KEY?

A **Foreign Key** is a column that creates a link between two tables.

```sql
FOREIGN KEY (user_id) REFERENCES users(id)
```

This means:
- `user_id` in blogs table MUST exist in `users` table's `id` column
- You cannot insert a blog with `user_id = 5` if there's no user with `id = 5`

#### What is ON DELETE CASCADE?

```sql
ON DELETE CASCADE
```

This means:
- If a user is deleted, ALL their blogs are automatically deleted
- If a blog is deleted, ALL its comments are automatically deleted
- This maintains data integrity (no orphan records)

**Example:**
```
User (id=1) → Writes Blog (id=5) → Has 10 Comments

If User is deleted:
- Blog (id=5) is automatically deleted
- All 10 comments are automatically deleted
```

---

## 4. PHP Concepts Used

### 4.1 Sessions

**What is a Session?**
Sessions store user data on the server. Each user gets a unique session ID.

```php
// Start session (MUST be first line before any HTML)
session_start();

// Store data in session
$_SESSION['user_id'] = 123;
$_SESSION['user_name'] = "Ali";

// Read from session
echo $_SESSION['user_name'];  // Output: Ali

// Check if session variable exists
if (isset($_SESSION['user_id'])) {
    echo "User is logged in";
}

// Destroy session (logout)
session_unset();   // Remove all variables
session_destroy(); // Destroy the session
```

### 4.2 Superglobals

PHP provides special global arrays:

| Variable | Purpose | Example |
|----------|---------|---------|
| `$_GET` | URL parameters | `$_GET['id']` from `?id=5` |
| `$_POST` | Form data (POST method) | `$_POST['email']` |
| `$_FILES` | Uploaded files | `$_FILES['image']['name']` |
| `$_SESSION` | Session data | `$_SESSION['user_id']` |
| `$_SERVER` | Server information | `$_SERVER['REQUEST_METHOD']` |

### 4.3 Include/Require

```php
// Include a file (continues if file not found)
include 'navbar.php';

// Require a file (stops if file not found)
require 'config.php';

// require_once - loads file only ONCE (prevents duplicates)
require_once 'config.php';  // Recommended for config files
```

### 4.4 Conditional Statements

```php
// Standard if-else
if ($age >= 18) {
    echo "Adult";
} elseif ($age >= 13) {
    echo "Teenager";
} else {
    echo "Child";
}

// Ternary operator (short if-else)
$status = ($age >= 18) ? "Adult" : "Minor";

// Null coalescing operator (check if exists)
$name = $_SESSION['name'] ?? 'Guest';
// Same as: $name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest';
```

### 4.5 Loops

```php
// While loop with database results
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['title'];
}

// Foreach loop with array
foreach ($comments as $comment) {
    echo $comment['text'];
}

// For loop
for ($i = 0; $i < 10; $i++) {
    echo $i;
}
```

---

## 5. Authentication & Authorization

### Authentication vs Authorization

| Concept | Question It Answers | Example |
|---------|-------------------|---------|
| **Authentication** | WHO are you? | Login with email/password |
| **Authorization** | WHAT can you do? | Can you delete this comment? |

### Authentication Flow

```
1. User enters email & password
           ↓
2. System checks if email exists in database
           ↓
3. System verifies password using password_verify()
           ↓
4. If correct → Create session with user data
           ↓
5. Redirect to homepage (logged in!)
```

### Code Example - Login

```php
// 1. Get email and password from form
$email = sanitize($_POST['email']);
$password = $_POST['password'];

// 2. Find user by email
$query = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $query);

// 3. Check if user exists
if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);

    // 4. Verify password
    if (password_verify($password, $user['password'])) {
        // 5. Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];

        // 6. Redirect to home
        header("Location: index.php");
        exit();
    }
}
```

### Authorization Example - Delete Comment

```php
// User can ONLY delete their OWN comments

// 1. Get comment and current user ID
$comment_id = $_GET['id'];
$current_user_id = $_SESSION['user_id'];

// 2. Check who owns the comment
$query = "SELECT user_id FROM comments WHERE id = $comment_id";
$result = mysqli_query($conn, $query);
$comment = mysqli_fetch_assoc($result);

// 3. Compare owner with current user
if ($comment['user_id'] != $current_user_id) {
    // NOT the owner - DENY deletion
    echo "You can only delete your own comments!";
    exit();
}

// 4. Owner confirmed - allow deletion
$delete = "DELETE FROM comments WHERE id = $comment_id";
mysqli_query($conn, $delete);
```

---

## 6. CRUD Operations

CRUD = Create, Read, Update, Delete

### CREATE - Adding New Data

```php
// Insert new user
$query = "INSERT INTO users (full_name, email, password)
          VALUES ('$name', '$email', '$hashed_password')";
mysqli_query($conn, $query);

// Get ID of newly created record
$new_id = mysqli_insert_id($conn);
```

### READ - Fetching Data

```php
// Read single record
$query = "SELECT * FROM users WHERE id = 1";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
echo $user['full_name'];

// Read multiple records
$query = "SELECT * FROM blogs ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
while ($blog = mysqli_fetch_assoc($result)) {
    echo $blog['title'];
}
```

### UPDATE - Modifying Data

```php
// Update user's name
$query = "UPDATE users SET full_name = '$new_name' WHERE id = $user_id";
mysqli_query($conn, $query);
```

### DELETE - Removing Data

```php
// Delete a comment
$query = "DELETE FROM comments WHERE id = $comment_id";
mysqli_query($conn, $query);
```

### JOIN Queries

JOIN connects data from multiple tables:

```php
// Get blogs with author names
$query = "SELECT blogs.*, users.full_name as author_name
          FROM blogs
          JOIN users ON blogs.user_id = users.id";
```

**What this does:**
- Gets all columns from `blogs` table
- Also gets `full_name` from `users` table
- Matches rows where `blogs.user_id` equals `users.id`

---

## 7. Bootstrap Integration

### What is Bootstrap?

Bootstrap is a CSS framework that provides pre-built:
- Grid system (responsive layouts)
- Components (cards, buttons, forms, navbars)
- Utilities (spacing, colors, typography)

### Including Bootstrap

```html
<!-- CSS (in <head>) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<!-- JavaScript (before </body>) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
```

### Common Bootstrap Classes Used

| Class | Purpose | Example |
|-------|---------|---------|
| `container` | Centers content with max-width | `<div class="container">` |
| `row` | Creates a horizontal row | `<div class="row">` |
| `col-md-6` | 50% width on medium screens | `<div class="col-md-6">` |
| `card` | Card container | `<div class="card">` |
| `btn btn-primary` | Primary button | `<button class="btn btn-primary">` |
| `form-control` | Styled input field | `<input class="form-control">` |
| `alert alert-danger` | Error message box | `<div class="alert alert-danger">` |
| `mb-3` | Margin bottom 3 units | `<div class="mb-3">` |
| `text-center` | Center text | `<p class="text-center">` |
| `d-flex` | Flexbox container | `<div class="d-flex">` |

### Bootstrap Grid Example

```html
<div class="container">
    <div class="row">
        <div class="col-md-4">Column 1 (33%)</div>
        <div class="col-md-4">Column 2 (33%)</div>
        <div class="col-md-4">Column 3 (33%)</div>
    </div>
</div>
```

---

## 8. Security Measures

### 8.1 Password Hashing

**NEVER store plain text passwords!**

```php
// When user registers - HASH the password
$hashed = password_hash($password, PASSWORD_DEFAULT);
// Result: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

// When user logs in - VERIFY the password
if (password_verify($entered_password, $hashed_from_db)) {
    echo "Password correct!";
}
```

### 8.2 SQL Injection Prevention

**SQL Injection** is when attackers insert malicious SQL code through user input.

```php
// DANGEROUS - vulnerable to SQL injection
$query = "SELECT * FROM users WHERE email = '$email'";

// SAFER - escape special characters
$email = mysqli_real_escape_string($conn, $email);
$query = "SELECT * FROM users WHERE email = '$email'";
```

**What `mysqli_real_escape_string()` does:**
- Converts `'` to `\'`
- Converts `"` to `\"`
- Prevents SQL commands from executing

### 8.3 XSS Prevention

**XSS (Cross-Site Scripting)** is when attackers inject JavaScript through user input.

```php
// User enters: <script>alert('Hacked!')</script>

// DANGEROUS - executes the script
echo $user_input;

// SAFE - converts < > to HTML entities
echo htmlspecialchars($user_input);
// Output: &lt;script&gt;alert('Hacked!')&lt;/script&gt;
```

**Always use `htmlspecialchars()` when displaying user data!**

### 8.4 File Upload Security

```php
// 1. Check for upload errors
if ($_FILES['image']['error'] !== 0) {
    echo "Upload failed";
}

// 2. Validate file extension (whitelist approach)
$allowed = ['jpg', 'jpeg', 'png', 'gif'];
$ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    echo "Invalid file type";
}

// 3. Use unique filename to prevent overwrites
$filename = time() . '_' . $_FILES['image']['name'];

// 4. Use move_uploaded_file() (validates the file came from upload)
move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $filename);
```

---

## 9. Helper Functions

These functions are defined in `config.php` and used throughout the application:

### isLoggedIn()

Checks if user is logged in:

```php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Usage
if (isLoggedIn()) {
    echo "Welcome back!";
} else {
    echo "Please login";
}
```

### getCurrentUserId()

Gets the logged-in user's ID:

```php
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Usage
$user_id = getCurrentUserId();
```

### sanitize()

Cleans user input:

```php
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Usage
$email = sanitize($_POST['email']);
```

### redirect()

Redirects to another page with optional message:

```php
function redirect($url, $message = '', $type = 'success') {
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit();
}

// Usage
redirect('login.php', 'Please login first', 'error');
```

### formatDate()

Formats database date for display:

```php
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Usage
echo formatDate('2024-01-15');  // Output: January 15, 2024
```

### timeAgo()

Shows relative time:

```php
function timeAgo($datetime) {
    // Returns: "5 minutes ago", "2 hours ago", "3 days ago", etc.
}

// Usage
echo timeAgo($comment['created_at']);  // Output: 2 hours ago
```

### truncateText()

Shortens text for previews:

```php
function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Usage
echo truncateText($blog['content'], 100);
```

---

## 10. How Each Page Works

### index.php (Homepage)

```
1. Include config.php (database + functions)
2. Query: Get all blogs with author info using JOIN
3. Display hero section
4. Loop through blogs and display as cards
5. Each card links to blog_details.php?id=X
```

### signup.php

```
1. Check if already logged in → redirect to home
2. If form submitted (POST):
   a. Get form data
   b. Validate all fields
   c. Check if email already exists
   d. Handle profile image upload
   e. Hash password
   f. Insert into database
   g. Redirect to login
3. Display form with Bootstrap styling
```

### login.php

```
1. Check if already logged in → redirect to home
2. If form submitted (POST):
   a. Get email and password
   b. Find user by email in database
   c. Verify password with password_verify()
   d. If correct: Create session, redirect to home
   e. If wrong: Show error message
3. Display login form
```

### blog_details.php

```
1. Get blog ID from URL ($_GET['id'])
2. Fetch blog with author info (JOIN query)
3. If blog not found → redirect to home
4. Display blog content
5. If user logged in:
   a. Fetch all comments for this blog
   b. Show comment form
   c. Display comments with delete button (only for own comments)
6. If user NOT logged in:
   a. Show "Login to see comments" message
```

### add_comment.php

```
1. Check if user is logged in
2. Get blog_id and comment_text from POST
3. Validate inputs
4. Insert comment into database
5. Redirect back to blog_details page
```

### delete_comment.php

```
1. Check if user is logged in
2. Get comment ID from URL
3. Fetch comment from database
4. CHECK: Does this comment belong to current user?
   - YES → Delete comment
   - NO → Show error "You can only delete your own comments"
5. Redirect back to blog_details page
```

---

## Quick Reference Card

### PHP Functions Used

| Function | Purpose |
|----------|---------|
| `session_start()` | Start/resume session |
| `isset()` | Check if variable exists |
| `empty()` | Check if variable is empty |
| `mysqli_connect()` | Connect to database |
| `mysqli_query()` | Execute SQL query |
| `mysqli_fetch_assoc()` | Get row as array |
| `mysqli_num_rows()` | Count result rows |
| `mysqli_insert_id()` | Get last auto-increment ID |
| `mysqli_real_escape_string()` | Escape special characters |
| `password_hash()` | Hash a password |
| `password_verify()` | Verify hashed password |
| `htmlspecialchars()` | Prevent XSS attacks |
| `header()` | Send HTTP headers |
| `move_uploaded_file()` | Move uploaded file |
| `pathinfo()` | Get file info |
| `strtotime()` | Convert string to timestamp |
| `date()` | Format date |

### SQL Keywords Used

| Keyword | Purpose |
|---------|---------|
| `SELECT` | Read data |
| `INSERT INTO` | Add new data |
| `UPDATE` | Modify data |
| `DELETE` | Remove data |
| `WHERE` | Filter results |
| `ORDER BY` | Sort results |
| `JOIN` | Combine tables |
| `FOREIGN KEY` | Create relationship |
| `ON DELETE CASCADE` | Auto-delete related records |

---

## Setup Instructions

1. **Start XAMPP** (Apache + MySQL)

2. **Create Database**:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create new database: `blog_db`
   - Import `database.sql` file

3. **Access Application**:
   - Open browser: http://localhost/Week%2014/

4. **Test the Application**:
   - Register a new account
   - Login with your credentials
   - Write a blog post
   - Add comments
   - Try deleting your own comments

---

**Author:** Week 14 PHP Project
**Framework:** Bootstrap 5
**Backend:** PHP with MySQLi
