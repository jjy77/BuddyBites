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

if (!isset($_SESSION["group_id"])) {
    header("Location: group_order.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$group_id = $_SESSION["group_id"];
$item_id = intval($_GET["id"]);

$sql = "SELECT * FROM menu_items WHERE item_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$item_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows==1){

    $item = $result->fetch_assoc();

    $name = $item["item_name"];
    $restaurant = $item["restaurant_name"];
    $price = $item["price"];

    $check = "SELECT * FROM group_cart_items
              WHERE group_id=? AND user_id=? AND item_name=?";

    $stmt2 = $conn->prepare($check);
    $stmt2->bind_param("iis",$group_id,$user_id,$name);
    $stmt2->execute();
    $checkResult = $stmt2->get_result();

    if($checkResult->num_rows>0){

        $row = $checkResult->fetch_assoc();
        $qty = $row["quantity"] + 1;

        $update = "UPDATE group_cart_items
                   SET quantity=?
                   WHERE group_cart_id=?";

        $stmt3 = $conn->prepare($update);
        $stmt3->bind_param("ii",$qty,$row["group_cart_id"]);
        $stmt3->execute();

    }else{

        $insert = "INSERT INTO group_cart_items
        (group_id,user_id,item_name,restaurant_name,price,quantity,item_type)
        VALUES (?,?,?,?,?,1,'normal')";

        $stmt4 = $conn->prepare($insert);
        $stmt4->bind_param(
            "iissd",
            $group_id,
            $user_id,
            $name,
            $restaurant,
            $price
        );

        $stmt4->execute();
    }

}

header("Location: group_order.php");
exit();
?>