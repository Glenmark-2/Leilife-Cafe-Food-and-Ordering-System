<?php
/**
 * ProductCard Component
 *
 * A small reusable card that displays:
 *  - a logo in the center
 *  - a title at the bottom
 *
 * Usage:
 * include 'components/product-card.php';
 * echo productCard("Meal", "meal.png");
 * echo productCard("Drinks", "drink.png", 140, "#e0f7fa");
 *
 * @param string $title - The text shown at the bottom (e.g., "Meal")
 * @param string $logo  - The image path for the logo
 * @param int    $size  - (optional) Size of the card (default: 120px)
 * @param string $bgcolor - (optional) Background color (default: #fdf9f5)
 * @param string $color - (optional) Background color (default: #171717ff)
 */
function productCard($title, $logo, $size = 120, $bgcolor = "#fdf9f5", $color="#171717ff") {
    return "
    <div style='
      width: {$size}px;
      height: {$size}px;
      background: {$bgcolor};
      color: {$color};
      border-radius: 16px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      box-shadow: 0 3px 8px rgba(0,0,0,0.12);
      margin: 10px;
    '>
      <img src='{$logo}' alt='{$title}' style='
        width: 50%;
        height: auto;
        object-fit: contain;
        margin-bottom: 8px;
      '>
      <span style='font-size: 14px; font-weight: 500; color: {$color};'>{$title}</span>
    </div>
    ";
}
?>
