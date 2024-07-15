<?php
session_start();

// CSRF protection: Generate token and store in session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a random token
}
$csrf_token = $_SESSION['csrf_token'];

// Database configuration
require_once 'config.php';
$db = new PDO($dsn, $username, $password, $options);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        die("CSRF token validation failed."); // Handle CSRF attack
    }

    // Sanitize and validate inputs
    $name = htmlspecialchars($_POST["username"]);
    $pass = htmlspecialchars($_POST["password"]);
    $role = htmlspecialchars($_POST["role"]);
    $user_id = htmlspecialchars($_POST["user_id"]);

    // Check if the username already exists (prepared statement)
    $checkexist = "SELECT COUNT(*) FROM users WHERE username = :username";
    $stmt = $db->prepare($checkexist);
    $stmt->execute(array(':username' => $name));
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "<p class='error'>Username already exists. Please choose a different username.</p>";
    } else {

        // Insert new user into database (prepared statement)
        $sql = "INSERT INTO users (username, password, role, user_id) VALUES (:uname, :pass, :role, :user_id)";
        $stmt = $db->prepare($sql);
        $stmt->execute(array(':uname' => $name, ':pass' => $pass, ':role' => $role, ':user_id' => $user_id));

        echo "<p class='success'>Registration successful! ></p>";
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        body {
            background-color: #1a1a1a; /* Very deep color */
            color: #fff;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #333;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #fff;
        }

        input[type="text"],
        input[type="password"],
        input[type="number"] {
            width: calc(100% - 12px);
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
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

        input[type="submit"]:active {
            background-color: #3e8e41;
        }

        .error {
            color: #ff6347; /* Tomato color for error messages */
            font-weight: bold;
        }

        .success {
            color: #32cd32; /* Lime green color for success messages */
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sign Up</h1>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </div><br><br>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div><br><br>

            <div class="form-group">
                <label for="user_id">User ID:</label>
                <input type="number" name="user_id" id="user_id">
            </div><br><br>

            <div class="form-group">
                <label for="role">Role:</label>
                <input type="text" name="role" id="role" required>
            </div><br><br>

            <input type="submit" value="Sign Up">
        </form>
    </div>
</body>
</html>
