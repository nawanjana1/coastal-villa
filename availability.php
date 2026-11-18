<?php
require_once __DIR__ . '/config/database.php';

function escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function validDate($value)
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

    return $date && $date->format('Y-m-d') === $value;
}

$checkIn = $_GET['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? '';
$guests = filter_input(INPUT_GET, 'guests', FILTER_VALIDATE_INT);
$errors = [];
$rooms = [];
$searched = isset($_GET['check_in'], $_GET['check_out'], $_GET['guests']);

if ($searched) {
    $today = date('Y-m-d');

    if (!validDate($checkIn) || $checkIn < $today) {
        $errors[] = 'Check-in date must be today or a future date.';
    }

    if (!validDate($checkOut) || $checkOut <= $checkIn) {
        $errors[] = 'Check-out date must be after the check-in date.';
    }

    if ($guests === false || $guests < 1 || $guests > 10) {
        $errors[] = 'Guest count must be between 1 and 10.';
    }

    if (!$errors) {
        $sql = "
            SELECT r.*
            FROM rooms r
            WHERE r.room_status = 'active'
              AND r.max_guests >= :guests
              AND NOT EXISTS (
                  SELECT 1
                  FROM bookings b
                  WHERE b.room_id = r.room_id
                    AND b.booking_status = 'confirmed'
                    AND b.check_in < :requested_check_out
                    AND b.check_out > :requested_check_in
              )
            ORDER BY r.price_per_night ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'guests' => $guests,
            'requested_check_out' => $checkOut,
            'requested_check_in' => $checkIn,
        ]);

        $rooms = $stmt->fetchAll();
    }
}

$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Availability | Coastal Villa</title>
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
            <p class="hero-label">Plan a little escape</p>
            <h1>Find a room for your dates</h1>
            <p>Choose your dates and group size to see available rooms.</p>
        </div>

        <?php foreach ($errors as $error): ?>
            <p class="form-error"><?= escape($error) ?></p>
        <?php endforeach; ?>

        <form class="search-panel reveal" method="get" action="availability.php" id="availability-form">
            <div>
                <label for="check_in">Check-in</label>
                <input type="date" id="check_in" name="check_in"
                       min="<?= escape($today) ?>"
                       value="<?= escape($checkIn) ?>" required>
            </div>
            <div>
                <label for="check_out">Check-out</label>
                <input type="date" id="check_out" name="check_out"
                       min="<?= escape($today) ?>"
                       value="<?= escape($checkOut) ?>" required>
            </div>
            <div>
                <label for="guests">Guests</label>
                <input type="number" id="guests" name="guests"
                       min="1" max="10"
                       value="<?= escape($guests ?: 1) ?>" required>
            </div>
            <button type="submit">Search rooms</button>
        </form>

        <?php if ($searched && !$errors): ?>
            <div class="page-heading reveal">
                <p class="hero-label">Your stay options</p>
                <h2>Available rooms</h2>
            </div>

            <?php if (!$rooms): ?>
                <p>No rooms match these dates. Try different dates or guest numbers.</p>
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
                                <p><strong>Maximum guests:</strong> <?= (int)$room['max_guests'] ?></p>
                                <p class="room-price">
                                    Rs. <?= number_format((float)$room['price_per_night'], 2) ?>
                                    <small>/ night</small>
                                </p>
                                <a class="card-link" href="booking.php?room_id=<?= (int)$room['room_id'] ?>&amp;check_in=<?= escape($checkIn) ?>&amp;check_out=<?= escape($checkOut) ?>&amp;guests=<?= (int)$guests ?>">
                                    Choose this room
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>

