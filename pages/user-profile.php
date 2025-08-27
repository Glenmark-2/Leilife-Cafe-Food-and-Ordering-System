<style>
    .white-box {
        display: flex;
        flex-direction: column;
        width: 80vw;
        padding: 20px;
        margin: auto;
        margin-top: 20px;
        background-color: white;
        border-radius: 25px;
    }

    #first-box {
        display: flex;
        flex-direction: row;
        align-items: center; 
    }

    .info {
        display: flex;
        flex-direction: column;
        align-items: flex-start; 
        line-height: 1;         
        margin-top: 0; 
        width: 15%;
        margin-right: 200px;
    }

    .first-info,
    .second-info {
        margin: 0;  
    }

    .title-info {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    .title-info h3 {
        margin: 0; /* removes the default top margin */
    }

    .info p{
        color: gray;
        font-size: small;
        margin-bottom: 0;
    }

    .info h4{
        margin-top: 0;
    }

    .row-info{
        display: flex;
        flex-direction: row;
    }

    input{
        margin-top: 2px;
        /* background-color: white; */
        border-radius: 5px;
        /* border: 1px solid gray; */
    }

</style>

<div class="white-box">
    <div id="first-box">
        <div style="display: flex; justify-content: center; align-items: center; margin-right:20px;">
            <img src="../public/assests/about us.png" alt="profile-photo"
                style="width:100px;
                       height:100px;
                       border-radius:50px;">
        </div>

        <div class="info">
            <h2 class="first-info">Customer Name</h2>
            <p class="second-info">User role</p>
        </div>
    </div>
</div>

<div class="white-box">
    <div style="width: 100%;">
        <div class="title-info">
            <h3>Personal Information</h3>
            
        </div>
        <hr>
    </div>

    <div class="row-info">
        <div class="info">
            <p>First name</p>
            <h4>Ennovie </h4>
        </div>

        <div class="info">
            <p>Last name</p>
            <h4>Cutie</h4>
        </div>

        <div class="info">
            <p>Email address</p>
            <h4>Cutie@gmail.com</h4>
        </div>
    </div>

    <div class="row-info" >
        <div class="info" >
            <p>Contact number</p>
            <h4 style="margin-bottom: 0;">1234567890 </h4>
        </div>

        <div class="info" >
            <p>User role</p>
            <h4 style="margin-bottom: 0;">Customer</h4>
        </div>

    </div>
</div>

<div class="white-box">
    <div style="width: 100%;">
        <div class="title-info">
            <h3>Address</h3>
            <?php include "../components/buttonTemplate.php"; 
            echo createButton(30,70,"edit", "edit-address"); ?>
        </div>
        <hr>
    </div>

     <div class="row-info">
        <div class="info">
            <p>Street</p>
            <input value="Esguerra" disabled>
        </div>

        <div class="info">
            <p>City</p>
            <input value="Esguerra" disabled>
        </div>

        <div class="info">
            <p>Province</p>
            <input value="Esguerra" disabled>
        </div>
    </div>

    <div class="row-info">
        <div class="info">
            <p>Region</p>
            <input value="Esguerra" disabled>
        </div>

        <div class="info">
            <p>Postal Code</p>
            <input value="Esguerra" disabled>
        </div>

    </div>
</div>



