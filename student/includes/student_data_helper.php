<?php
/**
 * Shared data helpers for student-facing modules.
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Fetch the authenticated student's profile row.
 */
function fetchStudentProfile(PDO $conn, int $studentId): ?array
{
    $stmt = $conn->prepare("
        SELECT id, name, email, student_id, created_at, organization_id
        FROM students
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$studentId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    return $profile ?: null;
}

/**
 * Calculate aggregate stats for dashboard cards.
 */
function fetchStudentStats(PDO $conn, int $studentId): array
{
    $stats = [
        'total_quizzes' => 0,
        'completed_quizzes' => 0,
        'pending_quizzes' => 0,
        'in_progress' => 0,
        'average_score' => 0,
    ];

    $stats['total_quizzes'] = (int)$conn->query("
        SELECT COUNT(*) FROM quizzes WHERE status = 'published'
    ")->fetchColumn();

    $submissionStmt = $conn->prepare("
        SELECT status
        FROM quiz_submissions
        WHERE student_id = ?
    ");
    $submissionStmt->execute([$studentId]);

    $completed = 0;
    $inProgress = 0;

    while ($row = $submissionStmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['status'] === 'in_progress') {
            $inProgress++;
        }
        if (in_array($row['status'], ['submitted', 'auto_submitted'], true)) {
            $completed++;
        }
    }

    $stats['completed_quizzes'] = $completed;
    $stats['in_progress'] = $inProgress;
    $stats['pending_quizzes'] = max(0, $stats['total_quizzes'] - $completed - $inProgress);

    $scoreStmt = $conn->prepare("
        SELECT q.total_marks, qs.total_score, qs.percentage
        FROM quiz_submissions qs
        JOIN quizzes q ON q.id = qs.quiz_id
        WHERE qs.student_id = ?
          AND qs.status IN ('submitted', 'auto_submitted')
    ");
    $scoreStmt->execute([$studentId]);

    $scores = [];
    while ($row = $scoreStmt->fetch(PDO::FETCH_ASSOC)) {
        $score = calculateSubmissionPercentage($row['total_score'], $row['total_marks'], $row['percentage']);
        if ($score !== null) {
            $scores[] = $score;
        }
    }
    if (!empty($scores)) {
        $stats['average_score'] = round(array_sum($scores) / count($scores));
    }

    return $stats;
}

/**
 * Fetch recent submissions for tables/charts.
 */
function fetchStudentRecentSubmissions(PDO $conn, int $studentId, ?int $limit = 5): array
{
    $sql = "
        SELECT
            qs.id AS submission_id,
            qs.quiz_id,
            qs.status,
            qs.started_at,
            qs.submitted_at,
            qs.time_taken,
            qs.total_score,
            qs.percentage,
            qs.auto_submitted,
            q.title,
            q.subject,
            q.total_questions,
            q.total_marks,
            q.duration
        FROM quiz_submissions qs
        JOIN quizzes q ON q.id = qs.quiz_id
        WHERE qs.student_id = ?
        ORDER BY COALESCE(qs.submitted_at, qs.started_at) DESC
    ";

    if ($limit !== null) {
        $sql .= " LIMIT " . (int)$limit;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute([$studentId]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$row) {
        $row['score_percent'] = calculateSubmissionPercentage(
            $row['total_score'],
            $row['total_marks'],
            $row['percentage']
        );
        $row['score_category'] = categorizeScore($row['score_percent']);
    }
    unset($row);

    return $rows;
}

/**
 * Fetch published quizzes that the student has not completed yet.
 */
function fetchStudentUpcomingQuizzes(PDO $conn, int $studentId, ?int $limit = 5): array
{
    $sql = "
        SELECT
            q.id,
            q.title,
            q.subject,
            q.duration,
            q.created_at
        FROM quizzes q
        LEFT JOIN quiz_submissions qs
            ON qs.quiz_id = q.id
           AND qs.student_id = ?
        WHERE q.status = 'published'
          AND (
                qs.id IS NULL
                OR qs.status NOT IN ('submitted', 'auto_submitted')
              )
        ORDER BY q.created_at DESC
    ";

    if ($limit !== null) {
        $sql .= " LIMIT " . (int)$limit;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute([$studentId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch aggregated correctness stats for a list of submissions.
 */
function fetchSubmissionAnswerStats(PDO $conn, array $submissionIds): array
{
    if (empty($submissionIds)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($submissionIds), '?'));
    $sql = "
        SELECT
            sa.submission_id,
            SUM(
                CASE
                    WHEN q.question_type IN ('multiple_choice', 'true_false')
                         AND qo.is_correct = 1 THEN 1
                    ELSE 0
                END
            ) AS correct_answers,
            SUM(
                CASE
                    WHEN q.question_type IN ('multiple_choice', 'true_false')
                         AND sa.answer_value IS NOT NULL
                         AND sa.answer_value <> ''
                         AND sa.is_postponed = 0 THEN 1
                    ELSE 0
                END
            ) AS attempted_mcq,
            SUM(
                CASE
                    WHEN sa.answer_value IS NOT NULL
                         AND sa.answer_value <> ''
                         AND sa.is_postponed = 0 THEN 1
                    ELSE 0
                END
            ) AS answered_total
        FROM student_answers sa
        JOIN questions q ON q.id = sa.question_id
        LEFT JOIN question_options qo
            ON qo.question_id = sa.question_id
           AND qo.option_value = sa.answer_value
           AND qo.is_correct = 1
        WHERE sa.submission_id IN ($placeholders)
        GROUP BY sa.submission_id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($submissionIds);

    $stats = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stats[(int)$row['submission_id']] = [
            'correct' => (int)$row['correct_answers'],
            'attempted' => (int)$row['attempted_mcq'],
            'answered_total' => (int)$row['answered_total'],
        ];
    }

    return $stats;
}

/**
 * Build notification items based on recent submissions and upcoming exams.
 */
function buildStudentNotifications(array $recentSubmissions, array $upcomingQuizzes): array
{
    $notifications = [];

    foreach ($recentSubmissions as $submission) {
        if (!in_array($submission['status'], ['submitted', 'auto_submitted'], true)) {
            continue;
        }
        if (empty($submission['submitted_at'])) {
            continue;
        }
        $scoreText = $submission['score_percent'] !== null
            ? number_format($submission['score_percent'], 2) . '%'
            : 'completed';

        $notifications[] = [
            'type' => 'result',
            'title' => 'Result Published',
            'description' => sprintf(
                '%s – your score is %s.',
                $submission['title'],
                $scoreText
            ),
            'timestamp' => $submission['submitted_at'],
            'url' => sprintf('results/result_detail.php?id=%d', $submission['submission_id']),
        ];
    }

    foreach ($upcomingQuizzes as $quiz) {
        $notifications[] = [
            'type' => 'exam',
            'title' => 'New Examination Available',
            'description' => sprintf(
                '%s (%s) is ready to attempt.',
                $quiz['title'],
                $quiz['subject'] ?: 'General'
            ),
            'timestamp' => $quiz['created_at'],
            'url' => sprintf('quizzes/quiz_instructions.php?id=%d', $quiz['id']),
        ];
    }

    usort($notifications, function ($a, $b) {
        $timeA = !empty($a['timestamp']) ? strtotime($a['timestamp']) : 0;
        $timeB = !empty($b['timestamp']) ? strtotime($b['timestamp']) : 0;
        return $timeB <=> $timeA;
    });

    return $notifications;
}

/**
 * Fetch full submission detail including questions, answers, and AI evaluation.
 */
function fetchSubmissionDetail(PDO $conn, int $submissionId, int $studentId): ?array
{
    $stmt = $conn->prepare("
        SELECT
            qs.*,
            q.title,
            q.subject,
            q.total_questions,
            q.total_marks,
            q.duration
        FROM quiz_submissions qs
        JOIN quizzes q ON q.id = qs.quiz_id
        WHERE qs.id = ? AND qs.student_id = ?
        LIMIT 1
    ");
    $stmt->execute([$submissionId, $studentId]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$submission) {
        return null;
    }

    $questionStmt = $conn->prepare("
        SELECT
            q.id AS question_id,
            q.question_text,
            q.question_type,
            q.question_order,
            q.marks,
            q.max_marks,
            q.criteria,
            sa.id AS answer_id,
            sa.answer_value,
            sa.is_postponed,
            sa.ai_score,
            sa.is_correct
        FROM questions q
        LEFT JOIN student_answers sa
            ON sa.question_id = q.id
           AND sa.submission_id = ?
        WHERE q.quiz_id = ?
        ORDER BY q.question_order ASC
    ");
    $questionStmt->execute([$submissionId, $submission['quiz_id']]);
    $questionRows = $questionStmt->fetchAll(PDO::FETCH_ASSOC);

    $questionIds = array_column($questionRows, 'question_id');
    $optionsMap = [];

    if (!empty($questionIds)) {
        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        $optionStmt = $conn->prepare("
            SELECT
                question_id,
                option_text,
                option_value,
                is_correct,
                option_order
            FROM question_options
            WHERE question_id IN ($placeholders)
            ORDER BY option_order ASC, option_value ASC
        ");
        $optionStmt->execute($questionIds);
        while ($option = $optionStmt->fetch(PDO::FETCH_ASSOC)) {
            $qid = (int)$option['question_id'];
            if (!isset($optionsMap[$qid])) {
                $optionsMap[$qid] = [];
            }
            $optionsMap[$qid][] = $option;
        }
    }

    $aiStmt = $conn->prepare("
        SELECT *
        FROM ai_evaluations
        WHERE submission_id = ?
    ");
    $aiStmt->execute([$submissionId]);

    $aiEvaluations = [];
    while ($row = $aiStmt->fetch(PDO::FETCH_ASSOC)) {
        $answerId = (int)$row['answer_id'];
        if (!empty($row['criteria_scores']) && is_string($row['criteria_scores'])) {
            $decoded = json_decode($row['criteria_scores'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $row['criteria_scores'] = $decoded;
            }
        }
        $aiEvaluations[$answerId] = $row;
    }

    $questionDetails = [];
    $answeredCount = 0;
    $correctCount = 0;

    foreach ($questionRows as $row) {
        $questionId = (int)$row['question_id'];
        $options = $optionsMap[$questionId] ?? [];

        $correctOptionValue = null;
        $correctOptionText = null;
        foreach ($options as $option) {
            if ((int)$option['is_correct'] === 1) {
                $correctOptionValue = $option['option_value'];
                $correctOptionText = $option['option_text'];
                break;
            }
        }

        $answerValue = $row['answer_value'];
        $studentAnswerText = $answerValue;

        if (in_array($row['question_type'], ['multiple_choice', 'true_false'], true) && $answerValue !== null) {
            foreach ($options as $option) {
                if ($option['option_value'] === $answerValue) {
                    $studentAnswerText = $option['option_text'];
                    break;
                }
            }
        }

        $status = 'unanswered';
        $isCorrect = false;
        if ($answerValue !== null && $answerValue !== '') {
            $answeredCount++;
            if (in_array($row['question_type'], ['multiple_choice', 'true_false'], true)) {
                $isCorrect = ($correctOptionValue !== null && $answerValue === $correctOptionValue);
                if ($isCorrect) {
                    $correctCount++;
                }
                $status = $isCorrect ? 'correct' : 'incorrect';
            } else {
                $status = 'answered';
            }
        }

        $aiData = null;
        if (!empty($row['answer_id']) && isset($aiEvaluations[$row['answer_id']])) {
            $aiData = $aiEvaluations[$row['answer_id']];
        }

        $questionDetails[] = [
            'id' => $questionId,
            'order' => (int)$row['question_order'],
            'text' => $row['question_text'],
            'type' => $row['question_type'],
            'marks' => (int)$row['marks'],
            'max_marks' => (int)$row['max_marks'],
            'student_answer_value' => $answerValue,
            'student_answer_text' => $studentAnswerText,
            'correct_answer_value' => $correctOptionValue,
            'correct_answer_text' => $correctOptionText,
            'status' => $status,
            'options' => $options,
            'ai' => $aiData,
        ];
    }

    return [
        'submission' => $submission,
        'questions' => $questionDetails,
        'summary' => [
            'total_questions' => count($questionDetails),
            'answered' => $answeredCount,
            'correct' => $correctCount,
            'incorrect' => max(0, $answeredCount - $correctCount),
        ],
    ];
}

/**
 * Helper: calculate percentage with fallbacks.
 */
function calculateSubmissionPercentage($totalScore, $totalMarks, $storedPercentage = null): ?float
{
    if ($totalMarks > 0 && $totalScore !== null) {
        return round(($totalScore / $totalMarks) * 100, 2);
    }
    if ($storedPercentage !== null) {
        return (float)$storedPercentage;
    }
    return null;
}

/**
 * Helper: categorize score ranges.
 */
function categorizeScore(?float $score): string
{
    if ($score === null) {
        return 'pending';
    }
    if ($score >= 90) {
        return 'excellent';
    }
    if ($score >= 70) {
        return 'passed';
    }
    if ($score >= 50) {
        return 'average';
    }
    return 'failed';
}

/**
 * Helper: format timestamps into human friendly labels.
 */
function formatRelativeTime(?string $dateTime): string
{
    if (empty($dateTime)) {
        return '—';
    }

    $timestamp = strtotime($dateTime);
    if ($timestamp === false) {
        return date('M d, Y', strtotime($dateTime));
    }

    $diff = time() - $timestamp;
    if ($diff < 60) {
        return 'just now';
    }
    if ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    }
    if ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    }
    if ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }

    return date('M d, Y', $timestamp);
}

