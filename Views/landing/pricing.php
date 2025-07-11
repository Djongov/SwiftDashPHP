<?php

use Components\Html;


$button_free = ($usernameArray) ? 'Proceed to API Keys' : 'Get Started Now';
$button_link = ($usernameArray) ? '/api-keys' : '/login?destination=/api-keys';
$pricing_plans = [
    'Free' => [
        'description' => 'No payment/credit card needed',
        'cost' => 0,
        'button' => $button_free,
        'button_link' => $button_link,
        'features' => [
            '1 API Key',
            FREE_TIER_DAILY_EXECUTION_LIMIT . ' API calls per day'
        ]
    ]
];

echo '<div class="container px-4 mx-auto mt-6">';
echo '<div class="text-center">';
echo '<div class="container px-4 mx-auto">';
echo '<div class="text-center">';
echo Html::badge('Pricing', $theme);
echo Html::h1('While we are running on a beta phase, we are offering our services for free but with a limit');
echo '</div>';
echo '<div class="flex flex-wrap flex-row justify-center -mx-4">';
foreach ($pricing_plans as $title => $pricing_arrays) {
    echo '<div class="p-4">';
        echo '<div class="flex flex-col pt-8 pb-8 h-full bg-gray-100 hover:bg-' . $theme . '-300 dark:bg-gray-900 dark:hover:bg-gray-700 rounded-md shadow-md hover:scale-105 transition duration-500">';
            echo '<div class="px-8">';
                echo Html::h2($title);
    echo '<p class="mb-6 text-coolGray-400 font-medium">' . $pricing_arrays['description'] . '</p>';
    echo '<div class="mb-6">';
    echo '<span class="relative -top-10 right-1 text-3xl text-coolGray-900 font-bold">$</span>';
    echo '<span class="text-6xl md:text-7xl text-coolGray-900 font-semibold tracking-tighter">' . $pricing_arrays['cost'] . '</span>';
    echo '<span class="inline-block ml-1 text-coolGray-500 font-semibold">/mo</span>';
    echo '</div>';
    echo '<a class="inline-block py-4 px-7 mb-8 w-full text-base md:text-lg leading-6 text-' . $theme . '-50 font-medium text-center bg-' . $theme . '-500 hover:bg-' . $theme . '-600 focus:ring-2 focus:ring-' . $theme . '-500 focus:ring-opacity-50 rounded-md shadow-sm" href="' . $pricing_arrays['button_link'] . '">' . $pricing_arrays['button'] . '</a>';
    echo '<ul class="self-start px-8">';
    foreach ($pricing_arrays['features'] as $feature) {
        echo '<li class="flex items-center mb-4 text-coolGray-500 font-medium">';
        echo '✅';
        echo '<span>' . $feature . '</span>';
        echo '</li>';
    }
    echo '</ul>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
