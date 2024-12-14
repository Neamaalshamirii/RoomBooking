<?php 
include_once 'config/config.php'; 

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="dist/styles.css" rel="stylesheet">
    <title>Room Booking | Signup</title>
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 min-h-screen text-gray-100">
    <?php include_once 'components/navbar.php'; ?>
    
    <section class="flex items-center justify-center min-h-screen px-4">
        <div class="backdrop-blur-lg bg-gray-800/50 rounded-xl border border-gray-700 p-8 max-w-md w-full">
            <h2 class="text-2xl font-bold text-violet-300 mb-6">Create Account</h2>
            
            <?php 
                if(isset($_SESSION['error'])) {
                    echo "<div class='bg-red-500/20 border border-red-500/50 text-red-200 p-4 rounded-lg mb-4'>" . $_SESSION['error'] . "</div>";
                    unset($_SESSION['error']);
                }
                
                if(isset($_SESSION['success'])) {
                    echo "<div class='bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 p-4 rounded-lg mb-4'>" . $_SESSION['success'] . "</div>";
                    unset($_SESSION['success']);
                }
            ?>

            <form class="space-y-6" action="controllers/process_signup.php" method="POST">
                <div class="form-group">
                    <label for="fullname" class="block text-gray-300 mb-2">Full Name</label>
                    <input type="text" id="fullname" name="fullname" 
                           class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 placeholder-gray-500 focus:border-violet-500 focus:ring-violet-500 focus:ring-1" required>
                </div>

                <div class="form-group">
                    <label for="email" class="block text-gray-300 mb-2">Email</label>
                    <input type="email" id="email" name="email" 
                           class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 placeholder-gray-500 focus:border-violet-500 focus:ring-violet-500 focus:ring-1" required>
                </div>

                <div class="form-group">
                    <label for="username" class="block text-gray-300 mb-2">Username</label>
                    <input type="text" id="username" name="username" 
                           class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 placeholder-gray-500 focus:border-violet-500 focus:ring-violet-500 focus:ring-1" required>
                </div>

                <div class="form-group">
                    <label for="password" class="block text-gray-300 mb-2">Password</label>
                    <input type="password" id="password" name="password" 
                           class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 placeholder-gray-500 focus:border-violet-500 focus:ring-violet-500 focus:ring-1" required>
                </div>

                <div class="form-group">
                    <label for="repassword" class="block text-gray-300 mb-2">Confirm Password</label>
                    <input type="password" id="repassword" name="repassword" 
                           class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 placeholder-gray-500 focus:border-violet-500 focus:ring-violet-500 focus:ring-1" required>
                </div>

                <button type="submit" 
                        class="w-full bg-gradient-to-r from-violet-600 to-purple-600 text-white font-semibold py-2 px-4 rounded-lg hover:from-violet-500 hover:to-purple-500 transition-all duration-300">
                    Sign Up
                </button>

                <p class="text-center text-gray-400 mt-4">
                    Already have an account? 
                    <a href="<?php echo BASE_URL ?>/login.php" class="text-violet-400 hover:text-violet-300 transition-colors">
                        Log In
                    </a>
                </p>
            </form>
        </div>
    </section>
</body>
</html>
