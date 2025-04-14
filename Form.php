<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Analysis Tool</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<!-- Navigation Bar -->
<header>
    <nav>
        <h1>Survey Analysis Tool</h1>
        <ul>
            <li><a href="Form.php">Home</a></li>
            <li><a href="survey.php">Survey</a></li>
            <li><a href="results.php">Results</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        

            <?php if (isset($_SESSION["user_id"])): ?>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

</>


<section class="hero" style="background: url('survyback.png') no-repeat center center/cover; height:100vh; position:relative; display: flex; align-items: center; justify-content: center; color: white; text-align: center;">
  <div class="hero-content" style="position:relative; z-index:2; max-width:700px;">
  <h2 style="color: #ffd700;">Welcome to the Survey Analysis Tool!</h2>

  <p style="color: #00ff00;">Analyze customer feedback, improve service quality, and make data-driven decisions.</p>

    <a href="survey.php" class="cta-button">Start Your Survey</a>
  </div>
  <!-- Optional Overlay: If you want a dark overlay over the image -->
  <div style="position:absolute; top:0; left:0; right:0; bottom:0; background: rgba(0, 0, 0, 0.5); z-index:1;"></div>
</section>


<!-- Footer -->
<footer>
    <p>&copy; 2025 Survey Analysis Tool | Designed for SMEs</p>
</footer>

</body>
</html> 