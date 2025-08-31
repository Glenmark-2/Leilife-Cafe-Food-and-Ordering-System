<style>
/* Modal container */
#box-container {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    justify-content: center;
    background-color: white;
    padding: 40px 50px 30px 50px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    width: fit-content;
    border-radius: 20px;
    font-family: Arial, sans-serif;
    z-index: 1000;
    margin-top: 40px;
}

/* Close button */
#close-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background: transparent;
    border: none;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    color: #333;
}

#close-btn:hover {
    color: red;
}

/* Content inside the modal */
#box-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    width: 100%;
}

/* Image and heading */
#box-content img {
    height: 100px;
    width: 100px;
}

#box-content h1 {
    text-align: center;
    margin: 0;
}

/* Form */
form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    max-width: 300px;
    align-items: flex-start;
}

label {
    font-weight: bold;
}

input {
    width: 100%;
    height: 40px;
    background-color: #f4f4f4;
    border: none;
    border-radius: 5px;
    padding-left: 10px;
    font-size: 14px;
    box-sizing: border-box;
}

/* Login button */
button.login-btn {
    width: 100%;
    padding: 10px 0;
    background-color: #22333c;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 15px;
    margin-bottom: 0;
}

button.login-btn:hover {
    background-color: #445566;
}

/* Continue with Google button */
button.google-btn {
    width: 100%;
    padding: 5px 0;
    background-color: white;
    color: #444;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

/* button.google-btn img {
    height: 20px;
    width: 20px;
} */

button.google-btn:hover {
    background-color: #f4f4f4;
}

/* Forgot password button */
#forgot-pass {
    border: none;
    background-color: transparent;
    color: red;
    padding: 0;
    margin-top: -10px;
    font-size: 13px;
    cursor: pointer;
}

/* Terms & Conditions */
.terms {
    text-align: center;
    font-size: 12px;
    color: #666;
    line-height: 1.4;
    max-width: 300px;
}

/* Sign up section */
.signup {
    text-align: center;
    font-size: 14px;
    height: fit-content;
}

.signup button {
    background: transparent;
    border: none;
    color: red;
    cursor: pointer;
    font-weight: bold;
}

.signup button:hover {
    text-decoration: underline;
}

/* Responsive adjustments for mobile */
@media (max-width: 480px) {
    #box-container {
        padding: 20px 35px 25px 35px;
        width: 60%;      /* make modal nearly full width */
        
    }

    #box-content img {
        width: 70px;     /* smaller logo */
        height: auto;    /* maintain aspect ratio */
    }

    button.google-btn img {
        width: 20px;     /* smaller Google icon */
        height: 20px;
    }

    h1 {
        font-size: 20px; /* smaller heading */
    }

    form {
        max-width: 100%; /* form fills modal width */
    }

    .terms {
        max-width: 100%;
        font-size: 11px;
    }

    .signup {
        font-size: 12px;
    }
}

</style>

<center>
<div id="box-container">
    <!-- Close button -->
    <button id="close-btn">&times;</button>

    <div id="box-content">
        <img src="\Leilife\public\assests\Mask group.png" alt="Logo">
        <h1>Welcome back!</h1>

        <form action="login.php" method="POST">
            <label for="email">Email <span style="color: red;">*</span></label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="password">Password <span style="color: red;">*</span></label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button type="submit" class="login-btn">Login</button>

            <!-- Continue with Google -->
            <button type="button" class="google-btn">
                <img id="google-logo"  src="../public/assests/google.logo.webp" alt="Google Logo"
                style="width: 25px; height:25px; max-width:30px; max-height:30px"
                >
                Continue with Google
            </button>
        </form>

        <button id="forgot-pass">Forgot your password?</button>

        <div class="terms">
            <p style="margin-top: 0;">By continuing, you agree to our updated Terms & Conditions and Privacy Policy.</p>
        </div>

        <div class="signup" style="margin-top: 0;">
            <p style="margin:0;">Don't have an account? <button>Sign up</button></p>
        </div>
    </div>
</div>
</center>

<script>
document.getElementById('close-btn').addEventListener('click', () => {
    document.getElementById('loginModal').style.display = 'none';
});
</script>
