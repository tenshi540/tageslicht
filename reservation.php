<?php
session_start();
require_once("dbtest.php"); // Database connection

if (!isset($_SESSION["user"])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

// Initialize reservations array
$reservations = [];

// Fetch reservations from the database
$query = "SELECT roomnumber, name, arrival, departure, breakfast, parking, pets, author FROM reservations";
if ($result = $db_obj->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
    $result->free();
} else {
    echo "<p class='text-danger'>Error fetching reservations: " . $db_obj->error . "</p>";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_number = $_POST['room_number'];
    $customer_name = $_POST['customer_name'];
    $arrival_date = $_POST['arrival_date'];
    $departure_date = $_POST['departure_date'];
    $breakfast = ($_POST['breakfast'] === "Yes") ? 1 : 0; // Convert to boolean
    $parking = ($_POST['parking'] === "Yes") ? 1 : 0; // Convert to boolean
    $pets = $_POST['pets'];
    $author = $_SESSION['user']; // Automatically include username from session

    // Check for conflicting reservations
    $conflict_query = "
        SELECT COUNT(*) AS conflict_count 
        FROM reservations 
        WHERE roomnumber = ? 
        AND (
            (arrival < ? AND departure > ?) -- Overlapping period
            OR (arrival = ? AND departure = ?) -- Same day check-in/out allowed
        )
    ";
    $stmt = $db_obj->prepare($conflict_query);
    $stmt->bind_param("issss", $room_number, $departure_date, $arrival_date, $departure_date, $arrival_date);
    $stmt->execute();
    $stmt->bind_result($conflict_count);
    $stmt->fetch();
    $stmt->close();

    if ($conflict_count > 0) {
        echo "<script>alert('Error: The room is already booked for the selected dates.');</script>";
    } else {
        // Insert reservation into the database
        $stmt = $db_obj->prepare("INSERT INTO reservations (roomnumber, name, arrival, departure, breakfast, parking, pets, author) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $room_number, $customer_name, $arrival_date, $departure_date, $breakfast, $parking, $pets, $author);
    
        if ($stmt->execute()) {
            header("Location: reservation.php"); // Refresh the page to show the updated list
            exit;
        } else {
            echo "<script>alert('Error: " . addslashes($stmt->error) . "');</script>";
        }
        $stmt->close();
    }
    
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <title>Zimmer buchen</title>
    <script>
    // Client-side validation and upcharge prompt
    function validateReservationForm(event) {
        const arrivalDate = new Date(document.getElementById('arrival_date').value);
        const departureDate = new Date(document.getElementById('departure_date').value);

        if (departureDate <= arrivalDate) {
            event.preventDefault();
            alert('Check-Out must be after Check-In.');
            return;
        }

        // Check for upcharges
        const parking = document.getElementById('parking').value === "Yes";
        const pets = document.getElementById('pets').value.trim() !== "";
        const breakfast = document.getElementById('breakfast').value === "Yes";

        let upcharge = 0;
        if (parking) upcharge += 50;
        if (pets) upcharge += 50;
        if (breakfast) upcharge += 50;

        if (upcharge > 0) {
            const confirmMessage = `An additional charge of $${upcharge} applies for the following:\n` +
                `${parking ? "- Parking\n" : ""}` +
                `${pets ? "- Pets\n" : ""}` +
                `${breakfast ? "- Breakfast\n" : ""}` +
                "Do you want to proceed?";
            if (!confirm(confirmMessage)) {
                event.preventDefault();
            }
        }
    }
</script>
</head>
<body>
    <?php include("nav.php"); ?>
    <div class="container mt-5">
        <!-- Form for room booking -->
        <form method="post" action="reservation.php" class="form-container" onsubmit="validateReservationForm(event)">
    <h2>Zimmerbuchungen</h2>
    <div class="form-group">
        <label for="room_number">Zimmernummer (1-99):</label>
        <input type="number" id="room_number" name="room_number" min="1" max="99" required class="form-control">
    </div>
    <div class="form-group">
        <label for="customer_name">Name:</label>
        <input type="text" id="customer_name" name="customer_name" required class="form-control">
    </div>
    <div class="form-group">
        <label for="arrival_date">Ankunftsdatum:</label>
        <input type="date" id="arrival_date" name="arrival_date" required class="form-control">
    </div>
    <div class="form-group">
        <label for="departure_date">Abreisedatum:</label>
        <input type="date" id="departure_date" name="departure_date" required class="form-control">
    </div>
    <div class="form-group">
        <label>Frühstück:</label>
        <select name="breakfast" id="breakfast" class="form-control" required>
            <option value="Yes">Ja</option>
            <option value="No">Nein</option>
        </select>
    </div>
    <div class="form-group">
        <label>Parkgelegenheit:</label>
        <select name="parking" id="parking" class="form-control" required>
            <option value="Yes">Ja</option>
            <option value="No">Nein</option>
        </select>
    </div>
    <div class="form-group">
        <label for="pets">Haustiere:</label>
        <input type="text" id="pets" name="pets" placeholder="Art (falls zutreffend)" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Zimmer buchen</button>
</form>

        
    </div>
</body>
</html>
