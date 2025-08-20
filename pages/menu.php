<style>
.background-color {
    background-color: #D9D9D9;
    height: 70px;
    font-size: 50px; 
    margin: 0;
    padding: 0;
    
    display: flex;
    align-items: center;
    justify-content: center;
}

.background-color .tagline {
    max-width: 1000px;    /* same as .menu */
    width: 100%;
    margin: 0 auto;
    padding: 0 20px;      /* same horizontal padding */
    font-size: 18px;
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
.category-title{
    text-align: left;
    margin: 50px 0 10px ;
    font-size: 30px;
}



</style>
<div class="background-color">
    <h6 class="tagline">When Coffee Meets Good Food, Great Conversations Begin.</h6>
</div>


<div class="menu">
    <div class="category-buttons">
    <?php $Text = "Meal"; include '../components/button.php' ?>
    <?php $Text = "Drinks";include '../components/button.php' ?>
    <?php $Text = "Featured";include '../components/button.php' ?>
    </div>
    <div class="category-title">Rice Meal</div>
    </h2>
    <div class="menu-cards">
    <?php $name = "Tiramisu" ;$price =  "100" ; $image = "../public/assests/image-43.png" ;include '../partials/menu-card.php'?>
    <?php $name = "Tiramisu" ;$price = "100" ;$image = "../public/assests/image-43.png";include '../partials/menu-card.php'?>
    <?php $name = "Tiramisu" ;$price = "100" ;$image = "../public/assests/image-43.png";include '../partials/menu-card.php'?>
    <?php $name = "Tiramisu" ;$price = "100" ;$image = "../public/assests/image-43.png";include '../partials/menu-card.php'?>
    <?php $name = "Tiramisu" ;$price = "100" ;$image = "../public/assests/image-43.png";include '../partials/menu-card.php'?>
    </div>
     <div class="category-title">Rice Meal</div>
    </h2>
    <div class="menu-cards">
    <?php $name = "Tiramisu" ;$price =  "100" ; $image = "../public/assests/image-43.png" ;include '../partials/menu-card.php'?>
    <?php $name = "Tiramisu" ;$price = "100" ;$image = "../public/assests/image-43.png";include '../partials/menu-card.php'?>
    <?php $name = "Tiramisu" ;$price = "100" ;$image = "../public/assests/image-43.png";include '../partials/menu-card.php'?>
    <?php $name = "Tiramisu" ;$price = "100" ;$image = "../public/assests/image-43.png";include '../partials/menu-card.php'?>
    <?php $name = "Tiramisu" ;$price = "100" ;$image = "../public/assests/image-43.png";include '../partials/menu-card.php'?>
    

</div>
