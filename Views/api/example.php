<?php declare(strict_types=1);

use App\Api\Response;

sleep(1);

//Response::output('This is a test response', 400);
//Response::output('This is a test response');

// Run htmlspecialchars on $_POST with array_map intead of array_filter to avoid removing empty values
$_POST = array_map(function ($value) {
    return htmlspecialchars($value);
}, $_POST);

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
