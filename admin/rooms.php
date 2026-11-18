<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if (($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Administrator access required.');
}

require_once __DIR__ . '/../config/database.php';

function escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'Unable to validate the request. Refresh the page and try again.';
    } elseif (in_array($action, ['deactivate', 'activate'], true)) {
        $roomId = filter_var($_POST['room_id'] ?? '', FILTER_VALIDATE_INT);

        if (!$roomId) {
            $errors[] = 'Invalid room.';
        } else {
            $newStatus = $action === 'activate' ? 'active' : 'inactive';
            $stmt = $pdo->prepare(
                'UPDATE rooms SET room_status = ? WHERE room_id = ?'
            );
            $stmt->execute([$newStatus, $roomId]);

            header('Location: rooms.php?saved=1');
            exit;
        }
    } elseif (in_array($action, ['add', 'edit'], true)) {
        $roomId = filter_var($_POST['room_id'] ?? '', FILTER_VALIDATE_INT);
        $roomName = trim($_POST['room_name'] ?? '');
        $roomType = trim($_POST['room_type'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $facilities = trim($_POST['facilities'] ?? '');
        $price = filter_var($_POST['price_per_night'] ?? '', FILTER_VALIDATE_FLOAT);
        $maxGuests = filter_var($_POST['max_guests'] ?? '', FILTER_VALIDATE_INT);
        $status = $_POST['room_status'] ?? 'active';

        if ($roomName === '' || $roomType === '' || $description === '') {
            $errors[] = 'Room name, type saha description required.';
        }

        if ($price === false || $price <= 0) {
            $errors[] = 'Price must be a number greater than zero.';
        }

        if ($maxGuests === false || $maxGuests < 1 || $maxGuests > 20) {
            $errors[] = 'Maximum guests must be between 1 and 20.';
        }

        if (!in_array($status, ['active', 'inactive'], true)) {
            $errors[] = 'Invalid room status.';
        }

        if ($action === 'edit' && !$roomId) {
            $errors[] = 'No room was selected for editing.';
        }

        if (!$errors) {
            if ($action === 'add') {
                $stmt = $pdo->prepare(
                    'INSERT INTO rooms
                        (room_name, room_type, description, facilities,
                         price_per_night, max_guests, room_status)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $roomName,
                    $roomType,
                    $description,
                    $facilities,
                    $price,
                    $maxGuests,
                    $status,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE rooms
                     SET room_name = ?, room_type = ?, description = ?,
                         facilities = ?, price_per_night = ?, max_guests = ?,
                         room_status = ?
                     WHERE room_id = ?'
                );
                $stmt->execute([
                    $roomName,
                    $roomType,
                    $description,
                    $facilities,
                    $price,
                    $maxGuests,
                    $status,
                    $roomId,
                ]);
            }

            header('Location: rooms.php?saved=1');
            exit;
        }
    } else {
        $errors[] = 'Invalid action.';
    }
}

$rooms = $pdo->query(
    'SELECT * FROM rooms ORDER BY room_id DESC'
)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms | Coastal Villa</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php
$basePath = '../';
$adminArea = true;
require __DIR__ . '/../includes/navigation.php';
?>

    <main class="page-shell">
        <h1>Manage Rooms</h1>

        <?php if (isset($_GET['saved'])): ?>
            <p>Room details saved.</p>
        <?php endif; ?>

        <?php foreach ($errors as $error): ?>
            <p><?= escape($error) ?></p>
        <?php endforeach; ?>

        <section class="room-card">
            <h2>Add a Room</h2>
            <form method="post" action="rooms.php">
                <input type="hidden" name="csrf_token"
                       value="<?= escape($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="add">

                <label>Room name</label><br>
                <input name="room_name" required><br><br>

                <label>Room type</label><br>
                <input name="room_type" required><br><br>

                <label>Description</label><br>
                <textarea name="description" required></textarea><br><br>

                <label>Facilities</label><br>
                <textarea name="facilities"></textarea><br><br>

                <label>Price per night (Rs.)</label><br>
                <input name="price_per_night" type="number"
                       min="1" step="0.01" required><br><br>

                <label>Maximum guests</label><br>
                <input name="max_guests" type="number"
                       min="1" max="20" required><br><br>

                <button type="submit">Add Room</button>
            </form>
        </section>

        <h2>Existing Rooms</h2>

        <?php foreach ($rooms as $room): ?>
            <details class="room-card">
                <summary>
                    <?= escape($room['room_name']) ?>
                    — <?= escape(ucfirst($room['room_status'])) ?>
                </summary>

                <form method="post" action="rooms.php">
                    <input type="hidden" name="csrf_token"
                           value="<?= escape($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="room_id"
                           value="<?= (int)$room['room_id'] ?>">

                    <label>Room name</label><br>
                    <input name="room_name" value="<?= escape($room['room_name']) ?>"
                           required><br><br>

                    <label>Room type</label><br>
                    <input name="room_type" value="<?= escape($room['room_type']) ?>"
                           required><br><br>

                    <label>Description</label><br>
                    <textarea name="description" required><?= escape($room['description']) ?></textarea><br><br>

                    <label>Facilities</label><br>
                    <textarea name="facilities"><?= escape($room['facilities']) ?></textarea><br><br>

                    <label>Price per night (Rs.)</label><br>
                    <input name="price_per_night" type="number" min="1" step="0.01"
                           value="<?= escape($room['price_per_night']) ?>" required><br><br>

                    <label>Maximum guests</label><br>
                    <input name="max_guests" type="number" min="1" max="20"
                           value="<?= (int)$room['max_guests'] ?>" required><br><br>

                    <label>Status</label><br>
                    <select name="room_status">
                        <option value="active"
                            <?= $room['room_status'] === 'active' ? 'selected' : '' ?>>
                            Active
                        </option>
                        <option value="inactive"
                            <?= $room['room_status'] === 'inactive' ? 'selected' : '' ?>>
                            Inactive
                        </option>
                    </select><br><br>

                    <button type="submit">Save Changes</button>
                </form>

                <?php if ($room['room_status'] === 'active'): ?>
                    <form method="post" action="rooms.php">
                        <input type="hidden" name="csrf_token"
                               value="<?= escape($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="action" value="deactivate">
                        <input type="hidden" name="room_id"
                               value="<?= (int)$room['room_id'] ?>">
                        <button type="submit">Deactivate Room</button>
                    </form>
                <?php else: ?>
                    <form method="post" action="rooms.php">
                        <input type="hidden" name="csrf_token"
                               value="<?= escape($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="action" value="activate">
                        <input type="hidden" name="room_id"
                               value="<?= (int)$room['room_id'] ?>">
                        <button type="submit">Reactivate Room</button>
                    </form>
                <?php endif; ?>
            </details>
        <?php endforeach; ?>
    </main>
</body>
</html>
