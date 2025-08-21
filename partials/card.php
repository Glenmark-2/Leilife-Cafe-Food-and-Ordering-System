<style>
.card {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  border-radius: 16px;
  background: linear-gradient(to bottom, #f2f2f2, #293b42);
  padding: 1em; /* relative padding */
  box-shadow: 0 3px 10px rgba(0,0,0,0.12);
  width: 100%;
  max-width: 160px;
  aspect-ratio: 3 / 4; /* maintain proportion */
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 5px 14px rgba(0,0,0,0.22);
}

.card-img-top {
  width: 100%;
  height: auto;
  max-height: 55%;
  object-fit: contain;
  margin-bottom: 0.5em;
}

.card-body {
  margin-top: auto;
  text-align: left;
  padding: 0.25em 0;
}

.card-title {
  color: #fff;
  font-size: 1em; /* relative font size */
  margin: 0;
}

.card-price {
  color: #fff;
  font-size: 0.9em; /* relative font size */
  margin-top: 0.25em;
}

/* Mobile adjustments for single card */
@media (max-width: 520px) {
  .card {
    max-width: 140px;    /* shrink card */
    padding: 0.5em;      /* shrink padding */
    border-radius: 12px;
  }

  .card-img-top {
    max-height: 50%;     /* shrink image */
  }

  .card-title {
    font-size: 0.85em;   /* shrink title text */
  }

  .card-price {
    font-size: 0.8em;    /* shrink price text */
  }

  .card-body {
    padding: 0.2em 0;    /* shrink inner spacing */
  }
}
</style>

<div class="card">
  <img src="/Leilife/public/assests/image 39.png" class="card-img-top" alt="Spanish Latte">
  <div class="card-body">
    <p class="card-title">Spanish Latte</p>
    <p class="card-price">75 PHP</p>
  </div>
</div>
