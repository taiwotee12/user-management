<?php
    session_start();
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: login.php");
        exit;
    }

    require_once'config/database.php';

    // get user Information
    $user_id = $_SESSION["id"];
    $sql = "SELECT firstname, lastname, username , email, created_at , profile_image FROM users WHERE id=:id";
    $stmt = $pdo->prepare(($sql));
    $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
     <div class="container">
        <div class="header">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
            <div class="nav">
                <a href="profile.php" class="btn">My Profile</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        
        <div class="dashboard-content">
            <div class="user-info">
                <h3>Your Account Information</h3>
                <div class="info-card">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" style="max-width: 150px; max-height: 150px; display: block; margin-bottom: 10px;">
                    <?php endif; ?>
                    <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['firstname']); ?></p>
                    <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['lastname']); ?></p>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Member since:</strong> <?php echo date("F j, Y", strtotime($user['created_at'])); ?></p>
                </div>
            </div>
            
            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="action-buttons">
                    <a href="profile.php" class="btn">Edit Profile</a>
                   <a href="change_password.php" class="btn">Change Password</a>
                    <a href="#" class="btn">Account Settings</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>