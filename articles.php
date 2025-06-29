<?php
session_start();
require_once("dbtest.php"); // Database connection



// Fetch articles from the database
$articles = [];
$query = "SELECT author, text, image, timestamp FROM articles ORDER BY timestamp DESC";
if ($result = $db_obj->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
    $result->free();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <title>Beiträge</title>
</head>
<body>
    <?php include("nav.php"); $articles = array_reverse($articles); ?>
    <div class="container mt-5">
        <h2>Beiträge</h2>
        <?php if (!empty($articles)): ?>
            <?php foreach ($articles as $article): ?>
                <div class="card mb-3">
                    <img src="<?php echo htmlspecialchars($article['image']); ?>" class="card-img-top" alt="Article Image">
                    <div class="card-body">
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($article['text'])); ?></p>
                        <p class="text-muted">Erstellt von: <?php echo htmlspecialchars($article['author']); ?> am <?php echo htmlspecialchars($article['timestamp']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Keine Beiträge gefunden.</p>
        <?php endif; ?>
    </div>
</body>
</html>
