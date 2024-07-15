<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle form submission to add a new course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    // Sanitize input data
    $course_name = htmlspecialchars($_POST['course_name']);
    $course_id = htmlspecialchars($_POST['course_id']);
    $user_id = $_SESSION['user_id'];

    // Database connection
    require_once 'config.php';
    $db = new PDO($dsn, $username, $password, $options);

    // Prepare SQL statement to insert new course
    $sql = "INSERT INTO courses (user_id, course_name, course_id) VALUES (:user_id, :course_name, :course_id)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':course_name' => $course_name,
        ':course_id' => $course_id
    ]);

}

// Generate CSRF token and store in session if not already set
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
            background-color: #1a1a1a; /* Very deep color */
            color: #fff;
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        h2 {
            color: #4CAF50;
        }

        form {
            max-width: 400px;
            margin: 20px auto;
            background-color: #333;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #fff;
        }

        input[type="text"] {
            width: calc(100% - 16px);
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #555;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .button-container {
            margin-top: 20px;
        }

        .button-container button {
            background-color: #555;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }

        .button-container button:hover {
            background-color: #777;
        }
    </style>
</head>
<body>
    <h2>Manage Courses</h2>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <label for="course_name">Course Name:</label><br>
        <input type="text" id="course_name" name="course_name" required><br>

        <label for="course_id">Course ID:</label><br>
        <input type="text" id="course_id" name="course_id" required><br>

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="submit" name="add_course" value="Add Course">
    </form>

    <div class="button-container">
        <button onclick="window.location.href='courses.php';">Your Courses</button><br><br>
        <button onclick="window.location.href='update.php';">Update User Info</button><br><br>
        <button onclick="window.location.href='login.php';">Log Out</button><br><br>
    </div>

</body>
</html>
