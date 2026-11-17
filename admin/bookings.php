<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if (($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Admin access one.');
}

require_once __DIR__ . '/../config/database.php';

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
    $bookingId = filter_var(
        $_POST['booking_id'] ?? '',
        FILTER_VALIDATE_INT
    );
    $action = $_POST['action'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $message = 'Request eka validate karanna bari una. Page eka refresh karala aye try karanna.';
    } elseif (!$bookingId || !in_array($action, ['confirm', 'cancel'], true)) {
        $message = 'Invalid request.';
    } elseif ($action === 'cancel') {
        $cancelStmt = $pdo->prepare(
            "UPDATE bookings
             SET booking_status = 'cancelled'
             WHERE booking_id = ?
               AND booking_status IN ('pending', 'confirmed')"
        );
        $cancelStmt->execute([$bookingId]);

        $message = $cancelStmt->rowCount() === 1
            ? 'Booking eka cancel kala.'
            : 'Cancel karanna puluwan booking ekak hambune naha.';
    } else {
        try {
            $pdo->beginTransaction();

            $bookingStmt = $pdo->prepare(
                "SELECT booking_id, room_id, check_in, check_out
                 FROM bookings
                 WHERE booking_id = ?
                   AND booking_status = 'pending'
                 FOR UPDATE"
            );
            $bookingStmt->execute([$bookingId]);
            $booking = $bookingStmt->fetch();

            if (!$booking) {
                $pdo->rollBack();
                $message = 'Confirm karanna pending booking ekak hambune naha.';
            } else {
                // Same room eke confirmations ekawara ekin eka process karanawa.
                $roomStmt = $pdo->prepare(
                    'SELECT room_status FROM rooms WHERE room_id = ? FOR UPDATE'
                );
                $roomStmt->execute([$booking['room_id']]);
                $roomStatus = $roomStmt->fetchColumn();

                if ($roomStatus !== 'active') {
                    $pdo->rollBack();
                    $message = 'Me room eka active nisa confirm karanna ba.';
                } else {
                    $conflictStmt = $pdo->prepare(
                        "SELECT COUNT(*)
                         FROM bookings
                         WHERE room_id = ?
                           AND booking_id <> ?
                           AND booking_status = 'confirmed'
                           AND check_in < ?
                           AND check_out > ?"
                    );
                    $conflictStmt->execute([
                        $booking['room_id'],
                        $booking['booking_id'],
                        $booking['check_out'],
                        $booking['check_in'],
                    ]);

                    if ((int)$conflictStmt->fetchColumn() > 0) {
                        $pdo->rollBack();
                        $message = 'Dates overlap wena confirmed booking ekak thiyenawa.';
                    } else {
                        $confirmStmt = $pdo->prepare(
                            "UPDATE bookings
                             SET booking_status = 'confirmed'
                             WHERE booking_id = ?
                               AND booking_status = 'pending'"
                        );
                        $confirmStmt->execute([$bookingId]);

                        $pdo->commit();
                        $message = 'Booking eka confirm kala.';
                    }
                }
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $message = 'Booking eka update karaddi error ekak una.';
        }
    }
}

$bookingsStmt = $pdo->query(
    'SELECT b.booking_id, b.check_in, b.check_out, b.guests,
            b.total_amount, b.booking_status, b.booking_date,
            u.name AS guest_name, u.email, u.contact_number,
            r.room_name
     FROM bookings b
     INNER JOIN users u ON u.user_id = b.user_id
     INNER JOIN rooms r ON r.room_id = b.room_id
     ORDER BY b.booking_date DESC'
);
$bookings = $bookingsStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings | Coastal Villa</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php
$basePath = '../';
$adminArea = true;
require __DIR__ . '/../includes/navigation.php';
?>

    <main class="page-shell">
        <h1>Manage Bookings</h1>

        <?php if ($message !== ''): ?>
            <p><?= escape($message) ?></p>
        <?php endif; ?>

        <?php if (!$bookings): ?>
            <p>Thama booking requests naha.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="booking-table">
                    <thead>
                        <tr>
                            <th>Guest</th>
                            <th>Room</th>
                            <th>Dates</th>
                            <th>Guests</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>
                                    <?= escape($booking['guest_name']) ?><br>
                                    <?= escape($booking['email']) ?><br>
                                    <?= escape($booking['contact_number']) ?>
                                </td>
                                <td><?= escape($booking['room_name']) ?></td>
                                <td>
                                    <?= escape($booking['check_in']) ?><br>
                                    to <?= escape($booking['check_out']) ?>
                                </td>
                                <td><?= (int)$booking['guests'] ?></td>
                                <td>
                                    Rs. <?= number_format((float)$booking['total_amount'], 2) ?>
                                </td>
                                <td><?= escape(ucfirst($booking['booking_status'])) ?></td>
                                <td>
                                    <?php if ($booking['booking_status'] === 'pending'): ?>
                                        <form method="post" action="bookings.php">
                                            <input type="hidden" name="booking_id"
                                                   value="<?= (int)$booking['booking_id'] ?>">
                                            <input type="hidden" name="csrf_token"
                                                   value="<?= escape($_SESSION['csrf_token']) ?>">
                                            <button name="action" value="confirm"
                                                    type="submit">Confirm</button>
                                            <button name="action" value="cancel"
                                                    type="submit">Cancel</button>
                                        </form>
                                    <?php elseif ($booking['booking_status'] === 'confirmed'): ?>
                                        <form method="post" action="bookings.php">
                                            <input type="hidden" name="booking_id"
                                                   value="<?= (int)$booking['booking_id'] ?>">
                                            <input type="hidden" name="csrf_token"
                                                   value="<?= escape($_SESSION['csrf_token']) ?>">
                                            <button name="action" value="cancel"
                                                    type="submit">Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>