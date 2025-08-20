<style>
    #body-container {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    #title {
        text-align: center;
        font-size: 20px;
    }

    #subtitle {
        margin-top: -20px;
        font-size: 15px;
    }

    #box {

        background-color: white;
        padding: 40px;
        border-radius: 25px;

    }

    .input-box {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
    }

    .input-box input {
        flex: 1 1 calc(50% - 30px);
        box-sizing: border-box;
    }

    input {
        width: 15vw;
        height: 6vh;
        background-color: #f4f4f4;
        border: none;
        border-radius: 5px;
        padding-left: 10px;

    }

    .label-input {
        font-weight: bolder;
    }

.terms-check {
  display: flex;
  align-items: center; 
  gap: 8px;           
  font-size: 14px;
  line-height: 1.4;
  margin-top: 15px;
}

.terms-check input {
  width: 16px;   
  height: 16px;
  transform: none;  
  margin: 0;         
}


    
</style>

<div id="body-container">
    <div id="title">
        <h1>Ready to sign up to Leilife?</h1>
        <p id="subtitle">Tell us more about you so we can give you a better delivery experience.</p>
    </div>

    <div id="box">
        <p class="label-input">User Details</p>
        <div class="input-box">
            <input type="text" name="fname" required placeholder="First name">
            <input type="text" name="lname" required placeholder="Last name">
        </div>

        <p class="label-input">Login & Contact Details</p>
        <div class="input-box">
            <input type="email" name="email" required placeholder="Email address">
            <input type="tel" name="phone_number" required placeholder="Phone number">
            <input type="password" name="password" required placeholder="Password">
            <input type="password" name="confirm_password" required placeholder="Password">
        </div>
        <label class="terms-check">
        <input  type="checkbox" id="agree" name="agree" required>
            By registering your details, you agree with our 
            <a href="/terms" target="_blank" rel="noopener">Terms &amp; Conditions</a>.
        </label>
        
        <?php include "../components/button.php"; ?>
    </div>
</div>