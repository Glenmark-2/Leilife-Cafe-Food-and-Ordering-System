<?php
function infoCard($logo, $title, $text) {
    return "
    <div class='info-card' style=\"
      display: flex; 
      flex-direction: column; 
      align-items: center; 
      text-align: center; 
      background: #f9f9f9; 
      padding: clamp(16px, 4vw, 22px); 
      border-radius: 16px; 
      box-shadow: 0 2px 6px rgba(0,0,0,0.12);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    \" 
      onmouseover=\"this.style.transform='translateY(-4px)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.2)';\" 
      onmouseout=\"this.style.transform='none'; this.style.boxShadow='0 2px 6px rgba(0,0,0,0.12)';\">
      
      <div style=\"
        font-size: clamp(36px, 7vw, 50px); 
        margin-bottom: clamp(12px, 3vw, 18px);
      \">
        {$logo}
      </div>

      <h3 style=\"
        margin: 0 0 clamp(10px, 2.5vw, 14px) 0; 
        font-size: clamp(18px, 3vw, 20px); 
        font-weight: 600;
        color: #222;
      \">
        {$title}
      </h3>

      <p style=\"
        margin: 0; 
        font-size: clamp(14px, 2.5vw, 15px); 
        color: #555; 
        line-height: 1.6;
        max-width: 90%;
      \">
        {$text}
      </p>
    </div>
    ";
}
?>
