<?php
include("../includes/auth_check.php");
requireRole("admin");
include("../includes/db.php");

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$statuses = ["Open", "Closed", "Temporarily Unavailable"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $restaurant_id = (int)($_POST["restaurant_id"] ?? 0);

    if (isset($_POST["save_restaurant"])) {
        $owner_name = trim($_POST["owner_name"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
        $opening_hours = trim($_POST["opening_hours"] ?? "");
        $status = $_POST["status"] ?? "Open";

        if (!in_array($status, $statuses, true)) {
            $status = "Open";
        }

        $stmt = $conn->prepare("
            UPDATE restaurants
            SET owner_name = ?, phone = ?, opening_hours = ?, status = ?
            WHERE restaurant_id = ?
        ");
        $stmt->bind_param("ssssi", $owner_name, $phone, $opening_hours, $status, $restaurant_id);
        $stmt->execute();

        header("Location: restaurant_management.php");
        exit();
    }

    if (isset($_POST["delete_restaurant"])) {
        $stmt = $conn->prepare("DELETE FROM restaurants WHERE restaurant_id = ?");
        $stmt->bind_param("i", $restaurant_id);
        $stmt->execute();

        header("Location: restaurant_management.php");
        exit();
    }
}

$restaurants = $conn->query("
    SELECT
        restaurants.restaurant_id,
        restaurants.restaurant_name,
        restaurants.owner_name,
        restaurants.phone,
        restaurants.opening_hours,
        restaurants.status,
        (
            SELECT menu_items.image
            FROM menu_items
            WHERE menu_items.restaurant_name = restaurants.restaurant_name
              AND menu_items.image IS NOT NULL
              AND menu_items.image != ''
            ORDER BY menu_items.item_id ASC
            LIMIT 1
        ) AS restaurant_image
    FROM restaurants
    ORDER BY restaurant_name ASC
");
?>

<?php include("../includes/header.php"); ?>

<main class="admin-shell admin-premium admin-restaurants-page">
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
            <a class="active" href="restaurant_management.php">Restaurants</a>
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
                <p>Admin Restaurant Management</p>
                <h1>Restaurants</h1>
                <small>Update restaurant owner details, operating hours, and status.</small>
            </div>
            <span class="page-hero-mark"><i class="fa-solid fa-store"></i></span>
        </header>

        <section class="admin-panel restaurant-management-panel">
            <div class="admin-section-heading">
                <h2>All Restaurants</h2>
                <p><?php echo $restaurants ? $restaurants->num_rows : 0; ?> campus vendors available for profile and status updates.</p>
            </div>

            <div class="admin-card-grid">
                <?php if ($restaurants && $restaurants->num_rows > 0) { ?>
                    <?php while ($restaurant = $restaurants->fetch_assoc()) {
                        $form_id = "restaurant-form-" . (int)$restaurant["restaurant_id"];
                        $status_class = strtolower(str_replace(" ", "-", (string)$restaurant["status"]));
                        $restaurant_image = $restaurant["restaurant_image"] ?: "rice-bowl.jpg";
                    ?>
                        <article class="management-card restaurant-management-card">
                            <form id="<?php echo e($form_id); ?>" method="POST" action="restaurant_management.php">
                                <input type="hidden" name="restaurant_id" value="<?php echo (int)$restaurant["restaurant_id"]; ?>">
                            </form>

                            <div class="restaurant-card-layout">
                                <div class="restaurant-card-left">
                                    <div class="management-card-header">
                                        <img
                                            class="restaurant-management-thumb"
                                            src="../images/<?php echo e($restaurant_image); ?>"
                                            alt="<?php echo e($restaurant["restaurant_name"]); ?>"
                                        >
                                        <div>
                                            <h3><?php echo e($restaurant["restaurant_name"]); ?></h3>
                                            <span class="status-badge status-<?php echo e($status_class); ?>"><?php echo e($restaurant["status"]); ?></span>
                                        </div>
                                    </div>

                                    <label>
                                        <span>Owner</span>
                                        <input form="<?php echo e($form_id); ?>" type="text" name="owner_name" value="<?php echo e($restaurant["owner_name"]); ?>">
                                    </label>
                                    <label>
                                        <span>Phone</span>
                                        <input form="<?php echo e($form_id); ?>" type="text" name="phone" value="<?php echo e($restaurant["phone"]); ?>">
                                    </label>
                                </div>

                                <div class="restaurant-card-right">
                                    <label>
                                        <span>Opening Hours</span>
                                        <input form="<?php echo e($form_id); ?>" type="text" name="opening_hours" value="<?php echo e($restaurant["opening_hours"]); ?>">
                                    </label>
                                    <label>
                                        <span>Status</span>
                                        <select form="<?php echo e($form_id); ?>" name="status">
                                            <?php foreach ($statuses as $status) { ?>
                                                <option value="<?php echo e($status); ?>" <?php echo $restaurant["status"] === $status ? "selected" : ""; ?>>
                                                    <?php echo e($status); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </label>

                                    <div class="admin-table-actions management-actions">
                                        <button form="<?php echo e($form_id); ?>" class="admin-save-btn" type="submit" name="save_restaurant">
                                            <i class="fa-solid fa-floppy-disk"></i> Save
                                        </button>
                                        <button form="<?php echo e($form_id); ?>" class="admin-delete-btn" type="submit" name="delete_restaurant" onclick="return confirm('Delete this restaurant?')">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php } ?>
                <?php } else { ?>
                    <p class="empty-state">No restaurants found.</p>
                <?php } ?>
            </div>
        </section>
    </section>
</main>

<?php include("../includes/footer.php"); ?>
