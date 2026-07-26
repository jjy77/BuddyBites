<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION["role"] ?? "guest";
?>

<hr>

<a href="home.php">Home</a> |
<a href="menu.php">Menu</a> |
<a href="about.php">About</a> |
<a href="contact.php">Contact</a> |

<?php if ($role === "student") { ?>
    <a href="cart.php">Cart</a> |
    <a href="order_history.php">My Orders</a> |
<?php } ?>

<?php if ($role === "restaurant") { ?>
    <a href="restaurant_dashboard.php">Restaurant Dashboard</a> |
    <a href="restaurant_menu.php">Menu Management</a> |
    <a href="restaurant_orders.php">Orders</a> |
<?php } ?>

<?php if ($role === "rider") { ?>
    <a href="rider_dashboard.php">Rider Dashboard</a> |
<?php } ?>

<?php if ($role === "admin") { ?>
    <a href="admin_dashboard.php">Admin Dashboard</a> |
    <a href="manage_orders.php">Manage Orders</a> |
    <a href="restaurant_management.php">Restaurants</a> |
<?php } ?>

<?php if ($role === "guest") { ?>
    <a href="login.php">Login</a> |
    <a href="register.php">Register</a>
<?php } else { ?>
    <a href="logout.php">Logout</a>
<?php } ?>

<hr>