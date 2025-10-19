<div style="
  display: flex; 
  justify-content: center; 
  align-items: center; 
  min-height: 100vh; 
  background: #f5f5f0;
">
  <div style="
    display: flex; 
    background: #e0e0e0; 
    border-radius: 12px; 
    box-shadow: 0 3px 8px rgba(0,0,0,0.15); 
    max-width: 900px; 
    width: 100%; 
    overflow: hidden;
  ">
    <!-- Left Image -->
    <div style="flex: 1; min-width: 40%; display:flex; align-items:center; justify-content:left; background:#1e2d33;">
      <img src="\Leilife\public\assests\image 41.png" alt="Food" style="width:100%; height:100%; object-fit:cover;">
    </div>

    <!-- Right Content -->
    <div style="flex: 1.2; padding: 30px; display:flex; flex-direction:column; justify-content:space-between;">
      <div>
        <h2 style="font-size: 24px; font-weight: bold; margin-bottom: 30px; color:#000;text-align: left;">
          When Coffee Meets Good Food, Great Conversations Begin.
        </h2>
        <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px; color:#333; text-align: justify;">
          At Leilife Cafe and Resto, we believe every meal should be a moment to savor. 
          From freshly brewed coffee to hearty meals, we combine quality ingredients, 
          skilled preparation, and a warm ambiance to create the perfect dining experience 
          for every guest.
        </p>
      </div>
    <div style="display:flex; justify-content:flex-end;">
  <?php 
    // Using the createButton component:
    // height = 40px, width = 120px, text = "Explore", id = "exploreBtn"
    include "../components/buttonTemplate.php"; echo createButton(40, 120, "Explore", "exploreBtn"); 
  ?>
    </div>

    </div>
  </div>
</div>
