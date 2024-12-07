<?php

session_start();

include_once dirname(__DIR__) . '/config/config.php';


if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please login to remove a booking";
    header('Location: ' . BASE_URL . '/login');
    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'];
    $userId = $_SESSION['user']['id'];

    echo $bookingId;

    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $bookingId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        echo $stmt->rowCount();
        if ($stmt->rowCount() > 0) {
            $stmt = $conn->prepare("DELETE FROM bookings WHERE id = :id");
            $stmt->bindParam(':id', $bookingId, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['success_remove'] = "Booking removed successfully";

            header('Location: ' . BASE_URL . '/bookings.php');
        } else {
            header('Location: ' . BASE_URL . '/bookings.php');
            $_SESSION['error_remove'] = "Booking not found";
        }
    } catch (PDOException $e) {
        $_SESSION['error_remove'] = "Database error: " . $e->getMessage();
    }
}



?>