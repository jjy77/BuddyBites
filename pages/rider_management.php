<?php
include("../includes/auth_check.php");
requireRole("admin");
include("../includes/db.php");

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function ensureColumn($conn, $table, $column, $definition) {
    $column = $conn->real_escape_string($column);
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");

    if ($result && $result->num_rows === 0) {
        $conn->query("ALTER TABLE `$table` ADD `$column` $definition");
    }
}

ensureColumn($conn, "users", "phone", "VARCHAR(30) DEFAULT ''");
ensureColumn($conn, "users", "status", "VARCHAR(30) DEFAULT 'Pending'");

$allowed_actions = [
    "approve" => "Approved",
    "reject" => "Rejected",
    "deactivate" => "Inactive",
    "activate" => "Active",
];

if (isset($_GET["action"], $_GET["id"]) && isset($allowed_actions[$_GET["action"]])) {
    $user_id = (int)$_GET["id"];
    $status = $allowed_actions[$_GET["action"]];

    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ? AND role = 'rider'");
    $stmt->bind_param("si", $status, $user_id);
    $stmt->execute();

    header("Location: rider_management.php");
    exit();
}

$riders = $conn->query("
    SELECT user_id, full_name, email, phone, status
    FROM users
    WHERE role = 'rider'
    ORDER BY full_name ASC
");
?>

<?php include("../includes/header.php"); ?>

<main class="admin-shell admin-premium admin-riders-page">
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
            <a class="active" href="rider_management.php">Riders</a>
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
                <p>Admin Rider Management</p>
                <h1>Riders</h1>
                <small>Approve, reject, activate, or deactivate rider accounts.</small>
            </div>
            <span class="page-hero-mark"><i class="fa-solid fa-motorcycle"></i></span>
        </header>

        <section class="admin-panel rider-management-panel">
            <div class="admin-section-heading">
                <h2>Rider Accounts</h2>
                <p><?php echo $riders ? $riders->num_rows : 0; ?> rider profiles ready for approval and account control.</p>
            </div>

            <div class="admin-card-grid rider-card-grid">
                <?php if ($riders && $riders->num_rows > 0) { ?>
                    <?php while ($rider = $riders->fetch_assoc()) {
                        $rider_status = $rider["status"] ?: "Pending";
                        $status_class = strtolower(str_replace(" ", "-", (string)$rider_status));
                    ?>
                        <article class="management-card rider-management-card">
                            <div class="management-card-header">
                                <span class="management-card-icon rider-avatar"><?php echo e(strtoupper(substr($rider["full_name"], 0, 1))); ?></span>
                                <div>
                                    <h3><?php echo e($rider["full_name"]); ?></h3>
                                    <span class="status-badge status-<?php echo e($status_class); ?>"><?php echo e($rider_status); ?></span>
                                </div>
                            </div>

                            <div class="rider-details">
                                <p><i class="fa-regular fa-envelope"></i> <?php echo e($rider["email"]); ?></p>
                                <p><i class="fa-solid fa-phone"></i> <?php echo e($rider["phone"] ?: "-"); ?></p>
                            </div>

                            <div class="rider-action-groups">
                                <div>
                                    <small>Approval</small>
                                    <div class="admin-table-actions">
                                        <a class="admin-save-btn" href="rider_management.php?action=approve&id=<?php echo (int)$rider["user_id"]; ?>">
                                            <i class="fa-solid fa-check"></i> Approve
                                        </a>
                                        <a class="admin-delete-btn" href="rider_management.php?action=reject&id=<?php echo (int)$rider["user_id"]; ?>">
                                            <i class="fa-solid fa-xmark"></i> Reject
                                        </a>
                                    </div>
                                </div>
                                <div>
                                    <small>Account</small>
                                    <div class="admin-table-actions">
                                        <a href="rider_management.php?action=activate&id=<?php echo (int)$rider["user_id"]; ?>">
                                            <i class="fa-solid fa-toggle-on"></i> Activate
                                        </a>
                                        <a href="rider_management.php?action=deactivate&id=<?php echo (int)$rider["user_id"]; ?>">
                                            <i class="fa-solid fa-toggle-off"></i> Deactivate
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php } ?>
                <?php } else { ?>
                    <p class="empty-state">No riders found.</p>
                <?php } ?>
            </div>
        </section>
    </section>
</main>

<?php include("../includes/footer.php"); ?>
