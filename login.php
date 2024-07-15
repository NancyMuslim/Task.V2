<?php
session_start();

// Generate CSRF token and store it in the session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a random token
}

// Validate CSRF token on form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    require_once 'config.php'; 
    $db = new PDO($dsn, $username, $password, $options);

    $name = htmlspecialchars($_POST["username"]);
    $pass = htmlspecialchars($_POST["password"]);

    // Prepare SQL statement to prevent SQL injection
    $sql = "SELECT * FROM users WHERE username=:uname AND password=:pass";
    $stmt = $db->prepare($sql);
    $stmt->execute(array(
        ':uname' => $name,
        ':pass' => $pass
    ));

    $checkeduser = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch as associative array

    if ($checkeduser) {
        $_SESSION['user_id'] = $checkeduser['user_id'];
        $_SESSION['username'] = $checkeduser['username'];
        $_SESSION['role'] = $checkeduser['role'];

        // Redirect based on user role
        if ($checkeduser['role'] === 'admin' || $checkeduser['role'] === 'Admin') {
            header('Location: admin.php');
        } else {
            header('Location: user.php');
        }
        exit();
    } else {
        // Invalid username or password
        echo "<p style='color: red;'>Invalid username or password.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
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

        .login-container {
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
        }

        input[type="text"],
        input[type="password"] {
            width: calc(100% - 16px);
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        button:active {
            background-color: #3e8e41;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Login Form</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">

        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div><br>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div><br>

        <button type="submit">Login</button><br><br>

        Don't have an account? <a href="signup.php">Sign Up</a>

    </form>
</div>
</body>
</html>
