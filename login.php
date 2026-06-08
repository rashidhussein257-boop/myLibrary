<?php
require_once '../config/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['username']);
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':username' => $user]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($pass, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        // Evaluate user role permissions for correct dashboard redirection
        if ($row['role'] === 'admin') {
            header("Location: ../home/index.php"); // Admin Dashboard
        } else {
            header("Location: ../home/student_index.php"); // Student Dashboard
        }
        exit;
    } else {
        echo "<p style='color: red; text-align: center; font-weight: bold;'>Invalid Username or Password!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library System</title>
    
    <link rel="stylesheet" href="../css/login.css?v=1">
</head>
<body>

    <div class="login-container">
        <h2>Sign In</h2>
        
        <form method="POST" action="">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <p>Don't have an account? <a href="../register/register.php">Register here</a></p>
    </div>

</body>
</html>