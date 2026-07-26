<?php
include("../includes/auth_check.php");
requireRole("student");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION["last_order_id"])) {
    header("Location: cart.php");
    exit();
}

$order_id = $_SESSION["last_order_id"];

/* Short unique receipt number */
$receipt_no = "BB" . str_pad($order_id, 5, "0", STR_PAD_LEFT);

$_SESSION["receipt_no"] = $receipt_no;
$_SESSION["payment_time"] = date("d M Y, h:i A");
$_SESSION["payment_method"] = $_POST["payment_demo"] ?? $_SESSION["payment_method"] ?? "Touch 'n Go eWallet";
$_SESSION["bank"] = $_POST["bank"] ?? $_SESSION["bank"] ?? "-";

$_SESSION["cart"] = [];
$_SESSION["custom_cart"] = [];

header("Location: receipt.php?order_id=" . $order_id);
exit();
?>
