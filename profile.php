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

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user']['id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile Management</title>
    <link href="dist/styles.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-gray-900 to-gray-800 min-h-screen text-gray-100">
    <?php include_once 'components/navbar.php'; ?>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="backdrop-blur-lg bg-gray-800/50 rounded-xl border border-gray-700 p-8">
            <div class="flex flex-col md:flex-row items-center gap-6 mb-8">
                <div class="relative group">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-violet-500 to-purple-600 rounded-full animate-pulse">
                    </div>
                    <img src="<?php echo htmlspecialchars(BASE_URL . '/' . (isset($user['image']) && !empty(trim($user['image'])) ? $user['image'] : 'assets/userimages/default-profile.jpg')); ?>"
                        alt="Profile Picture"
                        class="w-32 h-32 rounded-full object-cover relative z-10 border-4 border-gray-800"
                        id="preview-image">
                </div>
                <div class="text-center md:text-left">
                    <h1 class="text-3xl font-bold text-violet-300">Profile Management</h1>
                    <p class="text-gray-400 mt-2">Update your personal information</p>
                </div>
            </div>

            <form class="space-y-6" action="controllers/process_profile.php" method="POST"
                enctype="multipart/form-data">
                <?php if (isset($_SESSION['error_profile'])): ?>
                    <div class="bg-red-500/20 border border-red-500/50 text-red-200 p-4 rounded-lg">
                        <?php echo $_SESSION['error_profile'];
                        unset($_SESSION['error_profile']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success_profile'])): ?>
                    <div class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 p-4 rounded-lg">
                        <?php echo $_SESSION['success_profile'];
                        unset($_SESSION['success_profile']); ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label for="full-name" class="block text-gray-300 mb-2">Full Name</label>
                        <input type="text" id="full-name" name="full_name"
                            value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>"
                            class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 focus:border-violet-500 focus:ring-violet-500"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="username" class="block text-gray-300 mb-2">Username</label>
                        <input type="text" id="username" name="username"
                            value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
                            class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 focus:border-violet-500 focus:ring-violet-500"
                            required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="block text-gray-300 mb-2">Email</label>
                    <input type="email" id="email" name="email"
                        value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                        class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 focus:border-violet-500 focus:ring-violet-500"
                        required>
                </div>


                <h2 class="text-xl font-bold text-violet-300 mb-6">Change Password</h2>

                <div class="space-y-6">
                    <div class="form-group">
                        <label for="current-password" class="block text-gray-300 mb-2">Current Password</label>
                        <input type="password" id="current-password" name="current_password"
                            class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 focus:border-violet-500 focus:ring-violet-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="new-password" class="block text-gray-300 mb-2">New Password</label>
                            <input type="password" id="new-password" name="new_password"
                                class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 focus:border-violet-500 focus:ring-violet-500">
                        </div>

                        <div class="form-group">
                            <label for="confirm-password" class="block text-gray-300 mb-2">Confirm New Password</label>
                            <input type="password" id="confirm-password" name="confirm_password"
                                class="w-full bg-gray-900/50 border border-gray-700 rounded-lg py-2 px-4 text-gray-100 focus:border-violet-500 focus:ring-violet-500">
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="profile-picture" class="block text-gray-300 mb-2">Update Profile Picture</label>
                        <input type="file" id="profile-picture" name="image" accept="image/*"
                            class="w-full text-gray-300" onchange="previewImage(this);">
                    </div>
                    <div class="flex flex-col md:flex-row justify-end gap-4 mt-8">
                        <?php if (isset($user['image']) && !empty(trim($user['image'])) && $user['image'] != 'assets/userimages/default-profile.jpg'): ?>
                            <button type="button"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-500 transition-colors"
                                onclick="confirmDeleteImage()">
                                Delete Profile Picture
                            </button>
                        <?php endif; ?>

                        <button type="submit"
                            class="px-6 py-2 bg-gradient-to-r from-violet-600 to-purple-600 text-white font-semibold rounded-lg hover:from-violet-500 hover:to-purple-500 transition-all duration-300">
                            Save Changes
                        </button>
                    </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('preview-image').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function confirmDeleteImage() {
            if (confirm('Are you sure you want to delete your profile picture?')) {
                window.location.href = 'controllers/process_delete_image.php';
            }
        }
    </script>
</body>

</html>