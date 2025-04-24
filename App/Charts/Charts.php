<?php

declare(strict_types=1);

namespace App\Charts;

use App\Charts\QuickChart;

class Charts
{
    // Format values are: svg, png, jpeg, webp
    // Radial Gauge good for measuring percentages or values out of max values
    public function radialGauge(string $label, int $data, array $range = [0, 100], string|int $width = 250, string|int $height = 250, string $format = 'svg', bool $shortUrl = false): string
    {
        $chart = new QuickChart(
            [
            'width' => $width,
            'height' => $height,
            'format' => $format,
            ]
        );
        // Let's calculate how much of the range we are at, percentage wise
        $percentage = floor(($data / $range[1]) * 100);
        // Let's set the background color based on the percentage
        // If we are between 0 and 50, we are green, from 50 to 75 we are orange, from 75 to 80 we are crimson, from 80 to 100 we are red
        // Determine background color dynamically
        if ($percentage >= 0 && $percentage <= 25) {
            $background = 'getGradientFillHelper("horizontal", ["lime", "green"])';
        } elseif ($percentage > 25 && $percentage < 50) {
            $background = 'getGradientFillHelper("horizontal", ["yellow", "green"])';
        } elseif ($percentage >= 50 && $percentage < 75) {
            $background = 'getGradientFillHelper("horizontal", ["orange", "yellow"])';
        } elseif ($percentage >= 75 && $percentage <= 85) {
            $background = 'getGradientFillHelper("horizontal", ["yellow", "crimson"])';
        } elseif ($percentage > 85 && $percentage <= 100) {
            $background = 'getGradientFillHelper("horizontal", ["crimson", "red"])';
        } else {
            $background = 'getGradientFillHelper("horizontal", ["green", "lime"])';
        }

        // Create configuration array
        $config = [
            "type" => "radialGauge",
            "data" => [
                "datasets" => [[
                    "data" => [$data],
                    "backgroundColor" => null, // Placeholder, we'll replace it later
                    "borderWidth" => 1,
                    "borderColor" => "rgba(0,0,0, 0.95)",
                    "label" => $label
                ]]
            ],
            "options" => [
                "domain" => $range,
                "trackColor" => "rgba(119,119,119, 0.95)", // Gray remainder color
                "trackBorderWidth" => 2,
                "roundedCorners" => false,
                "legend" => [],
                "title" => [
                    "display" => true,
                    "text" => $label
                ],
                "centerPercentage" => 80,
                "centerArea" => [
                    "fontSize" => 16,
                    "displayText" => true,
                    "text" => "$data / {$range[1]}",
                    "subText" => "($percentage%)",
                    "padding" => 1,
                    "fontColor" => "#777",
                    "fontWeight" => "bold"
                ],
                "responsive" => true,
                "title" => [
                    "display" => true,
                    "fontSize" => 18,
                    "text" => $label,
                    "color" => "#777",
                    "align" => "center",
                    "position" => "top",
                    "fullSize" => false,
                    "fontWeight" => "bold"
                ],
                "legend" => [
                    "display" => false,
                    "position" => "right",
                    "align" => "top",
                    "labels" => [
                        "fontColor" => "#777",
                        "fontStyle" => "bold",
                        "fontSize" => 14,
                        "padding" => 12
                    ]
                ]
            ]
        ];

        // Convert config to JSON
        $jsonConfig = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Manually inject `getGradientFillHelper(...)` into JSON
        $jsonConfig = str_replace('"backgroundColor":null', '"backgroundColor":' . $background, $jsonConfig);

        $chart->setConfig($jsonConfig);

        return ($shortUrl) ?  '<figure class="m-1"><img src="' . $chart->getShortUrl() . '" title="' . $label . '" alt="' . $label . '" width="' . $width . '" height="' . $height . '"  /></figure>' : '<figure class="m-1"><img src="' . $chart->getUrl() . '" title="' . $label . '" alt="' . $label . '" width="' . $width . '" height="' . $height . '" /></figure>';
    }
    // Donut or Pie chart in one
    public function doughnutOrPieChart(string $type, string $title, array $labels, array $data, string|int $width = 300, string|int $height = 300, string $format = 'svg', bool $shortUrl = false): string
    {
        $chart = new QuickChart(
            [
            'width' => $width,
            'height' => $height,
            'format' => $format,
            ]
        );

        // Convert labels array to JSON-safe format
        $labels_json = json_encode($labels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Background color array (kept as JS array)
        $background_color_string = '[
            "rgba(54, 162, 235, 1)",  // blue
            "rgba(75, 192, 192, 1)",  // green
            "rgba(255, 99, 132, 1)",  // red
            "rgba(255, 159, 64, 1)",  // orange
            "rgba(153, 102, 255, 1)", // purple
            "rgba(255, 206, 86, 1)",  // yellow
            "rgba(255, 0, 0, 1)",     // bright red
            "rgba(0, 255, 255, 1)",   // cyan
            "rgba(255, 0, 255, 1)",   // magenta
            "rgba(128, 128, 128, 1)"  // grey
        ]';

        // Chart configuration as an array
        $config = [
            "type" => $type,
            "data" => [
                "labels" => json_decode($labels_json), // Decode to prevent double encoding
                "datasets" => [[
                    "label" => $title,
                    "backgroundColor" => null, // Placeholder
                    "data" => $data,
                    "borderColor" => "rgba(0,0,0, 0.95)",
                    "borderWidth" => 0,
                    "weight" => 600,
                    "pointBackgroundColor" => null // Placeholder
                ]]
            ],
            "options" => [
                "responsive" => true,
                "title" => [
                    "display" => true,
                    "fontSize" => 20,
                    "text" => $title,
                    "color" => "#777",
                    "align" => "center",
                    "position" => "top",
                    "fullSize" => true
                ],
                "legend" => [
                    "display" => true,
                    "position" => "top",
                    "align" => "top",
                    "labels" => [
                        "fontColor" => "#777",
                        "fontStyle" => "bold",
                        "fontSize" => 12,
                        "padding" => 12
                    ]
                ],
                "plugins" => [
                    "doughnutlabel" => [
                        "labels" => [[
                            "text" => array_sum($data),
                            "font" => [
                                "size" => 30,
                                "family" => "Arial, Helvetica, sans-serif",
                                "weight" => "bold"
                            ],
                            "backgroundColor" => "green",
                            "color" => "#777"
                        ]]
                    ],
                    "datalabels" => [
                        "anchor" => "center",
                        "align" => "center",
                        "color" => "white",
                        "backgroundColor" => "black",
                        "borderColor" => "black",
                        "borderWidth" => 1,
                        "borderRadius" => 6,
                        "font" => [
                            "weight" => "bold",
                            "size" => 12
                        ]
                    ]
                ]
            ]
        ];

        // Convert config to JSON
        $jsonConfig = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Manually inject JavaScript expressions
        $jsonConfig = str_replace('"backgroundColor":null', '"backgroundColor":' . $background_color_string, $jsonConfig);
        $jsonConfig = str_replace('"pointBackgroundColor":null', '"pointBackgroundColor": function(context) { var index = context.dataIndex; var value = context.dataset.data[index]; return value === \'DenyList\' ? \'green\' : \'blue\'; }', $jsonConfig);

        $chart->setConfig($jsonConfig);


        return ($shortUrl) ?  '<figure class="m-2"><img src="' . $chart->getShortUrl() . '" title="' . $title . '" alt="' . $title . '" width="' . $width . '" height="' . $height . '" /></figure>' : '<figure class="m-2"><img src="' . $chart->getUrl() . '" title="' . $title . '" alt="' . $title . '" width="' . $width . '" height="' . $height . '" /></figure>';
    }
    public function lineChart(string $title, array $data, string|int $width, string|int $height, string $format, bool $shortUrl = false): string
    {
        $chart = new QuickChart(
            [
            'width' => $width,
            'height' => $height,
            'format' => $format,
            ]
        );

        // Background color array
        $backgroundColorArray = [
            'rgba(54, 162, 235, 1)',    // blue
            'rgba(75, 192, 192, 1)',    // green
            'rgba(255, 99, 132, 1)',    // red
            'rgba(255, 159, 64, 1)',    // orange
            'rgba(153, 102, 255, 1)',   // purple
            'rgba(255, 206, 86, 1)',    // yellow
            'rgba(255, 0, 0, 1)',       // bright red
            'rgba(0, 255, 255, 1)',     // cyan
            'rgba(255, 0, 255, 1)',     // magenta
            'rgba(128, 128, 128, 1)',   // grey
            'rgba(0, 128, 0, 1)',       // greenish
            'rgba(255, 165, 0, 1)',     // orange-yellow
            'rgba(0, 0, 255, 1)',       // pure blue
            'rgba(255, 140, 0, 1)',     // dark orange
            'rgba(148, 0, 211, 1)',     // dark violet
            'rgba(255, 69, 0, 1)',      // red-orange
            'rgba(0, 255, 0, 1)',       // pure green
            'rgba(255, 215, 0, 1)',     // gold
            'rgba(0, 255, 127, 1)',     // spring green
            'rgba(255, 20, 147, 1)',    // deep pink
        ];

        // Get datasets
        $datasets = $data['datasets'];

        // Ensure that datasets is an array
        if (!is_array($datasets)) {
            $datasets = [];
        }

        // Calculate background colors dynamically based on the number of datasets
        $backgroundColors = array_slice($backgroundColorArray, 0, count($datasets));

        // Apply dynamic color and properties to each dataset
        foreach ($datasets as $index => &$dataset) {
            $dataset['backgroundColor'] = $backgroundColors[$index];
            $dataset['borderColor'] = $backgroundColors[$index];
            $dataset['fill'] = false;  // Set fill to false for line chart
            $dataset['tension'] = 0.1;  // Set the tension for smoother lines
        }

        // Prepare chart configuration as an array
        $config = [
            "type" => "line",
            "data" => [
                "labels" => $data['labels'],  // Labels for the chart
                "datasets" => $datasets  // Datasets with dynamic background and border colors
            ],
            "options" => [
                "responsive" => true,
                "title" => [
                    "display" => true,
                    "fontSize" => 16,
                    "text" => $title,
                    "color" => "black",
                    "align" => "center",
                    "position" => "top",
                    "fullSize" => true,
                    "fontWeight" => "bold"
                ],
                "legend" => [
                    "display" => true,
                    "position" => "top",
                    "align" => "center",
                    "fontSize" => 9,
                    "labels" => [
                        "padding" => 20,
                        "color" => "black",
                        "fontSize" => 12,
                        "borderWidth" => 1
                    ]
                ],
                "scales" => [
                    "yAxes" => [[
                        "ticks" => [
                            "beginAtZero" => true
                        ]
                    ]]
                ]
            ]
        ];

        // Convert the config array to JSON with proper encoding
        $jsonConfig = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Set the configuration for the chart
        $chart->setConfig($jsonConfig);


        return ($shortUrl) ?  '<figure class="m-1"><img src="' . $chart->getShortUrl() . '" title="' . $title . '" alt="' . $title . '" /></figure>' : '<figure class="m-1"><img height="' . $height . '" width="' . $width . '" src="' . $chart->getUrl() . '" title="' . $title . '" alt="' . $title . '" /></figure>';
    }
    public function barChart(string $title, array $labels, array $data, string|int $width = 300, string|int $height = 300, string $format = 'svg', bool $shortUrl = false): string
    {
        $chart = new QuickChart(
            [
            'width' => $width,
            'height' => $height,
            'format' => $format,
            ]
        );

        $chartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $title,
                        'backgroundColor' => [
                            'rgba(54, 162, 235, 1)',  // blue
                            'rgba(75, 192, 192, 1)',  // green
                            'rgba(255, 99, 132, 1)',  // red
                            'rgba(255, 159, 64, 1)',  // orange
                            'rgba(153, 102, 255, 1)', // purple
                            'rgba(255, 206, 86, 1)',  // yellow
                            'rgba(255, 0, 0, 1)',     // bright red
                            'rgba(0, 255, 255, 1)',   // cyan
                            'rgba(255, 0, 255, 1)',   // magenta
                            'rgba(128, 128, 128, 1)', // grey
                        ],
                        'data' => $data,
                        'borderColor' => 'rgba(0,0,0, 0.95)',
                        'borderWidth' => 0,
                        'weight' => 600,
                        'pointBackgroundColor' => 'function(context) {
                            var index = context.dataIndex;
                            var value = context.dataset.data[index];
                            return value === "DenyList" ? "green" : "blue";
                        }',
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'title' => [
                    'display' => true,
                    'fontSize' => 20,
                    'text' => $title,
                    'color' => '#777',
                    'align' => 'center',
                    'position' => 'top',
                    'fullSize' => true,
                ],
                'legend' => [
                    'display' => false,
                    'position' => 'top',
                    'align' => 'top',
                    'labels' => [
                        'fontColor' => '#777',
                        'fontStyle' => 'bold',
                        'fontSize' => 12,
                        'padding' => 12,
                    ],
                ],
                'plugins' => [
                    'doughnutlabel' => [],
                    'datalabels' => [
                        'anchor' => 'center',
                        'align' => 'center',
                        'color' => 'white',
                        'backgroundColor' => 'black',
                        'borderColor' => 'black',
                        'borderWidth' => 1,
                        'borderRadius' => 6,
                        'font' => [
                            'weight' => 'bold',
                            'size' => 12,
                        ],
                    ],
                ],
            ],
        ];

        $chart->setConfig(json_encode($chartConfig));

        return ($shortUrl) ?  '<figure class="m-2"><img src="' . $chart->getShortUrl() . '" title="' . $title . '" alt="' . $title . '" width="' . $width . '" height="' . $height . '" /></figure>' : '<figure class="m-2"><img src="' . $chart->getUrl() . '" title="' . $title . '" alt="' . $title . '" width="' . $width . '" height="' . $height . '" /></figure>';
    }
}
