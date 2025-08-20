<style>
.card {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  border-radius: 16px;  /* smaller corners */
  background: linear-gradient(to bottom, #f2f2f2, #293b42);
  padding: 10px;  /* smaller padding */
  box-shadow: 0 3px 10px rgba(0,0,0,0.12);
  width: 145px;   /* reduced width */
  height: 190px;  /* reduced height */
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 5px 14px rgba(0,0,0,0.22);
}

.card-img-top {
  width: 100%;
  max-height: 110px; /* scaled image */
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
  font-size: 5px; /* smaller text */
  margin: 0;
}

.card-price {
  color: #fff;
  font-size: 11px; /* smaller price */
  margin-top: 2px;
}

</style>

<div class="card">
  <img src="/Leilife/public/assests/image 39.png" class="card-img-top" alt="Spanish Latte">
  <div class="card-body">
    <p class="card-title">Spanish Latte</p>
    <p class="card-price">75 PHP</p>
  </div>
</div>


