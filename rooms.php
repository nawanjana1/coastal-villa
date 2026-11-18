<?php
require_once __DIR__ . '/config/database.php';

$sql = "SELECT room_name, room_type, description, facilities,
               price_per_night, max_guests, image_name
        FROM rooms
        WHERE room_status = 'active'
        ORDER BY price_per_night ASC";

$rooms = $pdo->query($sql)->fetchAll();

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
    <title>Our Rooms | Coastal Villa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
    <?php
    $basePath = '';
    $adminArea = false;
    require __DIR__ . '/includes/navigation.php';
    ?>

    <main class="page-shell">
        <div class="page-heading reveal">
            <p class="hero-label">Find your coastal hideaway</p>
            <h1>Rooms made for slowing down</h1>
            <p>Choose a comfortable room and check the dates for your stay.</p>
        </div>

        <?php if (count($rooms) === 0): ?>
            <p>No active rooms are available at the moment.</p>
        <?php else: ?>
            <div class="room-grid">
                <?php foreach ($rooms as $room): ?>
                    <?php $imageName = $room['image_name'] ?: 'villa-hero.jpg'; ?>
                    <article class="room-card reveal">
                        <img class="room-card__image"
                             src="assets/images/<?= escape(basename($imageName)) ?>"
                             alt="<?= escape($room['room_name']) ?> room"
                             loading="lazy">
                        <div class="room-card__body">
                            <p class="room-type"><?= escape($room['room_type']) ?></p>
                            <h2><?= escape($room['room_name']) ?></h2>
                            <p><?= escape($room['description']) ?></p>
                            <p><strong>Facilities:</strong> <?= escape($room['facilities']) ?></p>
                            <p><strong>Maximum guests:</strong> <?= (int)$room['max_guests'] ?></p>
                            <p class="room-price">
                                Rs. <?= number_format((float)$room['price_per_night'], 2) ?>
                                <small>/ night</small>
                            </p>
                            <a class="card-link" href="availability.php">Check dates</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>

