<?php
include_once 'config/config.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/login');
    exit();
}

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $roomStmt = $conn->query("SELECT * FROM rooms ORDER BY room_id DESC");
    $rooms = $roomStmt->fetchAll(PDO::FETCH_ASSOC);

    $commentStmt = $conn->prepare("
        SELECT c.*, u.username, r.roomtitle 
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        JOIN rooms r ON c.room_id = r.room_id 
        ORDER BY c.created_at DESC 
        LIMIT 5
    ");
    $commentStmt->execute();
    $recentComments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="dist/styles.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 min-h-screen text-gray-100">
    <?php include_once 'components/navbar.php'; ?>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-violet-300">Dashboard</h1>
            <button onclick="showAddRoomForm()" 
                    class="bg-gradient-to-r from-violet-600 to-purple-600 text-white font-semibold py-2 px-4 rounded-lg hover:from-violet-500 hover:to-purple-500 transition-all duration-300">
                Add New Room
            </button>
        </div>

        <!-- Room Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-12">
            <?php foreach($rooms as $room): ?>
                <div class="backdrop-blur-lg bg-gray-800/50 rounded-xl overflow-hidden border border-gray-700 hover:border-violet-500 transition-all duration-300">
                    <div class="relative h-48">
                        <img src="<?php echo BASE_URL . '/' . $room['roomimage']; ?>" 
                             alt="Room image" 
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 to-transparent"></div>
                        <h3 class="absolute bottom-4 left-4 text-xl font-semibold text-white">
                            <?php echo htmlspecialchars($room['roomtitle']); ?>
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 rounded-lg bg-gray-700/50">
                                <div class="text-violet-300">Price</div>
                                <div class="text-gray-300">$<?php echo htmlspecialchars($room['roomprice']); ?>/hr</div>
                            </div>
                            <div class="text-center p-3 rounded-lg bg-gray-700/50">
                                <div class="text-violet-300">Seats</div>
                                <div class="text-gray-300"><?php echo htmlspecialchars($room['roomseats']); ?></div>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <button onclick="editRoom(<?php echo htmlspecialchars(json_encode($room)); ?>)" 
                                    class="flex-1 mr-2 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-500 transition-colors">
                                Edit
                            </button>
                            <button onclick="deleteRoom(<?php echo $room['room_id']; ?>)" 
                                    class="flex-1 ml-2 py-2 bg-red-600 text-white rounded-lg hover:bg-red-500 transition-colors">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Comments Section -->
        <div class="backdrop-blur-lg bg-gray-800/50 rounded-xl p-6 border border-gray-700">
            <h2 class="text-2xl font-bold text-violet-300 mb-6">Recent Comments</h2>
            <div class="space-y-4">
                <?php foreach($recentComments as $comment): ?>
                    <div class="bg-gray-700/30 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="h-8 w-8 rounded-full bg-violet-600 flex items-center justify-center text-white font-semibold">
                                    <?php echo strtoupper(substr($comment['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="text-violet-300 font-semibold"><?php echo htmlspecialchars($comment['username']); ?></div>
                                    <div class="text-sm text-gray-400"><?php echo date('F j, Y', strtotime($comment['created_at'])); ?></div>
                                </div>
                            </div>
                            <span class="text-sm text-gray-400"><?php echo htmlspecialchars($comment['roomtitle']); ?></span>
                        </div>
                        <p class="text-gray-300"><?php echo htmlspecialchars($comment['comment']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div id="roomForm" class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm hidden flex items-center justify-center">
        <div class="backdrop-blur-lg bg-gray-800/50 rounded-xl border border-gray-700 p-6 w-full max-w-md">
            <form action="controllers/process_room.php" method="POST" enctype="multipart/form-data">
                <h3 class="text-xl font-bold text-violet-300 mb-6">Room Details</h3>
                
                <input type="hidden" id="roomId" name="room_id">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-300 mb-2">Room Title</label>
                        <input type="text" name="roomtitle" 
                               class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 focus:border-violet-500 focus:ring-violet-500" required>
                    </div>
                    
                    <div>
                        <label class="block text-gray-300 mb-2">Price per Hour</label>
                        <input type="number" name="roomprice" step="0.01" 
                               class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 focus:border-violet-500 focus:ring-violet-500" required>
                    </div>
                    
                    <div>
                        <label class="block text-gray-300 mb-2">Number of Seats</label>
                        <input type="number" name="roomseats" 
                               class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 focus:border-violet-500 focus:ring-violet-500" required>
                    </div>
                    
                    <div>
                        <label class="block text-gray-300 mb-2">Number of Projectors</label>
                        <input type="number" name="roomprojectors" 
                               class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 focus:border-violet-500 focus:ring-violet-500" required>
                    </div>
                    
                    <div>
                        <label class="block text-gray-300 mb-2">Room Image</label>
                        <input type="file" name="roomimage" accept="image/*" 
                               class="w-full text-gray-300">
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="hideRoomForm()" 
                            class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg hover:from-violet-500 hover:to-purple-500 transition-all">
                        Save Room
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddRoomForm() {
            document.getElementById('roomId').value = '';
            document.getElementById('roomForm').classList.remove('hidden');
        }

        function hideRoomForm() {
            document.getElementById('roomForm').classList.add('hidden');
        }

        function editRoom(room) {
            const form = document.getElementById('roomForm');
            form.classList.remove('hidden');
            
            document.getElementById('roomId').value = room.room_id;
            form.querySelector('[name="roomtitle"]').value = room.roomtitle;
            form.querySelector('[name="roomprice"]').value = room.roomprice;
            form.querySelector('[name="roomseats"]').value = room.roomseats;
            form.querySelector('[name="roomprojectors"]').value = room.roomprojectors;
        }

        function deleteRoom(roomId) {
            if(confirm('Are you sure you want to delete this room?')) {
                window.location.href = `controllers/process_delete_room.php?id=${roomId}`;
            }
        }
    </script>
</body>
</html>