<?php
session_start();
require 'db_connection.php'; // Include your database connection script

// Initialize an error message variable
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $username_or_email = trim($_POST['credential']);
    $password = $_POST['password'];

    // Prepare and execute SQL statement
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = :username_or_email OR email = :username_or_email)");
    $stmt->execute(['username_or_email' => $username_or_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the user exists
    if ($user) {
        // Verify user credentials
        if (password_verify($password, $user['password'])) {
            // Check if password change is required
            if ($user['needs_password_change']) {
                $_SESSION['email'] = $user['email'];
                header('Location: change_password.php'); // Redirect to password change page
                exit();
            } else {
                // Store user info in session
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];

                // Redirect based on role
                switch (strtolower(trim($user['role']))) {
                    case 'admin':
                        header('Location: admin_dashboard.php');
                        exit(); // Ensure exit after redirect
                    case 'parent':
                        header('Location: parent_dashboard.php');
                        exit(); // Ensure exit after redirect
                    default:
                        $error_message = "Unrecognized user role."; // Fallback if role is unexpected
                        break;
                }
            }
        } else {
            // Password is incorrect
            $error_message = "Invalid login credentials.";
        }
    } else {
        // User does not exist
        $error_message = "Account does not exist.";
    }
}

// Display error message if login fails
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="styles_index.css">
</head>
<body>

<div class="header">
    <img src="../csnlogo.png" alt="CSN Logo"/>
        <h1>Parañaque City Center</h1>
        <h2>for Children with Special Needs</h2>
</div>



<div class="container">
    <h2 class="mt-5 text-center">Parent Portal</h2>
    <?php
    // Assuming $error_message is defined elsewhere in your code
    if (isset($error_message) && !empty($error_message)) {
        echo "<div class='alert alert-danger text-center'>$error_message</div>";
    }
    ?>
    <form action="" method="POST" class="mt-3">
        <!-- Either Email (Parent) or Username (Admin) -->
        <div class="form-group">
            <label for="credential">Email/Username:</label>
            <input type="text" id="credential" name="credential" class="form-control" required autocomplete="off">
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="form-control" required autocomplete="off">
        </div>
        <div class="mt-3 text-center">
            <p>Don't have an account yet? Sign up <a href="pre_registration.php">here</a></p>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
</div>

<div class="footer text-center">
    <p>Privacy Statement | Policy | Contact Us</p>
    <p>2024 by CSN Paranaque. All Rights Reserved.</p>
</div>

</body>
</html>