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
<title>BuddyBites - Terms & Conditions</title>

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
    letter-spacing: 0;
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

.back-btn:hover {
    opacity: 0.9;
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
    <h1>BuddyBites Terms & Conditions</h1>

    <p class="updated"><strong>Last Updated:</strong> June 2026</p>

    <p>
        Welcome to BuddyBites. By creating an account or placing an order through our platform,
        you agree to comply with the following Terms and Conditions.
    </p>

    <h2>1. User Accounts</h2>
    <ul>
        <li>Users must provide accurate and complete registration information.</li>
        <li>Users are responsible for maintaining the confidentiality of their account credentials.</li>
        <li>BuddyBites reserves the right to suspend accounts involved in fraudulent activities.</li>
    </ul>

    <h2>2. Food Orders</h2>
    <ul>
        <li>Orders are subject to restaurant availability.</li>
        <li>Prices displayed include only the listed menu price unless otherwise stated.</li>
        <li>Orders cannot be modified after payment has been confirmed.</li>
    </ul>

    <h2>3. Payments</h2>
    <ul>
        <li>Payments must be completed before an order is processed.</li>
        <li>BuddyBites accepts multiple secure payment methods.</li>
        <li>Payment confirmation serves as proof of purchase.</li>
    </ul>

    <h2>4. Delivery</h2>
    <ul>
        <li>Delivery times are estimates and may vary due to traffic or weather conditions.</li>
        <li>Customers must provide accurate delivery information.</li>
        <li>BuddyBites is not responsible for delays caused by unforeseen circumstances.</li>
    </ul>

    <h2>5. Refund Policy</h2>
    <ul>
        <li>Refund requests will be reviewed on a case-by-case basis.</li>
        <li>Approved refunds will be processed using the original payment method.</li>
    </ul>

    <h2>6. Contact</h2>
    <p>Email: support@buddybites.com</p>

    <a href="register.php" class="back-btn">&larr; Back</a>
</div>
</body>
</html>
