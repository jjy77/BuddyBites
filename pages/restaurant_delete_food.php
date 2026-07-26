<?php
include("../includes/auth_check.php");
requireRole("restaurant");
include("../includes/db.php");

$id = (int)$_GET["id"];
$restaurant = $_SESSION["full_name"];

$sql = "DELETE FROM menu_items
        WHERE item_id = ?
        AND restaurant_name = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id, $restaurant);
$stmt->execute();

header("Location: restaurant_menu.php");
exit();