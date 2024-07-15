<?php
session_start();

// Verify CSRF token
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Validate CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCsrfToken($_POST['csrf_token'])) {
    die("CSRF token validation failed.");
}

// Database connection
require_once 'config.php';
$db = new PDO($dsn, $username, $password, $options);

// Delete course if delete_course button is clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_course'])) {
    // Sanitize inputs
    $course_id = htmlspecialchars($_POST['course_id']);
    $user_ID = htmlspecialchars($_SESSION['user_id']);

    // Prepare SQL statement to delete the course
    $sql = "DELETE FROM courses WHERE course_id = :course_id AND user_id = :user_id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['course_id' => $course_id, 'user_id' => $user_ID ]);

    // Check if any rows were affected
    if ($stmt->rowCount() > 0) {
        echo "<p>Course deleted successfully.</p>";
    } else {
        echo "<p>Failed to delete course. Please try again.</p>";
    }
}
?>
