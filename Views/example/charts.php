<?php

declare(strict_types=1);

use Components\Html;
use App\Charts\Charts;

echo Html::h1('Charts', true);

echo Html::p('This is a chart page. Here is how we can use the charting abilities built into the system.', ['text-center']);

echo Html::h2('Image charts (Quickchart.io)');

echo Html::p('We can control which quickchart host we use by setting the QUICKCHART_HOST environment variable. Default is quickchart.io but you can host your own.');

echo Html::p('shortUrls are used to shorten the URLs for the images so they can be used in emails for example. Also shortURLs are higher quality and are also only available at quickchart.io. ' . Html::a("Read more", "https://quickchart.io/documentation/usage/short-urls-and-templates/#:~:text=To%20generate%20a%20short%20URL,.io%2Fchart%2Fcreate%20.&text=Go%20to%20the%20URL%20in,URLs%20to%20become%20active%20globally.", $theme, '_blank'));

// Let's use this general data for all the charts
$minRandomChartData = 1;
$maxRandomChartData = 1000;

$chartData = [
    'January' => rand($minRandomChartData, $maxRandomChartData),
    'February' => rand($minRandomChartData, $maxRandomChartData),
    'March' => rand($minRandomChartData, $maxRandomChartData),
    'April' => rand($minRandomChartData, $maxRandomChartData),
    'May' => rand($minRandomChartData, $maxRandomChartData),
    'June' => rand($minRandomChartData, $maxRandomChartData),
];

$chartDataTwo = [
    'January' => rand($minRandomChartData, $maxRandomChartData),
    'February' => rand($minRandomChartData, $maxRandomChartData),
    'March' => rand($minRandomChartData, $maxRandomChartData),
    'April' => rand($minRandomChartData, $maxRandomChartData),
    'May' => rand($minRandomChartData, $maxRandomChartData),
    'June' => rand($minRandomChartData, $maxRandomChartData),
];

$min = 0;

$max = 100;

$randomNumber = rand($min, $max);

$chart = new Charts();

echo '<div class="my-12 flex flex-wrap flex-row justify-center items-center">';
    // Pie chart
    echo '<div>';

        echo $chart->doughnutOrPieChart('pie', 'Pie Chart', array_keys($chartData), array_values($chartData));

    echo '</div>';
    // Doughnut chart
    echo '<div>';

        echo $chart->doughnutOrPieChart('donut', 'donut Chart', array_keys($chartData), array_values($chartData));

    echo '</div>';
    // Bar gauge
    echo '<div>';

        echo $chart->barChart('Bar chart', array_keys($chartData), array_values($chartData));

    echo '</div>';
    // Radial gauge
    echo '<div>';

        echo $chart->radialGauge('random number ouf of ' . $max, $randomNumber, [$min, $max]);

    echo '</div>';
    // Line chart
    echo '<div>';

        $lineChartData = [
            'labels' => array_keys($chartData),
            'datasets' => [
                [
                    'label' => 'Data set 1',
                    'data' => array_values($chartData)
                ],
                [
                    'label' => 'Data set 2',
                    'data' => array_values($chartDataTwo)
                ]
            ]
        ];

        echo $chart->lineChart('Line chart', $lineChartData, 400, 200, 'svg');

        echo '</div>';
        echo '</div>';

        echo Html::h2('Interactive charts (Chart.js)');

        echo Html::p('We can spawn interactive charts using Chart.js. This is a JavaScript library that allows us to create charts and graphs. We are passing the data to the JavaScript by using hidden inputs with the name "autoload".');

        echo '<div id="doughnut-limits-holder" class="my-12 flex flex-wrap flex-row justify-center items-center">';
    // initiate an array that will pass the following data into hidden inputs so Javascript can have access to this data on page load and draw the charts
        $chartsArray = [
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
        'type' => 'donutchart',
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
        'type' => 'barchart',
        'data' => [
            'parentDiv' => 'doughnut-limits-holder',
            'title' => 'Bar chart',
            'width' => 300,
            'height' => 300,
            'labels' => array_keys($chartData),
            'data' => array_values($chartData)
        ]
        ],
        [
        'type' => 'gaugechart',
        'data' => [
            'parentDiv' => 'doughnut-limits-holder',
            'title' => 'random number ouf of ' . $max,
            'width' => 250,
            'height' => 250,
            'data' => [$randomNumber, $max]
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
                    'label' => 'Data set 1',
                    'data' => array_values($chartData)
                ],
                [
                    'label' => 'Data set 2',
                    'data' => array_values($chartDataTwo)
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
