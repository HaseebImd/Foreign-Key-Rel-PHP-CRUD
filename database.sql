-- =====================================================
-- WEEK 14: BLOG APPLICATION DATABASE SCHEMA
-- =====================================================
-- This file creates the database and tables for our blog application
-- Includes: users, blogs, and comments tables with relationships

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS blog_db;

-- Select the database to use
USE blog_db;

-- =====================================================
-- TABLE 1: USERS TABLE
-- =====================================================
-- Stores user registration information
-- Fields: id, full_name, email, password, phone, bio, profile_image, created_at

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,          -- Unique identifier for each user (Auto-increments)
    full_name VARCHAR(100) NOT NULL,            -- User's full name (Required)
    email VARCHAR(100) NOT NULL UNIQUE,         -- Email must be unique (no duplicate accounts)
    password VARCHAR(255) NOT NULL,             -- Stores hashed password (bcrypt needs 60+ chars)
    phone VARCHAR(20),                          -- Optional phone number
    bio TEXT,                                   -- User's biography/about section
    profile_image VARCHAR(255) DEFAULT 'default.png', -- Profile picture filename
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP    -- Auto-set when user registers
);

-- =====================================================
-- TABLE 2: BLOGS TABLE
-- =====================================================
-- Stores blog posts created by users
-- Has a FOREIGN KEY relationship with users table

CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,          -- Unique identifier for each blog
    user_id INT NOT NULL,                       -- Who created this blog (links to users table)
    title VARCHAR(255) NOT NULL,                -- Blog title
    content TEXT NOT NULL,                      -- Blog content/body
    header_image VARCHAR(255),                  -- Header/featured image for the blog
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,   -- When blog was created

    -- FOREIGN KEY CONSTRAINT:
    -- This creates a RELATIONSHIP between blogs and users
    -- user_id in blogs table MUST exist in users table id column
    -- ON DELETE CASCADE: If a user is deleted, all their blogs are also deleted
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLE 3: COMMENTS TABLE
-- =====================================================
-- Stores comments on blog posts
-- Has TWO FOREIGN KEY relationships: with users AND blogs

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,          -- Unique identifier for each comment
    user_id INT NOT NULL,                       -- Who wrote this comment
    blog_id INT NOT NULL,                       -- Which blog this comment belongs to
    comment_text TEXT NOT NULL,                 -- The actual comment content
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,   -- When comment was posted

    -- FOREIGN KEY to users table
    -- Links comment to the user who wrote it
    -- ON DELETE CASCADE: If user is deleted, their comments are also deleted
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    -- FOREIGN KEY to blogs table
    -- Links comment to the blog post
    -- ON DELETE CASCADE: If blog is deleted, all its comments are also deleted
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE
);

-- =====================================================
-- UNDERSTANDING RELATIONSHIPS (For Students)
-- =====================================================

-- 1. ONE-TO-MANY Relationship: users -> blogs
--    One user can have MANY blogs
--    But each blog belongs to only ONE user
--    Example: User "Ali" can write 10 blogs, but each blog has only one author

-- 2. ONE-TO-MANY Relationship: users -> comments
--    One user can write MANY comments
--    But each comment is written by only ONE user

-- 3. ONE-TO-MANY Relationship: blogs -> comments
--    One blog can have MANY comments
--    But each comment belongs to only ONE blog

-- VISUAL REPRESENTATION:
--
--    USERS (1) -----> (Many) BLOGS
--      |                      |
--      |                      |
--      v                      v
--    (1)                    (1)
--      |                      |
--      -----> COMMENTS <------
--           (Many)     (Many)

-- =====================================================
-- SAMPLE DATA (Optional - for testing)
-- =====================================================

-- Sample user (password is 'password123' hashed with bcrypt)
-- INSERT INTO users (full_name, email, password, phone, bio) VALUES
-- ('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '03001234567', 'I am a test user');

-- Sample blog
-- INSERT INTO blogs (user_id, title, content, header_image) VALUES
-- (1, 'My First Blog', 'This is the content of my first blog post. Welcome to my blog!', 'blog1.jpg');

-- Sample comment
-- INSERT INTO comments (user_id, blog_id, comment_text) VALUES
-- (1, 1, 'Great blog post! Keep writing.');

-- =====================================================
-- USEFUL QUERIES FOR LEARNING
-- =====================================================

-- Get all blogs with author name (JOIN query):
-- SELECT blogs.*, users.full_name as author_name
-- FROM blogs
-- JOIN users ON blogs.user_id = users.id;

-- Get all comments for a specific blog with commenter name:
-- SELECT comments.*, users.full_name as commenter_name
-- FROM comments
-- JOIN users ON comments.user_id = users.id
-- WHERE comments.blog_id = 1;

-- Count total blogs by each user:
-- SELECT users.full_name, COUNT(blogs.id) as total_blogs
-- FROM users
-- LEFT JOIN blogs ON users.id = blogs.user_id
-- GROUP BY users.id;
