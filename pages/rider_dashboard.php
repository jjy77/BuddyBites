<?php
include("../includes/auth_check.php");
requireRole("rider");
include("../includes/db.php");

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

if (isset($_GET["pickup"])) {
    $order_id = (int) $_GET["pickup"];
    $stmt = $conn->prepare("UPDATE orders SET status='Out for Delivery' WHERE order_id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    header("Location: rider_dashboard.php");
    exit();
}

if (isset($_GET["delivered"])) {
    $order_id = (int) $_GET["delivered"];
    $stmt = $conn->prepare("UPDATE orders SET status='Delivered' WHERE order_id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    header("Location: rider_dashboard.php");
    exit();
}

$sql = "
SELECT orders.order_id, orders.total_amount, orders.status, orders.order_date,
       users.full_name AS student_name,
       GROUP_CONCAT(order_items.item_name SEPARATOR ', ') AS foods,
       GROUP_CONCAT(DISTINCT order_items.restaurant_name SEPARATOR ', ') AS restaurants
FROM orders
JOIN users ON orders.user_id = users.user_id
JOIN order_items ON orders.order_id = order_items.order_id
WHERE orders.status IN ('Ready', 'Ready for Pickup', 'Out for Delivery')
GROUP BY orders.order_id, orders.total_amount, orders.status, orders.order_date, users.full_name
ORDER BY orders.order_date DESC
";

$result = $conn->query($sql);

$deliveries = [];
$ready_count = 0;
$active_count = 0;
$total_value = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $deliveries[] = $row;
        $total_value += (float)$row["total_amount"];

        if ($row["status"] === "Out for Delivery") {
            $active_count++;
        } else {
            $ready_count++;
        }
    }
}
?>

<?php include("../includes/header.php"); ?>

<div class="restaurant-dashboard rider-dashboard-page">
    <div class="restaurant-nav">
        <div class="logo"><i class="fa-solid fa-motorcycle"></i> BuddyBites Rider</div>

        <div class="nav-links">
            <a class="active" href="rider_dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="restaurant-page-heading">
        <div>
            <h1 class="restaurant-title">Rider Dashboard</h1>
            <p class="restaurant-subtitle">Manage pickup and delivery orders.</p>
        </div>
    </div>

    <div class="rider-summary-strip">
        <div>
            <span>Ready for Pickup</span>
            <strong><?php echo $ready_count; ?></strong>
        </div>
        <div>
            <span>Out for Delivery</span>
            <strong><?php echo $active_count; ?></strong>
        </div>
        <div>
            <span>Queue Value</span>
            <strong>RM<?php echo number_format($total_value, 2); ?></strong>
        </div>
    </div>

    <div class="rider-dashboard-grid">
        <div class="incoming-orders">
            <h2><i class="fa-solid fa-box"></i> Available Deliveries</h2>

            <?php if (!empty($deliveries)) { ?>
                <?php foreach ($deliveries as $row) { ?>
                    <?php
                        $status_class = strtolower(str_replace(" ", "-", (string)$row["status"]));
                        $foods = array_filter(array_map("trim", explode(",", (string)$row["foods"])));
                    ?>
                    <div class="restaurant-order rider-delivery-card">
                        <div class="rider-order-main">
                            <span class="rider-order-label">Order</span>
                            <h3>BB<?php echo str_pad($row["order_id"], 5, "0", STR_PAD_LEFT); ?></h3>
                            <p><?php echo h($row["student_name"]); ?></p>
                            <p><?php echo h($row["restaurants"]); ?></p>
                            <small><?php echo date("d M Y, h:i A", strtotime($row["order_date"])); ?></small>
                            <span class="status-badge status-<?php echo h($status_class); ?>"><?php echo h($row["status"]); ?></span>
                        </div>

                        <div class="rider-order-details">
                            <span class="rider-order-label">Items</span>
                            <ul>
                                <?php foreach ($foods as $food) { ?>
                                    <li><?php echo h($food); ?></li>
                                <?php } ?>
                            </ul>
                            <div class="rider-total-row">
                                <span>Total</span>
                                <b>RM<?php echo number_format((float)$row["total_amount"], 2); ?></b>
                            </div>
                        </div>

                        <div class="restaurant-buttons rider-order-actions">
                            <span class="rider-order-label">Action</span>
                            <?php if ($row["status"] === "Ready" || $row["status"] === "Ready for Pickup") { ?>
                                <a class="accept-btn"
                                   href="rider_dashboard.php?pickup=<?php echo (int)$row["order_id"]; ?>">
                                    Pick Up
                                </a>
                            <?php } ?>

                            <?php if ($row["status"] === "Out for Delivery") { ?>
                                <a class="ready-btn"
                                   href="rider_dashboard.php?delivered=<?php echo (int)$row["order_id"]; ?>">
                                    Delivered
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="rider-empty-state">
                    <i class="fa-solid fa-box-open"></i>
                    <p>No deliveries available yet.</p>
                </div>
            <?php } ?>
        </div>

        <aside class="rider-contact-card">
            <h2><i class="fa-solid fa-headset"></i> Rider Support</h2>

            <div class="rider-contact-item">
                <span>Phone</span>
                <b>+60 11-8888 1234</b>
            </div>

            <div class="rider-contact-item">
                <span>Email</span>
                <b>support@buddybites.com</b>
            </div>

            <div class="rider-contact-item">
                <span>Address</span>
                <b>
                    BuddyBites Sdn. Bhd.<br>
                    Innovation Hub<br>
                    Cyberjaya<br>
                    63000 Selangor<br>
                    Malaysia
                </b>
            </div>
        </aside>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
