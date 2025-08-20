<style>
.background-color {
    background-color: #D9D9D9;
    height: 70px;
    font-size: 50px; 
    margin: 0;
    padding: 0;
    text-align: center;   /* tagline centered */
    display: flex;
    align-items: center;
    justify-content: center;
}

.background-color h6 {
    margin: 0;
    padding: 0;
    font-size: 18px;
}

.menu {
    max-width: 1300px;    /* keeps content from stretching too wide */
    margin: 30px 80px;    /* centers the whole menu section */
    padding: 0 20px;
    text-align: center;
}

.category-buttons {
    margin-bottom: 20px;
}

.menu-cards {

    display: flex;
    flex-wrap: wrap;      /* allows cards to wrap to next line */
    justify-content: left; /* center cards horizontally */
    gap: 20px;            /* spacing between cards */
}



</style>
<div class="background-color">
    <h6>When Coffee Meets Good Food, Great Conversations Begin.</h6>
</div>


<div class="menu">
    <div class="category-buttons">
    <?php $Text = "Meal"; include '../components/button.php' ?>
    <?php $Text = "Drinks";include '../components/button.php' ?>
    <?php $Text = "Featured";include '../components/button.php' ?>
    </div>

    <div class="menu-cards">
    <?php $name = "Tiramisu" ;$price =  "100" ; $image = "../public/assests/image-43.png" ;include '../partials/menu-card.php'?>
    <?php $name = "Tiramisu" ;$price = "100" ;$image = "../public/assests/image-43.png";include '../partials/menu-card.php'?>
    <?php $name = "Tiramisu" ;$price = "100" ;$image = "../public/assests/image-43.png";include '../partials/menu-card.php'?>
    <?php $name = "Tiramisu" ;$price = "100" ;$image = "../public/assests/image-43.png";include '../partials/menu-card.php'?>
    <?php $name = "Tiramisu" ;$price = "100" ;$image = "../public/assests/image-43.png";include '../partials/menu-card.php'?>
    
    

    </div>

</div>
