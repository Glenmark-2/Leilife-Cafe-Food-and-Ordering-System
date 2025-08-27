<?php
function createButton($height, $width, $text, $id = "", $fontSize = 16) {
    $hoverColor = "#445566"; 
    $defaultColor = "#22333c";

    $style = "background-color: {$defaultColor}; color: white; 
              height: {$height}px; width: {$width}px; 
              border: none; border-radius: 25px; 
              cursor: pointer; font-size: {$fontSize}px;";

    $idAttribute = $id ? "id='{$id}'" : "";

    return "<button {$idAttribute} style='{$style}' 
                onmouseover=\"this.style.backgroundColor='{$hoverColor}'\" 
                onmouseout=\"this.style.backgroundColor='{$defaultColor}'\">{$text}</button>";
}?>

<!-- // Usage examples:
//include it first, then 

// Default font size (16px)
echo createButton(45, 150, "Default Btn");

// Custom font size (20px)
echo createButton(50, 200, "Big Text", "btn1", 20);

// With id and default font size
echo createButton(45, 360, "Create your Account", "create-btn"); -->


