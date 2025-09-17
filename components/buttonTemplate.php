<?php
if (!function_exists('createButton')) {
    function createButton(
        $height = 40,
        $width = 120,
        $text = "Button",
        $id = "",
        $fontSize = 16,
        $type = "button",
        $attributes = []
    ) {
        $defaultGradient = "linear-gradient(135deg, #22333c, #5e4f3e)";
        $hoverGradient   = "linear-gradient(135deg, #5e4f3e, #22333c)";

        $style = "
            background: {$defaultGradient};
            color: white;
            height: {$height}px;
            width: {$width}px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: {$fontSize}px;
            font-weight: 500;
            box-shadow: 0 3px 6px rgba(0,0,0,0.15);
            transition: all 0.25s ease;
        ";

        $idAttribute = $id ? "id='{$id}'" : "";

        // build extra attributes
        $extraAttrs = "";
        foreach ($attributes as $key => $value) {
            $safeValue = htmlspecialchars($value, ENT_QUOTES);
            $extraAttrs .= " {$key}=\"{$safeValue}\"";
        }

        return "
            <button type='{$type}' {$idAttribute} {$extraAttrs} style='{$style}'
                onmouseover=\"this.style.background='{$hoverGradient}'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(0,0,0,0.2)'\"
                onmouseout=\"this.style.background='{$defaultGradient}'; this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 6px rgba(0,0,0,0.15)'\">
                {$text}
            </button>
        ";
    }
}
?>
