<?php
// =====================================================
// WEEK 14: BLOG APPLICATION - USER LOGIN
// =====================================================
// This page handles user authentication
// Uses password_verify() to compare hashed passwords

require_once 'config.php';

// Redirect to home if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

// Initialize error variable
$error = '';

// =====================================================
// FORM PROCESSING
// =====================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get form data
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    // Validation
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    }
    else {
        // =====================================================
        // QUERY DATABASE FOR USER
        // =====================================================
        // Fetch user by email
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);

        // mysqli_num_rows() returns number of rows in result
        if (mysqli_num_rows($result) == 1) {

            // mysqli_fetch_assoc() fetches result as associative array
            $user = mysqli_fetch_assoc($result);

            // =====================================================
            // VERIFY PASSWORD
            // =====================================================
            // password_verify() compares plain password with hashed password
            // Returns true if they match, false otherwise
            if (password_verify($password, $user['password'])) {

                // Password is correct - create session
                setUserSession($user);

                // Redirect to home page
                redirect('index.php', 'Welcome back, ' . $user['full_name'] . '!');

            } else {
                $error = 'Incorrect password. Please try again.';
            }
        } else {
            $error = 'No account found with this email address.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BlogApp</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .login-card {
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
        <div class="col-md-5 col-lg-4">

            <!-- Login Card -->
            <div class="card login-card">
                <div class="card-body p-4 p-md-5">

                    <!-- Header -->
                    <div class="text-center mb-4">
                        <i class="bi bi-box-arrow-in-right text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-3">Welcome Back</h2>
                        <p class="text-muted">Login to your account</p>
                    </div>

                    <!-- Flash Message (from redirects) -->
                    <?php echo showFlashMessage(); ?>

                    <!-- Error Message -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form method="POST">

                        <!-- Email Field -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i>Email Address
                            </label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email"
                                   placeholder="Enter your email"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   required>
                        </div>

                        <!-- Password Field -->
                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-1"></i>Password
                            </label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password"
                                   placeholder="Enter your password" required>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                        </button>

                    </form>

                    <!-- Signup Link -->
                    <div class="text-center mt-4">
                        <p class="mb-0">Don't have an account?
                            <a href="signup.php" class="text-decoration-none">Sign up here</a>
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
