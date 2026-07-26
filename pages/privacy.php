<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BuddyBites - Privacy Policy</title>

<style>
body {
    margin: 0;
    min-height: 100vh;
    font-family: "Segoe UI", Inter, Roboto, "Helvetica Neue", Arial, sans-serif;
    background:
        radial-gradient(circle at 50% 0%, rgba(245, 158, 11, 0.07), transparent 32%),
        #fff9f5;
    color: #2c2422;
}

.container {
    max-width: 820px;
    margin: 46px auto;
    padding: 34px 42px;
    background: #ffffff;
    border: 1px solid #eaded6;
    border-radius: 18px;
    box-shadow: 0 16px 36px rgba(82, 54, 44, 0.075);
}

h1 {
    margin: 0 0 14px;
    color: #8b1e2d;
    font-size: 34px;
    line-height: 1.15;
}

h2 {
    margin: 28px 0 10px;
    color: #8b1e2d;
    font-size: 23px;
    line-height: 1.25;
}

p {
    margin: 0 0 18px;
    color: #3a302d;
    font-size: 16px;
    line-height: 1.65;
}

ul {
    margin: 0 0 10px 18px;
    padding: 0;
    color: #3a302d;
    font-size: 15px;
    line-height: 1.65;
}

li {
    margin: 6px 0;
}

.updated {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 18px;
    padding: 8px 12px;
    border: 1px solid #eaded6;
    border-radius: 999px;
    background: #fff9f5;
    color: #6b5752;
    font-size: 14px;
}

.updated strong {
    color: #8b1e2d;
}

.back-btn {
    display: inline-block;
    margin-top: 28px;
    padding: 11px 22px;
    background: #8b1e2d;
    color: #ffffff;
    text-decoration: none;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 700;
}

@media (max-width: 700px) {
    .container {
        margin: 20px;
        padding: 26px 22px;
    }

    h1 {
        font-size: 28px;
    }
}
</style>
</head>

<body>
<div class="container">
    <h1>BuddyBites Privacy Policy</h1>
    <p class="updated"><strong>Last Updated:</strong> June 2026</p>

    <p>
        BuddyBites respects your privacy. This Privacy Policy explains how we collect, use,
        and protect user information.
    </p>

    <h2>1. Information We Collect</h2>
    <ul>
        <li>Full name</li>
        <li>Email address</li>
        <li>Phone number</li>
        <li>Delivery address</li>
        <li>Order and payment details</li>
    </ul>

    <h2>2. How We Use Your Information</h2>
    <ul>
        <li>To create and manage user accounts.</li>
        <li>To process food orders and delivery requests.</li>
        <li>To contact users regarding order updates.</li>
        <li>To improve BuddyBites services and user experience.</li>
    </ul>

    <h2>3. Data Protection</h2>
    <p>We use reasonable security measures to protect user data from unauthorised access, misuse, or disclosure.</p>

    <h2>4. Data Sharing</h2>
    <p>BuddyBites does not sell personal information. Data may only be shared with restaurants or riders for order fulfilment.</p>

    <h2>5. User Rights</h2>
    <p>Users may request correction or removal of their personal information where applicable.</p>

    <h2>6. Contact</h2>
    <p>Email: support@buddybites.com</p>

    <a href="register.php" class="back-btn">&larr; Back</a>
</div>
</body>
</html>
