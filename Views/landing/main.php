<?php declare(strict_types=1);

use Components\Alerts;
use Components\Html;

define('INSTALL_PATH', '/install');

try {
    $db = new App\Database\DB(); // Initialize the DB object
    $pdo = $db->getConnection(); // Retrieve the PDO connection object
} catch (\PDOException $e) {
    $errorMessage = $e->getMessage();
    error_log("Caught PDOException: " . $errorMessage);

    // MySQL error code 1049 is for unknown database
    if (str_contains($errorMessage, 'Unknown database')) {
        // Pick up the database name from the error
        $databaseName = explode('Unknown database ', $errorMessage)[1];
        $errorMessage = 'Database ' . $databaseName . ' not found. Please install the application by going to ' . Components\Html::a(INSTALL_PATH, INSTALL_PATH, $theme);
    }
    // Postgres 08006 is for connection failure database does not exist
    if (str_contains($errorMessage, 'does not exist')) {
        $databaseName = explode('database ', $errorMessage)[1];
        $errorMessage = 'Database ' . $databaseName . '. Please install the application by going to ' . Components\Html::a(INSTALL_PATH, INSTALL_PATH, $theme);
    }
    echo Alerts::danger($errorMessage); // Handle the exception
    return;
}


echo Alerts::success('Successfully connected to the database');

echo Html::h1('Current Issues', true);

$currentIssues = [
  'DataGrid' => [
    'filters not activating in javascript autoload sometimes',
    'in Javascript, filters do not get red border',
    'DataGrid filters not working when special characters are in the cell body'
  ],
  'Charts' => [
    'ApexCharts not rendering properly in the dark mode',
    'More diverse chart types needed',
  ],
  'Docs' => [
    'Docs need to be updated for the new features',
  ]
];

foreach ($currentIssues as $category => $array) {
  echo Html::h3($category, true);
  echo Components\Table::auto($array);
}

?>


<!--
<div class="max-w-sm mx-auto md:mx-4 w-full my-6 bg-white rounded-lg shadow dark:bg-gray-800 p-4 md:p-6">
  <div id="line-chart"></div>
</div>

<script nonce="1nL1n3JsRuN1192kwoko2k323WKE">
    
    const options = {
  chart: {
    height: "150px",
    maxWidth: "300px",
    type: "line",
    fontFamily: "Inter, sans-serif",
    dropShadow: {
      enabled: false,
    },
    toolbar: {
      show: true, // Enable the toolbar
      tools: {
        download: true, // Show only the download option
        selection: false, // Disable selection (hand)
        zoom: true, // Disable zoom
        zoomin: true, // Disable zoom-in
        zoomout: true, // Disable zoom-out
        pan: true, // Disable panning (drag)
        reset: true // Disable the reset icon
      },
      export: {
        csv: true, // Enable CSV download (for chart data)
        svg: true, // Enable SVG download
        png: true, // Enable PNG download
      }
    }
  },
  tooltip: {
    enabled: true,
    x: {
      show: false,
    },
  },
  dataLabels: {
    enabled: false,
  },
  stroke: {
    width: 6,
  },
  grid: {
    show: true,
    strokeDashArray: 4,
    padding: {
      left: 2,
      right: 2,
      top: -26
    },
  },
  series: [
    {
      name: "Clicks",
      data: [6500, 6418, 6456, 6526, 6356, 6456],
      color: "#1A56DB",
    },
    {
      name: "CPC",
      data: [6456, 6356, 6526, 6332, 6418, 6500],
      color: "#7E3AF2",
    },
    {
        name: "CTR",
        data: [6418, 6456, 6356, 6418, 6526, 6332],
        color: "#F472B6",
    }
  ],
  legend: {
    show: false
  },
  stroke: {
    curve: 'smooth'
  },
    title: {
            text: 'Stock Price Movement',
            align: 'left'
        },
  xaxis: {
    categories: ['01 Feb', '02 Feb', '03 Feb', '04 Feb', '05 Feb', '06 Feb', '07 Feb'],
    labels: {
      show: true,
      style: {
        fontFamily: "Inter, sans-serif",
        cssClass: 'text-xs font-normal fill-gray-500 dark:fill-gray-400'
      }
    },
    axisBorder: {
      show: false,
    },
    axisTicks: {
      show: false,
    },
  },
  yaxis: {
    show: false,
  },
}

if (document.getElementById("line-chart") && typeof ApexCharts !== 'undefined') {
  const chart = new ApexCharts(document.getElementById("line-chart"), options);
  chart.render();
}

</script>

-->
