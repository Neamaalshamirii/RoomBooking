<?php
// process_booking.php
include_once dirname(__DIR__) . '/config/config.php';
session_start();

if(!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please login to book a room";
    header('Location: ' . BASE_URL . '/login');
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = $_POST['room_id'];
    $userId = $_SESSION['user']['id'];
    $bookingDate = $_POST['booking_date'];
    $timeSlot = $_POST['time_slot'];
    $duration = 1;

    $checkIn = date('Y-m-d H:i:s', strtotime("$bookingDate $timeSlot"));
    $checkOut = date('Y-m-d H:i:s', strtotime("$checkIn + $duration hour"));

    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $checkStmt = $conn->prepare("
            SELECT id FROM bookings 
            WHERE room_id = :room_id 
            AND time_slot = :time_slot
            AND DATE(check_in) = :booking_date
        ");
        
        $checkStmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
        $checkStmt->bindParam(':time_slot', $timeSlot);
        $checkStmt->bindParam(':booking_date', $bookingDate);
        $checkStmt->execute();

        if($checkStmt->rowCount() > 0) {
            $_SESSION['error_booking'] = "This time slot is already booked";
        } else {
            $stmt = $conn->prepare("
                INSERT INTO bookings (room_id, user_id, check_in, check_out, time_slot, duration) 
                VALUES (:room_id, :user_id, :check_in, :check_out, :time_slot, :duration)
            ");
            
            $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':check_in', $checkIn);
            $stmt->bindParam(':check_out', $checkOut);
            $stmt->bindParam(':time_slot', $timeSlot);
            $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);

            if($stmt->execute()) {
                $_SESSION['success_booking'] = "Booking successful! From " . 
                    date('F j, Y g:i A', strtotime($checkIn)) . 
                    " to " . date('g:i A', strtotime($checkOut));
            } else {
                $_SESSION['error_booking'] = "Booking failed";
            }
        }
    } catch(PDOException $e) {
        $_SESSION['error_booking'] = "Database error: " . $e->getMessage();
    }
}

header('Location: ' . BASE_URL . '/roomview.php?id=' . $roomId);
exit();