<?php
session_start();

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
    <title>Coastal Villa | Stay by the Sea</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php
$basePath = '';
$adminArea = false;
require __DIR__ . '/includes/navigation.php';
?>

    <main>
        <section class="hero">
            <div class="hero-content">
                <p class="hero-label">A relaxing coastal stay</p>
                <h1>Welcome to Coastal Villa</h1>
                <p>
                    Enjoy a comfortable stay, warm hospitality and the beauty
                    of Sri Lanka’s southern coast.
                </p>
                <a class="button-link" href="rooms.php">Explore Our Rooms</a>
            </div>
        </section>

        <section class="page-shell">
            <h2>Your stay, made simple</h2>
            <p>
                Browse our rooms, check the facilities and send a booking
                request for your preferred dates.
            </p>
        </section>
    </main>
</body>
</html>