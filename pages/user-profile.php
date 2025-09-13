<br>

<?php
require_once __DIR__ . '/../backend/db_script/db.php';
include "../components/buttonTemplate.php";
$appData = new AppData($pdo);

$user_id = $_SESSION['user_id'];
$userInfo = $appData->loadUserInfo($user_id);
$userAddress = $appData->loadUserAddress($user_id);
?>

<!-- Profile Header -->
<div class="white-box">
    <div id="first-box"> 
        <img src="../public/assests/uploadImg.jpg" alt="profile-photo"> 
        <div> 
            <h2><?= htmlspecialchars($userInfo["first_name"] ?? 'Unknown') . ' ' . htmlspecialchars($userInfo["last_name"] ?? 'Unknown'); ?></h2> 
            <p>Customer</p>
        </div>
    </div>
</div>

<!-- Personal Information -->
<div class="white-box">
    <form id="personal-form" method="POST" action="../backend/update_user_profile.php">
        <div class="title-info">
            <h3>Personal Information</h3>
            <?php
            echo createButton(
                25,
                70,
                "Edit",
                "edit-info",
                16,
                "submit",
                ['data-state' => 'edit', 'name' => 'update_info']
            );
            ?>
        </div>
        <hr>
        <div class="row-info">
            <div class="info">
                <p>First name</p>
                <h4 class="display-value"><?= htmlspecialchars($userInfo["first_name"] ?? ''); ?></h4>
                <input class="edit-input" type="text" name="first_name"
                       value="<?= htmlspecialchars($userInfo["first_name"] ?? ''); ?>" style="display:none;">
            </div>

            <div class="info">
                <p>Last name</p>
                <h4 class="display-value"><?= htmlspecialchars($userInfo["last_name"] ?? ''); ?></h4>
                <input class="edit-input" type="text" name="last_name"
                       value="<?= htmlspecialchars($userInfo["last_name"] ?? ''); ?>" style="display:none;">
            </div>

            <div class="info">
                <p>Phone number</p>
                <h4 class="display-value"><?= htmlspecialchars($userInfo["phone_number"] ?? ''); ?></h4>
                <input class="edit-input" type="text" name="phone_number" 
                       value="<?= htmlspecialchars($userInfo["phone_number"] ?? ''); ?>"
                       placeholder="+63" style="display:none;">
            </div>

            <div class="info">
                <p>Email address</p>
                <h4><?= htmlspecialchars($userInfo["email"] ?? ''); ?></h4>
            </div>

            <div class="info">
                <p>User role</p>
                <h4>Customer</h4>
            </div>
        </div>
    </form>
</div>

<!-- Address Information -->
<div class="white-box">
    <div class="title-info">
        <h3>Address</h3>
        <?php
        echo createButton(
            25,
            70,
            "Edit",
            "edit-address",
            16,
            "button",
            ['data-state' => 'edit']
        );
        ?>
    </div>
    <hr>

    <?php if (!$userAddress): ?>
        <div class="row-info" style="display:flex; justify-content:center;">
            <center><p>Set Address</p></center>
        </div>
    <?php else: ?>
        <div class="row-info">
            <div class="info">
                <p>Street</p>
                <h4><?= htmlspecialchars($userAddress["street_address"] ?? ''); ?></h4>
            </div>
            <div class="info">
                <p>Barangay</p>
                <h4><?= htmlspecialchars($userAddress["barangay"] ?? ''); ?></h4>
            </div>
            <div class="info">
                <p>City</p>
                <h4><?= htmlspecialchars($userAddress["city"] ?? ''); ?></h4>
            </div>
            <div class="info">
                <p>Province</p>
                <h4><?= htmlspecialchars($userAddress["province"] ?? ''); ?></h4>
            </div>
            <div class="info">
                <p>Region</p>
                <h4><?= htmlspecialchars($userAddress["region"] ?? ''); ?></h4>
            </div>
        </div>
    <?php endif; ?>

    <!-- Logout Button -->
    <div class="title-info" style="justify-content: flex-end; margin-top:10px;">
        <a href="/Leilife/backend/logout.php">
            <?php echo createButton(25, 90, "Logout", "logout-btn"); ?>
        </a>
    </div>
</div>

<!-- Order History -->
<div class="white-box">
    <div class="title-info">
        <h3>Order History</h3>
    </div>
    <hr>
    <p>No orders yet.</p>
</div>
<?php include "../components/admin/set-address-modal.php"; ?>

<script>
    const editInfo = document.getElementById("edit-info");
    const form = document.getElementById("personal-form");
    const editAddress = document.getElementById("edit-address");
    const modalOverlay = document.getElementById("modalOverlay");

    editInfo.addEventListener('click', (e) => {
        let state = editInfo.getAttribute("data-state");

        if (state === "edit") {
            e.preventDefault();
            editInfo.innerText = "Save";
            editInfo.style.backgroundColor = "green";
            editInfo.setAttribute("data-state", "save");

            document.querySelectorAll(".info").forEach(info => {
                const h4 = info.querySelector(".display-value");
                const input = info.querySelector(".edit-input");

                if (input) {
                    h4.style.display = "none";
                    input.style.display = "block";
                }
            });

            editInfo.type = "submit";

        } else if (state === "save") {
            const firstName = form.querySelector("input[name='first_name']");
            const lastName = form.querySelector("input[name='last_name']");
            const phoneInput = form.querySelector("input[name='phone_number']");
            let phoneValue = phoneInput.value.trim();

            if (firstName.value.trim() === "") {
                e.preventDefault();
                alert("First name cannot be blank.");
                firstName.focus();
                return;
            }

            if (lastName.value.trim() === "") {
                e.preventDefault();
                alert("Last name cannot be blank.");
                lastName.focus();
                return;
            }

            if (phoneValue !== "") {
                if (!phoneValue.startsWith("+63")) {
                    phoneValue = "+63" + phoneValue.replace(/^0/, "");
                    phoneInput.value = phoneValue;
                }

                const phoneRegex = /^\+63\d{9}$/;
                if (!phoneRegex.test(phoneValue)) {
                    e.preventDefault();
                    alert("Phone number must be in format +639XXXXXXXXX.");
                    phoneInput.focus();
                    return;
                }
            }
        }
    });

    if (editAddress) {
        editAddress.addEventListener("click", () => {
            if (modalOverlay) {
                modalOverlay.style.display = "flex"; 
            }
        });
    }

    function closeModal() {
        if (modalOverlay) {
            modalOverlay.style.display = "none"; 
        }
    }
</script>
