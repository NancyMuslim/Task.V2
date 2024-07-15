<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Database configuration
require_once 'config.php';
$db = new PDO($dsn, $username, $password, $options);

// Function to safely escape HTML output
function safeHtml($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Fetch users and their courses from the database (excluding admins)
$usersStmt = $db->prepare("SELECT user_id, username FROM users WHERE role != 'admin'");
$usersStmt->execute();
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch courses for each user
foreach ($users as &$user) {
    $user_id = $user['user_id'];
    $coursesStmt = $db->prepare("SELECT course_id, course_name FROM courses WHERE user_id = :user_id");
    $coursesStmt->execute([':user_id' => $user_id]);
    $user['courses'] = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($user);

// Function to display courses for a user, safely escaping output
function displayCourses($courses) {
    $output = '';
    foreach ($courses as $course) {
        $output .= safeHtml($course['course_name']) . ' (' . safeHtml($course['course_id']) . ')<br>';
    }
    return $output;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            background-color: #1a1a1a; /* Color de fondo muy oscuro */
            color: #fff;
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #fff;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #333;
        }

        tr:nth-child(even) {
            background-color: #555;
        }

        tr:hover {
            background-color: #777;
        }

        .button-container {
            margin-top: 20px;
        }

        .button-container a, .button-container button {
            background-color: #4CAF50;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            margin-right: 10px;
        }

        .button-container a:hover, .button-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Admin Dashboard</h1>

    <h2>Normal Users and Their Courses</h2>
    <table>
        <tr>
            <th>User ID</th>
            <th>Username</th>
            <th>Courses</th>
            <th>Action</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo safeHtml($user['username']); ?></td>
            <td><?php echo safeHtml($user['user_id']); ?></td>
            <td><?php echo displayCourses($user['courses']); ?></td>
            <td><a href="update.php?user_id=<?php echo safeHtml($user['user_id']); ?>">Update</a></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="button-container">
        <a href="update.php">Update User Info</a>
        <a href="logout.php">Logout</a>
    </div>

</body>
</html>
