<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: /Leilife/pages/admin/login-x9P2kL7zQ.php');
    exit;
}

$showWelcome = false;
if (isset($_SESSION['show_welcome']) && $_SESSION['show_welcome'] === true) {
    $showWelcome = true;
    unset($_SESSION['show_welcome']);
}
?>

<div id="first-row">
    <h2>Dashboard</h2>
</div>

<div id="second-row">
    <div class="box-row">
        <p>Pending</p>
        <h3 id="pending-count">0</h3>
    </div>

    <div class="box-row">
        <p>Preparing</p>
        <h3 id="preparing-count">0</h3>
    </div>

    <div class="box-row">
        <p>Ready to Deliver</p>
        <h3 id="ready-count">0</h3>
    </div>
</div>

<div id="third-row">
    <div id="top">
        <p>Recent Orders</p>
        <div class="sort-dropdown">
            <label for="sort">Sort by:</label>
            <select id="sort">
                <option value="order_date">Order Date</option>
                <option value="status">Status</option>
                <option value="total">Total</option>
            </select>
        </div>
    </div>

    <div id="table">
        <div id="table-title">
            <p style="width: 20%;">Order #</p>
            <p style="width: 25%;">Customer</p>
            <p style="width: 20%;">Amount</p>
            <p style="width: 10%;">Item</p>
            <p style="width: 20%;">Status</p>
        </div>
        <div id="table-body"></div>
        <div style="height: 20px; background-color: #fefefe; border-radius:0 0 20px 20px;"></div>
    </div>
</div>

<!-- ✅ Welcome Modal -->
<?php if ($showWelcome): ?>
    <div id="welcomeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeWelcome()">&times;</span>
            <h2>Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</h2>
            <p>You're now logged in.</p>
            <button onclick="closeWelcome()">Continue</button>
        </div>
    </div>
<?php endif; ?>

<script>

        <?php if ($showWelcome): ?>
            const welcomeModal = document.getElementById('welcomeModal');
            welcomeModal.style.display = 'flex';
            window.closeWelcome = function () {
                welcomeModal.style.display = 'none';
            }
        <?php endif; ?>

</script>
<script src="/Leilife/Scripts/admin/components/dashboard.js"></script>