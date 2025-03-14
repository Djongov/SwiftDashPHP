<?php declare(strict_types=1);

use Components\Alerts;
use Components\Html;
use App\Markdown\Page;

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

echo Html::h1('Welcome to SwiftDashPHP', true);

echo Html::p('SwiftDashPHP is a modern, open-source PHP framework for quickly building powerful applications.', ['text-center']);

echo Html::p('No Magic - Pure PHP behind and pure JS for front parts', ['text-center']);

echo Html::p('Makes it easy to start with built-in features such as:', ['text-center']);

$featuresArray = [
  // Authentication
  'Authentication for local, Google, MS (live and Azure)' => 'SwiftDashPHP comes with built-in authentication system that is easy to use and customize. Check it out on our ' . Html::a('Docs section', '/docs/authentication', $theme) . '. We also have some security features such as a Firewall that stops IPs from accessing parts we want to protect. Also parts that we want to keep public.',
  // DB
  'Support for MySQL/MariaDB/SQlite/Postgres' => 'SwiftDashPHP supports MySQL, MariaDB, SQLite and Postgres. You can easily switch between them by changing the .env file. The database connection is handled by PDO, with very little magic in between and taking advantage of the agnostic nature of PDO. There is no ORM so you write your own queries.',
  // TailwindCSS
  'TailwindCSS' => 'SwiftDashPHP uses TailwindCSS for styling. The framework uses a global <b>$theme</b> variable which can be switched easily and is using the tailwind native colors such as ' .
  implode(', ', THEME_COLORS) . '. Also each user has its own styling based on the same colors. There is also a global theming for the light and dark mode which is based on the some constants in the config file. And finally the dark/light switch is based on the dark class mode in TailwindCSS. All comes ready with a swticher, default system theming, chart theming and very little for you to care about.',
  // DataGrid
  'DataGrid with powerful features' => 'SwiftDashPHP comes with a powerful DataGrid component (based on Datatables) that allows you to display data in a table with features such as sorting, filtering, pagination, and more. Can display PHP Arrays, DB queries, whole DB tables and provides CRUD for those. More on ' . Html::a('DataGrid' , '/datagrid', $theme),
  // Charts
  'Charts (via Chart.js and QuickChart.io)' => 'Chart.js and Quickchart.io ready to use chart functions for the most popular chart types. Also easily autoload JS charts only with PHP code with the autoloading mechanism. See more the example ' . Html::a('Charts', '/charts', $theme),
  // Markdown
  'Markdown rendering' => 'With the power of Parsedown (which sadly as of now is not up to date for PHP 8.4) and some custom classes we have here, you can render locally stored or remotely stored Markdown files automatically styled with Tailwind. Check out the ' . Html::a('Docs', '/docs', $theme) . ' section',
  // Forms
  'Forms' => 'This is a big one. All (or almost all) of the buttons that do something on the framework are actually Form Components. Forms component takes out the big headache of creating the form and the submission hurdles of it. Easily do modals too. Built-in CSRF protecton too. Check out the ' . Html::a('Forms', '/forms', $theme) . ' section',
  // API
  'API' => 'Since it\'s a PHP, we know doing API endpoints is not hard. The framework helps a bit with some Response classes and some API checks and few other tools like JWT capabilities and API keys.',
  // Admin Panel
  'Admin Panel' => 'We have an Admin panel which is basic but cool and expandable.',
  // HTML Components
  'HTML Components' => 'We have an Html component which has static html methods providing html elements. DataGrid and Forms components are using it, as well as normal html output, for a standardized output everwhere.',
  // User settings
  'User Settings' => 'Comes with a user settigns page as well, built-in with some features.',
  // Easy Containerization
  'Easy containerization with Docker' => 'Tested to run in a container with ready to use Dockerfile that can get the app running on major cloud platforms in few clicks.',
  // Dark Mode
  'Dark Mode' => 'Bult-in Dark/Light mode.',
  // Localisation
  'Localisation' => 'Paved the way for localisation. Foundation is there, you just need to expand it. With a working language switcher',
  // SendGrid
  'SendGrid mailsender' => 'API endpoint for sending mails and a tinymce endpoint for sending manually.'
];

echo Html::divBox(Html::ul(array_keys($featuresArray)), ['text-center', 'mx-auto']);

foreach ($featuresArray as $title => $text) {
  echo Html::horizontalLine();
  echo Html::h2($title, true, ['my-4']);
  echo Html::p($text, ['text-center']);
}

// Let's render the mardwon we have for the Auth
//echo Page::render(ROOT . '/Views/docs/authentication', $theme);


$currentIssues = [
  'DataGrid' => [
    'filters not activating in javascript autoload sometimes',
    'in Javascript, filters do not get red border',
    'DataGrid filters not working when special characters are in the cell body'
  ],
  'Docs' => [
    'Docs need to be updated for the new features',
  ]
];

echo Html::horizontalLine();
echo Html::h2('Current Issues', true, ['mt-10']);

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
