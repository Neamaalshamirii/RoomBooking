<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Search Bar -->
        <div class="mb-12 max-w-2xl mx-auto">
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-violet-500/20 to-purple-500/20 rounded-lg blur"></div>
                <div class="relative backdrop-blur-xl bg-gray-800/50 rounded-lg border border-gray-700 group-hover:border-violet-500 transition-colors duration-300">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-violet-400"></i>
                    <input type="text" 
                           id="searchInput" 
                           placeholder="Search rooms..." 
                           class="w-full pl-12 pr-4 py-4 bg-transparent text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-violet-500 rounded-lg transition-all duration-300">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8" id="roomsGrid">
            <?php 
            include_once dirname(__DIR__) . '/config/config.php';
            include_once dirname(__DIR__) . '/components/roomcard.php';
            
            try {
                $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                $stmt = $conn->prepare("SELECT * FROM rooms");
                $stmt->execute();

                while ($room = $stmt->fetch()) {
                    $roomCard = new Roomcard(
                        $room['room_id'], 
                        $room['roomtitle'], 
                        $room['roomimage'], 
                        $room['roomprice'], 
                        $room['roomseats'], 
                        $room['roomprojectors']
                    );
                    echo $roomCard->display();
                }
            } catch (PDOException $e) {
                echo '<div class="col-span-full">
                        <div class="backdrop-blur-lg bg-red-500/10 border border-red-500/50 rounded-lg p-4 text-red-200">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-3 text-red-400"></i>
                                <p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>
                            </div>
                        </div>
                      </div>';
            }
            ?>
        </div>
    </div>

    <script>
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rooms = document.querySelectorAll('#roomsGrid .room-card');
            
            rooms.forEach(room => {
                const title = room.querySelector('.room-title').textContent.toLowerCase();
                const isVisible = title.includes(searchTerm);
                
                room.style.opacity = isVisible ? '1' : '0';
                room.style.transform = isVisible ? 'scale(1)' : 'scale(0.95)';
                room.style.display = isVisible ? 'block' : 'none';
            });
        });
    </script>
</section>

<style>
    #searchInput::placeholder {
        color: rgba(156, 163, 175, 0.7);
    }
    
    .room-card {
        transition: all 0.3s ease-in-out;
    }
</style>