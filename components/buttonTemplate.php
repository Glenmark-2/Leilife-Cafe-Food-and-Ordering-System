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
?>
