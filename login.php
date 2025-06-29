<head>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<title>Login</title>
</head>

<?php
session_start();
require_once("dbtest.php");


if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    unset($_SESSION["user"]);
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check credentials in the database
    $stmt = $db_obj->prepare("SELECT password, active FROM users WHERE username = ?");

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password, $active);
        $stmt->fetch();
    
        if ($active == 0) {
            $loginError = "Your account is inactive. Please contact an administrator.";
        } elseif (password_verify($password, $hashed_password)) {
            $_SESSION["user"] = $username;
            header("Location: reservation.php");
            exit;
        } else {
            $loginError = "Invalid login credentials.";
        }
    } else {
        $loginError = "Invalid login credentials.";
    }
    
    $stmt->close();
}

include("nav.php");
?>

<div class="container mt-5">
    <?php if (isset($_SESSION["user"])): ?>
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION["user"]); ?>!</h2>
        <a href="reservation.php" class="btn btn-primary">Go to Reservations</a>
        <a href="login.php?logout=true" class="btn btn-danger">Logout</a>
    <?php else: ?>
        <form method="post" action="login.php" class="form-container">
            <h2 class="text-center">Login</h2>
            <?php if (!empty($loginError)): ?>
                <p class="text-danger"><?php echo $loginError; ?></p>
            <?php endif; ?>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    <?php endif; ?>
</div>
