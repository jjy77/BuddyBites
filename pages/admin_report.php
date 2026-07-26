<?php
include("../includes/auth_check.php");
requireRole("admin");
include("../includes/db.php");

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$todayOrders = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE DATE(order_date) = CURDATE()
")->fetch_assoc()["total"];

$todayRevenue = $conn->query("
    SELECT IFNULL(SUM(total_amount), 0) AS total
    FROM orders
    WHERE DATE(order_date) = CURDATE()
")->fetch_assoc()["total"];

$completedOrders = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE status IN ('Completed', 'Delivered')
")->fetch_assoc()["total"];

$cancelledOrders = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE status IN ('Cancelled', 'Rejected')
")->fetch_assoc()["total"];

$pendingOrders = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE status NOT IN ('Completed', 'Delivered', 'Cancelled', 'Rejected')
")->fetch_assoc()["total"];

$topRestaurants = $conn->query("
    SELECT restaurant_name, COUNT(*) AS total_orders, IFNULL(SUM(price * quantity), 0) AS revenue
    FROM order_items
    GROUP BY restaurant_name
    ORDER BY total_orders DESC, revenue DESC
    LIMIT 5
");

$topFoods = $conn->query("
    SELECT item_name, SUM(quantity) AS sold
    FROM order_items
    GROUP BY item_name
    ORDER BY sold DESC
    LIMIT 5
");

$latestOrders = $conn->query("
    SELECT orders.order_id, users.full_name, orders.status, orders.total_amount, orders.order_date
    FROM orders
    JOIN users ON orders.user_id = users.user_id
    ORDER BY orders.order_date DESC
    LIMIT 10
");

$reportCards = [
    ["title" => "Today's Orders", "value" => $todayOrders, "icon" => "fa-receipt"],
    ["title" => "Today's Revenue", "value" => "RM" . number_format($todayRevenue, 2), "icon" => "fa-sack-dollar"],
    ["title" => "Completed Orders", "value" => $completedOrders, "icon" => "fa-circle-check"],
    ["title" => "Cancelled Orders", "value" => $cancelledOrders, "icon" => "fa-circle-xmark"],
    ["title" => "Pending Orders", "value" => $pendingOrders, "icon" => "fa-clock"],
];
?>

<?php include("../includes/header.php"); ?>

<main class="admin-shell admin-premium admin-report-page">
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
            <a href="manage_orders.php">Orders</a>
            <a href="restaurant_management.php">Restaurants</a>
            <a href="rider_management.php">Riders</a>
            <a class="active" href="admin_report.php">Reports</a>
        </div>

        <div class="admin-user-nav">
            <a class="admin-logout" href="logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>
        </div>
    </nav>

    <section class="admin-content">
        <header class="admin-hero">
            <div>
                <p>System Report</p>
                <h1>Reports</h1>
                <small>View order, revenue, restaurant, and food performance.</small>
            </div>
        </header>

        <section class="admin-summary-grid" aria-label="Report summary">
            <?php foreach ($reportCards as $card) { ?>
                <article class="admin-stat-card">
                    <span class="admin-card-icon"><i class="fa-solid <?php echo e($card["icon"]); ?>"></i></span>
                    <div>
                        <p><?php echo e($card["title"]); ?></p>
                        <h2><?php echo e($card["value"]); ?></h2>
                    </div>
                </article>
            <?php } ?>
        </section>

        <section class="admin-grid-two">
            <div class="admin-panel">
                <div class="admin-section-heading">
                    <h2>Top 5 Restaurants</h2>
                    <p>Ranked by order item count.</p>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Restaurant</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($restaurant = $topRestaurants->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo e($restaurant["restaurant_name"]); ?></td>
                                    <td><?php echo e($restaurant["total_orders"]); ?></td>
                                    <td>RM<?php echo number_format($restaurant["revenue"], 2); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="admin-panel">
                <div class="admin-section-heading">
                    <h2>Top 5 Selling Foods</h2>
                    <p>Ranked by quantity sold.</p>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Food</th>
                                <th>Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($food = $topFoods->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo e($food["item_name"]); ?></td>
                                    <td><?php echo e($food["sold"]); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-section-heading">
                <h2>Latest 10 Orders</h2>
                <p>Most recent system orders.</p>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Student</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $latestOrders->fetch_assoc()) { ?>
                            <tr>
                                <td>#<?php echo (int)$order["order_id"]; ?></td>
                                <td><?php echo e($order["full_name"]); ?></td>
                                <td><?php echo e($order["status"]); ?></td>
                                <td>RM<?php echo number_format($order["total_amount"], 2); ?></td>
                                <td><?php echo e(date("d M Y, h:i A", strtotime($order["order_date"]))); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</main>

<?php include("../includes/footer.php"); ?>
