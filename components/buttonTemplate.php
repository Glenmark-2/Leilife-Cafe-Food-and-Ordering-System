<?php
if (!function_exists('createButton')) {
    function createButton($height, $width, $text, $id = "", $fontSize = 16, $type = "button", $attributes = []) {
        $hoverColor = "#445566"; 
        $defaultColor = "#22333c";

        $style = "background-color: {$defaultColor}; color: white; 
                  height: {$height}px; width: {$width}px; 
                  border: none; border-radius: 25px; 
                  cursor: pointer; font-size: {$fontSize}px;";

        $idAttribute = $id ? "id='{$id}'" : "";

        // initialize string for extra attributes
        $extraAttrs = "";

        // convert $attributes array to HTML string
        foreach ($attributes as $key => $value) {
            $safeValue = htmlspecialchars($value, ENT_QUOTES);
            $extraAttrs .= " {$key}=\"{$safeValue}\"";
        }

        return "<button type='{$type}' {$idAttribute} {$extraAttrs} style='{$style}' 
                    onmouseover=\"this.style.backgroundColor='{$hoverColor}'\" 
                    onmouseout=\"this.style.backgroundColor='{$defaultColor}'\">{$text}</button>";
    }
}
?>
