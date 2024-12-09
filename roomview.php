<?php
include_once 'config/config.php';
session_start();

$roomId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get room details
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE room_id = ?");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get comments
    $commentStmt = $conn->prepare("
        SELECT c.*, u.username, u.image as user_image 
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.room_id = ? 
        ORDER BY c.created_at DESC
    ");
    $commentStmt->execute([$roomId]);
    $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room View - <?php echo htmlspecialchars($room['roomtitle']); ?></title>
    <link href="dist/styles.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-gray-900 to-gray-800 min-h-screen text-gray-100">
    <?php include_once 'components/navbar.php'; ?>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="backdrop-blur-lg bg-gray-800/50 rounded-xl border border-gray-700 p-8">
            <div class="flex flex-col lg:flex-row">
                <img src="<?php echo BASE_URL . '/' . htmlspecialchars($room['roomimage']); ?>"
                    alt="<?php echo htmlspecialchars($room['roomtitle']); ?>"
                    class="w-full lg:w-1/2 h-64 object-cover rounded-lg mb-6 lg:mb-0 lg:mr-6">

                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-violet-300 mb-4">
                        <?php echo htmlspecialchars($room['roomtitle']); ?>
                    </h1>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <div class="bg-gray-700/50 p-4 rounded-lg">
                            <span class="block text-gray-300 font-semibold">Price:</span>
                            <span
                                class="block text-gray-100">$<?php echo htmlspecialchars($room['roomprice']); ?>/hour</span>
                        </div>
                        <div class="bg-gray-700/50 p-4 rounded-lg">
                            <span class="block text-gray-300 font-semibold">Seats:</span>
                            <span class="block text-gray-100"><?php echo htmlspecialchars($room['roomseats']); ?></span>
                        </div>
                        <div class="bg-gray-700/50 p-4 rounded-lg">
                            <span class="block text-gray-300 font-semibold">Projectors:</span>
                            <span
                                class="block text-gray-100"><?php echo htmlspecialchars($room['roomprojectors']); ?></span>
                        </div>
                    </div>

                    <div class="bg-gray-700/50 p-4 rounded-lg">
                        <h2 class="text-2xl font-bold text-violet-300 mb-4">Book This Room</h2>
                        <?php if (isset($_SESSION['user'])): ?>

                            <form action="controllers/process_booking.php" method="POST" class="space-y-4">

                                <?php if (isset($_SESSION['error_booking'])): ?>
                                    <div class="bg-red-500/20 border border-red-500/50 text-red-200 p-4 rounded-lg">
                                        <?php echo $_SESSION['error_booking'];
                                        unset($_SESSION['error_booking']); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['success_booking'])): ?>
                                    <div class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 p-4 rounded-lg">
                                        <?php echo $_SESSION['success_booking'];
                                        unset($_SESSION['success_booking']); ?>
                                    </div>
                                <?php endif; ?>

                                <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                <div class="form-group">
                                    <label for="booking_date" class="block text-gray-300">Date</label>
                                    <input type="date" id="booking_date" name="booking_date"
                                        class="mt-1 block w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 focus:border-violet-500 focus:ring-violet-500"
                                        required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="time_slot" class="block text-gray-300">Time Slot</label>
                                    <select id="time_slot" name="time_slot"
                                        class="mt-1 block w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 focus:border-violet-500 focus:ring-violet-500"
                                        required>

                                        <option value="">Select time</option>
                                        <option value="09:00">09:00 - 10:00</option>
                                        <option value="10:00">10:00 - 11:00</option>
                                        <option value="11:00">11:00 - 12:00</option>
                                        <option value="12:00">12:00 - 13:00</option>
                                        <option value="13:00">13:00 - 14:00</option>
                                        <option value="14:00">14:00 - 15:00</option>
                                        <option value="15:00">15:00 - 16:00</option>
                                        <option value="16:00">16:00 - 17:00</option>
                                    </select>
                                </div>
                                <button type="submit"
                                    class="w-full bg-gradient-to-r from-violet-600 to-purple-600 text-white font-semibold py-2 px-4 rounded-lg hover:from-violet-500 hover:to-purple-500 transition-all duration-300">Book
                                    Now</button>
                            </form>
                        <?php else: ?>
                            <p class="text-gray-400">Please <a href="<?php echo BASE_URL; ?>/login"
                                    class="text-violet-400 hover:text-violet-300 transition-colors">login</a> to book this
                                room.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-2xl font-bold text-violet-600 mb-4">Comments</h2>
                <?php if (isset($_SESSION['user'])): ?>
                    <form action="controllers/process_comment.php" method="POST" class="space-y-4 mb-6">
                        <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                        <textarea name="comment"
                            class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 focus:border-violet-500 focus:ring-violet-500"
                            required placeholder="Write your comment..."></textarea>
                        <button type="submit"
                            class="w-full bg-gradient-to-r from-violet-600 to-purple-600 text-white font-semibold py-2 px-4 rounded-lg hover:from-violet-500 hover:to-purple-500 transition-all duration-300">Post
                            Comment</button>
                    </form>
                <?php endif; ?>

                <div class="space-y-6">
                    <?php
                    $parentComments = array_filter($comments, function ($comment) {
                        return $comment['parent_id'] === null;
                    });

                    foreach ($parentComments as $comment):
                        $replies = array_filter($comments, function ($reply) use ($comment) {
                            return $reply['parent_id'] === $comment['id'];
                        });
                        ?>
                        <div class="backdrop-blur-lg bg-gray-800/50 rounded-xl border border-gray-700 p-6">
                            <div class="flex items-start mb-4">
                                <img src="<?php echo BASE_URL . '/' . (isset($comment['user_image']) && !empty(trim($comment['user_image'])) ? $comment['user_image'] : 'assets/userimages/default-profile.jpg'); ?>"
                                    alt="User profile" class="w-12 h-12 rounded-full object-cover mr-4">
                                <div class="flex-1">
                                    <div class="flex justify-between items-center">
                                        <strong
                                            class="text-violet-300"><?php echo htmlspecialchars($comment['username']); ?></strong>
                                        <small
                                            class="text-gray-400"><?php echo date('F j, Y', strtotime($comment['created_at'])); ?></small>
                                    </div>
                                    <p class="text-gray-300"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                    <?php if (isset($_SESSION['user']) && ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['id'] === $comment['user_id'])): ?>
                                        <button class="text-red-400 hover:text-red-300 mt-2"
                                            onclick="confirmDelete(<?php echo $comment['id']; ?>)">Delete</button>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                                        <button class="text-violet-400 hover:text-violet-300 mt-2 ml-4"
                                            onclick="toggleReplyForm(<?php echo $comment['id']; ?>)">Reply</button>
                                        <form id="replyForm-<?php echo $comment['id']; ?>"
                                            action="controllers/process_comment.php" method="POST"
                                            class="space-y-4 mt-4 hidden">
                                            <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                            <input type="hidden" name="room_id" value="<?php echo $roomId; ?>">
                                            <textarea name="comment"
                                                class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 focus:border-violet-500 focus:ring-violet-500"
                                                required placeholder="Write a reply..."></textarea>
                                            <button type="submit"
                                                class="w-full bg-gradient-to-r from-violet-600 to-purple-600 text-white font-semibold py-2 px-4 rounded-lg hover:from-violet-500 hover:to-purple-500 transition-all duration-300">Post
                                                Reply</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php foreach ($replies as $reply): ?>
                                <div class="bg-gray-700/30 p-4 rounded-lg mt-4 ml-12">
                                    <div class="flex items-start mb-4">
                                        <img src="<?php echo BASE_URL . '/' . (isset($reply['user_image']) && !empty(trim($reply['user_image'])) ? $reply['user_image'] : 'assets/userimages/default-profile.jpg'); ?>"
                                            alt="Admin profile" class="w-12 h-12 rounded-full object-cover mr-4">
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <strong
                                                    class="text-violet-300"><?php echo htmlspecialchars($reply['username']); ?>
                                                    (Admin)</strong>
                                                <small
                                                    class="text-gray-400"><?php echo date('F j, Y', strtotime($reply['created_at'])); ?></small>
                                            </div>
                                            <p class="text-gray-300"><?php echo htmlspecialchars($reply['comment']); ?></p>
                                            <?php if (isset($_SESSION['user']) && ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['id'] === $reply['user_id'])): ?>
                                                <button class="text-red-400 hover:text-red-300 mt-2"
                                                    onclick="confirmDelete(<?php echo $reply['id']; ?>)">Delete</button>
                                            <?php endif; ?>
                                            <div class="text-gray-400 text-sm mt-2">
                                                Replying to <span
                                                    class="text-violet-300"><?php echo htmlspecialchars($comment['username']); ?></span>:
                                                "<?php echo htmlspecialchars(substr($comment['comment'], 0, 60)) . (strlen($comment['comment']) > 60 ? '...' : ''); ?>"
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleReplyForm(commentId) {
            const form = document.getElementById(`replyForm-${commentId}`);
            form.classList.toggle('hidden');
        }

        function confirmDelete(commentId) {
            if (confirm('Are you sure you want to delete this comment?')) {
                window.location.href = `controllers/process_delete_comment.php?id=${commentId}&room_id=<?php echo $roomId; ?>`;
            }
        }
    </script>
</body>

</html>