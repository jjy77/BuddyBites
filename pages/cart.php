<?php
include("../includes/auth_check.php");
requireRole("student");
?>
<?php
include("../includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

if (!isset($_SESSION["custom_cart"])) {
    $_SESSION["custom_cart"] = [];
}

$user_id = $_SESSION["user_id"];
$group_id = $_SESSION["group_id"] ?? null;

/* ADD NORMAL ITEM */
if (isset($_GET["action"]) && $_GET["action"] == "add" && isset($_GET["id"])) {
    $item_id = intval($_GET["id"]);

    $sql = "SELECT * FROM menu_items WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $item = $result->fetch_assoc();

        if (isset($_SESSION["cart"][$item_id])) {
            $_SESSION["cart"][$item_id]++;
        } else {
            $_SESSION["cart"][$item_id] = 1;
        }

        /* ALSO SAVE TO GROUP CART IF USER IS IN A GROUP */
        if (!empty($group_id)) {
            $checkSql = "SELECT * FROM group_cart_items 
                         WHERE group_id = ? AND user_id = ? AND item_name = ? AND item_type = 'menu'";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("iis", $group_id, $user_id, $item["item_name"]);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult && $checkResult->num_rows == 1) {
                $existing = $checkResult->fetch_assoc();
                $newQty = $existing["quantity"] + 1;

                $updateSql = "UPDATE group_cart_items 
                              SET quantity = ? 
                              WHERE group_cart_id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("ii", $newQty, $existing["group_cart_id"]);
                $updateStmt->execute();
            } else {
                $insertSql = "INSERT INTO group_cart_items 
                              (group_id, user_id, item_name, restaurant_name, price, quantity, item_type, details)
                              VALUES (?, ?, ?, ?, ?, 1, 'menu', '')";
                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->bind_param(
                    "iissd",
                    $group_id,
                    $user_id,
                    $item["item_name"],
                    $item["restaurant_name"],
                    $item["price"]
                );
                $insertStmt->execute();
            }
        }
    }

    header("Location: cart.php");
    exit();
}

/* DECREASE NORMAL ITEM */
if (isset($_GET["action"]) && $_GET["action"] == "decrease" && isset($_GET["id"])) {
    $item_id = intval($_GET["id"]);

    $sql = "SELECT * FROM menu_items WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    if (isset($_SESSION["cart"][$item_id])) {
        $_SESSION["cart"][$item_id]--;

        if ($_SESSION["cart"][$item_id] <= 0) {
            unset($_SESSION["cart"][$item_id]);
        }
    }

    if (!empty($group_id) && $item) {
        $checkSql = "SELECT * FROM group_cart_items 
                     WHERE group_id = ? AND user_id = ? AND item_name = ? AND item_type = 'menu'";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("iis", $group_id, $user_id, $item["item_name"]);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult && $checkResult->num_rows == 1) {
            $existing = $checkResult->fetch_assoc();
            $newQty = $existing["quantity"] - 1;

            if ($newQty <= 0) {
                $deleteSql = "DELETE FROM group_cart_items WHERE group_cart_id = ?";
                $deleteStmt = $conn->prepare($deleteSql);
                $deleteStmt->bind_param("i", $existing["group_cart_id"]);
                $deleteStmt->execute();
            } else {
                $updateSql = "UPDATE group_cart_items SET quantity = ? WHERE group_cart_id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("ii", $newQty, $existing["group_cart_id"]);
                $updateStmt->execute();
            }
        }
    }

    header("Location: cart.php");
    exit();
}

/* REMOVE NORMAL ITEM */
if (isset($_GET["action"]) && $_GET["action"] == "remove" && isset($_GET["id"])) {
    $item_id = intval($_GET["id"]);

    $sql = "SELECT * FROM menu_items WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    unset($_SESSION["cart"][$item_id]);

    if (!empty($group_id) && $item) {
        $deleteSql = "DELETE FROM group_cart_items 
                      WHERE group_id = ? AND user_id = ? AND item_name = ? AND item_type = 'menu'";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("iis", $group_id, $user_id, $item["item_name"]);
        $deleteStmt->execute();
    }

    header("Location: cart.php");
    exit();
}

/* REMOVE CUSTOM MEAL */
if (isset($_GET["action"]) && $_GET["action"] == "remove_custom" && isset($_GET["index"])) {
    $index = intval($_GET["index"]);

    if (isset($_SESSION["custom_cart"][$index])) {
        $customMeal = $_SESSION["custom_cart"][$index];

        unset($_SESSION["custom_cart"][$index]);
        $_SESSION["custom_cart"] = array_values($_SESSION["custom_cart"]);

        if (!empty($group_id)) {
            $details = "Base: " . $customMeal["base"] .
                       " | Protein: " . $customMeal["protein"] .
                       " | Toppings: " . $customMeal["topping"] .
                       " | Sauce: " . $customMeal["sauce"];

            $deleteSql = "DELETE FROM group_cart_items 
                          WHERE group_id = ? AND user_id = ? AND item_type = 'custom' AND details = ?
                          LIMIT 1";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("iis", $group_id, $user_id, $details);
            $deleteStmt->execute();
        }
    }

    header("Location: cart.php");
    exit();
}

/* CLEAR CART */
if (isset($_GET["action"]) && $_GET["action"] == "clear") {
    $_SESSION["cart"] = [];
    $_SESSION["custom_cart"] = [];

    if (!empty($group_id)) {
        $deleteSql = "DELETE FROM group_cart_items WHERE group_id = ? AND user_id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("ii", $group_id, $user_id);
        $deleteStmt->execute();
    }

    header("Location: cart.php");
    exit();
}

$normal_count = array_sum($_SESSION["cart"]);
$custom_count = count($_SESSION["custom_cart"]);
$total_cart_count = $normal_count + $custom_count;
?>

<?php include("../includes/header.php"); ?>

<div class="cart-page">

    <div class="cart-nav">
        <div class="logo">BUDDY BITES</div>

        <div class="cart-nav-links">
            <a href="home.php"><i class="fa-solid fa-house"></i> Home</a>
            <a href="menu.php"><i class="fa-solid fa-utensils"></i> Menu</a>
            <a class="active" href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Cart (<?php echo $total_cart_count; ?>)</a>
            <a href="group_order.php"><i class="fa-solid fa-users"></i> Group Order</a>
            <a href="order_history.php"><i class="fa-solid fa-clipboard-list"></i> My Orders</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="cart-header-text">
        <h3>🛒 Your cart</h3>
        <p>
            Student budget meal order | BuddyBites checkout
            <?php if (!empty($group_id)) { ?>
                | Group order active
            <?php } ?>
        </p>
    </div>

    <div class="cart-filter">
        <span>⚱ Budget filter:</span>
        <button type="button" onclick="filterCartBudget(5)">RM5</button>
        <button type="button" onclick="filterCartBudget(10)" class="selected">RM10</button>
        <button type="button" onclick="filterCartBudget(15)">RM15</button>
        <button type="button" onclick="showAllCartItems()">All</button>
    </div>

    <div class="cart-layout">

        <div class="cart-left">

            <div class="cart-card">
                <h3>🛒 Your items</h3>

                <?php
                $grand_total = 0;
                $total_items = 0;
                $has_items = false;

                if (!empty($_SESSION["cart"])) {
                    foreach ($_SESSION["cart"] as $item_id => $quantity) {

                        $sql = "SELECT * FROM menu_items WHERE item_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $item_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows == 1) {
                            $has_items = true;
                            $item = $result->fetch_assoc();
                            $subtotal = $item["price"] * $quantity;
                            $grand_total += $subtotal;
                            $total_items += $quantity;
                ?>

                            <div class="cart-item-row" data-price="<?php echo $item["price"]; ?>">

                                <img class="cart-item-img"
                                     src="../images/<?php echo htmlspecialchars($item["image"]); ?>"
                                     alt="<?php echo htmlspecialchars($item["item_name"]); ?>">

                                <div class="cart-item-info">
                                    <h4><?php echo htmlspecialchars($item["item_name"]); ?></h4>
                                    <p><?php echo htmlspecialchars($item["restaurant_name"]); ?></p>
                                    <small>
                                        Budget meal option
                                        <?php if (!empty($group_id)) { echo " | Added to group"; } ?>
                                    </small>
                                </div>

                                <div class="cart-qty">
                                    <a class="qty-circle" href="cart.php?action=decrease&id=<?php echo $item_id; ?>">−</a>
                                    <span><?php echo $quantity; ?></span>
                                    <a class="qty-circle" href="cart.php?action=add&id=<?php echo $item_id; ?>">＋</a>
                                </div>

                                <div class="cart-price">
                                    RM<?php echo number_format($subtotal, 2); ?>
                                </div>

                                <a class="cart-delete" href="cart.php?action=remove&id=<?php echo $item_id; ?>">🗑</a>

                            </div>

                <?php
                        }
                    }
                }

                if (!empty($_SESSION["custom_cart"])) {
                    foreach ($_SESSION["custom_cart"] as $index => $customMeal) {
                        $has_items = true;
                        $custom_price = floatval($customMeal["total_price"]);
                        $grand_total += $custom_price;
                        $total_items++;
                ?>

                        <div class="cart-item-row" data-price="<?php echo $custom_price; ?>">

                            <img class="cart-item-img"
                                 src="../images/rice-bowl.jpg"
                                 alt="Custom Meal">

                            <div class="cart-item-info">
                                <h4>Build Your Own Meal</h4>
                                <p>
                                    Base: <?php echo htmlspecialchars($customMeal["base"]); ?><br>
                                    Protein: <?php echo htmlspecialchars($customMeal["protein"]); ?><br>
                                    Toppings: <?php echo htmlspecialchars($customMeal["topping"]); ?><br>
                                    Sauce: <?php echo htmlspecialchars($customMeal["sauce"]); ?>
                                </p>
                                <small>
                                    Custom meal option
                                    <?php if (!empty($group_id)) { echo " | Added to group"; } ?>
                                </small>
                            </div>

                            <div class="cart-qty">
                                <span>1</span>
                            </div>

                            <div class="cart-price">
                                RM<?php echo number_format($custom_price, 2); ?>
                            </div>

                            <a class="cart-delete" href="cart.php?action=remove_custom&index=<?php echo $index; ?>">🗑</a>

                        </div>

                <?php
                    }
                }

                if ($has_items) {
                ?>

                    <div class="cart-card-footer">
                        <a class="add-more-btn" href="menu.php">Add menu items</a>
                        <a class="add-more-btn" href="custom_meal.php">Add custom meal</a>
                        <?php if (!empty($group_id)) { ?>
                            <a class="add-more-btn" href="group_order.php">Back to group order</a>
                        <?php } ?>
                    </div>

                <?php } else { ?>

                    <p class="empty-cart-text">Your cart is empty.</p>
                    <a class="add-more-btn" href="menu.php">Back to Menu</a>

                <?php } ?>

            </div>

            <div class="cart-card group-box">
                <h3>👥 Order Info</h3>

                <div class="member-pill">
                    <b><?php echo htmlspecialchars($_SESSION["full_name"] ?? "Student"); ?></b>
                    <span>RM<?php echo number_format($grand_total, 2); ?> | <?php echo $total_items; ?> items</span>
                </div>

                <div class="member-pill">
                    <b>BuddyBites Campus Delivery</b>
                    <span>
                        <?php if (!empty($group_id)) { ?>
                            This cart is linked to your active group order
                        <?php } else { ?>
                            Fast • Affordable • Convenient
                        <?php } ?>
                    </span>
                </div>
            </div>

        </div>

        <div class="cart-right">

            <div class="cart-side-card">
                <h3>⏰ Delivery slot</h3>

                <label class="slot-option selected-slot">
                    <input type="radio" name="slot" checked>
                    <div>
                        <b>Lunch Delivery</b>
                    </div>
                    <span>Available</span>
                </label>

                <label class="slot-option">
                    <input type="radio" name="slot">
                    <div>
                        <b>Dinner Delivery</b>
                    </div>
                    <span>Available</span>
                </label>
            </div>

            <div class="delivery-banner">

            🎉 Free delivery when your order reaches RM30

            </div>

            <div class="cart-side-card">
                <h3>🧾 Order summary</h3>

                <?php
                $cartSubtotal = $grand_total;
                $platformFee = $has_items ? 0.50 : 0.00;
                $freeDeliveryMinimum = 30.00;
                $deliveryFee = ($has_items && (($cartSubtotal + $platformFee) >= $freeDeliveryMinimum)) ? 0.00 : ($has_items ? 1.00 : 0.00);
                $final_total = $cartSubtotal + $platformFee + $deliveryFee;
                ?>

                <div class="summary-line">
                    <span>Subtotal</span>
                    <b>RM<?php echo number_format($cartSubtotal, 2); ?></b>
                </div>

                <div class="summary-line">
                    <span>Delivery fee</span>
                    <b>RM<?php echo number_format($deliveryFee, 2); ?></b>
                </div>

                <div class="summary-line">
                    <span>Platform fee</span>
                    <b>RM<?php echo number_format($platformFee, 2); ?></b>
                </div>

                <div class="promo-box">

                    <input
                        type="text"
                        placeholder="Promo Code">

                     <button type="button">
                         Apply
                    </button>

                
                </div>
                <hr>

                <div class="summary-line total-line">
                    <span>Your total</span>
                    <b>RM<?php echo number_format($final_total, 2); ?></b>
                </div>

                <?php if ($has_items) { ?>
                    <a class="checkout-btn" href="checkout.php">→ Proceed to checkout</a>
                    <a href="menu.php" class="continue-shopping-btn">← Continue Shopping</a>
                    <a class="clear-cart-link" href="cart.php?action=clear">Clear Cart</a>
                <?php } else { ?>
                    <a class="checkout-btn" href="menu.php">Add items first</a>
                <?php } ?>

                <p class="arrival-time">

                ⏱ Estimated delivery:
                <b>15–20 mins</b>

                </p>
                
                <p class="cart-note">Order will be saved after checkout.</p>
            </div>

        </div>

    </div>

</div>

<script>
function filterCartBudget(maxPrice) {
    let items = document.querySelectorAll(".cart-item-row");

    items.forEach(function(item) {
        let price = parseFloat(item.getAttribute("data-price"));

        if (price <= maxPrice) {
            item.style.display = "grid";
        } else {
            item.style.display = "none";
        }
    });
}

function showAllCartItems() {
    let items = document.querySelectorAll(".cart-item-row");

    items.forEach(function(item) {
        item.style.display = "grid";
    });
}
</script>

<?php include("../includes/footer.php"); ?>
