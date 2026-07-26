<?php
include("../includes/auth_check.php");
requireRole("admin");
include("../includes/db.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION["full_name"] ?? "System Admin";

/* Dashboard Statistics */
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()["total"];

$totalRestaurants = $conn->query("SELECT COUNT(*) AS total FROM restaurants")->fetch_assoc()["total"];

$totalOrders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()["total"];

$totalRevenue = $conn->query("
SELECT IFNULL(SUM(total_amount),0) AS revenue
FROM orders
")->fetch_assoc()["revenue"];

$summary_cards = [

[
"title"=>"Total Users",
"icon"=>"fa-users",
"value"=>$totalUsers,
"subtitle"=>"Registered accounts",
"label"=>"Active directory"
],

[
"title"=>"Restaurants",
"icon"=>"fa-store",
"value"=>$totalRestaurants,
"subtitle"=>"Campus restaurants",
"label"=>"8 Open"
],

[
"title"=>"Total Orders",
"icon"=>"fa-receipt",
"value"=>$totalOrders,
"subtitle"=>"Orders received",
"label"=>"3 Pending"
],

[
"title"=>"Revenue",
"icon"=>"fa-sack-dollar",
"value"=>"RM".number_format($totalRevenue,2),
"subtitle"=>"Total sales",
"label"=>"Today's Revenue"
]

];

$quick_actions = [
    ["label" => "Add Restaurant", "icon" => "fa-plus", "href" => "restaurant_management.php"],
    ["label" => "Approve Rider", "icon" => "fa-motorcycle", "href" => "rider_management.php"],
    ["label" => "Manage Users", "icon" => "fa-users", "href" => "admin_users.php"],
    ["label" => "Generate Report", "icon" => "fa-file-lines", "href" => "admin_report.php"],
];

function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}
?>

<?php include("../includes/header.php"); ?>

<main class="admin-shell admin-premium admin-dashboard-page">
    <nav class="admin-topbar">
        <a class="admin-brand" href="admin_dashboard.php">
            <span class="admin-brand-icon"><i class="fa-solid fa-utensils"></i></span>
            <span>
                <b>Buddy Bites</b>
                <small>Admin Portal</small>
            </span>
        </a>

        <div class="admin-main-nav">
            <a class="active" href="admin_dashboard.php">Dashboard</a>
            <a href="manage_orders.php">Orders</a>
            <a href="restaurant_management.php">Restaurants</a>
            <a href="rider_management.php">Riders</a>
            <a href="admin_report.php">Reports</a>
        </div>

        <div class="admin-user-nav">
            <button type="button" class="admin-icon-btn" aria-label="Notifications">
                <i class="fa-regular fa-bell"></i>
            </button>
            <div class="admin-profile">
                <span><?php echo e(substr($admin_name, 0, 1)); ?></span>
                <div>
                    <b><?php echo e($admin_name); ?></b>
                    <small>System Admin</small>
                </div>
            </div>
            <a class="admin-logout" href="logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>
        </div>
    </nav>

    <section class="admin-content">
        <header class="admin-hero">
            <div>
                <p>Good Afternoon,</p>
                <h1>System Admin <span><i class="fa-solid fa-gauge-high"></i></span></h1>
                <small>Manage Buddy Bites from one place.</small>
            </div>
        </header>

        <section class="admin-summary-grid" aria-label="Summary">
            <?php foreach ($summary_cards as $card) { ?>
                <article class="admin-stat-card">
                    <span class="admin-card-icon"><i class="fa-solid <?php echo e($card["icon"]); ?>"></i></span>
                    <div>
                        <p><?php echo e($card["title"]); ?></p>
                        <h2><?php echo e($card["value"]); ?></h2>
                        <small><?php echo e($card["subtitle"]); ?></small>
                        <em><?php echo e($card["label"]); ?></em>
                    </div>
                </article>
            <?php } ?>
        </section>

        <section class="admin-section">
            <div class="admin-section-heading">
                <h2>Quick Actions</h2>
                <p>Common admin tasks for daily operations.</p>
            </div>
            <div class="admin-actions">
                <?php foreach ($quick_actions as $action) { ?>
                    <a href="<?php echo e($action["href"]); ?>">
                        <i class="fa-solid <?php echo e($action["icon"]); ?>"></i>
                        <?php echo e($action["label"]); ?>
                    </a>
                <?php } ?>
            </div>
        </section>

        <section class="admin-grid-two">
            <div class="admin-panel">
                <div class="admin-section-heading">
                    <h2>Latest Orders</h2>
                    <p>New order records will appear here.</p>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Student</th>
                                <th>Restaurant</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php

                        $orders = $conn->query("
                        SELECT
                            orders.order_id,
                            orders.status,
                            orders.total_amount,
                            users.full_name AS customer_name,
                            GROUP_CONCAT(DISTINCT order_items.restaurant_name SEPARATOR ', ') AS restaurant_name
                        FROM orders
                        JOIN users ON orders.user_id = users.user_id
                        LEFT JOIN order_items ON orders.order_id = order_items.order_id
                        GROUP BY orders.order_id, orders.status, orders.total_amount, users.full_name
                        ORDER BY orders.order_id DESC
                        LIMIT 5
                        ");

                        while ($row = $orders->fetch_assoc()) {

                        ?>

                        <tr>

                        <td>#<?php echo $row["order_id"]; ?></td>

                        <td><?php echo htmlspecialchars($row["customer_name"]); ?></td>

                        <td><?php echo htmlspecialchars($row["restaurant_name"] ?? "-"); ?></td>

                        <td><?php echo htmlspecialchars($row["status"]); ?></td>

                        <td>RM<?php echo number_format($row["total_amount"], 2); ?></td>

                        <td>

                        <a href="manage_orders.php?id=<?php echo $row["order_id"]; ?>">
                        View
                        </a>

                        </td>

                        </tr>

                        <?php } ?>

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="admin-panel">
                <div class="admin-section-heading">
                    <h2>Restaurant Management</h2>
                    <p>Restaurant approval and status records.</p>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Restaurant</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $restaurants = $conn->query("
                            SELECT *
                            FROM restaurants
                            ORDER BY restaurant_id DESC
                            LIMIT 5
                        ");

                        while ($r = $restaurants->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo e($r["restaurant_name"]); ?></td>
                            <td><?php echo e($r["owner_name"] ?: "-"); ?></td>
                            <td><?php echo e($r["status"]); ?></td>
                            <td>
                                <a href="restaurant_management.php">Manage</a>
                            </td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </section>

    <footer class="admin-footer">
        <span>Buddy Bites &copy; 2026</span>
        <span>Version 1.0</span>
    </footer>
</main>

<?php include("../includes/footer.php"); ?>
