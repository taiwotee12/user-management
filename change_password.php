<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config/database.php';

$user_id = $_SESSION["id"];

$current_password = $new_password = $confirm_password = "";
$current_password_err = $new_password_err = $confirm_password_err = $update_err = $update_success = "";

// Get current password hash from database
$sql = "SELECT password FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch();
$current_hash = $user['password'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate current password
    if (empty(trim($_POST["current_password"]))) {
        $current_password_err = "Please enter your current password.";
    } elseif (!password_verify(trim($_POST["current_password"]), $current_hash)) {
        $current_password_err = "Current password is incorrect.";
    } else {
        $current_password = trim($_POST["current_password"]);
    }

    // Validate new password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter a new password.";
    } elseif (strlen(trim($_POST["new_password"])) < 8) {
        $new_password_err = "Password must have at least 8 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm your new password.";
    } elseif (trim($_POST["new_password"]) != trim($_POST["confirm_password"])) {
        $confirm_password_err = "Passwords do not match.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
    }

    // Update password if no errors
    if (empty($current_password_err) && empty($new_password_err) && empty($confirm_password_err)) {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = :password WHERE id = :id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->bindParam(":password", $new_hash, PDO::PARAM_STR);
        $update_stmt->bindParam(":id", $user_id, PDO::PARAM_INT);

        if ($update_stmt->execute()) {
            $update_success = "Password updated successfully!";
        } else {
            $update_err = "Oops! Something went wrong. Please try again later.";
        }
        unset($update_stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Change Password</h2>
            <div class="nav">
                <a href="profile.php" class="btn">Profile</a>
                <a href="dashboard.php" class="btn">Dashboard</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>

        <?php
        if (!empty($update_err)) {
            echo '<div class="alert alert-danger">' . $update_err . '</div>';
        }
        if (!empty($update_success)) {
            echo '<div class="alert alert-success">' . $update_success . '</div>';
        }
        ?>

        <div class="profile-form">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group <?php echo (!empty($current_password_err)) ? 'has-error' : ''; ?>">
                    <label>Current Password</label>
                    <input type="password" name="current_password">
                    <span class="help-block"><?php echo $current_password_err; ?></span>
                </div>

                <div class="form-group <?php echo (!empty($new_password_err)) ? 'has-error' : ''; ?>">
                    <label>New Password</label>
                    <input type="password" name="new_password">
                    <span class="help-block"><?php echo $new_password_err; ?></span>
                </div>

                <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password">
                    <span class="help-block"><?php echo $confirm_password_err; ?></span>
                </div>

                <div class="form-group">
                    <input type="submit" class="btn" value="Change Password">
                    <a href="profile.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
