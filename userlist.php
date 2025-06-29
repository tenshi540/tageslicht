<?php
session_start();
require_once("dbtest.php"); // Database connection

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

if (!$is_admin) {
    die("Access denied. Admin privileges required.");
}

// Handle deletion request
if (isset($_GET['delete_user']) && !empty($_GET['delete_user'])) {
    $username_to_delete = $_GET['delete_user'];
    $delete_query = "DELETE FROM users WHERE username = ?";
    if ($delete_stmt = $db_obj->prepare($delete_query)) {
        $delete_stmt->bind_param("s", $username_to_delete);
        if ($delete_stmt->execute()) {
            echo "<p class='text-success'>User deleted successfully.</p>";
        } else {
            echo "<p class='text-danger'>Error deleting user: " . $delete_stmt->error . "</p>";
        }
        $delete_stmt->close();
    }
}

// Handle deactivate request
if (isset($_GET['deactivate_user']) && !empty($_GET['deactivate_user'])) {
    $username_to_deactivate = $_GET['deactivate_user'];
    $deactivate_query = "UPDATE users SET active = 0 WHERE username = ?";
    if ($deactivate_stmt = $db_obj->prepare($deactivate_query)) {
        $deactivate_stmt->bind_param("s", $username_to_deactivate);
        if ($deactivate_stmt->execute()) {
            echo "<p class='text-success'>User deactivated successfully.</p>";
        } else {
            echo "<p class='text-danger'>Error deactivating user: " . $deactivate_stmt->error . "</p>";
        }
        $deactivate_stmt->close();
    }
}

// Handle activate request
if (isset($_GET['activate_user']) && !empty($_GET['activate_user'])) {
    $username_to_activate = $_GET['activate_user'];
    $activate_query = "UPDATE users SET active = 1 WHERE username = ?";
    if ($activate_stmt = $db_obj->prepare($activate_query)) {
        $activate_stmt->bind_param("s", $username_to_activate);
        if ($activate_stmt->execute()) {
            echo "<p class='text-success'>User activated successfully.</p>";
        } else {
            echo "<p class='text-danger'>Error activating user: " . $activate_stmt->error . "</p>";
        }
        $activate_stmt->close();
    }
}

// Handle password reset request
if (isset($_GET['reset_password']) && !empty($_GET['reset_password']) && isset($_POST['new_password'])) {
    $username_to_reset = $_GET['reset_password'];
    $new_password = trim($_POST['new_password']);
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Prevent the admin from resetting their own password
    if ($_SESSION['user'] !== $username_to_reset) {
        $reset_query = "UPDATE users SET password = ? WHERE username = ?";
        if ($reset_stmt = $db_obj->prepare($reset_query)) {
            $reset_stmt->bind_param("ss", $hashed_password, $username_to_reset);
            if ($reset_stmt->execute()) {
                echo "<p class='text-success'>Password reset successfully for user: " . htmlspecialchars($username_to_reset) . "</p>";
            } else {
                echo "<p class='text-danger'>Error resetting password: " . $reset_stmt->error . "</p>";
            }
            $reset_stmt->close();
        }
    } else {
        echo "<p class='text-danger'>You cannot reset your own password through this interface.</p>";
    }
}

// Fetch user data
$users = [];
$query = "SELECT username, email, active, admin FROM users";
if ($result = $db_obj->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $result->free();
} else {
    echo "<p class='text-danger'>Error fetching user data: " . $db_obj->error . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User List</title>
    <script>
    function showPasswordResetPrompt(username) {
        const modal = new bootstrap.Modal(document.getElementById('passwordResetModal'));
        document.getElementById('resetUsername').value = username; // Set the username in the hidden field
        modal.show();
    }

    function submitPasswordResetForm() {
        const newPassword = document.getElementById('newPasswordInput').value;
        if (!newPassword || newPassword.trim() === "") {
            alert("Password cannot be empty.");
            return;
        }

        const username = document.getElementById('resetUsername').value;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `?reset_password=${encodeURIComponent(username)}`;

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'new_password';
        input.value = newPassword;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
</script>

</head>
<body>
    <?php include("nav.php"); ?>
    <h1>Registered Users</h1>
    <table border="1">
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Active</th>
            <th>Admin</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo $user['active'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo $user['admin'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <?php if ($_SESSION['user'] !== $user['username']): ?>
                        <a href="#" 
                           onclick="showPasswordResetPrompt('<?php echo htmlspecialchars($user['username']); ?>');" 
                           class="btn btn-info">Reset Password</a>
                        <a href="?delete_user=<?php echo urlencode($user['username']); ?>" 
                           onclick="return confirm('Are you sure you want to delete this user?');" 
                           class="btn btn-danger">Delete</a>
                        <?php if ($user['active']): ?>
                            <a href="?deactivate_user=<?php echo urlencode($user['username']); ?>" 
                               onclick="return confirm('Are you sure you want to deactivate this user?');" 
                               class="btn btn-warning">Deactivate</a>
                        <?php else: ?>
                            <a href="?activate_user=<?php echo urlencode($user['username']); ?>" 
                               onclick="return confirm('Are you sure you want to activate this user?');" 
                               class="btn btn-success">Activate</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- Password Reset Modal -->
    <div class="modal fade" id="passwordResetModal" tabindex="-1" aria-labelledby="passwordResetModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="passwordResetForm" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="passwordResetModalLabel">Reset Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="newPasswordInput">New Password:</label>
                            <input type="password" id="newPasswordInput" name="new_password" class="form-control" required>
                            <input type="hidden" id="resetUsername" name="reset_username">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="submitPasswordResetForm()">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
