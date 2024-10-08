<?php
// Start session and check if the user is logged in
session_start();

// Check if the user is logged in by verifying the session token and expiration
$loggedIn = isset($_SESSION['session_token']) && time() <= $_SESSION['expires_at'];

// Get the remaining time until session expiration
$remainingTime = isset($_SESSION['expires_at']) ? $_SESSION['expires_at'] - time() : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A.R.A.Y's Homepage</title>
    <style>
        header { text-align: center; font-size: 3em; margin-top: 20px; color: #4CAF50; }
        section { padding: 20px; text-align: center; }
        footer { margin-top: 40px; text-align: center; font-size: 1.2em; }
        .buttons { margin-top: 30px; }
        .buttons form { display: inline-block; margin-right: 20px; }
    </style>
    <script>
        // JavaScript to automatically log out after session expiration
        document.addEventListener('DOMContentLoaded', (event) => {
            const remainingTime = <?php echo $remainingTime; ?> * 1000; // Convert to milliseconds
            if (remainingTime > 0) {
                setTimeout(() => {
                    alert('Your session has expired. You will be logged out.');
                    window.location.href = 'logout.php'; // Redirect to logout page
                }, remainingTime);
            }
        });
    </script>
</head>
<body>
    <header>
        A.R.A.Y's Homepage
    </header>

    <section class="welcome-message">
        <p>Welcome to A.R.A.Y's official homepage! We're glad to have you here.</p>
    </section>

    <section class="buttons">
        <!-- Show buttons based on whether the user is logged in -->
        <?php if ($loggedIn): ?>
            <!-- If logged in, show a logout button -->
            <form action="logout.php" method="POST">
                <button type="submit">Logout</button>
            </form>
        <?php else: ?>
            <!-- If not logged in, show Register and Login buttons -->
            <form action="register.php">
                <button type="submit">Register Here</button>
            </form>
            <form action="login.php">
                <button type="submit">Login Here</button>
            </form>
        <?php endif; ?>
    </section>

    <footer>
        <p>Stay connected with us! <br> Contact: contact@aray.com</p>
        <nav>
            <a href="home.php">Home</a> | 
            <a href="#services">Services</a> | 
            <a href="#contact">Contact Us</a>
        </nav>
    </footer>
</body>
</html>

