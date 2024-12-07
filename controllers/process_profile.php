<?php
include_once dirname(__DIR__) . '/config/config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userId = $_SESSION['user']['id'];
    $fullname = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if (empty($fullname) || empty($username) || empty($email)) {
        $_SESSION['error_profile'] = "All fields are required";
        header('Location: ' . BASE_URL . '/profile.php');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_profile'] = "Invalid email format";
        header('Location: ' . BASE_URL . '/profile.php');
        exit();
    }

    if(!preg_match("/@stu\.uob\.edu\.bh$/", $email)) {
        $_SESSION['error_profile'] = "Email must be a valid @stu.uob.edu.bh address";
        header("Location: " . BASE_URL . "/signup.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE (email = :email OR username = :username) AND id != :id");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':id', $userId);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['error_profile'] = "Email or username already exists";
        header('Location: ' . BASE_URL . '/profile.php');
        exit();
    }




    if (!empty($_POST['current_password'])) {
        if (empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
            $_SESSION['error_profile'] = "Please fill all password fields";
            header('Location: ' . BASE_URL . '/profile.php');
            exit();
        }

        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $_SESSION['error_profile'] = "New passwords do not match";
            header('Location: ' . BASE_URL . '/profile.php');
            exit();
        }

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        $user = $stmt->fetch();

        $current_password_hash = hash('sha256', $_POST['current_password']);
        if ($current_password_hash !== $user['password']) {
            $_SESSION['error_profile'] = "Current password is incorrect";
            header('Location: ' . BASE_URL . '/profile.php');
            exit();
        }

        $new_password_hash = hash('sha256', $_POST['new_password']);
        $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->bindParam(':password', $new_password_hash);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
    }

    if (isset($_FILES['image']) && $_FILES['image']['error_profile'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $_SESSION['error_profile'] = "Invalid file type. Only JPG, PNG and GIF are allowed";
            header('Location: ' . BASE_URL . '/profile.php');
            exit();
        }

        if ($_FILES['image']['size'] > $max_size) {
            $_SESSION['error_profile'] = "File is too large. Maximum size is 5MB";
            header('Location: ' . BASE_URL . '/profile.php');
            exit();
        }

        $uploadDir = dirname(__DIR__) . '/assets/userimages/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $file = $_FILES['image'];
        $fileName = uniqid() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;
        $dbImagePath = 'assets/userimages/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Delete old image if exists
            if (isset($_SESSION['user']['image']) && $_SESSION['user']['image'] != 'assets/userimages/default-profile.jpg') {
                $oldImage = dirname(__DIR__) . '/' . $_SESSION['user']['image'];
                if (file_exists($oldImage)) {
                    unlink($oldImage);
                }
            }

            $stmt = $conn->prepare("UPDATE users SET image = :image WHERE id = :id");
            $stmt->bindParam(':image', $dbImagePath);
            $stmt->bindParam(':id', $userId);

            if ($stmt->execute()) {
                $_SESSION['user']['image'] = $dbImagePath;
            }
        }
    }

    $stmt = $conn->prepare("UPDATE users SET fullname = :fullname, username = :username, email = :email WHERE id = :id");
    $stmt->bindParam(':fullname', $fullname);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':id', $userId);

    if ($stmt->execute()) {
        $_SESSION['user']['fullname'] = $fullname;
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['email'] = $email;
        $_SESSION['success_profile'] = "Profile updated successfully";
    } else {
        $_SESSION['error_profile'] = "Error updating profile";
    }

} catch (PDOException $e) {
    $_SESSION['error_profile'] = "Database error: " . $e->getMessage();
}

header('Location: ' . BASE_URL . '/profile.php');
exit();