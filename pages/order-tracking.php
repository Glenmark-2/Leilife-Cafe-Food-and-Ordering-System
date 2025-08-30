<style>
    .tracking {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center; /* ✅ centers horizontally */
    }

    .your_order_title {
        text-align: center; /* ✅ fixes "align-text" */
        margin-bottom: 20px;
    }

    .progress-container {
      display: flex;
      justify-content: center;
      align-items: center;  
      gap: 40px; /* ✅ adds spacing between steps */
    }

    .step {
      text-align: center;
      width: fit-content;
    }

    .circle {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: #fff;
      border: 3px solid #ddd;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      color: #555;
    }

    .step.active .circle {
      background: #24343c;
      color: #fff;
      border-color: #24343c;
    }

    .label {
      margin-top: 8px;
      font-size: 14px;
      color: #333;
    }
    .order_details{
        display: grid;
        grid-template-columns: auto auto;
        gap: 20px;
        width: 100%;
    }
    .left-details{
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .right-details{
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .feedback-box {
      display: none;               /* Will change on depending on the state*/
      flex-direction: column;
      justify-content: flex-start;
      align-items: stretch;
        
      width: 400px;       /* fixed width */
      min-height: 250px;  /* enough space for form/text */
      max-width: 90%;     /* responsive on smaller screens */
        
      padding: 20px;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      margin-top: 0px 100px;
    }
    .feedback-box textarea {
      resize: none;
      height: 120px;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #ccc;
      margin-bottom: 15px;
      font-size: 14px;
    }
    .submit{
      display: none;               /* Will change on depending on the state*/
      justify-content: left;
    }
</style>

<?php 
    require_once "../components/buttonTemplate.php";
?>

<div class="tracking">
    <div class="your_order_title">
        <h3>Your Order</h3>
    </div>

    <div class="progress-container">
        <div class="step active">
            <div class="circle">1</div>
            <div class="label">Queuing...</div>
        </div>
        <div class="step">
            <div class="circle">2</div>
            <div class="label">Preparing...</div>
        </div>
        <div class="step">
            <div class="circle">3</div>
            <div class="label">Out for delivery...</div>
        </div>
        <div class="step">
            <div class="circle">4</div>
            <div class="label">Delivered</div>
        </div>
    </div>

    <div class="order_details">
        <div class="left-details">
            <div class="left-title">
              <p>Estimated time of delivery</p>  
            </div>
            <div>
               <p> 15 - 20 mins </p>
            </div>
            <div>
                <img src="\Leilife\public\assests\emojione_motorcycle.png" alt="Logo">
            </div>
            <div class="feedback-box">
              <h4>Give a Feedback</h4>
              <textarea placeholder="Write your feedback here..."></textarea>
            </div>
            <div class="submit">
                <?php echo createButton(45, 150, "Submit"); ?>
            </div>        
        </div>
        <div class="right-details">
            <div class="right-title">
                <p>Delivery details</p>
            </div>
            <div class="details-inside">
                <div class="right-address">
                    <p>10880 Malibu Point, Malibu, California, 90265</p>
                </div>
                <div class="right-payment">
                    <p>Cash on Delivery</p>
                </div>
            </div>
            <div class="order-details-part">
                <div class="order-details-part-title">
                    <p>Order details</p>
                </div>
            </div>
             <div class="details-inside">
                <div class="right-address">
                    <p>10880 Malibu Point, Malibu, California, 90265</p>
                </div>
                <div class="right-payment">
                    <p>1pc. Frappe na Masarap</p>
                </div>
            </div>
            <?php echo createButton(45, 150, "Cancel Order"); ?>
        </div>
    </div>
</div>
