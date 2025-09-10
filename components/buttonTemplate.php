<?php
if (!function_exists('createButton')) {
    function createButton($height, $width, $text, $id = "", $fontSize = 16, $type = "button") {
        $hoverColor = "#445566"; 
        $defaultColor = "#22333c";

        $style = "background-color: {$defaultColor}; color: white; 
                  height: {$height}px; width: {$width}px; 
                  border: none; border-radius: 25px; 
                  cursor: pointer; font-size: {$fontSize}px;";

        $idAttribute = $id ? "id='{$id}'" : "";

        return "<button type='{$type}' {$idAttribute} style='{$style}' 
                    onmouseover=\"this.style.backgroundColor='{$hoverColor}'\" 
                    onmouseout=\"this.style.backgroundColor='{$defaultColor}'\">{$text}</button>";
    }
}
?>
