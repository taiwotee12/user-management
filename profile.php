<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config/database.php';

$user_id = $_SESSION["id"];

$firstname= $lastname = $username = $email = "";
$firstname_err = $lastname_err = $username_err = $email_err = $update_err = $update_success = "";
$profile_image = "";
$profile_image_err = "";

// Get current user data
$sql = "SELECT firstname, lastname, username, email, profile_image FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch();

$firstname = $user['firstname'];
$lastname = $user['lastname'];
$username = $user['username'];
$email = $user['email'];
$profile_image = $user['profile_image'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

     // validate firstname
     if (empty(trim($_POST["firstname"]))) {
        $firstname_err = "Please enter a first name.";
    }else{
        $firstname = trim($_POST["firstname"]);
    }

    // validate lastname
     if (empty(trim($_POST["lastname"]))) {
        $lastname_err = "Please enter a last name.";
    }else{
        $lastname = trim($_POST["lastname"]);
    }
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $new_username = trim($_POST["username"]);
        
        // Check if username is different and if it's already taken
        if ($new_username !== $username) {
            $check_sql = "SELECT id FROM users WHERE username = :username AND id != :id";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->bindParam(":username", $new_username, PDO::PARAM_STR);
            $check_stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
            
            if ($check_stmt->execute()) {
                if ($check_stmt->rowCount() == 1) {
                    $username_err = "This username is already taken.";
                } else {
                    $username = $new_username;
                }
            }
            unset($check_stmt);
        }
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        $new_email = trim($_POST["email"]);
        
        // Check if email is different and if it's already taken
        if ($new_email !== $email) {
            $check_sql = "SELECT id FROM users WHERE email = :email AND id != :id";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->bindParam(":email", $new_email, PDO::PARAM_STR);
            $check_stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
            
            if ($check_stmt->execute()) {
                if ($check_stmt->rowCount() == 1) {
                    $email_err = "This email is already registered.";
                } else {
                    $email = $new_email;
                }
            }
            unset($check_stmt);
        }
    }

    // Handle profile image upload
    if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png", "gif" => "image/gif"];
        $filename = $_FILES["profile_image"]["name"];
        $filetype = $_FILES["profile_image"]["type"];
        $filesize = $_FILES["profile_image"]["size"];

        // Verify file extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!array_key_exists($ext, $allowed)) {
            $profile_image_err = "Error: Please select a valid image file (jpg, jpeg, png, gif).";
        }

        // Verify file size - 5MB maximum
        if ($filesize > 5 * 1024 * 1024) {
            $profile_image_err = "Error: File size is larger than the allowed limit.";
        }

        // Verify MIME type
        if (in_array($filetype, $allowed)) {
            // Check for errors before moving file
            if (empty($profile_image_err)) {
                $new_filename = uniqid() . "." . $ext;
                $upload_dir = "uploads/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $destination = $upload_dir . $new_filename;
                if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $destination)) {
                    $profile_image = $destination;
                } else {
                    $profile_image_err = "Error uploading the file.";
                }
            }
        } else {
            $profile_image_err = "Error: There was a problem with the file upload.";
        }
    }

    // Update profile if no errors
    if (empty($username_err) && empty($email_err) && empty($profile_image_err)) {
        $update_sql = "UPDATE users SET firstname = :firstname, lastname = :lastname, username = :username, email = :email, profile_image = :profile_image WHERE id = :id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->bindParam(":firstname", $firstname, PDO::PARAM_STR);
        $update_stmt->bindParam(":lastname", $lastname, PDO::PARAM_STR);
        $update_stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $update_stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $update_stmt->bindParam(":profile_image", $profile_image, PDO::PARAM_STR);
        $update_stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
        
        if ($update_stmt->execute()) {
            $_SESSION["username"] = $username;
            $update_success = "Profile updated successfully!";
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
    <title>Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>My Profile</h2>
            <div class="nav">
                <a href="dashboard.php" class="btn">Dashboard</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>

        <?php 
        if(!empty($update_err)){
            echo '<div class="alert alert-danger">' . $update_err . '</div>';
        }
        if(!empty($update_success)){
            echo '<div class="alert alert-success">' . $update_success . '</div>';
        }
        ?>

        <div class="profile-form">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                    <label>Firstname</label>
                    <input type="text" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>">
                    <span class="help-block"><?php echo $firstname_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                    <label>Lastname</label>
                    <input type="text" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>">
                    <span class="help-block"><?php echo $lastname_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                    <label>Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
                    <span class="help-block"><?php echo $username_err; ?></span>
                </div>
                
                <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <span class="help-block"><?php echo $email_err; ?></span>
                </div>

                <div class="form-group">
                    <label>Profile Image</label><br>
                    <?php if (!empty($profile_image)): ?>
                        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image" style="max-width: 150px; max-height: 150px; display: block; margin-bottom: 10px;">
                    <?php endif; ?>
                    <input type="file" name="profile_image" accept="image/*">
                    <span class="help-block"><?php echo $profile_image_err; ?></span>
                </div>
                
                <div class="form-group">
                    <input type="submit" class="btn" value="Update Profile">
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
