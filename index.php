<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="dist/styles.css" rel="stylesheet">
    <title>Room bookings</title>
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 min-h-screen text-gray-100 font-sans antialiased">
    <?php include_once 'components/navbar.php'; ?>
    <?php include_once 'components/hero.php'; ?>
    <?php include_once 'components/browserrooms.php'; ?>

    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rooms = document.getElementsByClassName('room-card');

            Array.from(rooms).forEach(room => {
                try {
                    const title = room.querySelector('.room-title')?.textContent?.toLowerCase() || '';
                    const price = room.querySelector('.room-price')?.textContent?.toLowerCase() || '';
                    const seats = room.querySelector('.room-details .seats')?.textContent?.toLowerCase() || '';

                    const isMatch = title.includes(searchValue) || 
                                  price.includes(searchValue) || 
                                  seats.includes(searchValue);

                    if (isMatch) {
                        room.classList.remove('hidden', 'opacity-0', 'scale-95');
                        room.classList.add('block', 'opacity-100', 'scale-100', 'transition-all', 'duration-300', 'ease-in-out');
                    } else {
                        room.classList.remove('block', 'opacity-100', 'scale-100');
                        room.classList.add('hidden', 'opacity-0', 'scale-95', 'transition-all', 'duration-300', 'ease-in-out');
                    }

                } catch (error) {
                    console.error('Error processing room:', error);
                }
            });
        });

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.remove('opacity-0', 'translate-y-4');
                    entry.target.classList.add('opacity-100', 'translate-y-0');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.room-card').forEach(card => {
            card.classList.add('transition-all', 'duration-500', 'ease-in-out');
            observer.observe(card);
        });
    </script>
</body>
</html>