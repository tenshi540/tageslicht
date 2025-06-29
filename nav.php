<head>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<style>
        .custom-login-button {
            background-color: transparent !important;
            border: none !important; /* Optional: Removes borders */
        }
    </style>
</head>


<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <!--hauyptknopf-->
    <a class="navbar-brand" href="index.php">Tageslicht</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="imprint.php">Impressum</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="help.php">Hilfe</a>
            </li>
            
            <?php if (isset($_SESSION["user"])): ?>
                
                <li class="nav-item">
                    <a class="nav-link" href="reservation.php">Zimmerbuchung durchfuehren</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservationlist.php">Zimmerbuchungen</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="createarticle.php">Beitrag erstellen</a>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                    <a class="nav-link" href="articles.php">Beitraege anzeigen</a>
                </li>
        </ul>
        <ul class="navbar-nav ml-auto">
    <?php if (isset($_SESSION["user"])): ?>
        <?php
        // Check if the logged-in user is an admin
        $is_admin = false;
        require_once("dbtest.php"); // Database connection

        $query = "SELECT admin FROM users WHERE username = ?";
        if ($stmt = $db_obj->prepare($query)) {
            $stmt->bind_param("s", $_SESSION["user"]);
            $stmt->execute();
            $stmt->bind_result($is_admin_flag);
            $stmt->fetch();
            $stmt->close();
            $is_admin = $is_admin_flag == 1; // Convert to boolean
        }
        ?>
        <!-- Display the username -->
        <li class="nav-item">
        <p class="navbar-text">Hallo, <?php echo htmlspecialchars($_SESSION["user"], ENT_QUOTES, 'UTF-8'); ?>! </p>

        </li>
        <?php if ($is_admin): ?>
            <li class="nav-item">
                <a class="nav-link btn btn-warning text-white" href="userlist.php">Admin-Bereich (Nutzerliste)</a>
            </li>
        <?php endif; ?>
        <li class="nav-item">
            <a class="nav-link btn btn-secondary text-white" href="userdata.php">Benutzerdaten verwalten</a>
        </li>
        <li class="nav-item">
            <a class="nav-link btn btn-danger text-white" href="login.php?logout=true">Ausloggen</a>
        </li>
    <?php else: ?>
        <li class="nav-item">
        <a class="nav-link btn btn-primary text-white custom-login-button" href="login.php">Einloggen</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="register.php">Registrieren</a>
        </li>
    <?php endif; ?>
</ul>

    </div>
</nav>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

