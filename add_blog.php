<?php
// =====================================================
// WEEK 14: BLOG APPLICATION - ADD NEW BLOG
// =====================================================
// This page allows logged-in users to create new blog posts
// Protected page - requires authentication

require_once 'config.php';

// =====================================================
// AUTHENTICATION CHECK
// =====================================================
// Redirect to login if not logged in
if (!isLoggedIn()) {
    redirect('login.php', 'Please login to write a blog.', 'error');
}

// Initialize variables
$error = '';
$success = '';

// =====================================================
// FORM PROCESSING
// =====================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get form data
    $title = sanitize($_POST['title']);
    $content = sanitize($_POST['content']);
    $user_id = getCurrentUserId();

    // Validation
    if (empty($title) || empty($content)) {
        $error = 'Please fill in all required fields.';
    }
    elseif (strlen($title) < 5) {
        $error = 'Title must be at least 5 characters long.';
    }
    elseif (strlen($content) < 50) {
        $error = 'Content must be at least 50 characters long.';
    }
    else {
        // =====================================================
        // HANDLE HEADER IMAGE UPLOAD
        // =====================================================
        $header_image = '';

        if (isset($_FILES['header_image']) && $_FILES['header_image']['error'] == 0) {

            $file_extension = strtolower(pathinfo($_FILES['header_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($file_extension, $allowed_extensions)) {

                // Create unique filename
                $header_image = 'blog_' . time() . '_' . $_FILES['header_image']['name'];
                $upload_path = 'uploads/' . $header_image;

                // Create uploads directory if needed
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }

                // Move uploaded file
                if (!move_uploaded_file($_FILES['header_image']['tmp_name'], $upload_path)) {
                    $error = 'Failed to upload header image.';
                }
            } else {
                $error = 'Invalid file type. Only JPG, JPEG, PNG, GIF, WEBP are allowed.';
            }
        }

        // =====================================================
        // INSERT BLOG INTO DATABASE
        // =====================================================
        if (empty($error)) {
            $insert_query = "INSERT INTO blogs (user_id, title, content, header_image)
                             VALUES ($user_id, '$title', '$content', '$header_image')";

            if (mysqli_query($conn, $insert_query)) {
                // Get the ID of the newly created blog
                // mysqli_insert_id() returns the auto-generated ID from the last query
                $new_blog_id = mysqli_insert_id($conn);

                redirect('blog_details.php?id=' . $new_blog_id, 'Blog published successfully!');
            } else {
                $error = 'Failed to publish blog: ' . mysqli_error($conn);
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
    <title>Write a Blog - BlogApp</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        .preview-image {
            max-width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        #content {
            min-height: 300px;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<?php require_once 'navbar.php'; ?>

<!-- Main Content -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- Page Header -->
            <div class="d-flex align-items-center mb-4">
                <a href="index.php" class="btn btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h2 class="mb-0">
                        <i class="bi bi-pencil-square text-primary me-2"></i>Write a Blog
                    </h2>
                    <p class="text-muted mb-0">Share your thoughts with the world</p>
                </div>
            </div>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Blog Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <form method="POST" enctype="multipart/form-data">

                        <!-- Blog Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold">
                                <i class="bi bi-type-h1 me-1"></i>Blog Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="title" name="title"
                                   placeholder="Enter an engaging title for your blog"
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                                   required>
                            <small class="text-muted">Minimum 5 characters</small>
                        </div>

                        <!-- Header Image -->
                        <div class="mb-4">
                            <label for="header_image" class="form-label fw-bold">
                                <i class="bi bi-image me-1"></i>Header Image
                            </label>
                            <input type="file" class="form-control" id="header_image" name="header_image"
                                   accept="image/jpeg,image/png,image/gif,image/webp"
                                   onchange="previewImage(this)">
                            <small class="text-muted">Recommended size: 800x400 pixels. Allowed: JPG, PNG, GIF, WEBP</small>

                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <img id="previewImg" class="preview-image" src="" alt="Preview">
                            </div>
                        </div>

                        <!-- Blog Content -->
                        <div class="mb-4">
                            <label for="content" class="form-label fw-bold">
                                <i class="bi bi-body-text me-1"></i>Blog Content <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="content" name="content"
                                      placeholder="Write your blog content here..."
                                      required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                            <small class="text-muted">Minimum 50 characters</small>
                        </div>

                        <!-- Character Count -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span id="charCount" class="text-muted">0 characters</span>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-send me-2"></i>Publish Blog
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary btn-lg">
                                Cancel
                            </a>
                        </div>

                    </form>

                </div>
            </div>

            <!-- Writing Tips -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-lightbulb text-warning me-2"></i>Writing Tips
                    </h5>
                    <ul class="mb-0 text-muted">
                        <li>Use a catchy title that grabs attention</li>
                        <li>Add a relevant header image to make your blog stand out</li>
                        <li>Write clear and engaging content</li>
                        <li>Break long content into paragraphs for readability</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// =====================================================
// JAVASCRIPT FUNCTIONS
// =====================================================

// Image Preview Function
// Called when user selects an image file
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');

    if (input.files && input.files[0]) {
        // FileReader API reads file contents
        const reader = new FileReader();

        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };

        // Read the file as Data URL (base64)
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}

// Character Count Function
// Updates character count as user types
const contentTextarea = document.getElementById('content');
const charCount = document.getElementById('charCount');

contentTextarea.addEventListener('input', function() {
    const count = this.value.length;
    charCount.textContent = count + ' characters';

    // Change color based on minimum requirement
    if (count < 50) {
        charCount.className = 'text-danger';
    } else {
        charCount.className = 'text-success';
    }
});
</script>

</body>
</html>
