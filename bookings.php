<?php
include_once 'config/config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login');
    exit();
}

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("
        SELECT b.*, r.roomtitle, r.roomimage
        FROM bookings b
        JOIN rooms r ON b.room_id = r.room_id
        WHERE b.user_id = :user_id
        ORDER BY b.check_in DESC
    ");

    $stmt->bindParam(':user_id', $_SESSION['user']['id']);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link href="dist/styles.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-gray-900 to-gray-800 min-h-screen text-gray-100">
    <?php include_once 'components/navbar.php'; ?>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-violet-300 mb-8">My Bookings</h1>

        <?php if (!empty($bookings)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($bookings as $booking): ?>
                    <div
                        class="backdrop-blur-lg bg-gray-800/50 rounded-xl overflow-hidden border border-gray-700 transform hover:scale-105 transition-all duration-300 hover:border-violet-500">
                        <div class="relative">
                            <img src="<?php echo BASE_URL . '/' . $booking['roomimage']; ?>"
                                alt="<?php echo htmlspecialchars($booking['roomtitle']); ?>" class="w-full h-48 object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent to-transparent"></div>
                        </div>

                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-violet-300 mb-4">
                                <?php echo htmlspecialchars($booking['roomtitle']); ?>
                            </h2>

                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-gray-300">Check In: <span
                                            class="text-gray-400"><?php echo date('F j, Y g:i A', strtotime($booking['check_in'])); ?></span>
                                    </p>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-gray-300">Duration: <span
                                            class="text-gray-400"><?php echo $booking['duration']; ?> hour(s)</span></p>
                                </div>
                            </div>

                            <div class="mt-4">


                                <?php
                                $now = time();
                                $checkIn = strtotime($booking['check_in']);
                                $checkOut = strtotime($booking['check_out']);

                                if ($now < $checkIn) {
                                    $statusClass = 'bg-violet-500/20 text-violet-200 border-violet-500';
                                    $status = 'Upcoming';
                                } elseif ($now > $checkOut) {
                                    $statusClass = 'bg-gray-700/20 text-gray-300 border-gray-600';
                                    $status = 'Completed';
                                } else {
                                    $statusClass = 'bg-emerald-500/20 text-emerald-200 border-emerald-500';
                                    $status = 'Active';
                                }
                                ?>


                                <?php if ($status === 'Upcoming'): ?>
                                    <form action="controllers/process_remove_bookings.php" method="POST" class="mt-3 mb-3"
                                        onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                        <input type="hidden" name="booking_id"
                                            value="<?php echo htmlspecialchars($booking['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit"
                                            class="w-full bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-300">
                                            Cancel Booking
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border <?php echo $statusClass; ?>">
                                    <?php if ($status === 'Active'): ?>
                                        <span class="w-2 h-2 bg-emerald-400 rounded-full mr-2 animate-pulse"></span>
                                    <?php endif; ?>
                                    <?php echo $status; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <div class="bg-gray-800/50 rounded-xl p-8 backdrop-blur-lg border border-gray-700 inline-block">
                    <p class="text-gray-400 text-lg">You don't have any bookings yet.</p>
                    <a href="<?php echo BASE_URL; ?>"
                        class="mt-4 inline-block bg-gradient-to-r from-violet-600 to-purple-600 text-white font-semibold py-2 px-6 rounded-lg hover:from-violet-500 hover:to-purple-500 transition-all duration-300">Browse
                        Rooms</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>