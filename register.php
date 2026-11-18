<?php
session_start();
require_once __DIR__ . '/config/database.php';

$errors = [];
$name = '';
$email = '';
$contactNumber = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '') {
        $errors[] = 'Please enter your name.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($contactNumber === '') {
        $errors[] = 'Please enter your contact number.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if (!$errors) {
        $check = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
        $check->execute([$email]);

        if ($check->fetch()) {
            $errors[] = 'This email address is already registered.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insert = $pdo->prepare(
                'INSERT INTO users (name, email, contact_number, password)
                 VALUES (?, ?, ?, ?)'
            );
            $insert->execute([$name, $email, $contactNumber, $hashedPassword]);

            header('Location: login.php?registered=1');
            exit;
        }
    }
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
    <title>Register | Coastal Villa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
    <header class="site-header">
        <a href="index.php">Coastal Villa</a>
    </header>

    <main class="page-shell">
        <h1>Create a Guest Account</h1>

        <?php foreach ($errors as $error): ?>
            <p class="form-error"><?= escape($error) ?></p>
        <?php endforeach; ?>

        <form method="post" action="register.php" id="registration-form">
            <label for="name">Full name</label><br>
            <input id="name" name="name" type="text"
                   value="<?= escape($name) ?>" required><br><br>

            <label for="email">Email</label><br>
            <input id="email" name="email" type="email"
                   value="<?= escape($email) ?>" required><br><br>

            <label for="contact_number">Contact number</label><br>
            <input id="contact_number" name="contact_number" type="tel"
                   value="<?= escape($contactNumber) ?>" required><br><br>

            <label for="password">Password (at least 8 characters)</label><br>
            <input id="password" name="password" type="password"
                   minlength="8" required><br><br>

            <button type="submit">Register</button>
        </form>

        <p>Already have an account? <a href="login.php">Login</a></p>
    </main>
</body>
</html>
