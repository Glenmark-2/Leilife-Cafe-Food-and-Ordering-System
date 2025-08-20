<style> .category-buttons {
   
    display: flex;       /* arrange children in a row */
    gap: 10px;           /* space between buttons */
}

.card-button {
    margin-top: 10px;
}
.card-button button {
    
    padding: 10px 20px;
    border: none;
    background: #22333B;
    color: white;
    cursor: pointer;
    border-radius: 50px;
    transition: 0.3s ease;
}
.category-buttons button {
    width: 120px;
    height: 30px;
    padding: 10px 20px;
    border: none;
    background: #5E503F;
    color: white;
    cursor: pointer;
    border-radius: 50px;
    transition: 0.3s ease;
}




</style>
<?php

echo '
    
      <button class=".$class">'.$Text.'</button>
    
';