<?php
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard.php"); // Redirect after login
            exit;
        } else {
            $_SESSION['error'] = "Invalid username or password.";
           
            header("Location: login.php"); // Redirect back to login
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="logotype"><img src="images/logo.png" alt="logo" srcset="" style="width:100px;"></div>
        <div class="login-box">
            <h2>Acesso</h2>
            <form method="POST">
                <div class="input-group">
                    <span class="icon">&#128100;</span>
                    <input type="email" placeholder="&nbsp;&nbsp;Digite o e-mail de acesso aqui" name="username">
                </div>
                <div class="input-group">
                    <span class="icon">&#128274;</span>
                    <input type="password" placeholder="&nbsp;&nbsp;Digite a sua senha aqui" name="password">
                </div>
                <button type="submit" class="login-btn">Login</button>
                
            </form>
        </div>
    </div>
</body>
</html>

