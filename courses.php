<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
require_once 'config.php';
$db = new PDO($dsn, $username, $password, $options);

// User ID of the logged-in user
$user_id = $_SESSION['user_id'];

// Fetch courses for the current user using prepared statement
$stmt = $db->prepare("SELECT * FROM courses WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate CSRF token and store in session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            padding: 20px;
        }
        h3 {
            color: #0e4b77;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #0e4b77;
            color: #fff;
        }
        table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        table tbody tr:hover {
            background-color: #e0f7fa;
        }
        form {
            display: inline;
        }
        input[type="submit"] {
            background-color: #0e4b77;
            color: #fff;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #0d4064;
        }
    </style>
</head>
<body>
    <h3>Your Courses</h3>
    <table>
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Course ID</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
            <tr>
                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                <td><?php echo htmlspecialchars($course['course_id']); ?></td>
                <td>
                    <form method="POST" action="delete.php">
                        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['course_id']); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="submit" name="delete_course" value="Delete">
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
