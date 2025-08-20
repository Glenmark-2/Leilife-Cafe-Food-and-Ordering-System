<style>
.card {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  border-radius: 16px;
  background: linear-gradient(to bottom, #f2f2f2, #293b42);
  padding: clamp(6px, 1vw, 12px);
  box-shadow: 0 3px 10px rgba(0,0,0,0.12);
  width: 100%;
  max-width: 160px;  /* responsive limit */
  aspect-ratio: 3 / 4; /* keeps proportion instead of fixed height */
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 5px 14px rgba(0,0,0,0.22);
}

.card-img-top {
  width: 100%;
  height: 55%; /* responsive image size */
  object-fit: contain;
  margin: 0 auto;
  display: block;
}

.card-body {
  margin-top: auto;
  text-align: left;
}

.card-title {
  color: #fff;
  font-size: clamp(10px, 2vw, 14px);
  margin: 0;
}

.card-price {
  color: #fff;
  font-size: clamp(12px, 2.5vw, 16px);
  margin-top: 2px;
}

/* Responsive grid wrapper for cards */
.card-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 16px;
  justify-content: left;
  padding: 10px;
}

/* Smaller screens adjustments */
@media (max-width: 480px) {
  .card {
    max-width: 120px;
  }
  .card-title {
    font-size: 10px;
  }
  .card-price {
    font-size: 12px;
  }
}
</style>

<div class="card-container">
  <div class="card">
    <img src="/Leilife/public/assests/image 39.png" class="card-img-top" alt="Spanish Latte">
    <div class="card-body">
      <p class="card-title">Spanish Latte</p>
      <p class="card-price">75 PHP</p>
    </div>
  </div>
</div>
