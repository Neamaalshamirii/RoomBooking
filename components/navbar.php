<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once dirname(__DIR__) . '/config/config.php';
?>

<nav class="backdrop-blur-lg bg-gray-900/60 border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center py-4">
        <a class="flex items-center" href="<?php echo BASE_URL; ?>">
            <img src="<?php echo BASE_URL; ?>/assets/Logo.png" alt="Car Company Logo" class="h-8 w-auto">
        </a>

        <button class="menu-toggle block lg:hidden" type="button">
            <span class="menu-icon"></span>
        </button>
        <div class="hidden lg:flex lg:items-center lg:space-x-6">
            <ul class="flex space-x-6">
                <li>
                    <a href="<?php echo BASE_URL; ?>" 
                       class="text-gray-300 hover:text-violet-300 transition-colors duration-300">Home</a>
                </li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/analytics.php" 
                           class="text-gray-300 hover:text-violet-300 transition-colors duration-300">Analytics</a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/bookings.php" 
                           class="text-gray-300 hover:text-violet-300 transition-colors duration-300">My Bookings</a>
                    </li>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/dashboard.php" 
                               class="text-gray-300 hover:text-violet-300 transition-colors duration-300">Admin Dashboard</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </div>

        <div class="flex items-center space-x-4">
            <?php if (isset($_SESSION['user'])): ?>
                <div class="flex items-center space-x-3">
                    <span class="text-gray-300"><?php echo $_SESSION['user']['username']; ?></span>
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-violet-500 to-purple-600 rounded-full animate-pulse"></div>
                        <img src="<?php echo BASE_URL . '/' . (isset($_SESSION['user']['image']) && !empty(trim($_SESSION['user']['image'])) ? $_SESSION['user']['image'] : 'assets/userimages/default-profile.jpg'); ?>" 
                             alt="User Profile Picture" 
                             class="h-8 w-8 rounded-full object-cover relative z-10 border-2 border-violet-500">
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/profile.php" 
                   class="bg-violet-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-violet-700 transition-all duration-300">Profile</a>
                <a href="<?php echo BASE_URL; ?>/controllers/process_logout.php" 
                   class="bg-violet-700 text-gray-300 font-semibold py-2 px-4 rounded-lg hover:bg-violet-500  transition-all duration-300">Logout</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/login.php" 
                   class="bg-violet-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-violet-700 transition-all duration-300">Sign In</a>
                <a href="<?php echo BASE_URL; ?>/signup.php" 
                   class="bg-violet-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-violet-700 transition-all duration-300">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>