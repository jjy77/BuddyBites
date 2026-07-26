<?php
include("../includes/auth_check.php");
requireRole("restaurant");
include("../includes/db.php");

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$restaurant = $_SESSION["full_name"];
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
$heroStmt->bind_param("s", $restaurant);
$heroStmt->execute();
$heroResult = $heroStmt->get_result();

if ($heroResult && $heroResult->num_rows > 0) {
    $hero_item = $heroResult->fetch_assoc();
}

if (isset($_POST["save_profile"])) {
    $owner = $_POST["owner_name"];
    $phone = $_POST["phone"];
    $hours = $_POST["opening_hours"];
    $status = $_POST["status"];

    $stmt = $conn->prepare("
        UPDATE restaurants
        SET owner_name = ?,
            phone = ?,
            opening_hours = ?,
            status = ?
        WHERE restaurant_name = ?
    ");

    $stmt->bind_param("sssss", $owner, $phone, $hours, $status, $restaurant);
    $stmt->execute();
}

$stmt = $conn->prepare("SELECT * FROM restaurants WHERE restaurant_name = ?");
$stmt->bind_param("s", $restaurant);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    $conn->query("INSERT IGNORE INTO restaurants (restaurant_name) VALUES ('" . $conn->real_escape_string($restaurant) . "')");

    $stmt = $conn->prepare("SELECT * FROM restaurants WHERE restaurant_name = ?");
    $stmt->bind_param("s", $restaurant);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
}
?>

<?php include("../includes/header.php"); ?>

<div class="restaurant-dashboard">
    <div class="restaurant-nav">
        <div class="logo"><i class="fa-solid fa-store"></i> <?php echo h($restaurant); ?></div>

        <div class="nav-links">
            <a href="restaurant_dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a href="restaurant_orders.php"><i class="fa-solid fa-receipt"></i> Orders</a>
            <a href="restaurant_menu.php"><i class="fa-solid fa-bowl-food"></i> Menu</a>
            <a href="restaurant_analytics.php"><i class="fa-solid fa-chart-simple"></i> Analytics</a>
            <a class="active" href="restaurant_profile.php"><i class="fa-solid fa-user"></i> Profile</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <section class="restaurant-visual-hero">
        <div>
            <span>Restaurant identity</span>
            <h2><?php echo h($restaurant); ?></h2>
            <p>Your profile represents menu items like <?php echo h($hero_item["item_name"]); ?>.</p>
        </div>
        <img src="../images/<?php echo h($hero_item["image"]); ?>" alt="<?php echo h($hero_item["item_name"]); ?>">
    </section>

    <div class="restaurant-page-heading">
        <div>
            <h1 class="restaurant-title">Restaurant Profile</h1>
            <p class="restaurant-subtitle">Manage restaurant information and operating status.</p>
        </div>
    </div>

    <form method="POST" class="restaurant-profile-form">
        <div class="restaurant-profile-card">
            <div class="profile-row">
                <label>Restaurant Name</label>
                <input type="text" value="<?php echo h($restaurant); ?>" readonly>
            </div>

            <div class="profile-row">
                <label>Owner Name</label>
                <input type="text" name="owner_name" value="<?php echo h($data["owner_name"]); ?>">
            </div>

            <div class="profile-row">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?php echo h($data["phone"]); ?>">
            </div>

            <div class="profile-row">
                <label>Opening Hours</label>
                <input type="text" name="opening_hours" value="<?php echo h($data["opening_hours"]); ?>">
            </div>

            <div class="profile-row">
                <label>Restaurant Status</label>
                <select name="status">
                    <option value="Open" <?php if ($data["status"] == "Open") echo "selected"; ?>>Open</option>
                    <option value="Closed" <?php if ($data["status"] == "Closed") echo "selected"; ?>>Closed</option>
                    <option value="Temporarily Unavailable" <?php if ($data["status"] == "Temporarily Unavailable") echo "selected"; ?>>Temporarily Unavailable</option>
                </select>
            </div>

            <button type="submit" name="save_profile" class="save-profile-btn">
                Save Changes
            </button>
        </div>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
