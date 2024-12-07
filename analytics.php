<?php
include_once 'config/config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login');
    exit();
}

$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d', strtotime('+30 days'));
$userBookings = [];
$roomStats = [];
$timeSlots = [];

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $roomStatsStmt = $conn->prepare("
        SELECT 
            r.roomtitle, 
            r.room_id,
            COUNT(b.id) as total_bookings,
            COUNT(DISTINCT b.user_id) as unique_users,
            COALESCE(SUM(b.duration), 0) as total_hours
        FROM rooms r
        LEFT JOIN bookings b ON r.room_id = b.room_id 
        WHERE (:start_date IS NULL OR DATE(b.check_in) >= :start_date)
        AND (:end_date IS NULL OR DATE(b.check_in) <= :end_date)
        GROUP BY r.room_id, r.roomtitle
        ORDER BY total_bookings DESC, r.roomtitle ASC
    ");

    $roomStatsStmt->bindParam(':start_date', $startDate);
    $roomStatsStmt->bindParam(':end_date', $endDate);
    $roomStatsStmt->execute();
    $roomStats = $roomStatsStmt->fetchAll(PDO::FETCH_ASSOC);

    $availableSlots = [
        '09:00',
        '10:00',
        '11:00',
        '12:00',
        '13:00',
        '14:00',
        '15:00',
        '16:00'
    ];

    $slotStmt = $conn->prepare("
        SELECT 
            time_slot,
            COUNT(*) as slot_count
        FROM bookings
        GROUP BY time_slot
        ORDER BY CAST(time_slot AS TIME)
    ");
    $slotStmt->execute();
    $dbSlots = $slotStmt->fetchAll(PDO::FETCH_ASSOC);

    // Create time slots array with counts
    $slotCounts = array_column($dbSlots, 'slot_count', 'time_slot');
    $timeSlots = array_map(function ($slot) use ($slotCounts) {
        return [
            'time_slot' => $slot,
            'slot_count' => $slotCounts[$slot] ?? 0
        ];
    }, $availableSlots);

    $bookingStmt = $conn->prepare("
        SELECT 
            b.*,
            r.roomtitle,
            DATE_FORMAT(b.check_in, '%Y-%m-%d %H:%i') as start_time,
            DATE_FORMAT(b.check_out, '%Y-%m-%d %H:%i') as end_time,
            b.duration
        FROM bookings b
        JOIN rooms r ON b.room_id = r.room_id
        WHERE b.user_id = :user_id
        ORDER BY b.check_in DESC
        LIMIT 10
    ");

    $userId = $_SESSION['user']['id'];
    $bookingStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $bookingStmt->execute();
    $userBookings = $bookingStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>
    <link href="dist/styles.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-gray-900 to-gray-800 min-h-screen text-gray-100">
    <?php include_once 'components/navbar.php'; ?>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="backdrop-blur-lg bg-gray-800/50 rounded-xl p-8 shadow-2xl border border-gray-700">

            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-violet-300 mb-4">Room Usage Statistics</h2>
                    <?php if (!empty($roomStats)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full rounded-lg overflow-hidden">
                                <thead>
                                    <tr class="bg-gray-700/50">
                                        <th class="py-3 px-4 text-left text-sm font-medium text-violet-200 uppercase tracking-wider">
                                            Room Name
                                        </th>
                                        <th class="py-3 px-4 text-left text-sm font-medium text-violet-200 uppercase tracking-wider">
                                            Total Bookings
                                        </th>
                                        <th class="py-3 px-4 text-left text-sm font-medium text-violet-200 uppercase tracking-wider">
                                            Unique Users
                                        </th>
                                        <th class="py-3 px-4 text-left text-sm font-medium text-violet-200 uppercase tracking-wider">
                                            Total Hours
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    <?php foreach ($roomStats as $stat): ?>
                                        <tr class="hover:bg-gray-700/30 transition duration-150">
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($stat['roomtitle']); ?></td>
                                            <td class="py-3 px-4"><?php echo $stat['total_bookings']; ?></td>
                                            <td class="py-3 px-4"><?php echo $stat['unique_users']; ?></td>
                                            <td class="py-3 px-4"><?php echo $stat['total_hours'] ?? 0; ?> hours</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-400">No room statistics available for the selected period.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="mb-12">
                <h2 class="text-2xl font-bold text-violet-300 mb-6">Popular Time Slots</h2>
                <?php if (!empty($timeSlots)):
                    $maxBookings = max(array_column($timeSlots, 'slot_count')); ?>

                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                        <?php foreach ($timeSlots as $slot):
                            $percentage = ($slot['slot_count'] / $maxBookings) * 100;
                            $intensity = $percentage >= 75 ? 'from-rose-500 to-fuchsia-600' :
                                ($percentage >= 50 ? 'from-violet-500 to-purple-600' :
                                    'from-violet-400 to-purple-500');
                            ?>
                            <div
                                class="group relative bg-gray-800/30 rounded-lg p-4 hover:bg-gray-700/30 transition-all duration-300 overflow-hidden">
                                <div class="text-center mb-3">
                                    <span class="text-sm font-medium text-gray-300">
                                        <?php echo date('g:i A', strtotime($slot['time_slot'])); ?>
                                    </span>
                                </div>

                                <div class="h-32 w-full flex items-end justify-center mb-2">
                                    <div class="w-full bg-gray-700/30 rounded-t-lg relative">
                                        <div class="absolute bottom-0 w-full bg-gradient-to-t <?php echo $intensity; ?> 
                                      rounded-t-lg transition-all duration-500 group-hover:scale-105"
                                            style="height: <?php echo $percentage; ?>%">
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <span class="inline-block px-2 py-1 text-xs rounded-full 
                                   <?php echo $percentage >= 75 ? 'bg-rose-500/20 text-rose-200' :
                                       ($percentage >= 50 ? 'bg-violet-500/20 text-violet-200' :
                                           'bg-purple-500/20 text-purple-200'); ?>">
                                        <?php echo $slot['slot_count']; ?> bookings
                                    </span>
                                </div>

                                <?php if ($percentage >= 75): ?>
                                    <div class="absolute top-2 right-2">
                                        <span class="flex h-2 w-2">
                                            <span
                                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <div class="text-center py-8">
                        <p class="text-gray-400">No time slot data available.</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($userBookings)): ?>
                <div>
                    <h2 class="text-2xl font-bold text-violet-300 mb-4">Your Recent Bookings</h2>
                    <div class="grid grid-cols-1 gap-4">
                        <?php foreach ($userBookings as $booking): ?>
                            <div class="p-4 rounded-lg border border-gray-700 hover:border-violet-500 transition-colors bg-gray-800/50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-lg text-violet-300">
                                            <?php echo htmlspecialchars($booking['roomtitle']); ?></h3>
                                        <p class="text-gray-400">Duration: <?php echo $booking['duration']; ?> hours</p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs <?php echo strtotime($booking['check_out']) < time() ? 'bg-gray-600 text-gray-100' : 'bg-violet-600 text-white'; ?>">
                                        <?php echo strtotime($booking['check_out']) < time() ? 'Completed' : 'Upcoming'; ?>
                                    </span>
                                </div>
                                <div class="mt-2 text-sm text-gray-400">
                                    <p>Check In: <?php echo $booking['start_time']; ?></p>
                                    <p>Check Out: <?php echo $booking['end_time']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>