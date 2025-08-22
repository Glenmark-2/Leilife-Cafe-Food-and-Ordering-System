<?php
/**
 * InfoCard Component
 *
 * Usage:
 * echo infoCard("🍽️", "Fresh & Flavorful Dishes", "We use only the freshest ingredients...");
 * echo infoCard("☕", "Perfectly Brewed Coffee", "Our skilled baristas...");
 * echo infoCard("❤️", "A Taste to Remember", "Enjoy hearty meals...");
 *
 * @param string $logo - Emoji or <img> tag (logo/icon)
 * @param string $title - The heading text
 * @param string $text - The description text
 */
function infoCard($logo, $title, $text) {
    return "
    <div style=\"
      display: flex; 
      flex-direction: column; 
      align-items: center; 
      text-align: center; 
      background: #eaeaea; 
      padding: clamp(16px, 4vw, 24px); 
      border-radius: 16px; 
      width: 100%; 
      max-width: 280px; 
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      margin: 10px auto;
      flex: 1 1 240px;
    \">
      <!-- Logo -->
      <div style='
        font-size: clamp(32px, 6vw, 48px); 
        margin-bottom: clamp(10px, 3vw, 16px);
      '>
        {$logo}
      </div>

      <!-- Title -->
      <h3 style=\"
        margin: 0 0 clamp(8px, 2vw, 12px) 0; 
        font-size: clamp(16px, 2.5vw, 18px); 
        color: #171717;
      \">
        {$title}
      </h3>

      <!-- Text -->
      <p style=\"
        margin: 0; 
        font-size: clamp(13px, 2.5vw, 14px); 
        color: #333; 
        line-height: 1.5;
      \">
        {$text}
      </p>
    </div>
    ";
}
?>
