<?php
include("../includes/auth_check.php");
requireRole("student");
?>
<?php

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION["last_order_id"])) {
    header("Location: cart.php");
    exit();
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
            <a class="active" href="payment.php"><i class="fa-solid fa-credit-card"></i> Payment</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="checkout-layout payment-layout">
        <div class="checkout-card payment-card">
            <h2><span class="checkout-title-icon"><i class="fa-solid fa-credit-card"></i></span> Payment Method</h2>
            <p>Select your payment method to continue.</p>

            <form method="POST" action="payment_success.php">
                <label class="checkout-radio">
                    <input type="radio" name="payment_demo" value="Touch n Go eWallet" checked>
                    <div>
                        <b><i class="fa-solid fa-wallet payment-method-icon"></i> Touch 'n Go eWallet</b>
                        <p>Instant eWallet payment</p>
                    </div>
                    <span>Fast</span>
                </label>

                <label class="checkout-radio">
                    <input type="radio" name="payment_demo" value="Online Banking FPX">
                    <div>
                        <b><i class="fa-solid fa-building-columns payment-method-icon"></i> Online Banking FPX</b>
                        <p>Maybank2u &bull; CIMB Clicks &bull; Public Bank &bull; RHB &bull; Hong Leong &bull; Bank Islam</p>
                    </div>
                    <span>FPX</span>
                </label>

                <div id="bankSelection" class="bank-selection">
                    <label><b>Select Your Bank</b></label>

                    <select name="bank">
                        <option>Maybank2u</option>
                        <option>CIMB Clicks</option>
                        <option>Public Bank</option>
                        <option>RHB Bank</option>
                        <option>Hong Leong Bank</option>
                        <option>Bank Islam</option>
                        <option>BSN</option>
                        <option>AmBank</option>
                    </select>
                </div>

                <label class="checkout-radio">
                    <input type="radio" name="payment_demo" value="Cash on Delivery">
                    <div>
                        <b><i class="fa-solid fa-money-bill-wave payment-method-icon"></i> Cash on Delivery</b>
                        <p>Pay when food arrives</p>
                    </div>
                    <span>COD</span>
                </label>

                <button class="place-order-btn" type="submit">
                    <i class="fa-solid fa-lock"></i> Pay Now
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleBankSelection() {
    const selectedPayment = document.querySelector('input[name="payment_demo"]:checked');
    const bankSelection = document.getElementById("bankSelection");

    bankSelection.style.display = selectedPayment && selectedPayment.value === "Online Banking FPX"
        ? "block"
        : "none";
}

document.querySelectorAll('input[name="payment_demo"]').forEach((input) => {
    input.addEventListener("change", toggleBankSelection);
});

toggleBankSelection();
</script>

<?php include("../includes/footer.php"); ?>
