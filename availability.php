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
        $errors[] = 'Check-in date eka ada ho anagathaye date ekak wenna ona.';
    }

    if (!validDate($checkOut) || $checkOut <= $checkIn) {
        $errors[] = 'Check-out date eka check-in date ekata passe thiyenna ona.';
    }

    if ($guests === false || $guests < 1 || $guests > 10) {
        $errors[] = 'Guests ganana 1 saha 10 athara enter karanna.';
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
        <h1>Check Room Availability</h1>

        <?php foreach ($errors as $error): ?>
            <p><?= escape($error) ?></p>
        <?php endforeach; ?>

        <form method="get" action="availability.php" id="availability-form">
            <label for="check_in">Check-in</label><br>
            <input type="date" id="check_in" name="check_in"
                   min="<?= escape($today) ?>"
                   value="<?= escape($checkIn) ?>" required><br><br>

            <label for="check_out">Check-out</label><br>
            <input type="date" id="check_out" name="check_out"
                   min="<?= escape($today) ?>"
                   value="<?= escape($checkOut) ?>" required><br><br>

            <label for="guests">Guests</label><br>
            <input type="number" id="guests" name="guests"
                   min="1" max="10"
                   value="<?= escape($guests ?: 1) ?>" required><br><br>

            <button type="submit">Search Rooms</button>
        </form>

        <?php if ($searched && !$errors): ?>
            <h2>Available Rooms</h2>

            <?php if (!$rooms): ?>
                <p>Me dates walata matching rooms naha. Wena dates try karanna.</p>
            <?php else: ?>
                <div class="room-grid">
                    <?php foreach ($rooms as $room): ?>
                        <article class="room-card">
                            <p class="room-type"><?= escape($room['room_type']) ?></p>
                            <h2><?= escape($room['room_name']) ?></h2>
                            <p><?= escape($room['description']) ?></p>
                            <p><strong>Maximum guests:</strong>
                                <?= escape($room['max_guests']) ?></p>
                            <p class="room-price">
                                Rs. <?= number_format((float)$room['price_per_night'], 2) ?>
                                <small>/ night</small>
                            </p>
                            <a href="booking.php?room_id=<?= (int)$room['room_id'] ?>&amp;check_in=<?= escape($checkIn) ?>&amp;check_out=<?= escape($checkOut) ?>&amp;guests=<?= (int)$guests ?>">
                                Choose this room
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>