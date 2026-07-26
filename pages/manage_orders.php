<?php
include("../includes/auth_check.php");
requireRole("admin");
include("../includes/db.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

/* UPDATE ORDER STATUS */
if (isset($_POST["update_status"])) {
    $order_id = intval($_POST["order_id"]);
    $status = $_POST["status"];

    $sql = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();

    header("Location: manage_orders.php");
    exit();
}

/* FETCH ALL ORDERS */
$sql = "
        SELECT
            orders.order_id,
            orders.status,
            orders.total_amount,
            orders.order_date,
            users.full_name,
            GROUP_CONCAT(DISTINCT order_items.restaurant_name SEPARATOR ', ') AS restaurant_names,
            GROUP_CONCAT(CONCAT(order_items.quantity, ' x ', order_items.item_name) SEPARATOR '||') AS ordered_items
        FROM orders
        JOIN users ON orders.user_id = users.user_id
        LEFT JOIN order_items ON orders.order_id = order_items.order_id
        GROUP BY orders.order_id, orders.status, orders.total_amount, orders.order_date, users.full_name
        ORDER BY orders.order_date DESC";

$result = $conn->query($sql);
?>

<?php include("../includes/header.php"); ?>

<main class="admin-shell admin-premium admin-orders-page">
    <nav class="admin-topbar">
        <a class="admin-brand" href="admin_dashboard.php">
            <span class="admin-brand-icon"><i class="fa-solid fa-utensils"></i></span>
            <span>
                <b>Buddy Bites</b>
                <small>Admin Portal</small>
            </span>
        </a>

        <div class="admin-main-nav">
            <a href="admin_dashboard.php">Dashboard</a>
            <a class="active" href="manage_orders.php">Orders</a>
            <a href="restaurant_management.php">Restaurants</a>
            <a href="rider_management.php">Riders</a>
            <a href="admin_report.php">Reports</a>
        </div>

        <div class="admin-user-nav">
            <a class="admin-logout" href="logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>
        </div>
    </nav>

    <section class="admin-content">
        <header class="admin-hero admin-page-hero">
            <div>
                <p>Operations Control</p>
                <h1>Order Management</h1>
                <small>Track customer orders and update preparation or delivery status from one queue.</small>
            </div>
            <span class="page-hero-mark"><i class="fa-solid fa-clipboard-check"></i></span>
        </header>

        <section class="admin-panel order-box">
            <div class="admin-section-heading order-section-heading">
                <h2>Live Order Queue</h2>
                <p><?php echo $result ? $result->num_rows : 0; ?> orders currently listed, newest first.</p>
            </div>

        <?php if ($result && $result->num_rows > 0) { ?>

            <?php while ($row = $result->fetch_assoc()) { ?>
                <?php
                $status_class = strtolower(str_replace(" ", "-", (string)$row["status"]));
                $ordered_items = array_filter(explode("||", (string)($row["ordered_items"] ?? "")));
                ?>
                <article class="order-management-card">
                    <div class="order-card-column order-card-left">
                        <b class="order-card-id">Order #<?php echo str_pad($row["order_id"], 3, "0", STR_PAD_LEFT); ?></b>
                        <p><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($row["full_name"]); ?></p>
                        <p><i class="fa-solid fa-store"></i> <?php echo htmlspecialchars($row["restaurant_names"] ?? "-"); ?></p>
                        <p><i class="fa-regular fa-clock"></i> <?php echo date("d M Y, h:i A", strtotime($row["order_date"])); ?></p>
                        <span class="status-badge status-<?php echo htmlspecialchars($status_class); ?>">
                            <?php echo htmlspecialchars($row["status"]); ?>
                        </span>
                    </div>

                    <div class="order-card-column order-card-middle">
                        <small>Items</small>
                        <ul class="order-item-list">
                            <?php if (!empty($ordered_items)) { ?>
                                <?php foreach ($ordered_items as $item) { ?>
                                    <li><?php echo htmlspecialchars($item); ?></li>
                                <?php } ?>
                            <?php } else { ?>
                                <li>No items recorded</li>
                            <?php } ?>
                        </ul>
                        <div class="order-total-line">
                            <span>Total</span>
                            <strong>RM<?php echo number_format($row["total_amount"], 2); ?></strong>
                        </div>
                        <div class="payment-status-line">
                            <span>Payment</span>
                            <b>Confirmed</b>
                        </div>
                    </div>

                    <form class="order-card-column order-card-actions" method="POST" action="manage_orders.php">
                        <input type="hidden" name="order_id" value="<?php echo $row["order_id"]; ?>">

                        <label for="order-status-<?php echo (int)$row["order_id"]; ?>">Status</label>
                        <select id="order-status-<?php echo (int)$row["order_id"]; ?>" name="status" class="checkout-select">
                            <option value="Preparing" <?php if ($row["status"] == "Preparing") echo "selected"; ?>>Preparing</option>
                            <option value="Ready" <?php if ($row["status"] == "Ready") echo "selected"; ?>>Ready for Pickup</option>
                            <option value="Out for Delivery" <?php if ($row["status"] == "Out for Delivery") echo "selected"; ?>>Out for Delivery</option>
                            <option value="Delivered" <?php if ($row["status"] == "Delivered") echo "selected"; ?>>Delivered</option>
                            <option value="Cancelled" <?php if ($row["status"] == "Cancelled") echo "selected"; ?>>Cancelled</option>
                        </select>

                        <button class="history-btn" type="submit" name="update_status">
                            <i class="fa-solid fa-rotate"></i> Update
                        </button>
                    </form>
                </article>
            <?php } ?>

        <?php } else { ?>

            <p class="empty-order">No orders found.</p>

        <?php } ?>

        </section>
    </section>
</main>

<?php include("../includes/footer.php"); ?>
