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

$message = "";
$current_code = "";
$current_group_id = "";
$is_host = false;
$host_user_id = null;

/* CREATE GROUP */
if (isset($_POST["create_group"])) {
    $group_code = strval(rand(100000, 999999));

    $sql = "INSERT INTO group_orders (host_user_id, group_code) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $group_code);

    if ($stmt->execute()) {
        $group_id = $stmt->insert_id;

        $sql2 = "INSERT INTO group_members (group_id, user_id) VALUES (?, ?)";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("ii", $group_id, $user_id);
        $stmt2->execute();

        $_SESSION["group_code"] = $group_code;
        $_SESSION["group_id"] = $group_id;

        $current_code = $group_code;
        $current_group_id = $group_id;
        $message = "Group created successfully!";
    } else {
        $message = "Failed to create group.";
    }
}

/* JOIN GROUP */
if (isset($_POST["join_group"])) {
    $join_code = trim($_POST["join_code"]);

    if (empty($join_code)) {
        $message = "Please enter group code.";
    } else {
        $sql = "SELECT * FROM group_orders WHERE group_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $join_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $group = $result->fetch_assoc();
            $group_id = $group["group_id"];

            $check = "SELECT * FROM group_members WHERE group_id = ? AND user_id = ?";
            $checkStmt = $conn->prepare($check);
            $checkStmt->bind_param("ii", $group_id, $user_id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows == 0) {
                $sql2 = "INSERT INTO group_members (group_id, user_id) VALUES (?, ?)";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("ii", $group_id, $user_id);
                $stmt2->execute();
            }

            $_SESSION["group_code"] = $join_code;
            $_SESSION["group_id"] = $group_id;

            $current_code = $join_code;
            $current_group_id = $group_id;
            $message = "Joined group successfully!";
        } else {
            $message = "Invalid group code.";
        }
    }
}

/* KEEP CURRENT GROUP */
if (isset($_SESSION["group_code"])) {
    $current_code = $_SESSION["group_code"];
}

if (isset($_SESSION["group_id"])) {
    $current_group_id = $_SESSION["group_id"];
}

/* GET GROUP HOST */
if (!empty($current_group_id)) {
    $hostSql = "SELECT host_user_id FROM group_orders WHERE group_id = ?";
    $hostStmt = $conn->prepare($hostSql);
    $hostStmt->bind_param("i", $current_group_id);
    $hostStmt->execute();
    $hostResult = $hostStmt->get_result();

    if ($hostResult && $hostResult->num_rows == 1) {
        $hostRow = $hostResult->fetch_assoc();
        $host_user_id = $hostRow["host_user_id"];
        $is_host = ($host_user_id == $user_id);
    }
}

/* FETCH MEMBERS */
$members = [];

if (!empty($current_group_id)) {
    $sql = "SELECT users.full_name, users.user_id, group_orders.host_user_id
            FROM group_members
            JOIN users ON group_members.user_id = users.user_id
            JOIN group_orders ON group_members.group_id = group_orders.group_id
            WHERE group_members.group_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $current_group_id);
    $stmt->execute();
    $membersResult = $stmt->get_result();

    while ($row = $membersResult->fetch_assoc()) {
        $members[] = $row;
    }
}

/* FETCH ALL GROUP CART ITEMS FROM DATABASE */
$group_items = [];
$group_subtotal = 0;
$user_totals = [];

if (!empty($current_group_id)) {
    $itemSql = "SELECT group_cart_items.*, users.full_name
                FROM group_cart_items
                JOIN users ON group_cart_items.user_id = users.user_id
                WHERE group_cart_items.group_id = ?
                ORDER BY users.full_name ASC, group_cart_items.created_at ASC";

    $itemStmt = $conn->prepare($itemSql);
    $itemStmt->bind_param("i", $current_group_id);
    $itemStmt->execute();
    $itemResult = $itemStmt->get_result();

    while ($item = $itemResult->fetch_assoc()) {
        $line_total = floatval($item["price"]) * intval($item["quantity"]);
        $item["line_total"] = $line_total;

        $group_items[] = $item;
        $group_subtotal += $line_total;

        $item_user_id = $item["user_id"];

        if (!isset($user_totals[$item_user_id])) {
            $user_totals[$item_user_id] = [
                "name" => $item["full_name"],
                "total" => 0,
                "items" => 0
            ];
        }

        $user_totals[$item_user_id]["total"] += $line_total;
        $user_totals[$item_user_id]["items"] += intval($item["quantity"]);
    }
}

$member_count = count($members);
$shared_fee = $member_count > 0 ? 4 / $member_count : 0;
$platform_fee = 0.50;
$group_final_total = $group_subtotal > 0 ? $group_subtotal + 4 + ($platform_fee * max($member_count, 1)) : 0;

$cart_nav_count = array_sum($_SESSION["cart"]) + count($_SESSION["custom_cart"]);
?>

<?php include("../includes/header.php"); ?>

<div class="group-room-page">

    <div class="group-nav">
        <div class="logo">BUDDY BITES</div>

        <div>
            <a href="home.php"><i class="fa-solid fa-house"></i> Home</a>
            <a href="menu.php"><i class="fa-solid fa-utensils"></i> Menu</a>
            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Cart (<?php echo $cart_nav_count; ?>)</a>
            <a class="active" href="group_order.php"><i class="fa-solid fa-users"></i> Group Order</a>
            <a href="order_history.php"><i class="fa-solid fa-clipboard-list"></i> My Orders</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="group-header">
        <h3>👥 Group order room</h3>
        <p>BuddyBites shared student meal order | Batch delivery 12:00PM</p>
    </div>

    <div class="group-actions">
        <form method="POST" action="group_order.php">
            <button class="group-small-btn" type="submit" name="create_group">
                + Create new group
            </button>
        </form>
    </div>

    <div class="join-box">
        <form method="POST" action="group_order.php" onsubmit="return validateGroupCode()">
            <label>Join an existing group with a 6-digit code:</label>

            <div class="join-line">
                <input type="text" name="join_code" id="join_code" placeholder="e.g. 198119">
                <button type="submit" name="join_group">→ Join</button>
            </div>
        </form>

        <?php if (!empty($message)) { ?>
            <p class="group-message"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>
    </div>

    <div class="group-main-layout">

        <div class="group-left-card">

            <h3>👥 Group code</h3>

            <?php if (!empty($current_code)) { ?>
                <div class="code-box">
                    <p>Share this code with friends</p>
                    <h1><?php echo htmlspecialchars($current_code); ?></h1>
                    <button type="button" onclick="copyGroupCode()">📋 Copy code</button>
                </div>
            <?php } else { ?>
                <div class="code-box">
                    <p>Create or join a group first</p>
                    <h1>------</h1>
                </div>
            <?php } ?>

            <h3>👥 Members (<?php echo count($members); ?>/5)</h3>

            <div class="member-list">
                <?php if (!empty($members)) { ?>
                    <?php foreach ($members as $member) { 
                        $memberTotal = $user_totals[$member["user_id"]]["total"] ?? 0;
                    ?>
                        <div class="member-row">
                            <div class="member-avatar">👤</div>

                            <div>
                                <b><?php echo htmlspecialchars($member["full_name"]); ?></b>

                                <?php if ($member["user_id"] == $member["host_user_id"]) { ?>
                                    <span class="host-tag">Host</span>
                                <?php } ?>

                                <p>
                                    <?php
                                    if ($memberTotal > 0) {
                                        echo "Order total | RM" . number_format($memberTotal, 2);
                                    } else {
                                        echo "Joined group";
                                    }
                                    ?>
                                </p>
                            </div>

                            <span class="ready-tag">
                                <?php echo $memberTotal > 0 ? "added" : "ready"; ?>
                            </span>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p class="group-empty">No members yet.</p>
                <?php } ?>
            </div>

        </div>

        <div class="group-right-card">

            <h3>🧾 Combined order summary</h3>

            <?php if (!empty($group_items)) { ?>

                <?php
                $currentName = "";
                foreach ($group_items as $item) {
                    if ($currentName != $item["full_name"]) {
                        $currentName = $item["full_name"];
                ?>
                        <p class="summary-user">
                            <?php echo htmlspecialchars($currentName); ?>
                        </p>
                <?php } ?>

                    <div class="summary-item">
                        <div>
                            <b><?php echo htmlspecialchars($item["item_name"]); ?></b>
                            <p>
                                <?php echo htmlspecialchars($item["restaurant_name"]); ?> | Qty: <?php echo intval($item["quantity"]); ?>

                                <?php if (!empty($item["details"])) { ?>
                                    <br><small><?php echo htmlspecialchars($item["details"]); ?></small>
                                <?php } ?>
                            </p>
                        </div>

                        <span>RM<?php echo number_format($item["line_total"], 2); ?></span>
                    </div>

                <?php } ?>

            <?php } else { ?>
                <div class="summary-item">
                    <div>
                        <b>No group items yet</b>
                        <p>Members should add menu or custom meal first.</p>
                    </div>

                    <span>-</span>
                </div>
            <?php } ?>

            <hr>

            <div class="summary-total">
                <span>Group subtotal</span>
                <b>RM<?php echo number_format($group_subtotal, 2); ?></b>
            </div>

            <div class="summary-total">
                <span>Delivery fee total</span>
                <b>RM<?php echo $group_subtotal > 0 ? "4.00" : "0.00"; ?></b>
            </div>

            <div class="summary-total">
                <span>Shared delivery per member</span>
                <b>RM<?php echo $group_subtotal > 0 ? number_format($shared_fee, 2) : "0.00"; ?></b>
            </div>

            <div class="summary-total">
                <span>Platform fee per member</span>
                <b>RM<?php echo $group_subtotal > 0 ? number_format($platform_fee, 2) : "0.00"; ?></b>
            </div>

            <div class="summary-total final">
                <span>Group total</span>
                <b>RM<?php echo number_format($group_final_total, 2); ?></b>
            </div>

            <?php if (!empty($current_code) && !empty($group_items) && $is_host) { ?>
                <a class="group-checkout-btn" href="checkout.php?mode=group">Host checkout group order</a>
            <?php } elseif (!empty($current_code) && !empty($group_items) && !$is_host) { ?>
                <button class="group-checkout-btn disabled" type="button">
                    Waiting for host to checkout
                </button>
            <?php } else { ?>
                <button class="group-checkout-btn disabled" type="button">
                    Waiting for group/cart items...
                </button>
            <?php } ?>

            <p class="host-note">
                <?php if ($is_host && !empty($current_code)) { ?>
                    You are the host. Only you can finalize this group order.
                <?php } else { ?>
                    Only the group host can finalize the group order.
                <?php } ?>
            </p>

        </div>

    </div>

</div>

<script>
function validateGroupCode() {
    let code = document.getElementById("join_code").value.trim();

    if (code === "") {
        alert("Please enter group code.");
        return false;
    }

    if (code.length !== 6) {
        alert("Group code must be 6 digits.");
        return false;
    }

    return true;
}

function copyGroupCode() {
    let code = "<?php echo htmlspecialchars($current_code); ?>";

    if (code !== "") {
        navigator.clipboard.writeText(code);
        alert("Group code copied: " + code);
    }
}
</script>

<?php include("../includes/footer.php"); ?>
