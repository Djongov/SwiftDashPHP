<?php

declare(strict_types=1);

use App\Api\Response;

// Run htmlspecialchars on $_POST (nested arrays too, e.g. multi-selects) instead of array_filter to keep empty values
$escape = function ($value) use (&$escape) {
    return is_array($value) ? array_map($escape, $value) : htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$_POST = array_map($escape, $_POST);

if (isset($_POST['terms']) && $_POST['terms'] === "0") {
    Response::output('You must agree to the terms and conditions', 409);
}

if (isset($_POST['return']) && $_POST['return'] === 'json') {
    Response::output(json_encode($_POST));
} else {
    // Let's draw a square div with the color of $_POST['colors'] if set
    if (isset($_POST['colors'])) {
        $color = $_POST['colors'];
        echo "<div class='my-2 w-28 h-28 bg-$color-500 shadow-md rounded-md hoverbright'></div>";
    }
    dd($_POST);
}
