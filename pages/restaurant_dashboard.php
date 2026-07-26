<?php
include("../includes/auth_check.php");
requireRole("restaurant");
include("../includes/db.php");

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$restaurant_name = $_SESSION["full_name"] ?? "Restaurant";
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

$reviews = [];
$review_stats = [
    "average_rating" => 0,
    "total_reviews" => 0
];
$orders = [];
$stats = [
    "today_orders" => 0,
    "preparing" => 0,
    "completed" => 0,
    "revenue" => 0,
];

$sql = "
    SELECT
        orders.order_id,
        orders.total_amount,
        orders.status,
        orders.order_date,
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

$reviewSql = "
    SELECT 
        menu_items.item_name,
        menu_feedback.rating,
        menu_feedback.review,
        menu_feedback.created_at,
        users.full_name
    FROM menu_feedback
    JOIN menu_items ON menu_feedback.item_id = menu_items.item_id
    JOIN users ON menu_feedback.user_id = users.user_id
    WHERE menu_items.restaurant_name = ?
      AND menu_feedback.rating IS NOT NULL
    ORDER BY menu_feedback.created_at DESC
";

$reviewStmt = $conn->prepare($reviewSql);
$reviewStmt->bind_param("s", $restaurant_name);
$reviewStmt->execute();
$reviewResult = $reviewStmt->get_result();

$totalRating = 0;

while ($review = $reviewResult->fetch_assoc()) {
    $reviews[] = $review;
    $totalRating += (int)$review["rating"];
}

$review_stats["total_reviews"] = count($reviews);

if ($review_stats["total_reviews"] > 0) {
    $review_stats["average_rating"] = $totalRating / $review_stats["total_reviews"];
}

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;

    $status = strtolower((string) $row["status"]);
    $is_today = date("Y-m-d", strtotime($row["order_date"])) === date("Y-m-d");

    if ($is_today) {
        $stats["today_orders"]++;
        $stats["revenue"] += (float) $row["total_amount"];
    }

    if (in_array($status, ["preparing", "restaurant preparing", "pending"], true)) {
        $stats["preparing"]++;
    }

    if (in_array($status, ["completed", "delivered"], true)) {
        $stats["completed"]++;
    }
}
?>

<?php include("../includes/header.php"); ?>

<!-- restaurant dashboard cleaned: 2026-06-27 -->
<div class="restaurant-dashboard restaurant-dashboard-home">
    <div class="restaurant-nav">
        <div class="logo">
            <i class="fa-solid fa-store"></i> BuddyBites Restaurant
        </div>

        <div class="nav-links">
            <a class="active" href="restaurant_dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a href="restaurant_orders.php"><i class="fa-solid fa-receipt"></i> Orders</a>
            <a href="restaurant_menu.php"><i class="fa-solid fa-bowl-food"></i> Menu</a>
            <a href="restaurant_analytics.php"><i class="fa-solid fa-chart-simple"></i> Analytics</a>
            <a href="restaurant_profile.php"><i class="fa-solid fa-user"></i> Profile</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <section class="restaurant-visual-hero">
        <div>
            <span>Restaurant workspace</span>
            <h2><?php echo h($restaurant_name); ?></h2>
            <p>Featuring <?php echo h($hero_item["item_name"]); ?> from your current menu.</p>
        </div>
        <img src="../images/<?php echo h($hero_item["image"]); ?>" alt="<?php echo h($hero_item["item_name"]); ?>">
    </section>

    <div class="dashboard-header">
        <span class="dashboard-icon"><i class="fa-solid fa-bowl-rice"></i></span>
        <h1><?php echo h($restaurant_name); ?> Dashboard</h1>
        <p>Manage incoming orders, preparation status, and daily sales.</p>
    </div>

    <div class="dashboard-cards">
        <div class="restaurant-stat-card">
            <span><i class="fa-solid fa-receipt"></i></span>
            <h2><?php echo $stats["today_orders"]; ?></h2>
            <p>Today's Orders</p>
        </div>

        <div class="restaurant-stat-card">
            <span><i class="fa-solid fa-fire-burner"></i></span>
            <h2><?php echo $stats["preparing"]; ?></h2>
            <p>Preparing</p>
        </div>

        <div class="restaurant-stat-card">
            <span><i class="fa-solid fa-circle-check"></i></span>
            <h2><?php echo $stats["completed"]; ?></h2>
            <p>Completed</p>
        </div>

        <div class="restaurant-stat-card">
            <span><i class="fa-solid fa-sack-dollar"></i></span>
            <h2>RM<?php echo number_format($stats["revenue"], 2); ?></h2>
            <p>Today's Revenue</p>
        </div>

        <div class="restaurant-stat-card">
            <span><i class="fa-solid fa-star"></i></span>
            <h2><?php echo number_format($review_stats["average_rating"], 1); ?>/5</h2>
            <p>Average Rating</p>
        </div>

        <div class="restaurant-stat-card">
            <span><i class="fa-solid fa-comments"></i></span>
            <h2><?php echo $review_stats["total_reviews"]; ?></h2>
            <p>Total Reviews</p>
        </div>
    </div>

    <div class="dashboard-bottom-grid">
        <div class="incoming-orders">
            <h2><i class="fa-solid fa-bell-concierge"></i> Incoming Orders</h2>

            <?php if (!empty($orders)) { ?>
                <?php foreach ($orders as $order) { ?>
                    <div class="restaurant-order">
                        <div class="restaurant-order-info">
                            <h3>Order BB<?php echo str_pad($order["order_id"], 5, "0", STR_PAD_LEFT); ?></h3>
                            <p><b>Customer:</b> <?php echo h($order["full_name"]); ?></p>
                            <p><b>Food:</b> <?php echo h($order["foods"]); ?></p>
                            <p><b>Total:</b> RM<?php echo number_format((float) $order["total_amount"], 2); ?></p>
                            <p><b>Status:</b> <?php echo h($order["status"]); ?></p>
                        </div>

                        <div class="restaurant-buttons">
                            <form action="restaurant_update_order.php" method="POST">
                                <input type="hidden" name="order_id" value="<?php echo (int)$order["order_id"]; ?>">
                                <input type="hidden" name="status" value="Ready for Pickup">
                                <button class="accept-btn" type="submit">Accept</button>
                            </form>

                            <form action="restaurant_update_order.php" method="POST">
                                <input type="hidden" name="order_id" value="<?php echo (int)$order["order_id"]; ?>">
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

        <div class="incoming-orders">
            <h2><i class="fa-solid fa-star"></i> Customer Reviews</h2>

            <?php if (!empty($reviews)) { ?>
                <?php foreach ($reviews as $review) { ?>
                    <div class="restaurant-order restaurant-review-card">
                        <div class="restaurant-order-info">
                            <div class="review-card-top">
                                <h3><span class="review-icon"><i class="fa-solid fa-bowl-food"></i></span><?php echo h($review["item_name"]); ?></h3>

                                <div class="review-stars" aria-label="<?php echo (int)$review["rating"]; ?> out of 5 stars">
                                    <?php for ($i = 1; $i <= 5; $i++) { ?>
                                        <?php if ($i <= (int)$review["rating"]) { ?>
                                            <i class="fa-solid fa-star"></i>
                                        <?php } else { ?>
                                            <i class="fa-regular fa-star"></i>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>

                            <p class="review-person"><span><i class="fa-regular fa-user"></i></span><?php echo h($review["full_name"]); ?></p>
                            <p class="review-text"><span><i class="fa-solid fa-quote-left"></i></span><?php echo h($review["review"]); ?></p>
                            <p class="review-date"><span><i class="fa-regular fa-clock"></i></span><?php echo date("d M Y, h:i A", strtotime($review["created_at"])); ?></p>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p class="empty-state">No reviews for this restaurant yet.</p>
            <?php } ?>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
