<style>
.card {
  display: flex;
  flex-direction: column;
  border-radius: 30px;
   background-image: linear-gradient(to bottom, #f2f2f2, #293b42);
  padding: 20px 30px;
  box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
  max-width: 220px;
}


.card-img-top {
  width: 100%;
  height: auto;
  object-fit: contain;
  border-radius: 8px;
  margin-bottom: 10px;
}

.card-body {
  display: flex;
  flex-direction: column;
  justify-content: flex-end; /* pushes content to the bottom */
  flex: 1; /* fill remaining height */
}

.card-info {
  text-align: left;
  margin-top: auto; /* ensures it stays at the bottom */
}

.card-title {
  color: white;
  margin: 0;
}

.card-price {
  color: white;
  margin: 3px 0 0 0;
}

</style>

<div class="card mb-3">
  <img src="\Leilife\public\assests\image 39.png" class="card-img-top" alt="...">
  <div class="card-body">
    <div class="card-info">
      <p class="card-title">Spanish latte</p>
      <p class="card-price">75 PHP</p>
    </div>
  </div>
</div>


