<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

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

$roomId = filter_input(INPUT_GET, 'room_id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
}

if (!$roomId) {
    exit('Invalid room selection.');
}

$roomStmt = $pdo->prepare(
    "SELECT * FROM rooms WHERE room_id = ? AND room_status = 'active'"
);
$roomStmt->execute([$roomId]);
$room = $roomStmt->fetch();

if (!$room) {
    exit('Room eka hoyaganna ba, nathnam active naha.');
}

$userStmt = $pdo->prepare(
    'SELECT name, email, contact_number FROM users WHERE user_id = ?'
);
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch();

$errors = [];
$checkIn = $_GET['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? '';
$guests = (int)($_GET['guests'] ?? 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkIn = trim($_POST['check_in'] ?? '');
    $checkOut = trim($_POST['check_out'] ?? '');
    $guests = filter_input(INPUT_POST, 'guests', FILTER_VALIDATE_INT);

    if (!validDate($checkIn) || $checkIn < date('Y-m-d')) {
        $errors[] = 'Valid future check-in date ekak select karanna.';
    }

    if (!validDate($checkOut) || $checkOut <= $checkIn) {
        $errors[] = 'Check-out date eka check-in date ekata passe thiyenna ona.';
    }

    if ($guests === false || $guests < 1 || $guests > (int)$room['max_guests']) {
        $errors[] = 'Me room ekata guests 1 idan '
            . (int)$room['max_guests'] . ' dakwa select karanna.';
    }

    if (!$errors) {
        $availabilityStmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM bookings
             WHERE room_id = ?
               AND booking_status = 'confirmed'
               AND check_in < ?
               AND check_out > ?"
        );
        $availabilityStmt->execute([$roomId, $checkOut, $checkIn]);

        if ((int)$availabilityStmt->fetchColumn() > 0) {
            $errors[] = 'Me dates walata room eka dan available naha.';
        }
    }

    if (!$errors) {
        $start = new DateTimeImmutable($checkIn);
        $end = new DateTimeImmutable($checkOut);
        $nights = (int)$start->diff($end)->days;
        $total = $nights * (float)$room['price_per_night'];

        $insertStmt = $pdo->prepare(
            "INSERT INTO bookings
                (user_id, room_id, check_in, check_out, guests,
                 total_amount, booking_status)
             VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        );

        $insertStmt->execute([
            $_SESSION['user_id'],
            $roomId,
            $checkIn,
            $checkOut,
            $guests,
            $total,
        ]);

        header('Location: booking.php?room_id=' . $roomId . '&success=1');
        exit;
    }
}

$userName = $user['name'] ?? '';
$userEmail = $user['email'] ?? '';
$userPhone = $user['contact_number'] ?? '';
$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request a Booking | Coastal Villa</title>
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
        <h1>Request a Booking</h1>

        <?php if (isset($_GET['success'])): ?>
            <p>Booking request eka yawwa! Status eka Pending.</p>
            <p><a href="my-bookings.php">My Bookings balanna</a></p>
        <?php endif; ?>

        <article class="room-card">
            <h2><?= escape($room['room_name']) ?></h2>
            <p><?= escape($room['description']) ?></p>
            <p>Rs. <?= number_format((float)$room['price_per_night'], 2) ?> / night</p>
            <p>Me room eke maximum guests: <?= (int)$room['max_guests'] ?></p>
        </article>

        <h2>Guest details</h2>
        <p>Name: <?= escape($userName) ?></p>
        <p>Email: <?= escape($userEmail) ?></p>
        <p>Contact: <?= escape($userPhone) ?></p>

        <?php foreach ($errors as $error): ?>
            <p><?= escape($error) ?></p>
        <?php endforeach; ?>

        <form method="post" action="booking.php?room_id=<?= (int)$roomId ?>"
              id="booking-form"
              data-price="<?= escape($room['price_per_night']) ?>">

            <input type="hidden" name="room_id" value="<?= (int)$roomId ?>">

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
                   min="1" max="<?= (int)$room['max_guests'] ?>"
                   value="<?= escape($guests) ?>" required><br><br>

            <p id="booking-estimate">Dates select kalama estimated cost eka pennai.</p>

            <button type="submit">Send Booking Request</button>
        </form>
    </main>
</body>
</html>