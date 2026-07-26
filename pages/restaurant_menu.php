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

if (isset($_GET["toggle"])) {
    $item_id = (int) $_GET["toggle"];

    $updateSql = "
        UPDATE menu_items
        SET availability = IF(availability = 'Available', 'Out of Stock', 'Available')
        WHERE item_id = ? AND restaurant_name = ?
    ";

    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("is", $item_id, $restaurant_name);
    $updateStmt->execute();

    header("Location: restaurant_menu.php");
    exit();
}

$sql = "SELECT * FROM menu_items WHERE restaurant_name = ? ORDER BY item_name ASC";
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
            <a href="restaurant_orders.php"><i class="fa-solid fa-receipt"></i> Orders</a>
            <a class="active" href="restaurant_menu.php"><i class="fa-solid fa-bowl-food"></i> Menu</a>
            <a href="restaurant_analytics.php"><i class="fa-solid fa-chart-simple"></i> Analytics</a>
            <a href="restaurant_profile.php"><i class="fa-solid fa-user"></i> Profile</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <section class="restaurant-visual-hero">
        <div>
            <span>Menu catalogue</span>
            <h2><?php echo h($restaurant_name); ?></h2>
            <p>Keep availability updated for items like <?php echo h($hero_item["item_name"]); ?>.</p>
        </div>
        <img src="../images/<?php echo h($hero_item["image"]); ?>" alt="<?php echo h($hero_item["item_name"]); ?>">
    </section>

    <div class="restaurant-page-heading">
        <div>
            <h1 class="restaurant-title">Menu Management</h1>
            <p class="restaurant-subtitle">Manage your restaurant menu, prices and food availability.</p>
        </div>
        <a class="add-food-btn" href="restaurant_add_food.php"><i class="fa-solid fa-plus"></i> Add New Food</a>
    </div>

    <div class="restaurant-menu-grid">
        <?php if ($result->num_rows > 0) { ?>
            <?php while ($item = $result->fetch_assoc()) { ?>
                <div class="restaurant-food-card">
                    <img src="../images/<?php echo h($item["image"]); ?>" alt="<?php echo h($item["item_name"]); ?>">

                    <div class="restaurant-food-info">
                        <h3><?php echo h($item["item_name"]); ?></h3>
                        <p>RM<?php echo number_format((float)$item["price"], 2); ?></p>
                    </div>

                    <?php if ($item["availability"] === "Available") { ?>
                        <span class="available"><i class="fa-solid fa-circle"></i> Available</span>
                    <?php } else { ?>
                        <span class="out-stock"><i class="fa-solid fa-circle"></i> Out of Stock</span>
                    <?php } ?>

                    <div class="food-buttons">
                        <a class="edit-btn" href="restaurant_edit_food.php?id=<?php echo (int)$item["item_id"]; ?>">Edit</a>

                        <a class="<?php echo $item["availability"] === "Available" ? "disable" : "enable"; ?>"
                           href="restaurant_menu.php?toggle=<?php echo (int)$item["item_id"]; ?>">
                            <?php echo $item["availability"] === "Available" ? "Disable" : "Enable"; ?>
                        </a>

                        <a class="delete-btn"
                           onclick="return confirm('Delete this food?')"
                           href="restaurant_delete_food.php?id=<?php echo (int)$item["item_id"]; ?>">
                            Delete
                        </a>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p class="empty-state">No menu items yet.</p>
        <?php } ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
