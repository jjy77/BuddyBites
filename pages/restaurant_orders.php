<?php
include("../includes/auth_check.php");
requireRole("restaurant");
include("../includes/db.php");

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$restaurant_name = $_SESSION["full_name"];
$hero_item = [
    "item_name" => "your menu",
    "image" => "rice-bowl.jpg"
];

$heroStmt = $conn->prepare("
    SELECT item_name, image
    FROM menu_items
    WHERE restaurant_name = ?
      AND image IS NOT NULL
      AND image != ''
    ORDER BY item_id ASC
    LIMIT 1
");
$heroStmt->bind_param("s", $restaurant_name);
$heroStmt->execute();
$heroResult = $heroStmt->get_result();

if ($heroResult && $heroResult->num_rows > 0) {
    $hero_item = $heroResult->fetch_assoc();
}

$sql = "
    SELECT orders.order_id, orders.total_amount, orders.status, orders.order_date,
           users.full_name,
           GROUP_CONCAT(order_items.item_name SEPARATOR ', ') AS foods
    FROM orders
    JOIN users ON orders.user_id = users.user_id
    JOIN order_items ON orders.order_id = order_items.order_id
    WHERE order_items.restaurant_name = ?
    GROUP BY orders.order_id, orders.total_amount, orders.status, orders.order_date, users.full_name
    ORDER BY orders.order_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $restaurant_name);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include("../includes/header.php"); ?>

<div class="restaurant-dashboard">
    <div class="restaurant-nav">
        <div class="logo"><i class="fa-solid fa-store"></i> <?php echo h($restaurant_name); ?></div>

        <div class="nav-links">
            <a href="restaurant_dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a class="active" href="restaurant_orders.php"><i class="fa-solid fa-receipt"></i> Orders</a>
            <a href="restaurant_menu.php"><i class="fa-solid fa-bowl-food"></i> Menu</a>
            <a href="restaurant_analytics.php"><i class="fa-solid fa-chart-simple"></i> Analytics</a>
            <a href="restaurant_profile.php"><i class="fa-solid fa-user"></i> Profile</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <section class="restaurant-visual-hero">
        <div>
            <span>Order control</span>
            <h2><?php echo h($restaurant_name); ?></h2>
            <p>Manage orders for meals like <?php echo h($hero_item["item_name"]); ?>.</p>
        </div>
        <img src="../images/<?php echo h($hero_item["image"]); ?>" alt="<?php echo h($hero_item["item_name"]); ?>">
    </section>

    <div class="restaurant-page-heading">
        <div>
            <h1 class="restaurant-title">Incoming Orders</h1>
            <p class="restaurant-subtitle">Review student orders and update preparation status.</p>
        </div>
    </div>

    <div class="incoming-orders">
        <?php if ($result->num_rows > 0) { ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="restaurant-order">
                    <div class="restaurant-order-info">
                        <h3>Order BB<?php echo str_pad($row["order_id"], 5, "0", STR_PAD_LEFT); ?></h3>
                        <p><b>Customer:</b> <?php echo h($row["full_name"]); ?></p>
                        <p><b>Food:</b> <?php echo h($row["foods"]); ?></p>
                        <p><b>Total:</b> RM<?php echo number_format((float)$row["total_amount"], 2); ?></p>
                        <p><b>Status:</b> <?php echo h($row["status"]); ?></p>
                    </div>

                    <div class="restaurant-buttons">
                        <form action="restaurant_update_order.php" method="POST">
                            <input type="hidden" name="order_id" value="<?php echo (int)$row["order_id"]; ?>">
                            <input type="hidden" name="status" value="Ready for Pickup">
                            <button class="accept-btn" type="submit">Accept</button>
                        </form>

                        <form action="restaurant_update_order.php" method="POST">
                            <input type="hidden" name="order_id" value="<?php echo (int)$row["order_id"]; ?>">
                            <input type="hidden" name="status" value="Rejected">
                            <button class="reject-btn" type="submit">Reject</button>
                        </form>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p class="empty-state">No orders for this restaurant yet.</p>
        <?php } ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
