<?php
session_start();
require_once("dbtest.php"); // Database connection
require_once("nav.php"); // Navigation bar

if (!isset($_SESSION["user"])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

// Check if the logged-in user is an admin
$query = "SELECT admin FROM users WHERE username = ?";
$is_admin = false;
if ($stmt = $db_obj->prepare($query)) {
    $stmt->bind_param("s", $_SESSION["user"]);
    $stmt->execute();
    $stmt->bind_result($is_admin_flag);
    $stmt->fetch();
    $stmt->close();
    $is_admin = $is_admin_flag == 1; // Convert to boolean
}

// Handle status update request
if ($is_admin && isset($_GET['update_status']) && isset($_GET['roomnumber']) && in_array($_GET['update_status'], ['Confirmed', 'Cancelled'])) {
    $roomnumber = intval($_GET['roomnumber']);
    $new_status = $_GET['update_status'];
    $update_query = "UPDATE reservations SET status = ? WHERE roomnumber = ?";
    if ($stmt = $db_obj->prepare($update_query)) {
        $stmt->bind_param("si", $new_status, $roomnumber);
        if ($stmt->execute()) {
            echo "<p class='text-success'>Reservation status updated successfully.</p>";
        } else {
            echo "<p class='text-danger'>Error updating reservation: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// Fetch reservations
$reservations = [];
if ($is_admin) {
    // Admins see all reservations
    $query = "SELECT roomnumber, name, arrival, departure, breakfast, parking, pets, status FROM reservations";
    if ($result = $db_obj->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
        $result->free();
    } else {
        echo "<p class='text-danger'>Error fetching reservations: " . $db_obj->error . "</p>";
    }
} else {
    // Regular users see only their own reservations
    $query = "SELECT roomnumber, name, arrival, departure, breakfast, parking, pets, status FROM reservations WHERE author = ?";
    if ($stmt = $db_obj->prepare($query)) {
        $stmt->bind_param("s", $_SESSION["user"]);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
        $stmt->close();
    } else {
        echo "<p class='text-danger'>Error fetching reservations: " . $db_obj->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Reservations</title>
</head>
<body>
    <?php include("nav.php"); ?>
    <h1>Your Reservations</h1>
    <table border="1">
        <tr>
            <th>Room Number</th>
            <th>Name</th>
            <th>Arrival</th>
            <th>Departure</th>
            <th>Breakfast</th>
            <th>Parking</th>
            <th>Pets</th>
            <th>Status</th>
            <?php if ($is_admin): ?>
                <th>Actions</th>
            <?php endif; ?>
        </tr>
        <?php foreach ($reservations as $reservation): ?>
            <tr>
                <td><?php echo htmlspecialchars($reservation['roomnumber']); ?></td>
                <td><?php echo htmlspecialchars($reservation['name']); ?></td>
                <td><?php echo htmlspecialchars($reservation['arrival']); ?></td>
                <td><?php echo htmlspecialchars($reservation['departure']); ?></td>
                <td><?php echo ($reservation['breakfast']) ? 'Ja' : 'Nein'; ?></td>
                <td><?php echo ($reservation['parking']) ? 'Ja' : 'Nein'; ?></td>
                <td><?php echo htmlspecialchars($reservation['pets']); ?></td>
                <td>
                    <?php
                    switch ($reservation['status']) {
                        case 'New':
                            echo 'Neu';
                            break;
                        case 'Confirmed':
                            echo 'Bestätigt';
                            break;
                        case 'Cancelled':
                            echo 'Storniert';
                            break;
                        default:
                            echo 'Unbekannt';
                    }
                    ?>
                </td>
                <?php if ($is_admin): ?>
                    <td>
                        <a href="?update_status=Confirmed&roomnumber=<?php echo $reservation['roomnumber']; ?>" 
                           onclick="return confirm('Are you sure you want to confirm this reservation?');" 
                           class="btn btn-success">Confirm</a>
                        <a href="?update_status=Cancelled&roomnumber=<?php echo $reservation['roomnumber']; ?>" 
                           onclick="return confirm('Are you sure you want to cancel this reservation?');" 
                           class="btn btn-danger">Cancel</a>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
