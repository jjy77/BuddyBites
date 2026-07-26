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
$mode = $_GET["mode"] ?? ($_POST["mode"] ?? "individual");

$message = "";
$grand_total = 0;
$cart_items = [];
$total_items = 0;
$delivery_fee = 1.00;
$platform_fee = 0.50;
$final_total = 0;

$group_id = $_SESSION["group_id"] ?? null;
$is_group_checkout = ($mode === "group");
$is_host = false;
$host_user_id = null;
$group_code = "";
$members = [];
$member_count = 0;

if ($is_group_checkout) {
    if (empty($group_id)) {
        header("Location: group_order.php");
        exit();
    }

    $hostSql = "SELECT host_user_id, group_code FROM group_orders WHERE group_id = ?";
    $hostStmt = $conn->prepare($hostSql);
    $hostStmt->bind_param("i", $group_id);
    $hostStmt->execute();
    $hostResult = $hostStmt->get_result();

    if ($hostResult && $hostResult->num_rows == 1) {
        $hostRow = $hostResult->fetch_assoc();
        $host_user_id = $hostRow["host_user_id"];
        $group_code = $hostRow["group_code"];
        $is_host = ($host_user_id == $user_id);
    }

    if (!$is_host) {
        header("Location: group_order.php");
        exit();
    }

    $memberSql = "SELECT users.user_id, users.full_name
                  FROM group_members
                  JOIN users ON group_members.user_id = users.user_id
                  WHERE group_members.group_id = ?";
    $memberStmt = $conn->prepare($memberSql);
    $memberStmt->bind_param("i", $group_id);
    $memberStmt->execute();
    $memberResult = $memberStmt->get_result();

    while ($member = $memberResult->fetch_assoc()) {
        $members[] = $member;
    }

    $member_count = count($members);

    $itemSql = "SELECT group_cart_items.*, users.full_name
                FROM group_cart_items
                JOIN users ON group_cart_items.user_id = users.user_id
                WHERE group_cart_items.group_id = ?
                ORDER BY users.full_name ASC, group_cart_items.created_at ASC";

    $itemStmt = $conn->prepare($itemSql);
    $itemStmt->bind_param("i", $group_id);
    $itemStmt->execute();
    $itemResult = $itemStmt->get_result();

    while ($item = $itemResult->fetch_assoc()) {
        $subtotal = floatval($item["price"]) * intval($item["quantity"]);
        $grand_total += $subtotal;
        $total_items += intval($item["quantity"]);

        $cart_items[] = [
            "name" => $item["item_name"],
            "restaurant_name" => $item["restaurant_name"],
            "qty" => intval($item["quantity"]),
            "price" => $item["price"],
            "subtotal" => $subtotal
        ];
    }

    if (empty($cart_items)) {
        header("Location: group_order.php");
        exit();
    }

    $delivery_fee = 4.00;
    $platform_fee = 0.50 * max($member_count, 1);
    $final_total = $grand_total + $delivery_fee + $platform_fee;

} else {
    if (empty($_SESSION["cart"]) && empty($_SESSION["custom_cart"])) {
        header("Location: cart.php");
        exit();
    }

    foreach ($_SESSION["cart"] as $item_id => $quantity) {
        $sql = "SELECT * FROM menu_items WHERE item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $item = $result->fetch_assoc();
            $subtotal = $item["price"] * $quantity;
            $grand_total += $subtotal;
            $total_items += $quantity;

            $cart_items[] = [
                "name" => $item["item_name"],
                "restaurant_name" => $item["restaurant_name"],
                "qty" => $quantity,
                "price" => $item["price"],
                "subtotal" => $subtotal
            ];
        }
    }

    foreach ($_SESSION["custom_cart"] as $customMeal) {
        $subtotal = floatval($customMeal["total_price"]);
        $grand_total += $subtotal;
        $total_items++;

        $cart_items[] = [
            "name" => "Build Your Own Meal",
            "restaurant_name" => "Custom Meal",
            "qty" => 1,
            "price" => $subtotal,
            "subtotal" => $subtotal,
            "details" => "Base: " . $customMeal["base"] .
                         " | Protein: " . $customMeal["protein"] .
                         " | Toppings: " . $customMeal["topping"] .
                         " | Sauce: " . $customMeal["sauce"]
        ];
    }

    $delivery_fee = 1.00;
    $platform_fee = 0.50;
    $final_total = $grand_total + $delivery_fee + $platform_fee;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $address = trim($_POST["address"] ?? "");
    $block = trim($_POST["block"] ?? "");
    $room = trim($_POST["room"] ?? "");
    $delivery_note = trim($_POST["delivery_note"] ?? "");
    $delivery_slot = trim($_POST["delivery_slot"] ?? "");
    $payment_method = trim($_POST["payment_method"] ?? "");

    if (empty($address) || empty($block) || empty($room) || empty($delivery_slot) || empty($payment_method)) {
        $message = "Please complete address, delivery slot and payment method.";
    } else {

        $status = "Preparing";

        $sql = "INSERT INTO orders (user_id, status, total_amount)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isd", $user_id, $status, $final_total);

     if ($stmt->execute()) {
    $_SESSION["last_order_id"] = $stmt->insert_id;
    $order_id = $stmt->insert_id;

    $itemInsertSql = "INSERT INTO order_items 
        (order_id, item_name, restaurant_name, quantity, price)
        VALUES (?, ?, ?, ?, ?)";

    $itemInsertStmt = $conn->prepare($itemInsertSql);

    foreach ($cart_items as $cart) {
        $item_name = $cart["name"];
        $restaurant_name = $cart["restaurant_name"] ?? "Custom Meal";
        $quantity = $cart["qty"];
        $price = $cart["price"];

        $itemInsertStmt->bind_param(
            "issid",
            $order_id,
            $item_name,
            $restaurant_name,
            $quantity,
            $price
        );

        if (!$itemInsertStmt->execute()) {
            die($itemInsertStmt->error);
        }
    }
            if ($is_group_checkout && !empty($group_id)) {
                $deleteSql = "DELETE FROM group_cart_items WHERE group_id = ?";
                $deleteStmt = $conn->prepare($deleteSql);
                $deleteStmt->bind_param("i", $group_id);
                $deleteStmt->execute();

                unset($_SESSION["group_id"]);
                unset($_SESSION["group_code"]);
            } else if (!empty($group_id)) {
                $deleteSql = "DELETE FROM group_cart_items
                              WHERE group_id = ? AND user_id = ?";

                $deleteStmt = $conn->prepare($deleteSql);
                $deleteStmt->bind_param("ii", $group_id, $user_id);
                $deleteStmt->execute();
            }

            header("Location: payment.php");
            exit();
        } else {
            $message = "Checkout failed. Please try again.";
        }
    }
}
?>

<?php include("../includes/header.php"); ?>

<div class="checkout-page">

    <div class="checkout-nav">
        <div class="logo">BUDDY BITES</div>

        <div>
            <a href="home.php"><i class="fa-solid fa-house"></i> Home</a>
            <a href="menu.php"><i class="fa-solid fa-utensils"></i> Menu</a>
            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Cart</a>
            <a href="group_order.php"><i class="fa-solid fa-users"></i> Group Order</a>
            <a class="active" href="checkout.php"><i class="fa-solid fa-credit-card"></i> Checkout</a>
            <a href="order_history.php"><i class="fa-solid fa-clipboard-list"></i> My Orders</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="checkout-header">
        <h3><i class="fa-solid fa-cart-shopping"></i> Checkout</h3>

        <?php if ($is_group_checkout) { ?>
            <p>BuddyBites group order checkout | Group Code: <?php echo htmlspecialchars($group_code); ?></p>
        <?php } else { ?>
            <p>BuddyBites individual student meal order</p>
        <?php } ?>
    </div>

    <div class="checkout-progress">
        <span><i class="fa-solid fa-check"></i> Cart</span>
        <span class="active-step">2 Delivery</span>
        <span>3 Payment</span>
        <span>4 Confirm</span>
    </div>

    <?php if (!empty($message)) { ?>
        <p class="checkout-message"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <form method="POST" action="checkout.php?mode=<?php echo htmlspecialchars($mode); ?>" onsubmit="return validateCheckoutForm()">

        <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>">

        <div class="checkout-layout">

            <div class="checkout-left">

                <div class="checkout-card">
                    <h3><span class="checkout-title-icon"><i class="fa-solid fa-location-dot"></i></span> Delivery address</h3>

                    <label>Full address</label>
                    <input type="text" name="address" id="address" placeholder="MMU Hostel">

                    <div class="checkout-two">
                        <div>
                            <label>Block / Building</label>
                            <input type="text" name="block" id="block" placeholder="Block HB3">
                        </div>

                        <div>
                            <label>Room number</label>
                            <input type="text" name="room" id="room" placeholder="Ground Floor">
                        </div>
                    </div>

                    <label>Delivery note (optional)</label>
                    <input type="text" name="delivery_note" id="delivery_note" placeholder="Cyberjaya, Selangor">
                </div>

                <div class="checkout-card">
                    <h3><span class="checkout-title-icon"><i class="fa-solid fa-clock"></i></span> Delivery slot</h3>

                    <label class="checkout-radio">
                        <input type="radio" name="delivery_slot" value="12PM Lunch" checked>
                        <div>
                            <b>12:00 PM - Lunch</b>
                            <p>Cutoff: 11:30 AM</p>
                        </div>
                        <span>Open</span>
                    </label>

                    <label class="checkout-radio">
                        <input type="radio" name="delivery_slot" value="6PM Dinner">
                        <div>
                            <b>6:00 PM - Dinner</b>
                            <p>Cutoff: 5:30 PM</p>
                        </div>
                        <span>Open</span>
                    </label>

                    <?php if ($is_group_checkout) { ?>
                        <p class="checkout-note">This checkout is for the full group order. Only host can place it.</p>
                    <?php } else { ?>
                        <p class="checkout-note">This checkout is for your individual order only.</p>
                    <?php } ?>
                </div>

                <div class="checkout-card">
                    <h3><span class="checkout-title-icon"><i class="fa-solid fa-credit-card"></i></span> Payment method</h3>

                    <label class="checkout-radio">
                        <input type="radio" name="payment_method" value="Touch n Go eWallet" checked>
                        <div>
                            <b><i class="fa-solid fa-wallet payment-method-icon"></i> Touch 'n Go eWallet</b>
                        </div>
                        <span>Recommended</span>
                    </label>

                    <label class="checkout-radio">
                        <input type="radio" name="payment_method" value="Online Banking FPX">
                        <div>
                            <b><i class="fa-solid fa-building-columns payment-method-icon"></i> Online Banking (FPX)</b>
                        </div>
                    </label>

                    <label class="checkout-radio">
                        <input type="radio" name="payment_method" value="Cash on Delivery">
                        <div>
                            <b><i class="fa-solid fa-money-bill-wave payment-method-icon"></i> Cash on Delivery</b>
                        </div>
                    </label>
                </div>

            </div>

            <div class="checkout-right">

                <div class="checkout-side-card">
                    <h3><span class="checkout-title-icon"><i class="fa-solid fa-receipt"></i></span> Order summary</h3>

                    <p class="checkout-user">
                        <?php if ($is_group_checkout) { ?>
                            Group Order
                        <?php } else { ?>
                            <?php echo htmlspecialchars($_SESSION["full_name"] ?? "Student"); ?>
                        <?php } ?>
                    </p>

                    <?php foreach ($cart_items as $cart) { ?>
                        <div class="checkout-summary-item">
                            <span>
                                <?php if (!empty($cart["owner"])) { ?>
                                    <b><?php echo htmlspecialchars($cart["owner"]); ?></b><br>
                                <?php } ?>

                                <?php echo htmlspecialchars($cart["name"]); ?>

                                <?php if ($cart["qty"] > 1) { ?>
                                    x<?php echo $cart["qty"]; ?>
                                <?php } ?>

                                <?php if (!empty($cart["details"])) { ?>
                                    <br>
                                    <small><?php echo htmlspecialchars($cart["details"]); ?></small>
                                <?php } ?>
                            </span>

                            <b>RM<?php echo number_format($cart["subtotal"], 2); ?></b>
                        </div>
                    <?php } ?>

                    <hr>

                    <div class="checkout-summary-line">
                        <span>Subtotal</span>
                        <b>RM<?php echo number_format($grand_total, 2); ?></b>
                    </div>

                    <div class="checkout-summary-line">
                        <span>Delivery fee</span>
                        <b>RM<?php echo number_format($delivery_fee, 2); ?></b>
                    </div>

                    <div class="checkout-summary-line">
                        <span>Platform fee</span>
                        <b>RM<?php echo number_format($platform_fee, 2); ?></b>
                    </div>

                    <div class="promo-line">
                        <input type="text" placeholder="Promo code">
                        <button type="button">Apply</button>
                    </div>

                    <p style="font-size:12px;color:#888;margin-top:8px;">
                        Try: <strong>WELCOME10</strong>
                    </p>

                    <hr>

                    <div class="checkout-summary-line checkout-total">
                        <span>Your total</span>
                        <b>RM<?php echo number_format($final_total, 2); ?></b>
                    </div>
                </div>

                <div class="checkout-side-card">
                    <?php if ($is_group_checkout) { ?>
                        <h3><span class="checkout-title-icon"><i class="fa-solid fa-users"></i></span> Group checkout</h3>

                        <div class="checkout-summary-line">
                            <span>Members</span>
                            <b><?php echo $member_count; ?></b>
                        </div>

                        <div class="checkout-summary-line">
                            <span>Total items</span>
                            <b><?php echo $total_items; ?></b>
                        </div>

                        <div class="checkout-summary-line">
                            <span>Host payment</span>
                            <b>RM<?php echo number_format($final_total, 2); ?></b>
                        </div>
                    <?php } else { ?>
                        <h3><span class="checkout-title-icon"><i class="fa-solid fa-user"></i></span> Individual checkout</h3>

                        <div class="checkout-summary-line">
                            <span><?php echo htmlspecialchars($_SESSION["full_name"] ?? "Student"); ?></span>
                            <b>RM<?php echo number_format($final_total, 2); ?></b>
                        </div>

                        <div class="checkout-summary-line">
                            <span>Total items</span>
                            <b><?php echo $total_items; ?></b>
                        </div>
                    <?php } ?>
                </div>

                <button class="place-order-btn" type="submit">
                    <i class="fa-solid fa-check"></i> Place Order
                </button>

                <p class="secure-note">
                    <i class="fa-solid fa-shield-halved"></i> Secure Checkout<br>
                    Your order will be encrypted and processed safely.
                </p>

            </div>

        </div>

    </form>

</div>

<script>
function validateCheckoutForm() {
    let address = document.getElementById("address").value.trim();
    let block = document.getElementById("block").value.trim();
    let room = document.getElementById("room").value.trim();

    if (address === "" || block === "" || room === "") {
        alert("Please complete the delivery address.");
        return false;
    }

    return true;
}
</script>

<?php include("../includes/footer.php"); ?>

