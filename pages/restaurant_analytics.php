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

$todayRevenue = 0;
$todayOrders = 0;
$bestSeller = "None";
$weeklyRevenue = 0;
$topCategory = "None";

$sql = "
    SELECT
        COUNT(DISTINCT orders.order_id) AS total_orders,
        SUM(order_items.price * order_items.quantity) AS revenue
    FROM orders
    JOIN order_items ON orders.order_id = order_items.order_id
    WHERE order_items.restaurant_name = ?
    AND DATE(orders.order_date) = CURDATE()
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $restaurant_name);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

$todayOrders = $row["total_orders"] ?? 0;
$todayRevenue = $row["revenue"] ?? 0;

$sql = "
    SELECT item_name, SUM(quantity) AS total_qty
    FROM order_items
    WHERE restaurant_name = ?
    GROUP BY item_name
    ORDER BY total_qty DESC
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $restaurant_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $bestSeller = $result->fetch_assoc()["item_name"];
}

$sql = "
    SELECT SUM(order_items.price * order_items.quantity) AS weekly_revenue
    FROM orders
    JOIN order_items ON orders.order_id = order_items.order_id
    WHERE order_items.restaurant_name = ?
    AND orders.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $restaurant_name);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

$weeklyRevenue = $row["weekly_revenue"] ?? 0;

$sql = "
    SELECT menu_items.category, SUM(order_items.quantity) AS total_qty
    FROM order_items
    JOIN menu_items ON order_items.item_name = menu_items.item_name
    WHERE order_items.restaurant_name = ?
    AND menu_items.restaurant_name = ?
    GROUP BY menu_items.category
    ORDER BY total_qty DESC
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $restaurant_name, $restaurant_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $topCategory = $result->fetch_assoc()["category"];
}
?>

<?php include("../includes/header.php"); ?>

<div class="restaurant-dashboard">
    <div class="restaurant-nav">
        <div class="logo"><i class="fa-solid fa-store"></i> <?php echo h($restaurant_name); ?></div>

        <div class="nav-links">
            <a href="restaurant_dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a href="restaurant_orders.php"><i class="fa-solid fa-receipt"></i> Orders</a>
            <a href="restaurant_menu.php"><i class="fa-solid fa-bowl-food"></i> Menu</a>
            <a class="active" href="restaurant_analytics.php"><i class="fa-solid fa-chart-simple"></i> Analytics</a>
            <a href="restaurant_profile.php"><i class="fa-solid fa-user"></i> Profile</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <section class="restaurant-visual-hero">
        <div>
            <span>Sales snapshot</span>
            <h2><?php echo h($restaurant_name); ?></h2>
            <p>Track performance for items like <?php echo h($hero_item["item_name"]); ?>.</p>
        </div>
        <img src="../images/<?php echo h($hero_item["image"]); ?>" alt="<?php echo h($hero_item["item_name"]); ?>">
    </section>

    <div class="restaurant-page-heading">
        <div>
            <h1 class="restaurant-title">Sales Analytics</h1>
            <p class="restaurant-subtitle">Monitor restaurant sales and best-selling meals.</p>
        </div>
    </div>

    <div class="dashboard-cards">
        <div class="card">
            <h2>RM<?php echo number_format((float)$todayRevenue, 2); ?></h2>
            <p>Today's Revenue</p>
        </div>

        <div class="card">
            <h2><?php echo h($todayOrders); ?></h2>
            <p>Today's Orders</p>
        </div>

        <div class="card">
            <h2><?php echo h($bestSeller); ?></h2>
            <p>Best Seller</p>
        </div>

        <div class="card">
            <h2>12 mins</h2>
            <p>Average Prep Time</p>
        </div>
    </div>

    <div class="incoming-orders">
        <h2><i class="fa-solid fa-chart-column"></i> Weekly Performance</h2>

        <div class="restaurant-order analytics-row">
            <div>
                <h3>Weekly Revenue</h3>
                <p><b>RM<?php echo number_format((float)$weeklyRevenue, 2); ?></b></p>
                <p>Based on orders from the last 7 days.</p>
            </div>

            <div>
                <h3>Most Ordered Category</h3>
                <p><b><?php echo h($topCategory); ?></b></p>
                <p>Based on student orders.</p>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
