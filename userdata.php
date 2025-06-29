<?php
session_start();
require_once("dbtest.php");

if (!isset($_SESSION["user"])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit;
}

$message = '';
$errors = [];

// Fetch user data
$userData = [];
$query = "SELECT title, first_name, last_name, email, username FROM users WHERE username = ?";
if ($stmt = $db_obj->prepare($query)) {
    $stmt->bind_param("s", $_SESSION["user"]);
    $stmt->execute();
    $stmt->bind_result($title, $first_name, $last_name, $email, $username);
    if ($stmt->fetch()) {
        $userData = [
            "title" => $title,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "email" => $email,
            "username" => $username
        ];
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"]);
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Basic validation
    if (empty($password)) {
        $errors[] = "Password is required.";
    } else {
        // Validate password
        $query = "SELECT password FROM users WHERE username = ?";
        if ($stmt = $db_obj->prepare($query)) {
            $stmt->bind_param("s", $_SESSION["user"]);
            $stmt->execute();
            $stmt->bind_result($hashed_password);
            if ($stmt->fetch() && password_verify($password, $hashed_password)) {
                // Password is valid
            } else {
                $errors[] = "Invalid password.";
            }
            $stmt->close();
        }
    }

    if (empty($errors)) {
        $query = "UPDATE users SET title = ?, first_name = ?, last_name = ?, email = ?, username = ? WHERE username = ?";
        if ($stmt = $db_obj->prepare($query)) {
            $stmt->bind_param("ssssss", $title, $first_name, $last_name, $email, $username, $_SESSION["user"]);
            if ($stmt->execute()) {
                $message = "Your data has been updated successfully.";
                $_SESSION["user"] = $username; // Update session username
            } else {
                $errors[] = "Failed to update data. Please try again.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <title>Edit User Data</title>
    <script>
        function showPasswordPrompt() {
            const modal = new bootstrap.Modal(document.getElementById("passwordModal"));
            modal.show();
        }

        function submitForm() {
            const password = document.getElementById("passwordInput").value;
            if (!password) {
                alert("Password is required to apply changes.");
                return false;
            }
            const passwordField = document.getElementById("password");
            passwordField.value = password;
            document.getElementById("editForm").submit();
        }
    </script>
</head>
<body>
    <?php include("nav.php"); ?>
    <div class="container mt-5">
        <h2>Edit Your Information</h2>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="editForm" method="post" action="userdata.php">
            <input type="hidden" id="password" name="password">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($userData['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($userData['first_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($userData['last_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($userData['username']); ?>" required>
            </div>
            <button type="button" class="btn btn-primary" onclick="showPasswordPrompt()">Save Changes</button>
        </form>
    </div>

    <!-- Password Prompt Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordModalLabel">Enter Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="passwordInput">Password:</label>
                        <input type="password" id="passwordInput" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitForm()">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
