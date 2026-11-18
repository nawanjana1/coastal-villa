<?php
session_start();
require_once __DIR__ . '/config/database.php';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT user_id, name, email, password, role
         FROM users
         WHERE email = ?'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        header('Location: index.php');
        exit;
    }

    $error = 'The email address or password is incorrect.';
}

function escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Coastal Villa</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <a href="index.php">Coastal Villa</a>
    </header>

    <main class="page-shell">
        <h1>Guest Login</h1>

        <?php if (isset($_GET['registered'])): ?>
            <p>Registration successful. Please log in now.</p>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <p><?= escape($error) ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label for="email">Email</label><br>
            <input id="email" name="email" type="email"
                   value="<?= escape($email) ?>" required><br><br>

            <label for="password">Password</label><br>
            <input id="password" name="password" type="password"
                   required><br><br>

            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Register</a></p>
    </main>
</body>
</html>
