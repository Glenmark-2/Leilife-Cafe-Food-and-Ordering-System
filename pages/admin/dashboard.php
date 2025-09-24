<?php
session_start();
include "../components/admin/status.php";
if (!isset($_SESSION['admin_id'])) {
    header('Location: /leilife/pages/admin/login-x9P2kL7zQ.php');
    exit;
}

$showWelcome = false;
if (isset($_SESSION['show_welcome']) && $_SESSION['show_welcome'] === true) {
    $showWelcome = true;
    unset($_SESSION['show_welcome']);
}
// Get the sort option from GET request, default = order
$sortBy = $_GET['sort'] ?? 'order';

// Sample orders
$orders = [
    [
        "name" => "Ellie Imnida",
        "mode" => "Delivery",
        "order" => "1pc. Mang inasal",
        "amount" => "P100.00",
        "status" => "Pending"
    ],
    [
        "name" => "John Doe",
        "mode" => "Pick-up",
        "order" => "2pc. Burger",
        "amount" => "P150.00",
        "status" => "Preparing"
    ],
    [
        "name" => "Jane Smith",
        "mode" => "Delivery",
        "order" => "1pc. Pizza",
        "amount" => "P200.00",
        "status" => "Completed"
    ],
    [
        "name" => "Mark Allen",
        "mode" => "Delivery",
        "order" => "3pc. Sandwich",
        "amount" => "P180.00",
        "status" => "Cancelled"
    ],
    [
        "name" => "Lucy Heart",
        "mode" => "Delivery",
        "order" => "1pc. Pasta",
        "amount" => "P120.00",
        "status" => "Out for Delivery"
    ]
];

// Sort the array based on the selected option
usort($orders, function ($a, $b) use ($sortBy) {
    return strcmp($a[$sortBy], $b[$sortBy]);
});
?>

<div id="first-row">
    <h2>Dashboard</h2>
</div>

<div id="second-row">
    <div class="box-row">
        <p>Pending</p>
        <h3>10</h3>
    </div>

    <div class="box-row">
        <p>Preparing</p>
        <h3>10</h3>
    </div>

    <div class="box-row">
        <p>Ready to deliver</p>
        <h3>10</h3>
    </div>
</div>

<div id="third-row">
    <div id="top">
        <p>Recent Orders</p>
        <div class="sort-dropdown">
            <form method="GET" id="sortForm">
                <label for="sort">Sort by:</label>
                <select name="sort" id="sort" onchange="document.getElementById('sortForm').submit()">
                    <option value="order" <?= ($sortBy == 'order') ? 'selected' : '' ?>>Order</option>
                    <option value="name" <?= ($sortBy == 'name') ? 'selected' : '' ?>>Name</option>
                    <option value="status" <?= ($sortBy == 'status') ? 'selected' : '' ?>>Status</option>
                </select>
            </form>
        </div>
    </div>

    <div id="table">
        <div id="table-title">
            <p style="width: 19%;">Name</p>
            <p style="width: 29%;">Order</p>
            <p style="width: 19%;">Amount</p>
            <p style="width: 19%;">Status</p>
        </div>

        <!-- loop through orders -->
        <?php foreach ($orders as $order): ?>
            <div id="table-row-content">
                <div id="name-content">
                    <div>
                        <img src="public/assests/about us.png" alt="profile">
                    </div>
                    <div>
                        <p id="name"><?= $order['name'] ?></p>
                        <p id="orderMode"><?= $order['mode'] ?></p>
                    </div>
                </div>

                <div id="order-content">
                    <p><?= $order['order'] ?></p>
                </div>

                <div id="amount-content">
                    <p><?= $order['amount'] ?></p>
                </div>

                <div id="status-content">
                    <?= orderStatusBadge($order['status']) ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div style="height: 20px; background-color: #fefefe; border-radius:0 0 20px 20px;">
        </div>
    </div>
</div>


<!-- Welcome Modal -->
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

        function closeWelcome() {
            welcomeModal.style.display = 'none';
        }
    <?php endif; ?>
</script>