<!-- =====================================================
     WEEK 14: BLOG APPLICATION - NAVIGATION BAR COMPONENT
     =====================================================
     This file is INCLUDED in other pages using require_once
     It provides consistent navigation across all pages
     Uses Bootstrap 5 navbar component
-->

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <!-- Brand/Logo -->
        <!-- Links to homepage -->
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-journal-richtext me-2"></i>BlogApp
        </a>

        <!-- Mobile toggle button -->
        <!-- data-bs-toggle and data-bs-target are Bootstrap attributes -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-house-door me-1"></i>Home
                    </a>
                </li>

                <?php if (isLoggedIn()): ?>
                    <!-- Show these links only when user is logged in -->
                    <li class="nav-item">
                        <a class="nav-link" href="add_blog.php">
                            <i class="bi bi-plus-circle me-1"></i>Write Blog
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_blogs.php">
                            <i class="bi bi-file-earmark-text me-1"></i>My Blogs
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Right side of navbar -->
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <!-- User is logged in - show profile and logout -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <!-- Profile image -->
                            <?php
                            $profileImg = getCurrentUserImage();
                            $imgPath = 'uploads/' . $profileImg;
                            // Check if image file exists, else use placeholder
                            if (!file_exists($imgPath) || $profileImg == 'default.png') {
                                $imgPath = 'https://via.placeholder.com/32x32/667eea/ffffff?text=' . substr(getCurrentUserName(), 0, 1);
                            }
                            ?>
                            <img src="<?php echo $imgPath; ?>" alt="Profile" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                            <!-- htmlspecialchars() prevents XSS attacks -->
                            <?php echo htmlspecialchars(getCurrentUserName()); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="my_blogs.php"><i class="bi bi-file-text me-2"></i>My Blogs</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- User is NOT logged in - show login and signup -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-light text-primary px-3 ms-2" href="signup.php">
                            <i class="bi bi-person-plus me-1"></i>Sign Up
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
