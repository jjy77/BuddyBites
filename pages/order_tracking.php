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

if (!isset($_GET["order_id"])) {
    header("Location: order_history.php");
    exit();
}

function h($text) {
    return htmlspecialchars((string) $text, ENT_QUOTES, "UTF-8");
}

$order_id = (int) $_GET["order_id"];

$sql = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found.");
}

$order = $result->fetch_assoc();
$status = $order["status"] ?? "Preparing";

$steps = [
    "Order Placed",
    "Payment Confirmed",
    "Restaurant Preparing",
    "Rider Assigned",
    "Out for Delivery",
    "Delivered"
];

$currentStep = 2;

if ($status === "Rider Assigned") {
    $currentStep = 3;
} else if ($status === "Out for Delivery") {
    $currentStep = 4;
} else if ($status === "Delivered") {
    $currentStep = 5;
}
?>

<?php include("../includes/header.php"); ?>

<div class="tracking-page">
    <div class="tracking-card">
        <h1>Track Your Order</h1>
        <p>Order ID: <b>#<?php echo h($order_id); ?></b></p>

        <div class="tracking-status">
            <?php foreach ($steps as $index => $step) { ?>
                <?php $isDone = $index <= $currentStep; ?>

                <div class="tracking-step <?php echo $isDone ? "done" : ""; ?>">
                    <div class="tracking-icon">
                        <?php echo $isDone ? "&#10003;" : $index + 1; ?>
                    </div>

                    <div>
                        <h3><?php echo h($step); ?></h3>

                        <?php if ($index === $currentStep) { ?>
                            <p>Current status</p>
                        <?php } else if ($index < $currentStep) { ?>
                            <p>Completed</p>
                        <?php } else { ?>
                            <p>Waiting</p>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="rider-box">
            <h3>Rider Information</h3>
            <p><b>Status:</b> Rider will be assigned after restaurant confirms the order.</p>
            <p><b>Estimated Delivery:</b> 15-20 mins</p>
        </div>

        <div class="receipt-actions">
            <a href="order_history.php">View My Orders</a>
            <a href="home.php" class="secondary">Back Home</a>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
