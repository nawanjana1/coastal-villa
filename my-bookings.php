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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $message = 'Unable to validate the request. Refresh the page and try again.';
    } elseif ($bookingId) {
        $cancelStmt = $pdo->prepare(
            "UPDATE bookings
             SET booking_status = 'cancelled'
             WHERE booking_id = ?
               AND user_id = ?
               AND booking_status = 'pending'"
        );
        $cancelStmt->execute([$bookingId, $_SESSION['user_id']]);

        if ($cancelStmt->rowCount() === 1) {
            $message = 'Pending booking cancelled.';
        } else {
            $message = 'This booking cannot be cancelled. Only pending bookings can be cancelled.';
        }
    }
}

$bookingsStmt = $pdo->prepare(
    'SELECT b.booking_id, b.check_in, b.check_out, b.guests,
            b.total_amount, b.booking_status, b.booking_date,
            r.room_name, r.room_type
     FROM bookings b
     INNER JOIN rooms r ON r.room_id = b.room_id
     WHERE b.user_id = ?
     ORDER BY b.booking_date DESC'
);
$bookingsStmt->execute([$_SESSION['user_id']]);
$bookings = $bookingsStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | Coastal Villa</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php
$basePath = '';
$adminArea = false;
require __DIR__ . '/includes/navigation.php';
?>

    <main class="page-shell">
        <h1>My Bookings</h1>

        <?php if ($message !== ''): ?>
            <p><?= escape($message) ?></p>
        <?php endif; ?>

        <?php if (!$bookings): ?>
            <p>You have not made any bookings yet.</p>
            <a href="availability.php">Search rooms</a>
        <?php else: ?>
            <div class="room-grid">
                <?php foreach ($bookings as $booking): ?>
                    <article class="room-card">
                        <h2><?= escape($booking['room_name']) ?></h2>
                        <p><strong>Type:</strong> <?= escape($booking['room_type']) ?></p>
                        <p><strong>Check-in:</strong> <?= escape($booking['check_in']) ?></p>
                        <p><strong>Check-out:</strong> <?= escape($booking['check_out']) ?></p>
                        <p><strong>Guests:</strong> <?= (int)$booking['guests'] ?></p>
                        <p><strong>Total:</strong>
                            Rs. <?= number_format((float)$booking['total_amount'], 2) ?>
                        </p>
                        <p><strong>Status:</strong>
                            <?= escape(ucfirst($booking['booking_status'])) ?>
                        </p>

                        <?php if ($booking['booking_status'] === 'pending'): ?>
                            <form method="post" action="my-bookings.php">
                                <input type="hidden" name="booking_id"
                                       value="<?= (int)$booking['booking_id'] ?>">
                                <input type="hidden" name="csrf_token"
                                       value="<?= escape($_SESSION['csrf_token']) ?>">
                                <button type="submit">Cancel Pending Booking</button>
                            </form>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
