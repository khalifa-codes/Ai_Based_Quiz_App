-- =====================================================
-- Quiz System Database Schema
-- Version: 1.0
-- Created: 2024
-- Description: Complete database schema for QuizAura System
-- =====================================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS quiz_system 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE quiz_system;

-- =====================================================
-- Core Tables
-- =====================================================

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    student_id VARCHAR(50) UNIQUE,
    password_hash VARCHAR(255),
    organization_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_student_id (student_id),
    INDEX idx_organization_id (organization_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Teachers Table
CREATE TABLE IF NOT EXISTS teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255),
    organization_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_organization_id (organization_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Organizations Table (if needed)
CREATE TABLE IF NOT EXISTS organizations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    contact VARCHAR(20),
    address TEXT,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Quiz Tables
-- =====================================================

-- Quizzes Table
CREATE TABLE IF NOT EXISTS quizzes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    subject VARCHAR(100),
    description TEXT,
    duration INT NOT NULL COMMENT 'Duration in seconds',
    total_questions INT NOT NULL DEFAULT 0,
    total_marks INT NOT NULL DEFAULT 0,
    created_by INT NOT NULL COMMENT 'teacher_id',
    organization_id INT DEFAULT NULL,
    ai_provider VARCHAR(50) DEFAULT 'gemini' COMMENT 'AI provider for subjective evaluation',
    ai_model VARCHAR(100) DEFAULT NULL COMMENT 'Specific AI model to use',
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_created_by (created_by),
    INDEX idx_status (status),
    INDEX idx_organization_id (organization_id),
    FOREIGN KEY (created_by) REFERENCES teachers(id) ON DELETE RESTRICT,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Questions Table
CREATE TABLE IF NOT EXISTS questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'subjective', 'true_false', 'short_answer') DEFAULT 'multiple_choice',
    question_order INT NOT NULL DEFAULT 0,
    marks INT DEFAULT 1,
    max_marks INT DEFAULT 10 COMMENT 'Maximum marks for subjective questions',
    criteria JSON DEFAULT NULL COMMENT 'Evaluation criteria for subjective questions',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_quiz_id (quiz_id),
    INDEX idx_question_order (quiz_id, question_order),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Question Options (for multiple choice)
CREATE TABLE IF NOT EXISTS question_options (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question_id INT NOT NULL,
    option_text TEXT NOT NULL,
    option_value VARCHAR(10) NOT NULL COMMENT 'Option identifier: 1, 2, 3, 4, etc.',
    is_correct BOOLEAN DEFAULT FALSE,
    option_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_question_id (question_id),
    INDEX idx_option_value (question_id, option_value),
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Submission Tables
-- =====================================================

-- Quiz Submissions
CREATE TABLE IF NOT EXISTS quiz_submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    student_id INT NOT NULL,
    started_at TIMESTAMP NOT NULL,
    submitted_at TIMESTAMP NULL,
    time_taken INT NULL COMMENT 'Time taken in seconds',
    auto_submitted BOOLEAN DEFAULT FALSE,
    auto_submit_reason VARCHAR(255) NULL,
    total_score DECIMAL(5,2) DEFAULT 0.00,
    total_ai_marks DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Total marks from AI evaluation',
    total_max_marks DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Total maximum marks for AI evaluated questions',
    ai_percentage DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Percentage from AI evaluation',
    ai_provider VARCHAR(50) DEFAULT NULL COMMENT 'AI provider used for evaluation',
    ai_model VARCHAR(100) DEFAULT NULL COMMENT 'AI model used for evaluation',
    percentage DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('in_progress', 'submitted', 'auto_submitted') DEFAULT 'in_progress',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_quiz_student (quiz_id, student_id),
    INDEX idx_student_id (student_id),
    INDEX idx_quiz_id (quiz_id),
    INDEX idx_status (status),
    INDEX idx_submitted_at (submitted_at),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE RESTRICT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student Answers
CREATE TABLE IF NOT EXISTS student_answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    submission_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_value TEXT NOT NULL COMMENT 'Selected option value or text answer',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_postponed BOOLEAN DEFAULT FALSE,
    ai_score DECIMAL(5,2) NULL COMMENT 'AI evaluation score for this answer',
    is_correct BOOLEAN DEFAULT NULL COMMENT 'For MCQ: whether answer is correct',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_submission_question (submission_id, question_id),
    INDEX idx_submission_id (submission_id),
    INDEX idx_question_id (question_id),
    INDEX idx_is_postponed (submission_id, is_postponed),
    FOREIGN KEY (submission_id) REFERENCES quiz_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI Evaluation Details
CREATE TABLE IF NOT EXISTS ai_evaluations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    answer_id INT NOT NULL,
    question_id INT NOT NULL COMMENT 'Reference to question for easier queries',
    submission_id INT NOT NULL COMMENT 'Reference to submission for easier queries',
    ai_provider VARCHAR(50) NOT NULL,
    ai_model VARCHAR(100) NOT NULL,
    accuracy_score INT DEFAULT 0,
    completeness_score INT DEFAULT 0,
    clarity_score INT DEFAULT 0,
    logic_score INT DEFAULT 0,
    examples_score INT DEFAULT 0,
    structure_score INT DEFAULT 0,
    total_score DECIMAL(5,2) DEFAULT 0.00,
    total_marks DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Maximum marks for this question',
    feedback TEXT,
    criteria_scores JSON DEFAULT NULL COMMENT 'Detailed criteria scores',
    evaluated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_answer_id (answer_id),
    INDEX idx_question_id (question_id),
    INDEX idx_submission_id (submission_id),
    INDEX idx_provider_model (ai_provider, ai_model),
    FOREIGN KEY (answer_id) REFERENCES student_answers(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (submission_id) REFERENCES quiz_submissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Performance Indexes
-- =====================================================

-- Additional performance indexes
--CREATE INDEX IF NOT EXISTS idx_submission_student_status ON quiz_submissions(student_id, status);
--CREATE INDEX IF NOT EXISTS idx_answers_submission_postponed ON student_answers(submission_id, is_postponed);
--CREATE INDEX IF NOT EXISTS idx_quiz_status_created ON quizzes(status, created_by);
--CREATE INDEX IF NOT EXISTS idx_questions_quiz_order ON questions(quiz_id, question_order);

-- =====================================================
-- Rate Limiting Table (for security)
-- =====================================================

CREATE TABLE IF NOT EXISTS rate_limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    user_type ENUM('student', 'teacher', 'admin') DEFAULT 'student',
    action VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_action (user_id, user_type, action, created_at),
    INDEX idx_ip_action (ip_address, action, created_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Audit Log Table (for security tracking)
-- =====================================================

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    user_type ENUM('student', 'teacher', 'admin', 'organization') DEFAULT 'student',
    action VARCHAR(100) NOT NULL,
    resource_type VARCHAR(50),
    resource_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    details JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id, user_type),
    INDEX idx_action (action, created_at),
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Admin Tables
-- =====================================================

-- Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Credits Table
CREATE TABLE IF NOT EXISTS admin_credits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    credits DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Total credits available',
    used_credits DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Credits used so far',
    remaining_credits DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Remaining credits',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admin_id (admin_id),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Organization Branding Table
-- =====================================================

CREATE TABLE IF NOT EXISTS organization_branding (
    id INT PRIMARY KEY AUTO_INCREMENT,
    organization_id INT NOT NULL,
    logo_url VARCHAR(500) DEFAULT NULL,
    primary_color VARCHAR(7) DEFAULT '#0d6efd',
    secondary_color VARCHAR(7) DEFAULT '#0b5ed7',
    font_family VARCHAR(100) DEFAULT 'Inter',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_org_branding (organization_id),
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- IP Management Table
-- =====================================================

CREATE TABLE IF NOT EXISTS ip_management (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45) NOT NULL,
    ip_type ENUM('whitelist', 'blacklist') DEFAULT 'whitelist',
    description TEXT,
    created_by INT DEFAULT NULL COMMENT 'admin_id',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_ip (ip_address),
    INDEX idx_ip_type (ip_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- End of Schema
-- =====================================================
