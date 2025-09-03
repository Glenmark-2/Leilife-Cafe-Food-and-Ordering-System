<?php
function orderStatusBadge($status) {
    $colors = [
        "Pending" => ["bg" => "#FFE5B4", "border" => "#FFA500", "color" => "#FFA500"],
        "Preparing" => ["bg" => "#B0E0E6", "border" => "#0000FF", "color" => "#0000FF"],
        "Completed" => ["bg" => "#90EE90", "border" => "#008000", "color" => "#008000"],
        "Cancelled" => ["bg" => "#FFB6B6", "border" => "#FF0000", "color" => "#FF0000"],
        "Out for Delivery" => ["bg" => "#FFFACD", "border" => "#FFD700", "color" => "#FFD700"]
    ];

    $c = $colors[$status] ?? ["bg" => "gray", "border" => "black", "color" => "black"];

    return "<div style='padding:5px 20px; background-color:{$c['bg']}; border-radius:25px; border:1px solid {$c['border']}; color:{$c['color']}; display:inline-block;'>$status</div>";
}

?>
