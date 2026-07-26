<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("../includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function activeClass($condition)
{
    return $condition ? " active-filter" : "";
}

function isRestaurantOpenStatus($status): bool
{
    return strtolower(trim((string) $status)) === "open";
}

function menuUrl(array $params = [])
{
    $query = http_build_query(array_filter($params, static fn($value) => $value !== ""));
    return "menu.php" . ($query === "" ? "" : "?" . $query);
}

function foodBadge(string $category): string
{
    $badges = [
        "Rice" => '<span class="badge popular">🍚 Rice</span>',
        "Drinks" => '<span class="badge drink">🥤 Drink</span>',
        "Snacks" => '<span class="badge snack">🍟 Snack</span>',
        "Noodles" => '<span class="badge noodle">🍜 Noodles</span>',
    ];

    return $badges[$category] ?? "";
}

$conn->query("
    CREATE TABLE IF NOT EXISTS menu_feedback (
        feedback_id INT AUTO_INCREMENT PRIMARY KEY,
        item_id INT NOT NULL,
        user_id INT NOT NULL,
        rating TINYINT UNSIGNED DEFAULT NULL,
        liked TINYINT(1) NOT NULL DEFAULT 0,
        review TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_item (user_id, item_id),
        KEY idx_menu_feedback_item (item_id)
    )
");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["feedback_action"], $_POST["item_id"])) {
    $itemId = (int) $_POST["item_id"];
    $feedbackAction = $_POST["feedback_action"];

    if ($itemId > 0 && $feedbackAction === "toggle_like") {
        $stmt = $conn->prepare("
            INSERT INTO menu_feedback (item_id, user_id, liked)
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE liked = IF(liked = 1, 0, 1)
        ");
        $stmt->bind_param("ii", $itemId, $userId);
        $stmt->execute();
    }

    if ($itemId > 0 && $feedbackAction === "save_review") {
        $rating = (int) ($_POST["rating"] ?? 0);
        $review = trim($_POST["review"] ?? "");

        if ($rating >= 1 && $rating <= 5) {
            $stmt = $conn->prepare("
                INSERT INTO menu_feedback (item_id, user_id, rating, review)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE rating = VALUES(rating), review = VALUES(review)
            ");
            $stmt->bind_param("iiis", $itemId, $userId, $rating, $review);
            $stmt->execute();
        }
    }

    $redirect = $_POST["return_to"] ?? "menu.php";
    if (strpos($redirect, "menu.php") === false) {
        $redirect = "menu.php";
    }

    header("Location: " . $redirect);
    exit();
}

$search = trim($_GET["search"] ?? "");
$budget = $_GET["budget"] ?? "";
$category = $_GET["category"] ?? "";
$restaurant = trim($_GET["restaurant"] ?? "");
$returnTo = $_SERVER["REQUEST_URI"] ?? "menu.php";

$allowedBudgets = ["5", "10", "15"];
$allowedCategories = ["Rice", "Noodles", "Snacks", "Drinks", "Custom meal"];

if (!in_array($budget, $allowedBudgets, true)) {
    $budget = "";
}

if (!in_array($category, $allowedCategories, true)) {
    $category = "";
}

$sql = "
    SELECT
        menu_items.*,
        restaurants.status AS restaurant_status,
        COALESCE(feedback_stats.like_count, 0) AS like_count,
        COALESCE(feedback_stats.review_count, 0) AS review_count,
        COALESCE(feedback_stats.average_rating, 0) AS average_rating,
        COALESCE(feedback_stats.rating_count, 0) AS rating_count,
        COALESCE(user_feedback.liked, 0) AS user_liked,
        COALESCE(user_feedback.rating, 0) AS user_rating,
        COALESCE(user_feedback.review, '') AS user_review
    FROM menu_items
    LEFT JOIN restaurants
        ON restaurants.restaurant_name = menu_items.restaurant_name
    LEFT JOIN (
        SELECT
            item_id,
            SUM(CASE WHEN liked = 1 THEN 1 ELSE 0 END) AS like_count,
            SUM(CASE WHEN review IS NOT NULL AND TRIM(review) <> '' THEN 1 ELSE 0 END) AS review_count,
            AVG(CASE WHEN rating IS NOT NULL THEN rating END) AS average_rating,
            COUNT(rating) AS rating_count
        FROM menu_feedback
        GROUP BY item_id
    ) AS feedback_stats ON feedback_stats.item_id = menu_items.item_id
    LEFT JOIN menu_feedback AS user_feedback
        ON user_feedback.item_id = menu_items.item_id
        AND user_feedback.user_id = ?
";
$where = [];
$params = [$userId];
$types = "i";

if ($search !== "") {
    $where[] = "(menu_items.item_name LIKE CONCAT('%', ?, '%') OR menu_items.restaurant_name LIKE CONCAT('%', ?, '%'))";
    $params[] = $search;
    $params[] = $search;
    $types .= "ss";
}

if ($budget !== "") {
    $where[] = "menu_items.price <= ?";
    $params[] = (int) $budget;
    $types .= "i";
}

if ($category !== "") {
    $where[] = "menu_items.category = ?";
    $params[] = $category;
    $types .= "s";
}

if ($restaurant !== "") {
    $where[] = "menu_items.restaurant_name = ?";
    $params[] = $restaurant;
    $types .= "s";
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$budgetFilters = [
    ["label" => "RM5", "value" => "5"],
    ["label" => "RM10", "value" => "10"],
    ["label" => "RM15", "value" => "15"],
];

$categoryFilters = [
    ["label" => "All", "value" => "", "icon" => "fa-border-all"],
    ["label" => "Rice", "value" => "Rice", "icon" => "fa-bowl-rice"],
    ["label" => "Noodles", "value" => "Noodles", "icon" => "fa-bowl-food"],
    ["label" => "Snacks", "value" => "Snacks", "icon" => "fa-cookie-bite"],
    ["label" => "Drinks", "value" => "Drinks", "icon" => "fa-mug-saucer"],
    ["label" => "Custom meal", "value" => "Custom meal", "icon" => "fa-utensils"],
];
?>

<?php include("../includes/header.php"); ?>

<div class="menu-page">
    <div class="menu-top">
        <div class="logo">BUDDY BITES</div>

        <div class="menu-nav">
            <a href="home.php"><i class="fa-solid fa-house"></i> Home</a>
            <a class="active" href="menu.php"><i class="fa-solid fa-utensils"></i> Menu</a>
            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Cart</a>
            <a href="group_order.php"><i class="fa-solid fa-users"></i> Group Order</a>
            <a href="order_history.php"><i class="fa-solid fa-clipboard-list"></i> My Orders</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <form method="GET" action="menu.php" class="menu-search">
        <input
            type="text"
            name="search"
            id="liveSearch"
            placeholder="Search Nasi Lemak, Drinks, Student Cafe..."
            value="<?php echo h($search); ?>"
        >
        <button type="submit" aria-label="Search">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </form>

    <div class="banner-row-carousel">
        <img class="banner-img side-banner-img" src="../images/nasi-lemak.jpg" alt="Nasi Lemak">
        <img class="banner-img main-banner-img" src="../images/chicken-chop-rice.jpg" alt="Chicken Chop Rice">
        <img class="banner-img side-banner-img" src="../images/mee-goreng-mamak.jpg" alt="Mee Goreng Mamak">
    </div>

    <div class="dots">
        <span class="dot active-dot" onclick="showBannerSet(0)"></span>
        <span class="dot" onclick="showBannerSet(1)"></span>
        <span class="dot" onclick="showBannerSet(2)"></span>
    </div>

    <div class="filters">
        <p><i class="fa-solid fa-tag"></i> Budget:</p>

        <?php foreach ($budgetFilters as $filter) { ?>
            <a class="filter-btn<?php echo activeClass($budget === $filter["value"]); ?>" href="<?php echo h(menuUrl(["budget" => $filter["value"]])); ?>">
                <?php echo h($filter["label"]); ?>
            </a>
        <?php } ?>

        <a class="filter-btn<?php echo activeClass($budget === "" && $category === ""); ?>" href="menu.php">All</a>
    </div>

    <div class="categories">
        <?php foreach ($categoryFilters as $filter) { ?>
            <?php
            $isAll = $filter["value"] === "";
            $href = $isAll ? "menu.php" : menuUrl(["category" => $filter["value"]]);
            $isActive = $isAll ? ($category === "" && $budget === "") : ($category === $filter["value"]);
            ?>
            <a class="filter-btn<?php echo activeClass($isActive); ?>" href="<?php echo h($href); ?>">
                <i class="fa-solid <?php echo h($filter["icon"]); ?>"></i> <?php echo h($filter["label"]); ?>
            </a>
        <?php } ?>
    </div>

    <?php if ($restaurant !== "") { ?>
        <div class="restaurant-header">

            <div class="restaurant-icon">
                <i class="fa-solid fa-store"></i>
            </div>

            <h1><?php echo h($restaurant); ?></h1>

            <p class="restaurant-tagline">
                Student-friendly meals from this campus vendor
            </p>

            <?php
            $statusColor = "green";
            $statusText = "Open";
            ?>

<div class="restaurant-meta">

    <span>
        <i class="fa-regular fa-star"></i>
        No ratings yet
    </span>

    <span>
        <i class="fa-solid fa-clock"></i>
        10 - 15 mins
    </span>

    <span style="color:<?php echo h($statusColor); ?>">
        <i class="fa-solid fa-circle" style="color:<?php echo h($statusColor); ?>;"></i>
        <?php echo h($statusText); ?>
    </span>

    <span>
        <i class="fa-solid fa-utensils"></i>
        <?php echo $result->num_rows; ?> Items
    </span>

</div>

            <a class="restaurant-back" href="menu.php">
                ← Back to all restaurants
            </a>

        </div>
    <?php } ?>

    <div class="menu-slider-wrap">
        <?php if ($restaurant === "") { ?>
            <button class="scroll-btn left-btn" type="button" onclick="scrollMenuLeft()">&lsaquo;</button>
        <?php } ?>

        <div class="menu-items" id="menuSlider" dir="ltr">
            <?php if ($result && $result->num_rows > 0) { ?>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <?php
                    $isCustomMeal = $row["category"] === "Custom meal" || $row["item_name"] === "Build Your Own Meal";
                    $restaurantStatus = "Open";
                    $isOpen = true;
                    $likeCount = (int) $row["like_count"];
                    $reviewCount = (int) $row["review_count"];
                    $ratingCount = (int) $row["rating_count"];
                    $averageRating = (float) $row["average_rating"];
                    $userRating = (int) $row["user_rating"];
                    $isLiked = (int) $row["user_liked"] === 1;
                    $isPopular = $likeCount > 3 || $reviewCount > 3;
                    $categoryBadge = foodBadge($row["category"]);
                    $addUrl = $isCustomMeal ? "custom_meal.php" : "cart.php?action=add&id=" . urlencode($row["item_id"]);
                    $groupUrl = $isCustomMeal ? "custom_meal.php" : "group_add.php?id=" . urlencode($row["item_id"]);
                    ?>

                    <div
                        class="menu-card"
                        role="link"
                        tabindex="0"
                        onclick="openFoodDetails(event, 'food_details.php?id=<?php echo $row["item_id"]; ?>')"
                        onkeydown="openFoodDetailsFromKeyboard(event, 'food_details.php?id=<?php echo $row["item_id"]; ?>')"
                    >
                        <?php if ($isPopular) { ?>
                            <span class="popular-badge">
                                <i class="fa-solid fa-fire"></i> Popular
                            </span>
                        <?php } ?>

                        <?php if ($categoryBadge !== "") { ?>
                            <div class="food-badges">
                                <?php echo $categoryBadge; ?>
                            </div>
                        <?php } ?>

                        <form method="POST" action="menu.php" class="card-heart-form">
                            <input type="hidden" name="feedback_action" value="toggle_like">
                            <input type="hidden" name="item_id" value="<?php echo h($row["item_id"]); ?>">
                            <input type="hidden" name="return_to" value="<?php echo h($returnTo); ?>">
                            <button class="card-heart<?php echo $isLiked ? " liked" : ""; ?>" type="submit" aria-label="Like <?php echo h($row["item_name"]); ?>">
                                <i class="<?php echo $isLiked ? "fa-solid" : "fa-regular"; ?> fa-heart"></i>
                            </button>
                        </form>

                        <a href="food_details.php?id=<?php echo $row["item_id"]; ?>">
                            <img src="../images/<?php echo h($row["image"]); ?>"
                                 alt="<?php echo h($row["item_name"]); ?>">
                        </a>

                        <h4>
                            <a class="food-link"
                               href="food_details.php?id=<?php echo $row["item_id"]; ?>">
                                <?php echo h($row["item_name"]); ?>
                            </a>
                        </h4>

                        <div class="card-rating">
                            <div class="stars">
                                <?php for ($star = 1; $star <= 5; $star++) { ?>
                                    <i class="<?php echo $averageRating >= $star ? "fa-solid" : "fa-regular"; ?> fa-star"></i>
                                <?php } ?>
                                <?php if ($ratingCount > 0) { ?>

    <span>(<?php echo $ratingCount; ?> Reviews)</span>

<?php } else { ?>

    <span>No reviews yet</span>

<?php } ?>
                            </div>
                            <strong>RM<?php echo number_format((float) $row["price"], 2); ?></strong>
                        </div>

                        <p class="saved-count">
                            <i class="<?php echo $isLiked ? "fa-solid" : "fa-regular"; ?> fa-heart"></i>
                            <?php
if ($likeCount > 0) {
    echo $likeCount . " Saved";
} else {
    echo "Be the first to save";
}
?>
                        </p>

                        <button class="review-toggle" type="button" onclick="toggleReview(this)">
                            <i class="fa-solid fa-star"></i> Write Review
                        </button>

                        <form method="POST" action="menu.php" class="review-form">
                            <input type="hidden" name="feedback_action" value="save_review">
                            <input type="hidden" name="item_id" value="<?php echo h($row["item_id"]); ?>">
                            <input type="hidden" name="return_to" value="<?php echo h($returnTo); ?>">

                            <select class="review-select" name="rating" aria-label="Rating">
                                <?php for ($star = 5; $star >= 1; $star--) { ?>
                                    <option value="<?php echo $star; ?>" <?php echo $userRating === $star ? "selected" : ""; ?>>
                                        <?php echo $star; ?> star<?php echo $star === 1 ? "" : "s"; ?>
                                    </option>
                                <?php } ?>
                            </select>

                            <input
                                class="review-input"
                                type="text"
                                name="review"
                                placeholder="Short review"
                                value="<?php echo h($row["user_review"]); ?>"
                            >

                            <div class="review-form-actions">
                                <button type="submit">Save Review</button>

                                <button type="button" class="cancel-review-btn" onclick="closeReview(this)">
                                    Cancel
                                </button>
                            </div>
                        </form>

                        <p class="restaurant-name">
                            <i class="fa-solid fa-location-dot"></i>
                            <a class="restaurant-link" href="<?php echo h(menuUrl(["restaurant" => $row["restaurant_name"]])); ?>">
                                <?php echo h($row["restaurant_name"]); ?>
                            </a>
                        </p>

                        <p class="availability">
                            <?php if ($isOpen) { ?>
                                <span style="color:green;">
                                    <i class="fa-solid fa-circle"></i> Open
                                </span>
                            <?php } else { ?>
                                <span style="color:red;">
                                    <i class="fa-solid fa-circle"></i> <?php echo h($restaurantStatus); ?>
                                </span>
                            <?php } ?>
                        </p>

                        <p class="prep-time">
                            <i class="fa-regular fa-clock"></i>
                            10 - 15 mins
                        </p>

                        <div class="card-actions">
                            <?php if ($isOpen) { ?>
                                <a class="add-btn" href="<?php echo h($addUrl); ?>">+ Add</a>
                                <a class="group-btn" href="<?php echo h($groupUrl); ?>">
                                    <i class="fa-solid fa-user-group"></i>
                                </a>
                            <?php } else { ?>
                                <button class="add-btn" disabled style="background:#999;cursor:not-allowed;">
                                    Closed
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="no-menu-found">
                    <i class="fa-solid fa-bowl-food"></i>
                    <h3>No meals found</h3>
                    <p>Try another keyword or browse all meals.</p>
                    <a href="menu.php" class="back-btn">Browse All</a>
                </div>
            <?php } ?>
        </div>

        <?php if ($restaurant === "") { ?>
            <button class="scroll-btn right-btn" type="button" onclick="scrollMenuRight()">&rsaquo;</button>
        <?php } ?>
    </div>
</div>

<script>
let bannerIndex = 0;

const bannerSets = [
    [
        "../images/nasi-lemak.jpg",
        "../images/chicken-chop-rice.jpg",
        "../images/mee-goreng-mamak.jpg"
    ],
    [
        "../images/kampung-fried-rice.jpg",
        "../images/black-pepper-chicken-rice.jpg",
        "../images/char-kuey-teow.jpg"
    ],
    [
        "../images/tomyam-fried-rice.jpg",
        "../images/fried-chicken-rice.jpg",
        "../images/mini-hotdog.jpg"
    ]
];

window.onload = function() {
    const slider = document.getElementById("menuSlider");
    if (slider) {
        slider.scrollLeft = 0;
    }

    setInterval(nextBannerSet, 7000);
};

function showBannerSet(index) {
    const images = document.querySelectorAll(".banner-img");
    const dots = document.querySelectorAll(".dot");

    bannerIndex = index;

    images.forEach(function(img, i) {
        img.src = bannerSets[bannerIndex][i];
    });

    dots.forEach(function(dot) {
        dot.classList.remove("active-dot");
    });

    dots[bannerIndex].classList.add("active-dot");
}

function nextBannerSet() {
    bannerIndex++;

    if (bannerIndex >= bannerSets.length) {
        bannerIndex = 0;
    }

    showBannerSet(bannerIndex);
}

function scrollMenuRight() {
    document.getElementById("menuSlider").scrollBy({
        left: 300,
        behavior: "smooth"
    });
}

function scrollMenuLeft() {
    document.getElementById("menuSlider").scrollBy({
        left: -300,
        behavior: "smooth"
    });
}

function toggleReview(button) {
    const form = button.nextElementSibling;
    form.classList.toggle("show");
}

function closeReview(button) {
    const form = button.closest(".review-form");
    form.classList.remove("show");
}

function openFoodDetails(event, url) {
    if (event.target.closest("a, button, input, select, textarea, form")) {
        return;
    }

    window.location.href = url;
}

function openFoodDetailsFromKeyboard(event, url) {
    if (event.key !== "Enter" && event.key !== " ") {
        return;
    }

    event.preventDefault();
    window.location.href = url;
}
</script>

<?php include("../includes/footer.php"); ?>
