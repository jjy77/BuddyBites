<?php
$success = "";
$error = "";
$name = "";
$email = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($name == "" || $email == "" || $message == "") {
        $error = "Please fill in all required fields.";
    } else {
        $success = "Thank you for contacting BuddyBites. We will get back to you soon.";

        $name = "";
        $email = "";
        $message = "";
    }
}
?>

<?php include("../includes/header.php"); ?>

<main class="contact-page">
    <nav class="contact-topbar">
        <a class="logo" href="../index.php">BUDDY BITES</a>

        <div class="contact-nav">
            <a href="../index.php"><i class="fa-solid fa-house"></i> Home</a>
            <a href="menu.php"><i class="fa-solid fa-utensils"></i> Menu</a>
            <a href="about.php"><i class="fa-solid fa-circle-info"></i> About</a>
            <a class="active" href="contact.php"><i class="fa-solid fa-envelope"></i> Contact</a>
            <a href="login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
            <a href="register.php"><i class="fa-solid fa-user-plus"></i> Register</a>
        </div>
    </nav>

    <section class="contact-hero">
        <p>Contact BuddyBites</p>
        <h1>Contact Us</h1>
        <span>We'd love to hear from you.</span>
    </section>

    <section class="contact-layout">
        <div class="contact-info-grid">
            <article class="contact-info-card">
                <span><i class="fa-solid fa-phone"></i></span>
                <small>Phone</small>
                <b>+60 11-8888 1234</b>
            </article>

            <article class="contact-info-card">
                <span><i class="fa-solid fa-envelope"></i></span>
                <small>Email</small>
                <b>support@buddybites.com</b>
            </article>

            <article class="contact-info-card address-card">
                <span><i class="fa-solid fa-location-dot"></i></span>
                <small>Address</small>
                <b>
                    BuddyBites Sdn. Bhd.<br>
                    Innovation Hub<br>
                    Cyberjaya<br>
                    63000 Selangor<br>
                    Malaysia
                </b>
            </article>
        </div>

        <form class="contact-form" method="POST">
            <div class="contact-form-grid">
                <label>
                    Your Name
                    <input type="text" name="name" placeholder="Enter your name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                </label>

                <label>
                    Your Email
                    <input type="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                </label>
            </div>

            <label>
                Your Message
                <textarea name="message" placeholder="Write your message"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </label>

            <?php if ($error != "") { ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?>
                </div>
            <?php } ?>

            <?php if ($success != "") { ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success, ENT_QUOTES, "UTF-8"); ?>
                </div>
            <?php } ?>

            <button type="submit">
                <i class="fa-solid fa-paper-plane"></i>
                Send Message
            </button>
        </form>
    </section>
</main>

<?php include("../includes/footer.php"); ?>
