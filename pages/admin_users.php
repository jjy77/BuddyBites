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

ensureColumn($conn, "users", "status", "VARCHAR(30) DEFAULT 'Active'");

if (isset($_GET["deactivate"])) {
    $user_id = (int)$_GET["deactivate"];
    $status = "Inactive";
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
    $stmt->bind_param("si", $status, $user_id);
    $stmt->execute();

    header("Location: admin_users.php");
    exit();
}

if (isset($_GET["delete"])) {
    $user_id = (int)$_GET["delete"];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    header("Location: admin_users.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_user"])) {
    $user_id = (int)$_POST["user_id"];
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $role = $_POST["role"];
    $status = trim($_POST["status"]);

    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, status = ? WHERE user_id = ?");
    $stmt->bind_param("ssssi", $full_name, $email, $role, $status, $user_id);
    $stmt->execute();

    header("Location: admin_users.php");
    exit();
}

$editUser = null;
if (isset($_GET["edit"])) {
    $user_id = (int)$_GET["edit"];
    $stmt = $conn->prepare("SELECT user_id, full_name, email, role, status FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $editUser = $stmt->get_result()->fetch_assoc();
}

$users = $conn->query("
    SELECT user_id, full_name, email, role, status
    FROM users
    ORDER BY created_at DESC
");

$userRows = [];
$roleCounts = [
    "student" => 0,
    "restaurant" => 0,
    "rider" => 0,
    "admin" => 0
];
$activeAccounts = 0;
$pendingAccounts = 0;

if ($users) {
    while ($user = $users->fetch_assoc()) {
        $role = strtolower((string)$user["role"]);
        $status = $user["status"] ?: "Active";

        $userRows[] = $user;

        if (isset($roleCounts[$role])) {
            $roleCounts[$role]++;
        }

        if (strtolower($status) === "pending") {
            $pendingAccounts++;
        } elseif (strtolower($status) !== "inactive") {
            $activeAccounts++;
        }
    }
}

$totalAccounts = count($userRows);
?>

<?php include("../includes/header.php"); ?>

<main class="admin-shell admin-premium admin-users-page">
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
        <header class="admin-hero">
            <div>
                <p>Admin User Management</p>
                <h1>Users</h1>
                <small>View and manage all Buddy Bites accounts.</small>
            </div>
        </header>

        <section class="user-directory-summary" aria-label="User account summary">
            <article>
                <span>Total Accounts</span>
                <strong><?php echo $totalAccounts; ?></strong>
            </article>
            <article>
                <span>Students</span>
                <strong><?php echo $roleCounts["student"]; ?></strong>
            </article>
            <article>
                <span>Restaurants</span>
                <strong><?php echo $roleCounts["restaurant"]; ?></strong>
            </article>
            <article>
                <span>Active</span>
                <strong><?php echo $activeAccounts; ?></strong>
            </article>
            <article>
                <span>Pending</span>
                <strong><?php echo $pendingAccounts; ?></strong>
            </article>
        </section>

        <?php if ($editUser) { ?>
            <section class="admin-panel">
                <div class="admin-section-heading">
                    <h2>Edit User</h2>
                    <p>Update account details.</p>
                </div>
                <form class="admin-form-grid" method="POST" action="admin_users.php">
                    <input type="hidden" name="user_id" value="<?php echo (int)$editUser["user_id"]; ?>">
                    <label>
                        Name
                        <input type="text" name="full_name" value="<?php echo e($editUser["full_name"]); ?>" required>
                    </label>
                    <label>
                        Email
                        <input type="email" name="email" value="<?php echo e($editUser["email"]); ?>" required>
                    </label>
                    <label>
                        Role
                        <select name="role">
                            <?php foreach (["student", "restaurant", "rider", "admin"] as $role) { ?>
                                <option value="<?php echo e($role); ?>" <?php echo $editUser["role"] === $role ? "selected" : ""; ?>>
                                    <?php echo e(ucfirst($role)); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </label>
                    <label>
                        Status
                        <input type="text" name="status" value="<?php echo e($editUser["status"] ?: "Active"); ?>">
                    </label>
                    <button class="admin-save-btn" type="submit" name="save_user">Save User</button>
                </form>
            </section>
        <?php } ?>

        <section class="admin-panel user-directory-panel">
            <div class="admin-section-heading">
                <h2>Account Directory</h2>
                <p>Review account roles, status, and access controls.</p>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userRows as $user) {
                            $role = strtolower((string)$user["role"]);
                            $status = $user["status"] ?: "Active";
                            $statusClass = strtolower(str_replace(" ", "-", $status));
                        ?>
                            <tr>
                                <td>
                                    <div class="user-name-cell">
                                        <span><?php echo e(strtoupper(substr($user["full_name"], 0, 1))); ?></span>
                                        <b><?php echo e($user["full_name"]); ?></b>
                                    </div>
                                </td>
                                <td><?php echo e($user["email"]); ?></td>
                                <td><span class="role-pill role-<?php echo e($role); ?>"><?php echo e(ucfirst($role)); ?></span></td>
                                <td><span class="status-badge status-<?php echo e($statusClass); ?>"><?php echo e($status); ?></span></td>
                                <td>
                                    <div class="admin-table-actions user-actions">
                                        <a class="admin-save-btn" href="admin_users.php?edit=<?php echo (int)$user["user_id"]; ?>">Edit</a>
                                        <a href="admin_users.php?deactivate=<?php echo (int)$user["user_id"]; ?>">Deactivate</a>
                                        <a class="admin-delete-btn" href="admin_users.php?delete=<?php echo (int)$user["user_id"]; ?>" onclick="return confirm('Delete this user?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</main>

<?php include("../includes/footer.php"); ?>
