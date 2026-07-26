<?php
date_default_timezone_set("Asia/Kuala_Lumpur");

include("../includes/auth_check.php");
requireRole("student");

include("../includes/db.php");

if (!isset($_GET["order_id"])) {
    header("Location: order_history.php");
    exit();
}

$order_id = (int) $_GET["order_id"];

$receipt_no = "BB" . str_pad($order_id, 5, "0", STR_PAD_LEFT);

$payment_method = $_SESSION["payment_method"] ?? $_POST["payment_demo"] ?? "Touch 'n Go eWallet";
$bank = $_SESSION["bank"] ?? "-";

$orderSql = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$orderStmt = $conn->prepare($orderSql);
$orderStmt->bind_param("ii", $order_id, $_SESSION["user_id"]);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

if ($orderResult->num_rows == 0) {
    die("Order not found.");
}

$order = $orderResult->fetch_assoc();
$total = (float) $order["total_amount"];

$itemSql = "SELECT GROUP_CONCAT(DISTINCT restaurant_name SEPARATOR ', ') AS restaurants
            FROM order_items
            WHERE order_id = ?";
$itemStmt = $conn->prepare($itemSql);
$itemStmt->bind_param("i", $order_id);
$itemStmt->execute();
$itemData = $itemStmt->get_result()->fetch_assoc();
$restaurant_names = $itemData["restaurants"] ?? "-";

$payment_time = date("d M Y, h:i A", strtotime($order["order_date"]));

function h($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, "UTF-8");
}
?>

<?php include("../includes/header.php"); ?>

<div class="receipt-page">

    <div class="receipt-card">

        <div class="receipt-success">✓</div>

        <h1>Payment Successful</h1>
        <p class="receipt-subtitle">Thank you for ordering with BuddyBites.</p>

        <div class="receipt-info">
            <div>
                <span>Receipt No</span>
                <b><?php echo h($receipt_no); ?></b>
            </div>

            <div>
                <span>Order ID</span>
                <b>#<?php echo h($order_id); ?></b>
            </div>

            <div>
                <span>Restaurant</span>
                <b><?php echo h($restaurant_names); ?></b>
            </div>

            <div>
                <span>Date & Time</span>
                <b><?php echo h($payment_time); ?></b>
            </div>

            <div>
                <span>Payment Method</span>
                <b><?php echo h($payment_method); ?></b>
            </div>

            <?php if ($payment_method === "Online Banking FPX") { ?>
                <div>
                    <span>Bank</span>
                    <b><?php echo h($bank); ?></b>
                </div>
            <?php } ?>

            <div>
                <span>Estimated Delivery</span>
                <b>15–20 mins</b>
            </div>

            <div>
                <span>Order Status</span>
                <b>Preparing</b>
            </div>
        </div>

        <div class="receipt-total">
            <span>Total Paid</span>
            <b>RM<?php echo number_format($total, 2); ?></b>
        </div>

        <div class="receipt-actions">
            <a href="order_tracking.php?order_id=<?php echo $order_id; ?>">Track Order</a>
            <a href="home.php" class="secondary">Back Home</a>
        </div>

    </div>

</div>

<?php include("../includes/footer.php"); ?>
