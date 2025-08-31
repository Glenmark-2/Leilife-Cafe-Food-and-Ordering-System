<style>
.menu-header {
    background-color: #D9D9D9;
    height: 70px;
    font-size: 50px; 
    margin: 0;
    padding: 0;
    
    display: flex;
    align-items: center;
    justify-content: center;
}

.menu-header .menu-tagline {
    max-width: 1000px;    /* same as .menu */
    width: 100%;
    margin: 0 auto;
    padding: 0 20px;      /* same horizontal padding */
    font-size: 16px;
    text-align: left;     /* aligned with menu */
}

.menu {
    max-width: 1000px;   /* enough space for 5 cards (250px each + gaps) */
    margin: 50px auto;   /* center the whole section */
    padding: 0 20px;
    text-align: center;
}

.menu-cards {
    margin-top: 10px;
    display: grid;
    grid-template-columns: repeat(5, 1fr); /* exactly 5 cards per row */
    gap: 20px; /* space between cards */
    justify-items: center; /* center cards inside grid cells */
}

.category-title {
    text-align: left;
    margin: 50px 0 10px ;
    font-size: 30px;
}
@media (max-width: 1024px) {
    .menu-cards {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* Mobile (max-width: 768px) → show 2 per row */
@media (max-width: 768px) {
    .menu-cards {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Extra small (max-width: 480px) → show 1 per row */
@media (max-width: 410px) {
    .menu-cards {
        grid-template-columns: repeat(1, 1fr);
    }
}
</style>

<!-- Menu Header -->
<div class="menu-header">
    <h6  class="menu-tagline">When Coffee Meets Good Food, Great Conversations Begin.</h6>
</div>

<div class="menu">
    <div class="category-buttons">
        <?php $Text = "Meal"; include '../components/button.php' ?>
        <?php $Text = "Drinks"; include '../components/button.php' ?>
        <?php $Text = "Featured"; include '../components/button.php' ?>
    </div>

    <div class="category-title">Rice Meal</div>
    <div class="menu-cards">
        <?php $name = "Tiramisu"; $price = "100"; $image = "../public/assests/image-43.png"; include '../partials/menu-card.php' ?>
        <?php $name = "Tiramisu"; $price = "100"; $image = "../public/assests/image-43.png"; include '../partials/menu-card.php' ?>
        <?php $name = "Tiramisu"; $price = "100"; $image = "../public/assests/image-43.png"; include '../partials/menu-card.php' ?>
        <?php $name = "Tiramisu"; $price = "100"; $image = "../public/assests/image-43.png"; include '../partials/menu-card.php' ?>
        <?php $name = "Tiramisu"; $price = "100"; $image = "../public/assests/image-43.png"; include '../partials/menu-card.php' ?>
    </div>

    <div class="category-title">Rice Meal</div>
    <div class="menu-cards">
        <?php $name = "Tiramisu"; $price = "100"; $image = "../public/assests/image-43.png"; include '../partials/menu-card.php' ?>
        <?php $name = "Tiramisu"; $price = "100"; $image = "../public/assests/image-43.png"; include '../partials/menu-card.php' ?>
        <?php $name = "Tiramisu"; $price = "100"; $image = "../public/assests/image-43.png"; include '../partials/menu-card.php' ?>
        <?php $name = "Tiramisu"; $price = "100"; $image = "../public/assests/image-43.png"; include '../partials/menu-card.php' ?>
        <?php $name = "Tiramisu"; $price = "100"; $image = "../public/assests/image-43.png"; include '../partials/menu-card.php' ?>
    </div>
</div>
