<style>
.product-card {
  display: flex;
  align-items: center;
  background: #fdf9f5;
  border-radius: 16px;
  padding: 12px 18px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.12);
  max-width: 650px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 14px rgba(0,0,0,0.2);
}

.product-image {
  width: 120px;
  height: auto;
  border-radius: 14px;
  object-fit: contain;
  background: #fff;
}

.product-info {
  flex: 1;
  margin-left: 18px;
  display: flex;
  flex-direction: column;
  height: 100%;
}

/* Wrapper to balance title center + actions bottom */
.info-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center; /* centers title vertically */
}

.product-title {
  font-size: 15px;
  font-weight: 600;
  margin: 0;
  color: #222;
  text-align: center;
}

.product-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 10px; /* spacing from title */
}

.product-price {
  font-size: 14px;
  font-weight: 600;
  color: #111;
}

.buy-btn {
  padding: 6px 16px;
  border: none;
  border-radius: 20px;
  background: #293b42;
  color: #fff;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s ease;
}

.buy-btn:hover {
  background: #1d2a2f;
}
</style>

<div class="product-card">
  <img src="/Leilife/public/assests/image 3 (2).png" 
       alt="Spanish Latte + Classic Tiramisu" 
       class="product-image">
  <div class="product-info">
    <div class="info-wrapper">
      <p class="product-title">Spanish Latte + Classic Tiramisu</p>
    </div>
    <div class="product-actions">
      <p class="product-price">₱ 99.00</p>
      <button class="buy-btn">Buy</button>
    </div>
  </div>
</div>
