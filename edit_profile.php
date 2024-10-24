<?php
session_start();
require 'db.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


$user_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();
$error = '';
$success = '';


if (isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $profile_picture = $user['profile_picture']; 
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND id != :user_id");
    $stmt->execute(['email' => $email, 'user_id' => $user_id]);
    $existing_user = $stmt->fetch();

   
    if ($existing_user) {
        $error = "Email is already taken. Please choose another.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (!empty($new_password) && empty($old_password)) {
        $error = "Please provide your old password to change the password.";
    } elseif (!empty($new_password)) {
        if (!password_verify($old_password, $user['password'])) {
            $error = "Old password is incorrect.";
        } else {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT); 
        }
    }

    
    if (empty($error)) {
        if (!empty($_FILES['profile_picture']['name'])) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES['profile_picture']['name']);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $check = getimagesize($_FILES['profile_picture']['tmp_name']);
            if ($check !== false) {
                if ($_FILES['profile_picture']['size'] > 10000000) {
                    $error = "File is too large. Maximum size is 10MB.";
                } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
                    $error = "Only JPG, JPEG, and PNG files are allowed.";
                } else {
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                        $profile_picture = $_FILES['profile_picture']['name'];
                    } else {
                        $error = "Sorry, there was an error uploading your file.";
                    }
                }
            } else {
                $error = "File is not a valid image.";
            }
        }
        if (empty($error)) {
            if (!empty($new_password)) {
                $stmt = $pdo->prepare("UPDATE users SET full_name = :full_name, email = :email, phone_number = :phone_number, profile_picture = :profile_picture, password = :password WHERE id = :user_id");
                $stmt->execute([
                    'full_name' => $full_name,
                    'email' => $email,
                    'phone_number' => $phone_number,
                    'profile_picture' => $profile_picture,
                    'password' => $password_hash,
                    'user_id' => $user_id
                ]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name = :full_name, email = :email, phone_number = :phone_number, profile_picture = :profile_picture WHERE id = :user_id");
                $stmt->execute([
                    'full_name' => $full_name,
                    'email' => $email,
                    'phone_number' => $phone_number,
                    'profile_picture' => $profile_picture,
                    'user_id' => $user_id
                ]);
            }
            $success = true; 
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="style.css">
    </head>
<body>
<div class="container">
    <h2>Edit Profile</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <p><strong>User ID (cannot be changed):</strong> <?php echo $user['id']; ?></p>
        <p><strong>Full Name:</strong> <input type="text" name="full_name" value="<?php echo $user['full_name']; ?>" required></p>
        <p><strong>Email:</strong> <input type="email" name="email" value="<?php echo $user['email']; ?>" required></p>
        <p><strong>Phone Number:</strong> <input type="text" name="phone_number" value="<?php echo $user['phone_number']; ?>" required></p>
        <p><strong>Old Password (Required to change password):</strong> <input type="password" name="old_password"></p>
        <p><strong>New Password:</strong> <input type="password" name="new_password"></p>
        <p><strong>Confirm Password:</strong> <input type="password" name="confirm_password"></p>
        <p><strong>Profile Picture:</strong></p>
<div class="profile-picture-container">
    <img id="profile-picture-preview" src="<?php echo !empty($user['profile_picture']) ? 'uploads/' . $user['profile_picture'] : 'default-avatar.png'; ?>" alt="Profile Picture Preview">
    <input type="file" name="profile_picture" id="profile-picture-input" accept="image/*">
    <label for="profile-picture-input" class="custom-file-upload">Choose File</label>
</div>
        <div class="btn-container">
            <button type="submit" name="update_profile">Update Profile</button>
            <button type="button" class="cancel-btn" onclick="window.location.href='view_profile.php';">Cancel</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('profile-picture-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('profile-picture-preview').src = event.target.result;
        }
        reader.readAsDataURL(file);
    }
});
<?php if (!empty($success)): ?>
    Swal.fire({
        icon: 'success',
        title: 'Profile Updated',
        text: 'Your profile has been successfully updated.',
        confirmButtonText: 'OK'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'view_profile.php';
        }
    });
<?php elseif (!empty($error)): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '<?php echo $error; ?>',
        confirmButtonText: 'OK'
    });
<?php endif; ?>
</script>

</body>
</html>

