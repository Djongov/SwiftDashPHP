<?php declare(strict_types=1);

use Components\Html;
use App\Charts\Charts;

echo Html::h1('Charts', true);

echo Html::p('This is a chart page. Here is how we can use the charting abilities built into the system.', ['text-center']);

echo Html::h2('Image charts (Quickchart.io)');

echo Html::p('We can control which quickchart host we use by setting the QUICKCHART_HOST environment variable. Default is quickchart.io but you can host your own.');

echo Html::p('shortUrls are used to shorten the URLs for the images so they can be used in emails for example. Also shortURLs are higher quality and are also only available at quickchart.io. ' . Html::a("Read more", "https://quickchart.io/documentation/usage/short-urls-and-templates/#:~:text=To%20generate%20a%20short%20URL,.io%2Fchart%2Fcreate%20.&text=Go%20to%20the%20URL%20in,URLs%20to%20become%20active%20globally.", $theme, '_blank'));

// Let's use this general data for all the charts
$chartData = [
    'January' => rand(1, 1000),
    'February' => rand(1, 1000),
    'March' => rand(1, 1000),
    'April' => rand(1, 1000),
    'May' => rand(1, 1000),
    'June' => rand(1, 1000),
];

echo '<div class="my-12 flex flex-wrap flex-row justify-center items-center">';
    // Pie chart
    echo '<div class="w-full md:w-1/2 lg:w-1/3 p-2">';

        echo Html::h3('Pie chart');

        echo Html::p('This is a pie chart.');

        echo Charts::doughnutOrPieChart('pie', 'Pie chart', array_keys($chartData), array_values($chartData));

    echo '</div>';
    // Doughnut chart
    echo '<div class="w-full md:w-1/2 lg:w-1/3 p-2">';

        echo Html::h3('Doughnut chart');

        echo Html::p('This is a doughnut chart.');

        echo Charts::doughnutOrPieChart('doughnut', 'Doughnut chart',array_keys($chartData), array_values($chartData));

    echo '</div>';
    // Radial gauge
    echo '<div class="w-full md:w-1/2 lg:w-1/3 p-2">';

        echo Html::h3('Radial gauge');

        echo Html::p('This is a Radial gauge.');

        $min = 0;

        $max = 100;

        $randomNumber = rand($min, $max);

        echo Charts::radialGauge('random number ouf of ' . $max, $randomNumber, [$min, $max]);

    echo '</div>';
    // Line chart
    echo '<div class="w-full md:w-1/2 lg:w-1/3 p-2">';

        echo Html::h3('Line chart');

        echo Html::p('This is a line chart.');

        $lineChartData = [
            'labels' => array_keys($chartData),
            'datasets' => [
                [
                    'label' => 'User 1 data',
                    'data' => array_values($chartData)
                ],
                [
                    'label' => 'User 2 data',
                    // for the second user we will just use the same data but in reverse order
                    'data' => array_reverse(array_values($chartData))
                ]
            ]
        ];

        echo Charts::lineChart('Line chart', $lineChartData, 400, 200, 'svg');

    echo '</div>';
echo '</div>';

echo Html::h2('Interactive charts (Chart.js)');

echo Html::p('We can spawn interactive charts using Chart.js. This is a JavaScript library that allows us to create charts and graphs. We are passing the data to the JavaScript by using hidden inputs with the name "autoload".');

echo '<div id="doughnut-limits-holder" class="my-12 flex flex-wrap flex-row justify-center items-center">';
    // initiate an array that will pass the following data into hidden inputs so Javascript can have access to this data on page load and draw the charts
    $chartsArray = [
        [
            'type' => 'doughnut',
            'data' => [
                'parentDiv' => 'doughnut-limits-holder',
                'title' => 'Gauge chart',
                'width' => 180,
                'height' => 180,
                'labels' => ['used', 'remaining'],
                'data' => [$randomNumber, $max]
            ]
        ],
        [
            'type' => 'piechart',
            'data' => [
                'parentDiv' => 'doughnut-limits-holder',
                'title' => 'Pie Chart',
                'width' => 300,
                'height' => 300,
                'labels' => array_keys($chartData),
                'data' => array_values($chartData)
            ]
        ],
        [
            'type' => 'donutChart',
            'data' => [
                'parentDiv' => 'doughnut-limits-holder',
                'title' => 'donut Chart',
                'width' => 300,
                'height' => 300,
                'labels' => array_keys($chartData),
                'data' => array_values($chartData)
            ]
        ],
        [
            'type' => 'linechart',
            'data' => [
                'parentDiv' => 'doughnut-limits-holder',
                'title' => 'Line Chart',
                'width' => 400,
                'height' => 200,
                'labels' => array_keys($chartData),
                'datasets' => [
                    [
                        'label' => 'User 1 data',
                        'data' => array_values($chartData)
                    ],
                    [
                        'label' => 'User 2 data',
                        'data' => array_reverse(array_values($chartData))
                    ]
                ]
            ]
        ]
    ];
    // Now go through them and create an input hidden for each
    foreach ($chartsArray as $array) {
        echo '<input type="hidden" name="autoload" value="' . htmlspecialchars(json_encode($array)) . '" />';
    }
echo '</div>';
