-- =====================================================
-- Notifications Table
-- =====================================================

CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL COMMENT 'Teacher who sent the notification',
    student_id INT DEFAULT NULL COMMENT 'Student who receives the notification (NULL for broadcast)',
    quiz_id INT DEFAULT NULL COMMENT 'Related quiz/examination',
    title VARCHAR(255) NOT NULL COMMENT 'Notification title/subject',
    message TEXT NOT NULL COMMENT 'Notification message content',
    type ENUM('announcement', 'exam', 'result', 'info', 'warning', 'error') DEFAULT 'announcement',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    is_read TINYINT(1) DEFAULT 0 COMMENT '0 = unread, 1 = read',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_student_id (student_id),
    INDEX idx_quiz_id (quiz_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_type (type),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

