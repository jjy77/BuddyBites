<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("../includes/db.php");

if (!isset($_GET["id"])) {
    header("Location: menu.php");
    exit();
}

$id = (int) $_GET["id"];

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function isRestaurantOpenStatus($status): bool
{
    return strtolower(trim((string) $status)) === "open";
}

$stmt = $conn->prepare("SELECT * FROM menu_items WHERE item_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Food not found.");
}

$item = $result->fetch_assoc();

$reviewSql = "
    SELECT mf.rating, mf.review, mf.created_at, users.full_name
    FROM menu_feedback mf
    JOIN users ON mf.user_id = users.user_id
    WHERE mf.item_id = ?
    AND mf.rating IS NOT NULL
    ORDER BY mf.created_at DESC
";

$reviewStmt = $conn->prepare($reviewSql);
$reviewStmt->bind_param("i", $id);
$reviewStmt->execute();
$reviews = $reviewStmt->get_result();

$avgSql = "
    SELECT AVG(rating) AS avg_rating, COUNT(rating) AS rating_count
    FROM menu_feedback
    WHERE item_id = ?
    AND rating IS NOT NULL
";

$avgStmt = $conn->prepare($avgSql);
$avgStmt->bind_param("i", $id);
$avgStmt->execute();
$avgData = $avgStmt->get_result()->fetch_assoc();

$avgRating = round((float)($avgData["avg_rating"] ?? 0), 1);
$ratingCount = (int)($avgData["rating_count"] ?? 0);

$restaurantStatus = "Open";

$statusStmt = $conn->prepare("SELECT status FROM restaurants WHERE restaurant_name = ?");
$statusStmt->bind_param("s", $item["restaurant_name"]);
$statusStmt->execute();
$statusData = $statusStmt->get_result()->fetch_assoc();

if ($statusData && trim((string) $statusData["status"]) !== "") {
    $restaurantStatus = trim((string) $statusData["status"]);
}

$isOpen = isRestaurantOpenStatus($restaurantStatus);
$statusText = $isOpen ? "Open" : $restaurantStatus;
$statusColor = $isOpen ? "#2E7D32" : "#B3261E";

$descriptions = [
    "Nasi Lemak Set" => "Traditional Malaysian coconut rice served with sambal, fried chicken, egg and cucumber.",
    "Mee Goreng" => "Spicy stir-fried noodles served with vegetables and egg.",
    "Chicken Rice" => "Tender chicken served with fragrant rice and homemade sauce.",
    "Fried Chicken Rice" => "Crispy fried chicken served with rice and vegetables.",
    "Build Your Own Meal" => "Customize your own meal based on your favourite ingredients."
];

$calories = [
    "Nasi Lemak Set" => "720 kcal",
    "Chicken Rice" => "580 kcal",
    "Fried Chicken Rice" => "700 kcal",
    "Curry Chicken Rice" => "690 kcal",
    "Sweet Sour Chicken Rice" => "670 kcal",
    "Black Pepper Chicken Rice" => "680 kcal",
    "Sambal Fried Rice" => "640 kcal",
    "Egg Fried Rice" => "560 kcal",
    "White Rice + Fried Egg" => "430 kcal",
    "White Rice + Vegetables" => "390 kcal",
    "Chicken Chop Rice" => "760 kcal",
    "Fish Fillet Rice" => "650 kcal",
    "Kampung Fried Rice" => "690 kcal",
    "Pattaya Fried Rice" => "710 kcal",
    "Tomyam Fried Rice" => "670 kcal",
    "Seafood Fried Rice" => "690 kcal",
    "Chicken Fried Rice" => "650 kcal",
    "Anchovies Fried Rice" => "620 kcal",
    "Fried Rice + Egg" => "610 kcal",
    "Chicken Chop Fried Rice" => "760 kcal",
    "Mee Goreng" => "650 kcal",
    "Tomyam Meehoon" => "560 kcal",
    "Char Kuey Teow" => "720 kcal",
    "Build Your Own Meal" => "500 - 800 kcal",
    "French Fries" => "430 kcal",
    "Nuggets Set" => "470 kcal",
    "Chicken Popcorn" => "420 kcal",
    "Cheesy Fries" => "560 kcal",
    "Wedges" => "390 kcal",
    "Onion Rings" => "420 kcal",
    "Sausage Roll" => "340 kcal",
    "Hash Brown" => "250 kcal",
    "Mini Hotdog" => "280 kcal",
    "Chicken Meatballs" => "330 kcal",
    "Fried Wantan" => "300 kcal",
    "Chicken Roll" => "340 kcal",
    "Sandwich Set" => "420 kcal",
    "Chicken Sandwich" => "430 kcal",
    "Tuna Sandwich" => "410 kcal",
    "Egg Sandwich" => "360 kcal",
    "Chicken Wrap" => "520 kcal",
    "Tuna Wrap" => "490 kcal",
    "Egg Mayo Wrap" => "470 kcal",
    "Club Sandwich" => "610 kcal",
    "Chicken Tortilla" => "560 kcal",
    "Cheese Sandwich" => "390 kcal",
    "Double Chicken Wrap" => "710 kcal",
    "Iced Lemon Tea" => "90 kcal",
    "Iced Milo" => "220 kcal",
    "Iced Black Tea" => "60 kcal",
    "Iced Milk Tea" => "180 kcal",
    "Rose Milk Drink" => "170 kcal",
    "Iced Coffee" => "110 kcal",
    "Iced Chocolate" => "240 kcal",
    "Orange Juice" => "120 kcal",
    "Apple Juice" => "115 kcal",
    "Mineral Water" => "0 kcal",
    "Green Tea" => "5 kcal",
    "Strawberry Milkshake" => "320 kcal",
    "Vanilla Milkshake" => "340 kcal",
    "Mango Smoothie" => "270 kcal"
];

$description = $descriptions[$item["item_name"]] ?? "A delicious student-friendly meal prepared fresh every day.";
$kcal = $calories[$item["item_name"]] ?? "450 - 650 kcal";
$nutrition = [
    "Estimated Calories" => $kcal . " per serving"
];

$sqlRecommend = "
    SELECT *
    FROM menu_items
    WHERE item_id != ?
    ORDER BY (restaurant_name = ?) DESC, RAND()
    LIMIT 4
";
$stmtRecommend = $conn->prepare($sqlRecommend);
$stmtRecommend->bind_param("is", $id, $item["restaurant_name"]);
$stmtRecommend->execute();
$resultRecommend = $stmtRecommend->get_result();
?>

<?php include("../includes/header.php"); ?>

<div class="food-details-page">
    <a href="menu.php" class="back-menu">&larr; Back to Menu</a>

    <div class="food-details-card">
        <div class="food-image">
            <img
                src="../images/<?php echo h($item["image"]); ?>"
                alt="<?php echo h($item["item_name"]); ?>"
            >
        </div>

        <div class="food-info">
            <h1><?php echo h($item["item_name"]); ?></h1>
            <h2>RM<?php echo number_format((float) $item["price"], 2); ?></h2>

            <div class="food-rating">
                <span class="rating-stars">
                    <?php for ($star = 1; $star <= 5; $star++) { ?>
                        <i class="<?php echo $avgRating >= $star ? 'fa-solid' : 'fa-regular'; ?> fa-star"></i>
                    <?php } ?>
                </span>

                <span class="rating-text">
                    <?php echo $ratingCount > 0 ? $avgRating . " stars (" . $ratingCount . " reviews)" : "No reviews yet"; ?>
                </span>
            </div>

            <p>
    <a class="restaurant-link"
       href="menu.php?restaurant=<?php echo urlencode($item["restaurant_name"]); ?>">

        <i class="fa-solid fa-store"></i>
        <strong><?php echo h($item["restaurant_name"]); ?></strong>

    </a>
</p>
            <p class="food-meta-line">
                <i class="fa-solid fa-utensils"></i>
                <?php echo h($item["category"]); ?>
            </p>

            <p class="food-meta-line food-status-row">
                <span>
                    <i class="fa-solid fa-circle" style="color:<?php echo h($statusColor); ?>;"></i>
                    <?php echo h($statusText); ?>
                </span>
                <span>
                    <i class="fa-regular fa-clock"></i>
                    10 - 15 mins
                </span>
            </p>

            <div class="food-tabs">
                <button class="tab-btn active" type="button" onclick="openTab(event, 'description')">
                    Description
                </button>
                <button class="tab-btn" type="button" onclick="openTab(event, 'reviews')">
                    Reviews
                </button>
                <button class="tab-btn" type="button" onclick="openTab(event, 'nutrition')">
                    Nutrition
                </button>
            </div>

            <div id="description" class="tab-content active">
                <p><?php echo h($description); ?></p>
            </div>

            <div id="reviews" class="tab-content">
                <?php if ($reviews && $reviews->num_rows > 0) { ?>
                    <?php while ($review = $reviews->fetch_assoc()) { ?>
                        <div class="review-box">
                            <div class="review-stars">
                                <?php for ($star = 1; $star <= 5; $star++) { ?>
                                    <i class="<?php echo ((int)$review["rating"] >= $star) ? 'fa-solid' : 'fa-regular'; ?> fa-star"></i>
                                <?php } ?>
                            </div>

                            <h4><?php echo h($review["full_name"]); ?></h4>
                            <p><?php echo h($review["review"]); ?></p>
                            <small><?php echo date("d M Y", strtotime($review["created_at"])); ?></small>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="empty-review-state">
                        <i class="fa-regular fa-star"></i>
                        <h4>No reviews yet</h4>
                        <p>Reviews and ratings will appear here after users submit feedback.</p>
                    </div>
                <?php } ?>
            </div>

            <div id="nutrition" class="tab-content">
                <table class="nutrition-table">
                    <?php foreach ($nutrition as $label => $value) { ?>
                        <tr>
                            <td><?php echo h($label); ?></td>
                            <td><?php echo h($value); ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <h3>Quantity</h3>
            <div class="quantity-box">
                <button type="button" onclick="decreaseQty()">&minus;</button>
                <input type="text" id="qty" value="1" readonly>
                <button type="button" onclick="increaseQty()">+</button>
            </div>

            <a class="details-cart" href="cart.php?action=add&id=<?php echo h($item["item_id"]); ?>">
                + Add To Cart
            </a>
        </div>
    </div>

    <div class="recommended-section">
        <h2>You May Also Like</h2>
        <p>Recommended meals for you</p>

        <div class="recommended-grid">
            <?php while ($food = $resultRecommend->fetch_assoc()) { ?>
                <div class="recommend-card">
                    <a href="food_details.php?id=<?php echo h($food["item_id"]); ?>">
                        <img
                            src="../images/<?php echo h($food["image"]); ?>"
                            alt="<?php echo h($food["item_name"]); ?>"
                        >
                    </a>

                    <h4><?php echo h($food["item_name"]); ?></h4>
                    <p>RM<?php echo number_format((float) $food["price"], 2); ?></p>

                    <a class="view-food" href="food_details.php?id=<?php echo h($food["item_id"]); ?>">
                        View Food
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<script>
function increaseQty() {
    const qty = document.getElementById("qty");
    qty.value = parseInt(qty.value, 10) + 1;
}

function decreaseQty() {
    const qty = document.getElementById("qty");

    if (parseInt(qty.value, 10) > 1) {
        qty.value = parseInt(qty.value, 10) - 1;
    }
}

function openTab(event, tabName) {
    const contents = document.querySelectorAll(".tab-content");
    const buttons = document.querySelectorAll(".tab-btn");

    contents.forEach(function(content) {
        content.classList.remove("active");
    });

    buttons.forEach(function(button) {
        button.classList.remove("active");
    });

    document.getElementById(tabName).classList.add("active");
    event.currentTarget.classList.add("active");
}
</script>

<?php include("../includes/footer.php"); ?>
