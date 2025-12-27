<?php
session_start();
require 'connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    $stmt = $pdo->prepare(
        "SELECT id, password, role
         FROM users
         WHERE email = :email
         LIMIT 1"
    );
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        header("Location: dashboard.php");
        exit;
    }

    $error = "Invalid email or password.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dolphin CRM Login</title>
    <link rel="stylesheet" href="../styles/login.css">
</head>
<body>

<header>
    <div class="logo-container">
        <span class="logo-icon">🐬</span>
        <strong>Dolphin CRM</strong>
    </div>
</header>

<main>
    <div class="login-container">
        <h1>Login</h1>

        <?php if ($error): ?>
            <p class="error-msg"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="email" name="email" placeholder="Email address" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit">🔒 Login</button>
        </form>
    </div>
</main>

<footer>
    <hr>
    <p>&copy; 2022 Dolphin CRM</p>
</footer>

</body>
</html>
