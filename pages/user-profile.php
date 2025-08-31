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

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Profile</title>

<style>
    body {
        background-color: #f0f0f0;
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 10px;
    }

    .white-box {
        background: #fff;
        border-radius: 15px;
        padding: 16px;
        margin-bottom: 15px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    /* Header */
    #first-box {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    #first-box img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
    }

    #first-box h2 {
        margin: 0;
        font-size: 1.1rem;
    }

    #first-box p {
        margin: 2px 0 0;
        font-size: 0.9rem;
        color: gray;
    }

    /* Section Titles */
    .title-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .title-info h3 {
        margin: 0;
        font-size: 1rem;
    }

    hr {
        margin: 6px 0 12px;
        border: none;
        border-top: 1px solid #ccc;
    }

    /* Info Grid */
    .row-info {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* default mobile = 2 cols */
        gap: 15px 20px;
    }

    .info {
        display: flex;
        flex-direction: column;
    }

    .info p {
        margin: 0;
        font-size: 0.75rem;
        color: gray;
    }

    .info h4 {
        margin: 2px 0 0;
        font-size: 0.9rem;
        font-weight: normal;
    }

    input[disabled] {
        border: none;
        background: transparent;
        font-size: 0.9rem;
        padding: 0;
        color: black;
    }

    /* Responsive Desktop */
    @media (min-width: 768px) {
        .white-box {
            padding: 20px;
        }

        #first-box img {
            width: 100px;
            height: 100px;
        }

        #first-box h2 {
            font-size: 1.3rem;
        }

        .row-info {
            grid-template-columns: repeat(3, 1fr); /* desktop = 3 cols */
        }
    }
</style>
</head>
<body>

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

</body>
</html>
