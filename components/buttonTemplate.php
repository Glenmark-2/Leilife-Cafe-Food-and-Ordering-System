<?php
function createButton($height, $width, $text, $id = "") {
    $hoverColor = "#445566"; 
    $defaultColor = "#22333c";

    $style = "background-color: {$defaultColor}; color: white; 
              height: {$height}px; width: {$width}px; 
              border: none; border-radius: 25px; 
              cursor: pointer; font-size: 16px;";
    $idAttribute = $id ? "id='{$id}'" : "";
    return "<button {$idAttribute} style='{$style}' 
                onmouseover=\"this.style.backgroundColor='{$hoverColor}'\" 
                onmouseout=\"this.style.backgroundColor='{$defaultColor}'\">{$text}</button>";
}

// createButton(height, width, text, id)
// - height: button height in pixels
// - width: button width in pixels
// - text: label that appears inside the button
// - id (optional): HTML id attribute for targeting with JS/CSS
//
// Example:
// echo createButton(50, 200, "Click Me", "myBtn");

// how to apply it
//<?php include "../components/buttonTemplate.php";
// echo createButton(45, 360, "Create your Account","create-btn");
?>



