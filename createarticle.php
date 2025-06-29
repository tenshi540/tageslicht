<?php
session_start();
require_once("dbtest.php"); // Database connection

if (!isset($_SESSION["user"])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author = $_SESSION['user']; // Current logged-in user
    $timestamp = date('Y-m-d H:i:s'); // Current timestamp
    $text = htmlspecialchars(trim($_POST['text'])); // Article text

    // Validate and save image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/news/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Create directory if it doesn’t exist
}

        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $uniqueName = uniqid();

        // Check if image is jpg or jpeg
        if ($fileExtension === 'jpg' || $fileExtension === 'jpeg') {
            $newFileName = $uniqueName . '.jpg';
            $imagePath = 'uploads/news/' . $newFileName;
            $newFilePath = __DIR__ . '/' . $imagePath;

            // Resize and save image
            $image = imagecreatefromjpeg($fileTmpPath);
            $scaledImage = imagescale($image, 720, 480);
            imagejpeg($scaledImage, $newFilePath, 90);
            imagedestroy($image);
            imagedestroy($scaledImage);

            // Save article to database
            $stmt = $db_obj->prepare("INSERT INTO articles (author, text, image, timestamp) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $author, $text, $imagePath, $timestamp);

            if ($stmt->execute()) {
                $message = 'Beitrag erfolgreich eingereicht.';
            } else {
                $message = 'Fehler beim Speichern des Beitrags: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = 'Nur .jpg Dateien erlaubt.';
        }
    } else {
        $message = 'Bitte Bild beifuegen.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <title>Beitrag erstellen</title>
</head>
<body>
    <?php include("nav.php"); ?>
    <div class="container mt-5">
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="post" action="createarticle.php" enctype="multipart/form-data" class="form-container">
            <h2>Beitrag erstellen</h2>
            <div class="form-group">
                <label for="text">Beitragsinhalt:</label>
                <textarea id="text" name="text" rows="5" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label for="image">Bild (.jpg) hochladen:</label>
                <input type="file" id="image" name="image" accept=".jpg, .jpeg" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Beitrag veröffentlichen</button>
        </form>
    </div>
</body>
</html>
