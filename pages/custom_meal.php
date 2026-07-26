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

$message = "";

if (!isset($_SESSION["custom_cart"])) {
    $_SESSION["custom_cart"] = [];
}

$user_id = $_SESSION["user_id"];
$group_id = $_SESSION["group_id"] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $base = $_POST["base"] ?? "";
    $protein = $_POST["protein"] ?? [];
    $topping = $_POST["topping"] ?? [];
    $sauce = $_POST["sauce"] ?? [];
    $total_price = floatval($_POST["total_price"]);

    if (empty($base) || empty($protein) || empty($topping) || empty($sauce)) {
        $message = "Please complete all meal selections.";
    } else {

        $protein_text = implode(", ", $protein);
        $topping_text = implode(", ", $topping);
        $sauce_text = implode(", ", $sauce);

        $details = "Base: " . $base .
                   " | Protein: " . $protein_text .
                   " | Toppings: " . $topping_text .
                   " | Sauce: " . $sauce_text;

        $sql = "INSERT INTO custom_meals (user_id, base, protein, topping, sauce, total_price)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssd", $user_id, $base, $protein_text, $topping_text, $sauce_text, $total_price);

        if ($stmt->execute()) {

            $_SESSION["custom_cart"][] = [
                "base" => $base,
                "protein" => $protein_text,
                "topping" => $topping_text,
                "sauce" => $sauce_text,
                "total_price" => $total_price
            ];

            if (!empty($group_id)) {
                $item_name = "Build Your Own Meal";
                $restaurant_name = "Custom Meal Builder";
                $quantity = 1;
                $item_type = "custom";

                $insertGroupSql = "INSERT INTO group_cart_items
                                   (group_id, user_id, item_name, restaurant_name, price, quantity, item_type, details)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                $insertGroupStmt = $conn->prepare($insertGroupSql);
                $insertGroupStmt->bind_param(
                    "iissdiss",
                    $group_id,
                    $user_id,
                    $item_name,
                    $restaurant_name,
                    $total_price,
                    $quantity,
                    $item_type,
                    $details
                );
                $insertGroupStmt->execute();
            }

            header("Location: cart.php");
            exit();

        } else {
            $message = "Failed to save custom meal.";
        }
    }
}
?>

<?php include("../includes/header.php"); ?>

<div class="custom-builder-page">

    <div class="custom-nav">
        <div class="logo">BUDDY BITES</div>

        <div>
            <a href="home.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="cart.php">Cart</a>
            <a href="group_order.php">Group Order</a>
            <a class="active" href="custom_meal.php">Custom Meal</a>
            <a href="custom_history.php">History</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="custom-line">✦━━━━━━✦</div>

    <h3 class="custom-title">Custom Meal Builder</h3>

    <?php if (!empty($group_id)) { ?>
        <p class="custom-message">Group order is active. This custom meal will be added to your group order.</p>
    <?php } ?>

    <?php if (!empty($message)) { ?>
        <p class="custom-message"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <form method="POST" action="custom_meal.php" onsubmit="return validateCustomMeal()">

        <div class="custom-layout">

            <div class="builder-card">

                <h3>🍴 Build your own meal</h3>

                <div class="step-tabs">
                    <button type="button" class="tab active" id="tab-base" onclick="showStep('base')">1.Base</button>
                    <button type="button" class="tab" id="tab-protein" onclick="showStep('protein')">2.Protein</button>
                    <button type="button" class="tab" id="tab-topping" onclick="showStep('topping')">3.Toppings</button>
                    <button type="button" class="tab" id="tab-sauce" onclick="showStep('sauce')">4.Sauce</button>
                </div>

                <div class="step-section" id="step-base">
                    <p class="step-label">Choose your base:</p>

                    <div class="option-grid">
                        <label class="meal-option">
                            <input type="radio" name="base" value="White Rice" data-price="1" onchange="calculateMealPrice()">
                            <img class="meal-pic" src="../images/White-Rice.png">
                            <span>White Rice<br><b>+RM1.00</b></span>
                        </label>

                        <label class="meal-option">
                            <input type="radio" name="base" value="Noodles" data-price="3.5" onchange="calculateMealPrice()">
                            <img class="meal-pic" src="../images/mee-goreng.jpg">
                            <span>Noodles<br><b>+RM3.50</b></span>
                        </label>
                    </div>

                    <div class="builder-buttons">
                        <button type="button" class="outline-btn" disabled>← Back</button>
                        <button type="button" class="pink-btn" onclick="showStep('protein')">Next: Protein →</button>
                    </div>
                </div>

                <div class="step-section hidden" id="step-protein">
                    <p class="step-label">Choose your protein:</p>

                    <div class="option-grid">
                        <label class="meal-option">
                            <input type="checkbox" name="protein[]" value="Fried Chicken" data-price="2" onchange="calculateMealPrice()">
                            <img class="meal-pic" src="../images/Fried-Chicken.jpg">
                            <span>Fried Chicken<br><b>+RM2.00</b></span>
                        </label>

                        <label class="meal-option">
                            <input type="checkbox" name="protein[]" value="Fried Fish" data-price="3" onchange="calculateMealPrice()">
                            <img class="meal-pic" src="../images/Fried-Fish.jpg">
                            <span>Fried Fish<br><b>+RM3.00</b></span>
                        </label>

                        <label class="meal-option">
                            <input type="checkbox" name="protein[]" value="Fried Egg" data-price="1" onchange="calculateMealPrice()">
                            <img class="meal-pic" src="../images/Fried-Egg.png">
                            <span>Fried Egg<br><b>+RM1.00</b></span>
                        </label>

                        <label class="meal-option">
                            <input type="checkbox" name="protein[]" value="Vegetables" data-price="1" onchange="calculateMealPrice()">
                            <img class="meal-pic" src="../images/Vegetables.png">
                            <span>Vegetables<br><b>+RM1.00</b></span>
                        </label>
                    </div>

                    <div class="builder-buttons">
                        <button type="button" class="outline-btn" onclick="showStep('base')">← Back</button>
                        <button type="button" class="pink-btn" onclick="showStep('topping')">Next: Toppings →</button>
                    </div>
                </div>

                <div class="step-section hidden" id="step-topping">
                    <p class="step-label">Choose your topping:</p>

                    <div class="option-grid">
                        <label class="meal-option">
                            <input type="checkbox" name="topping[]" value="Egg" data-price="1.5" onchange="calculateMealPrice()">
                            <img class="meal-pic" src="../images/egg.png">
                            <span>Egg<br><b>+RM1.50</b></span>
                        </label>

                        <label class="meal-option">
                            <input type="checkbox" name="topping[]" value="Vegetables" data-price="1" onchange="calculateMealPrice()">
                            <img class="meal-pic" src="../images/vegetables.png">
                            <span>Vegetables<br><b>+RM1.00</b></span>
                        </label>

                        <label class="meal-option">
                            <input type="checkbox" name="topping[]" value="Cheese" data-price="2" onchange="calculateMealPrice()">
                            <img class="meal-pic" src="../images/cheese.jpeg">
                            <span>Cheese<br><b>+RM2.00</b></span>
                        </label>

                        <label class="meal-option">
                            <input type="checkbox" name="topping[]" value="None" data-price="0" onchange="calculateMealPrice()">
                            <span>None<br><b>+RM0.00</b></span>
                        </label>
                    </div>

                    <div class="builder-buttons">
                        <button type="button" class="outline-btn" onclick="showStep('protein')">← Back</button>
                        <button type="button" class="pink-btn" onclick="showStep('sauce')">Next: Sauce →</button>
                    </div>
                </div>

                <div class="step-section hidden" id="step-sauce">
                    <p class="step-label">Choose your sauce:</p>

                    <div class="option-grid">
                        <label class="meal-option">
                            <input type="checkbox" name="sauce[]" value="Spicy" data-price="0.5" onchange="calculateMealPrice()">
                            <img class="meal-pic" src="../images/spicy-sauce.jpg">
                            <span>Spicy<br><b>+RM0.50</b></span>
                        </label>

                        <label class="meal-option">
                            <input type="checkbox" name="sauce[]" value="Sweet" data-price="0.5" onchange="calculateMealPrice()">
                            <img class="meal-pic" src="../images/sweet-sauce.jpg">
                            <span>Sweet<br><b>+RM0.50</b></span>
                        </label>

                        <label class="meal-option">
                            <input type="checkbox" name="sauce[]" value="Black Pepper" data-price="1" onchange="calculateMealPrice()">
                            <img class="meal-pic" src="../images/blackpepper-sauce.jpg">
                            <span>Black Pepper<br><b>+RM1.00</b></span>
                        </label>

                        <label class="meal-option">
                            <input type="checkbox" name="sauce[]" value="None" data-price="0" onchange="calculateMealPrice()">
                            <span>None<br><b>+RM0.00</b></span>
                        </label>
                    </div>

                    <div class="builder-buttons">
                        <button type="button" class="outline-btn" onclick="showStep('topping')">← Back</button>
                        <button type="submit" class="pink-btn">Complete Meal 🛒</button>
                    </div>
                </div>

            </div>

            <div class="summary-side">

                <div class="total-box">
                    <p>Live total</p>
                    <h1>RM<span id="displayTotal">0.00</span></h1>
                    <small>Updates as you choose</small>
                </div>

                <div class="selection-box">
                    <h3>☰ Your selections</h3>
                    <p><b>Base:</b> <span id="baseText">-</span></p>
                    <p><b>Protein:</b> <span id="proteinText">-</span></p>
                    <p><b>Toppings:</b> <span id="toppingText">-</span></p>
                    <p><b>Sauce:</b> <span id="sauceText">-</span></p>
                </div>

                <button class="complete-side-btn" type="submit">Complete Meal</button>

            </div>

        </div>

        <input type="hidden" name="total_price" id="total_price" value="0">

    </form>

</div>

<script>
function showStep(step) {
    let steps = ["base", "protein", "topping", "sauce"];

    steps.forEach(function(item) {
        document.getElementById("step-" + item).classList.add("hidden");
        document.getElementById("tab-" + item).classList.remove("active");
    });

    document.getElementById("step-" + step).classList.remove("hidden");
    document.getElementById("tab-" + step).classList.add("active");
}

function getSingleValue(name) {
    let selected = document.querySelector("input[name='" + name + "']:checked");
    return selected ? selected.value : "-";
}

function getMultipleValues(name) {
    let selected = document.querySelectorAll("input[name='" + name + "[]']:checked");
    let values = [];

    selected.forEach(function(item) {
        values.push(item.value);
    });

    return values.length > 0 ? values.join(", ") : "-";
}

function getSinglePrice(name) {
    let selected = document.querySelector("input[name='" + name + "']:checked");
    return selected ? parseFloat(selected.getAttribute("data-price")) || 0 : 0;
}

function getMultiplePrice(name) {
    let selected = document.querySelectorAll("input[name='" + name + "[]']:checked");
    let total = 0;

    selected.forEach(function(item) {
        total += parseFloat(item.getAttribute("data-price")) || 0;
    });

    return total;
}

function calculateMealPrice() {
    let total = 0;

    total += getSinglePrice("base");
    total += getMultiplePrice("protein");
    total += getMultiplePrice("topping");
    total += getMultiplePrice("sauce");

    document.getElementById("displayTotal").innerText = total.toFixed(2);
    document.getElementById("total_price").value = total.toFixed(2);

    document.getElementById("baseText").innerText = getSingleValue("base");
    document.getElementById("proteinText").innerText = getMultipleValues("protein");
    document.getElementById("toppingText").innerText = getMultipleValues("topping");
    document.getElementById("sauceText").innerText = getMultipleValues("sauce");
}

function validateCustomMeal() {
    let base = document.querySelector("input[name='base']:checked");
    let protein = document.querySelectorAll("input[name='protein[]']:checked");
    let topping = document.querySelectorAll("input[name='topping[]']:checked");
    let sauce = document.querySelectorAll("input[name='sauce[]']:checked");

    if (!base || protein.length === 0 || topping.length === 0 || sauce.length === 0) {
        alert("Please complete all meal selections.");
        return false;
    }

    return true;
}
</script>

<?php include("../includes/footer.php"); ?>
