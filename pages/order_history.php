<?php
include("../includes/auth_check.php");
requireRole("student");
?>
<?php
include("../includes/db.php");

$user_id = $_SESSION["user_id"];

$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

function h($text) {
    return htmlspecialchars((string) $text, ENT_QUOTES, "UTF-8");
}

function statusBadge($status) {
    $status = strtolower((string) $status);

    if ($status === "delivered") {
        return '<span class="status-dot delivered"></span> Delivered';
    }

    if ($status === "out for delivery") {
        return '<span class="status-dot out"></span> Out for Delivery';
    }

    if ($status === "rider assigned") {
        return '<span class="status-dot assigned"></span> Rider Assigned';
    }

    if ($status === "cancelled") {
        return '<span class="status-dot cancelled"></span> Cancelled';
    }

    return '<span class="status-dot preparing"></span> Preparing';
}
?>

<?php include("../includes/header.php"); ?>

<div class="order-page">
    <div class="order-nav">
        <div class="logo">BUDDY BITES</div>

        <div>
            <a href="home.php"><i class="fa-solid fa-house"></i> Home</a>
            <a href="menu.php"><i class="fa-solid fa-utensils"></i> Menu</a>
            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Cart</a>
            <a class="active" href="order_history.php"><i class="fa-solid fa-clipboard-list"></i> My Orders</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="orders-container">
        <h1><span class="orders-title-icon"><i class="fa-solid fa-truck-fast"></i></span> My Orders</h1>
        <p>View your receipt and track your delivery status.</p>

        <?php if ($result->num_rows > 0) { ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <?php $receipt_no = "BB" . str_pad($row["order_id"], 5, "0", STR_PAD_LEFT); ?>

                <div class="modern-order-card">
                    <div class="modern-order-top">
                        <div>
                            <h3>Order <?php echo h($receipt_no); ?></h3>
                            <p><?php echo date("d M Y, h:i A", strtotime($row["order_date"])); ?></p>
                        </div>

                        <div class="modern-status">
                            <?php echo statusBadge($row["status"]); ?>
                        </div>
                    </div>

                    <div class="modern-order-details">
                        <div>
                            <span>Total Paid</span>
                            <b>RM<?php echo number_format($row["total_amount"], 2); ?></b>
                        </div>

                        <div>
                            <span>Estimated Delivery</span>
                            <b>15-20 mins</b>
                        </div>

                        <div>
                            <span>Payment</span>
                            <b>Paid</b>
                        </div>
                    </div>

                    <div class="modern-order-actions">
                        <a href="order_tracking.php?order_id=<?php echo $row["order_id"]; ?>">
                            Track Order
                        </a>

                        <a class="secondary" href="receipt.php?order_id=<?php echo $row["order_id"]; ?>">
                            View Receipt
                        </a>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="empty-order-box">
                <h2><i class="fa-solid fa-basket-shopping"></i> No orders yet</h2>
                <p>Your past orders will appear here after checkout.</p>
                <a href="menu.php">Browse Menu</a>
            </div>
        <?php } ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
