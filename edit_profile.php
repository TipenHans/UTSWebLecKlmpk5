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
                if ($_FILES['profile_picture']['size'] > 5000000) {
                    $error = "File is too large. Maximum size is 5MB.";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

</head>
<body>
    <div class="header">
        <h2>Event Management</h2>
    </div>
    <div class="container">
        <div class="d-flex justify-content-center">
            <div class="card mt-4">
                <div class="card-header">
                    <h2>Edit Profile</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label"><strong>User ID (cannot be changed):</strong></label>
                            <p class="form-control-plaintext"><?php echo $user['id']; ?></p>
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label"><strong>Full Name:</strong></label>
                            <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label"><strong>Email:</strong></label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="form-label"><strong>Phone Number:</strong></label>
                            <input type="text" id="phone_number" name="phone_number" class="form-control" value="<?php echo $user['phone_number']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="old_password" class="form-label"><strong>Old Password (Required to change password):</strong></label>
                            <input type="password" id="old_password" name="old_password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label"><strong>New Password:</strong></label>
                            <input type="password" id="new_password" name="new_password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label"><strong>Confirm Password:</strong></label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label"><strong>Profile Picture:</strong></label>
                            <input type="file" id="profile_picture" name="profile_picture" class="form-control">
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='view_profile.php';">Cancel</button>
                        </div>
                    </form>  
                </div>
            </div>
        </div>
    </div>

    <script>
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