<?php
// =====================================================
// WEEK 14: BLOG APPLICATION - USER REGISTRATION
// =====================================================
// This page handles user registration with 6 fields:
// 1. Full Name (required)
// 2. Email (required, unique)
// 3. Phone (optional)
// 4. Bio (optional)
// 5. Password (required)
// 6. Confirm Password (required)
// 7. Profile Image (optional)

// Include configuration file
// require_once ensures file is loaded only once
require_once 'config.php';

// Redirect to home if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

// Initialize variables
$error = '';
$success = '';

// =====================================================
// FORM PROCESSING
// =====================================================
// Check if form was submitted via POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // =====================================================
    // STEP 1: GET FORM DATA
    // =====================================================
    // $_POST contains form data sent via POST method
    // sanitize() function escapes special characters to prevent SQL injection

    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $bio = sanitize($_POST['bio']);
    $password = $_POST['password'];           // Don't sanitize password yet (will be hashed)
    $confirm_password = $_POST['confirm_password'];

    // =====================================================
    // STEP 2: VALIDATION
    // =====================================================
    // empty() checks if a variable is empty or not set

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields.';
    }
    // Check if passwords match
    elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    }
    // Check password length
    elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    }
    // Validate email format
    // filter_var() with FILTER_VALIDATE_EMAIL checks if email is valid
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    }
    else {
        // =====================================================
        // STEP 3: CHECK IF EMAIL ALREADY EXISTS
        // =====================================================
        // Query database to check for existing email
        $check_email = "SELECT id FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $check_email);

        if (mysqli_num_rows($result) > 0) {
            $error = 'This email is already registered. Please login instead.';
        }
        else {
            // =====================================================
            // STEP 4: HANDLE FILE UPLOAD (Profile Image)
            // =====================================================
            $profile_image = 'default.png'; // Default image

            // Check if file was uploaded
            // $_FILES contains uploaded file information
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {

                // Get file extension
                // pathinfo() extracts information from a path
                $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

                // Allowed file types
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                // Validate file type
                // in_array() checks if a value exists in an array
                if (in_array($file_extension, $allowed_extensions)) {

                    // Create unique filename using time() to prevent conflicts
                    $profile_image = time() . '_' . $_FILES['profile_image']['name'];
                    $upload_path = 'uploads/' . $profile_image;

                    // Create uploads directory if it doesn't exist
                    // is_dir() checks if path is a directory
                    if (!is_dir('uploads')) {
                        mkdir('uploads', 0777, true);  // 0777 = full permissions
                    }

                    // Move uploaded file from temp location to uploads folder
                    // move_uploaded_file() is more secure than copy()
                    if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                        $error = 'Failed to upload profile image.';
                    }
                } else {
                    $error = 'Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.';
                }
            }

            // =====================================================
            // STEP 5: INSERT USER INTO DATABASE
            // =====================================================
            if (empty($error)) {
                // Hash the password for security
                // password_hash() with PASSWORD_DEFAULT uses bcrypt algorithm
                // NEVER store plain text passwords!
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // SQL INSERT query
                $insert_query = "INSERT INTO users (full_name, email, password, phone, bio, profile_image)
                                 VALUES ('$full_name', '$email', '$hashed_password', '$phone', '$bio', '$profile_image')";

                // Execute the query
                if (mysqli_query($conn, $insert_query)) {
                    // Registration successful
                    $success = 'Account created successfully! Redirecting to login...';
                    // Redirect after 2 seconds using header refresh
                    header("refresh:2;url=login.php");
                } else {
                    $error = 'Registration failed: ' . mysqli_error($conn);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - BlogApp</title>

    <!-- Bootstrap 5 CSS -->
    <!-- Bootstrap is a CSS framework that provides pre-built components -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <!-- Icon library for Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* Custom styles to complement Bootstrap */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .signup-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<?php require_once 'navbar.php'; ?>

<!-- Main Content -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <!-- Card Component -->
            <!-- Bootstrap card provides a flexible container -->
            <div class="card signup-card">
                <div class="card-body p-4 p-md-5">

                    <!-- Header -->
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus-fill text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-3">Create Account</h2>
                        <p class="text-muted">Join our blog community</p>
                    </div>

                    <!-- Display Error Message -->
                    <?php if ($error): ?>
                        <!-- alert-danger is Bootstrap class for error styling -->
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Display Success Message -->
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bi bi-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Registration Form -->
                    <!-- enctype="multipart/form-data" is REQUIRED for file uploads -->
                    <form method="POST" enctype="multipart/form-data">

                        <!-- Full Name Field -->
                        <div class="mb-3">
                            <label for="full_name" class="form-label">
                                <i class="bi bi-person me-1"></i>Full Name <span class="text-danger">*</span>
                            </label>
                            <!-- value attribute retains input after form submission -->
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                   placeholder="Enter your full name"
                                   value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                   required>
                        </div>

                        <!-- Email Field -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i>Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="Enter your email"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   required>
                        </div>

                        <!-- Phone Field (Optional) -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="bi bi-phone me-1"></i>Phone Number
                            </label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   placeholder="Enter your phone number"
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>

                        <!-- Bio Field (Optional) -->
                        <div class="mb-3">
                            <label for="bio" class="form-label">
                                <i class="bi bi-card-text me-1"></i>Bio
                            </label>
                            <!-- textarea for multi-line input -->
                            <textarea class="form-control" id="bio" name="bio" rows="3"
                                      placeholder="Tell us about yourself..."><?php echo isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : ''; ?></textarea>
                        </div>

                        <!-- Password Field -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-1"></i>Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Create a password (min 6 characters)" required>
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-lock-fill me-1"></i>Confirm Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                   placeholder="Confirm your password" required>
                        </div>

                        <!-- Profile Image Field -->
                        <div class="mb-4">
                            <label for="profile_image" class="form-label">
                                <i class="bi bi-image me-1"></i>Profile Picture
                            </label>
                            <!-- accept attribute limits file types user can select -->
                            <input type="file" class="form-control" id="profile_image" name="profile_image"
                                   accept="image/jpeg,image/png,image/gif">
                            <small class="text-muted">Allowed: JPG, JPEG, PNG, GIF</small>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-person-plus me-2"></i>Create Account
                        </button>

                    </form>

                    <!-- Login Link -->
                    <div class="text-center mt-4">
                        <p class="mb-0">Already have an account?
                            <a href="login.php" class="text-decoration-none">Login here</a>
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap 5 JavaScript Bundle -->
<!-- Required for interactive components like dropdowns, modals, etc. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
