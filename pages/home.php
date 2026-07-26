<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("../includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>

<?php include("../includes/header.php"); ?>

<div class="home-page">

    <div class="home-hero">
        <div class="logo">BUDDY BITES</div>

        <div class="top-nav">
            <a class="active" href="home.php"><i class="fa-solid fa-house"></i> Home</a>
            <a href="menu.php"><i class="fa-solid fa-utensils"></i> Menu</a>
            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Cart</a>
            <a href="group_order.php"><i class="fa-solid fa-users"></i> Group Order</a>
            <a href="order_history.php"><i class="fa-solid fa-clipboard-list"></i> My Orders</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>

        <div class="hero-content">
            <h1>Eat together, save together.</h1>
            <p>Student-friendly food delivery with group ordering, budget filters and custom meals.</p>

            <form class="search-box" method="GET" action="menu.php">
                <input type="text" name="search" placeholder="Search meals, restaurants...">
                <button type="submit" aria-label="Search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>
    </div>

    <section class="why-section">
        <h2>Why Choose BuddyBites?</h2>
        <p>Designed specifically for students on a budget</p>

        <div class="feature-row">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h3>Group Ordering</h3>
                <p>Create a room, invite friends, split delivery fees automatically.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <h3>Budget Filter</h3>
                <p>Filter meals by RM5, RM10, or RM15.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-bowl-food"></i>
                </div>
                <h3>Custom Meals</h3>
                <p>Build your own meal. Pick base, protein, toppings, sauce.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-store"></i>
                </div>
                <h3>Campus Cafes</h3>
                <p>Explore meals from different campus cafes and student-favourite restaurants.</p>
            </div>
        </div>
    </section>

    <section class="budget-section">
        <section class="home-budget">
            <h2>Browse by Budget</h2>
            <p>Find affordable meals within your daily budget.</p>

            <div class="home-budget-buttons">
                <a href="menu.php?budget=5" class="filter-btn">RM5</a>
                <a href="menu.php?budget=10" class="filter-btn">RM10</a>
                <a href="menu.php?budget=15" class="filter-btn">RM15</a>
                <a href="menu.php" class="filter-btn">All Meals</a>
            </div>
        </section>

        <h1><i class="fa-solid fa-fire"></i> Popular Student Picks</h1>
        <p class="budget-subtitle">Most ordered meals under RM10</p>

        <div class="meal-row">

            <?php
            $sql = "
                SELECT *
                FROM menu_items
                WHERE price <= 10
                ORDER BY
                    FIELD(category, 'Rice', 'Noodles', 'Snacks', 'Drinks', 'Custom meal'),
                    price ASC,
                    item_name ASC
            ";
            $result = $conn->query($sql);
            $categoryOrder = ["Rice", "Noodles", "Snacks", "Drinks", "Custom meal"];
            $itemsByCategory = [];
            $seenNames = [];
            $displayMeals = [];

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $nameKey = strtolower(trim($row["item_name"]));

                    if (isset($seenNames[$nameKey])) {
                        continue;
                    }

                    $seenNames[$nameKey] = true;
                    $itemsByCategory[$row["category"]][] = $row;
                }

                while (count($displayMeals) < 6) {
                    $addedThisRound = false;

                    foreach ($categoryOrder as $pickCategory) {
                        if (!empty($itemsByCategory[$pickCategory]) && count($displayMeals) < 6) {
                            $displayMeals[] = array_shift($itemsByCategory[$pickCategory]);
                            $addedThisRound = true;
                        }
                    }

                    if (!$addedThisRound) {
                        break;
                    }
                }

                foreach ($displayMeals as $row) {
            ?>

                    <div class="menu-card home-card">
                        <img src="../images/<?php echo htmlspecialchars($row["image"]); ?>">

                        <h4><?php echo htmlspecialchars($row["item_name"]); ?></h4>

                        <div class="card-rating">
                            <span>
                                <i class="fa-regular fa-star"></i>
                                <i class="fa-regular fa-star"></i>
                                <i class="fa-regular fa-star"></i>
                                <i class="fa-regular fa-star"></i>
                                <i class="fa-regular fa-star"></i>
                                (0)
                            </span>

                            <strong>
                                RM<?php echo number_format($row["price"], 2); ?>
                            </strong>
                        </div>

                        <p class="restaurant-name">
                            <i class="fa-solid fa-location-dot"></i>
                            <?php echo htmlspecialchars($row["restaurant_name"]); ?>
                        </p>

                        <div class="card-actions">
                            <?php if ($row["category"] == "Custom meal" || $row["item_name"] == "Build Your Own Meal") { ?>
                                <a class="add-btn" href="custom_meal.php">+ Add</a>
                            <?php } else { ?>
                                <a class="add-btn" href="cart.php?action=add&id=<?php echo $row["item_id"]; ?>">
                                    + Add
                                </a>
                            <?php } ?>
                        </div>
                    </div>

            <?php
                }
            } else {
                echo "<p>No meals found.</p>";
            }
            ?>

            <a class="view-all" href="menu.php">
                <span>View All</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <section class="how-section">
        <div class="how-left">
            <h1>How BuddyBites Works</h1>
            <p>Ordering food has never been easier.</p>
        </div>

        <div class="how-right">
            <div class="step">
                <b><span class="step-number">1</span> Browse Meals</b><br>
                <span>Set your budget and browse affordable meals</span>
            </div>

            <div class="step">
                <b><span class="step-number">2</span> Add to Cart</b><br>
                <span>Pick your meals and add them when ready</span>
            </div>

            <div class="step">
                <b><span class="step-number">3</span> Create Group Order</b><br>
                <span>Invite friends with a 6-digit room code</span>
            </div>

            <div class="step">
                <b><span class="step-number">4</span> Checkout Together</b><br>
                <span>Confirm once and split delivery fees automatically</span>
            </div>
        </div>
    </section>

    <footer class="home-footer">
        <div class="footer-logo">BUDDY BITES</div>

        <div class="footer-links">
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            <a href="privacy.php">Privacy Policy</a>
            <a href="terms.php">Terms of Service</a>
        </div>
    </footer>

</div>

<?php include("../includes/footer.php"); ?>
