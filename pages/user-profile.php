<?php
// Example data (later this can come from DB)
$user = [
    "first_name"  => "Nico",
    "last_name"   => "Dublin",
    "email"       => "Nico@gmail.com",
    "phone"       => "+63 9123456789",
    "street"      => "Esguerra",
    "city"        => "Caloocan",
    "province"    => "Manila",
    "region"      => "NCR",
    "postal_code" => "1400"
];
?>



<!-- Header -->
<div class="white-box">
    <div id="first-box">
        <img src="../public/assests/about us.png" alt="profile-photo">
        <div>
            <h2>Basta Name To</h2>

        </div>
    </div>
</div>

<!-- Personal Information -->
<div class="white-box">
    <div class="title-info">
        <h3>Personal Information</h3>
        <?php 
        // Uncomment if you have your button component
         include "../components/buttonTemplate.php"; 
     echo createButton(20,70,"Edit", "edit-personal"); 
        ?>
    </div>
    <hr>
    <div class="row-info">
        <div class="info">
            <p>First name</p>
            <h4><?= $user['first_name']; ?></h4>
        </div>
        <div class="info">
            <p>Last name</p>
            <h4><?= $user['last_name']; ?></h4>
        </div>
        <div class="info">
            <p>Email address</p>
            <h4><?= $user['email']; ?></h4>
        </div>
        <div class="info">
            <p>Phone number</p>
            <h4><?= $user['phone']; ?></h4>
        </div>
    </div>
</div>

<!-- Address -->
<div class="white-box">
    <div class="title-info">
        <h3>Address</h3>
        <?php 
        echo createButton(20,70,"Edit", "edit-address"); 
        ?>
    </div>
    <hr>
    <div class="row-info">
        <div class="info">
            <p>Street</p>
            <input value="<?= $user['street']; ?>" disabled>
        </div>
        <div class="info">
            <p>City</p>
            <input value="<?= $user['city']; ?>" disabled>
        </div>
        <div class="info">
            <p>Province</p>
            <input value="<?= $user['province']; ?>" disabled>
        </div>
        <div class="info">
            <p>Region</p>
            <input value="<?= $user['region']; ?>" disabled>
        </div>
        <div class="info">
            <p>Postal Code</p>
            <input value="<?= $user['postal_code']; ?>" disabled>
        </div>
        <div class="title-info" style="justify-content: flex-end; align-items: flex-end;">
            <a href="/Leilife/backend/logout.php">
                <?php echo createButton(20,90,"Logout", "logout-btn"); ?>
            </a>
        </div>    
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

