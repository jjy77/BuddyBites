<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("../includes/db.php");

if (!isset($_GET["restaurant"])) {
    header("Location: menu.php");
    exit();
}

$restaurant = trim($_GET["restaurant"]);

function h($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, "UTF-8");
}

function isRestaurantOpenStatus($status): bool
{
    return strtolower(trim((string) $status)) === "open";
}

$restaurantStmt = $conn->prepare("
    SELECT restaurant_name, owner_name, phone, opening_hours, status
    FROM restaurants
    WHERE restaurant_name = ?
");
$restaurantStmt->bind_param("s", $restaurant);
$restaurantStmt->execute();
$restaurantData = $restaurantStmt->get_result()->fetch_assoc();

$menuStmt = $conn->prepare("
    SELECT item_id, item_name, category, price, image, availability
    FROM menu_items
    WHERE restaurant_name = ?
    ORDER BY category ASC, item_name ASC
");
$menuStmt->bind_param("s", $restaurant);
$menuStmt->execute();
$menuItems = $menuStmt->get_result();

$status = trim((string) ($restaurantData["status"] ?? "Open"));
$isOpen = isRestaurantOpenStatus($status);
$openingHours = $restaurantData["opening_hours"] ?? "8:00 AM - 10:00 PM";
$phone = $restaurantData["phone"] ?? "-";
?>

<?php include("../includes/header.php"); ?>

<main class="restaurant-page">
    <nav class="restaurant-public-nav">
        <a class="logo" href="home.php">BUDDY BITES</a>

        <div>
            <a href="home.php"><i class="fa-solid fa-house"></i> Home</a>
            <a href="menu.php"><i class="fa-solid fa-utensils"></i> Menu</a>
            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Cart</a>
            <a href="order_history.php"><i class="fa-solid fa-clipboard-list"></i> My Orders</a>
        </div>
    </nav>

    <section class="restaurant-public-hero">
        <a href="menu.php" class="back-menu">
            <i class="fa-solid fa-arrow-left"></i> Back to Menu
        </a>

        <div class="restaurant-hero-grid">
            <div>
                <span class="restaurant-kicker"><i class="fa-solid fa-store"></i> Campus Restaurant</span>
                <h1><?php echo h($restaurant); ?></h1>
                <p>Fresh meals prepared daily for MMU students.</p>
            </div>

            <div class="restaurant-detail-card">
                <div>
                    <span>Status</span>
                    <b class="<?php echo $isOpen ? "status-open-text" : "status-closed-text"; ?>">
                        <?php echo h($isOpen ? "Open" : $status); ?>
                    </b>
                </div>
                <div>
                    <span>Opening Hours</span>
                    <b><?php echo h($openingHours); ?></b>
                </div>
                <div>
                    <span>Phone</span>
                    <b><?php echo h($phone); ?></b>
                </div>
            </div>
        </div>
    </section>

    <section class="restaurant-public-menu">
        <div class="section-title-row">
            <div>
                <h2>Menu</h2>
                <p>Browse available meals from <?php echo h($restaurant); ?>.</p>
            </div>
        </div>

        <div class="restaurant-public-grid">
            <?php if ($menuItems && $menuItems->num_rows > 0) { ?>
                <?php while ($item = $menuItems->fetch_assoc()) { ?>
                    <article class="restaurant-public-card">
                        <img src="../images/<?php echo h($item["image"]); ?>" alt="<?php echo h($item["item_name"]); ?>">
                        <div>
                            <span><?php echo h($item["category"]); ?></span>
                            <h3><?php echo h($item["item_name"]); ?></h3>
                            <p>RM<?php echo number_format((float)$item["price"], 2); ?></p>
                        </div>
                        <?php if (($item["availability"] ?? "Available") === "Available" && $isOpen) { ?>
                            <a class="add-btn" href="cart.php?action=add&id=<?php echo (int)$item["item_id"]; ?>">+ Add</a>
                        <?php } else { ?>
                            <button class="add-btn disabled" type="button" disabled>Unavailable</button>
                        <?php } ?>
                    </article>
                <?php } ?>
            <?php } else { ?>
                <p class="empty-state">No menu items found for this restaurant.</p>
            <?php } ?>
        </div>
    </section>
</main>

<?php include("../includes/footer.php"); ?>
