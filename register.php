<?php
require_once '../config/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['username']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    // Securely hash the user's password
    $hashed_password = password_hash($pass, PASSWORD_BCRYPT);

    try {
        $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username' => $user,
            ':email' => $email,
            ':password' => $hashed_password
        ]);
        echo "<p style='color: green; text-align: center; font-weight: bold;'>Registration completed successfully! <a href='login.php'>Login here</a></p>";
    } catch(PDOException $e) {
        // This catch block usually triggers if the username or email already exists in the database
        echo "<p style='color: red; text-align: center; font-weight: bold;'>Error: " . $e->getMessage() . "</p>"; 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Library System</title>
    
    <link rel="stylesheet" href="../css/register.css">
</head>
<body>

    <div class="register-container">
        <h2>Create an Account</h2>
        
        <form method="POST" action="">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            
            <div class="input-group">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            
            <button type="submit">Register</button>
        </form>
        
        <p>Already have an account? <a href="../login/login.php">Login here</a></p>
    </div>

</body>
</html>