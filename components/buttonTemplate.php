<?php
if (!function_exists('createButton')) {
    function createButton(
        $height = 42,
        $width = 140,
        $text = "Button",
        $id = "",
        $fontSize = 16,
        $type = "button",
        $attributes = []
    ) {
        $defaultColor = "#a8927c"; // beige base
        $hoverColor   = "#5e4f3e"; // warm brown
        $activeColor  = "#0a0907"; // almost black
        $defaultText  = "#ffffffff"; // dark text on beige
        $hoverText    = "#ddd8ba"; // cream text on brown
        $activeText   = "#a8927c"; // beige text on black

        // Check if disabled
        $isDisabled = isset($attributes['disabled']) && $attributes['disabled'] == 'disabled';
        $disabledStyle = $isDisabled 
            ? "opacity:0.5; cursor:not-allowed;" 
            : "";

        $style = "
            background: {$defaultColor};
            color: {$defaultText};
            height: {$height}px;
            width: {$width}px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: {$fontSize}px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.15);
            transition: all 0.25s ease-in-out;
            {$disabledStyle}
        ";

        $idAttribute = $id ? "id='{$id}'" : "";

        $extraAttrs = "";
        foreach ($attributes as $key => $value) {
            $safeValue = htmlspecialchars($value, ENT_QUOTES);
            $extraAttrs .= " {$key}=\"{$safeValue}\"";
        }

        return "
            <button type='{$type}' {$idAttribute} {$extraAttrs} style='{$style}'
                " . ($isDisabled ? "" : "
                onmouseover=\"this.style.background='{$hoverColor}'; this.style.color='{$hoverText}'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(0,0,0,0.2)'\"
                onmouseout=\"this.style.background='{$defaultColor}'; this.style.color='{$defaultText}'; this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 6px rgba(0,0,0,0.15)'\"
                onmousedown=\"this.style.background='{$activeColor}'; this.style.color='{$activeText}'; this.style.transform='translateY(1px)'\"
                onmouseup=\"this.style.background='{$hoverColor}'; this.style.color='{$hoverText}'; this.style.transform='translateY(-2px)'\"
                ") . ">
                {$text}
            </button>
        ";
    }
}
?>
