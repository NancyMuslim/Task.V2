<?php
session_start();

// Check if user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection 
require_once 'config.php';
$db = new PDO($dsn, $username, $password, $options);

// Current user ID
$user_id = $_SESSION['user_id'];

// Function to generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    generateCSRFToken();

    if (isset($_POST['update_name'])) {
        $new_name = sanitizeInput($_POST['new_name']);
        $old_password = sanitizeInput($_POST['old_password']);

        // Verify old password before updating
        $sql = "SELECT password FROM users WHERE user_id = :user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data && password_verify($old_password, $data['password'])) {
            // Old password matches, proceed with updating name
            $sql_update_name = "UPDATE users SET username = :new_name WHERE user_id = :user_id";
            $stmt_update_name = $db->prepare($sql_update_name);
            $stmt_update_name->bindParam(':new_name', $new_name);
            $stmt_update_name->bindParam(':user_id', $user_id);
            $stmt_update_name->execute();

            echo "<p class='success'>Name updated successfully!</p>";
        } else {
            echo "<p class='error'>Incorrect old password. Please try again.</p>";
        }
    }

    // Update password form submission
    if (isset($_POST['update_password'])) {
        $new_password = sanitizeInput($_POST['new_password']);
        $old_password = sanitizeInput($_POST['old_password']);

        // Verify old password before updating
        $sql = "SELECT password FROM users WHERE user_id = :user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data && password_verify($old_password, $data['password'])) {
            // Old password matches, proceed with updating password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_update_password = "UPDATE users SET password = :new_password WHERE user_id = :user_id";
            $stmt_update_password = $db->prepare($sql_update_password);
            $stmt_update_password->bindParam(':new_password', $hashed_password);
            $stmt_update_password->bindParam(':user_id', $user_id);
            $stmt_update_password->execute();

            echo "<p class='success'>Password updated successfully!</p>";
        } else {
            // Old password is incorrect
            echo "<p class='error'>Incorrect old password. Please try again.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User Info</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            color: #333;
            padding: 20px;
        }
        h1, h2 {
            color: #0e4b77;
        }
        form {
            max-width: 400px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 10px;
            color: #333;
        }
        input[type="text"], input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
            box-sizing: border-box;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 8px rgba(76, 175, 80, 0.3);
        }
        input[type="submit"] {
            background-color: #0e4b77;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #0d4064;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <center>
    <h1>Update User Info</h1>
    </center>

    <!-- Update Name Form -->
     <center>
     <h2>Update Name</h2>
     </center>
    <form method="POST">
        <label for="new_name">New Name:</label>
        <input type="text" id="new_name" name="new_name" required><br>

        <label for="old_password">Old Password:</label>
        <input type="password" id="old_password" name="old_password" required><br>
        
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="submit" name="update_name" value="Update Name">
    </form>

    <!-- Update Password Form -->
   <center>
   <h2>Update Password</h2>
   </center>
    <form method="POST">
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required><br>

        <label for="old_password">Old Password:</label>
        <input type="password" id="old_password" name="old_password" required><br>

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="submit" name="update_password" value="Update Password">
    </form>

</body>
</html>
