
/* Charts */
const colors = [
    'rgba(54, 162, 235, 1)', // blue
    'rgba(75, 192, 192, 1)', // green
    'rgba(255, 99, 132, 1)', // red
    'rgba(255, 159, 64, 1)', // orange
    'rgba(153, 102, 255, 1)', // purple
    'rgba(255, 206, 86, 1)', // yellow
    'rgba(255, 0, 0, 1)', // bright red
    'rgba(0, 255, 255, 1)', // cyan
    'rgba(255, 0, 255, 1)', // magenta
    'rgba(128, 128, 128, 1)' // grey
];


const createPieChart = (name, parentDiv, canvasId, height, width, labels, data) => {
    let parent = document.getElementById(parentDiv);
    let containerDiv = document.createElement('div');
    parent.appendChild(containerDiv);

    // Set explicit container size
    containerDiv.style.width = width + "px";
    containerDiv.style.height = height + "px";
    containerDiv.classList.add('relative'); // Prevent Tailwind conflict
    // Also make sure that overflox-x is handled if needed
    containerDiv.classList.add('overflow-x-auto', 'm-4');

    let canvas = document.createElement('canvas');

    canvas.id = canvasId;

    // ✅ Set the actual canvas dimensions before rendering
    canvas.width = width;
    canvas.height = height;
    
    containerDiv.appendChild(canvas);

    // Find out the theme - dark or light
    const textColor = getCurrentChartColors().textColor;

    let backgroundColorArray = [];

    let colorScheme = [];
    labels.forEach(label => {
        let item = colorScheme[Math.floor(Math.random() * colorScheme.length)];
        // For Malicious confidences let's draw red orange green for the good and bad malicious confidences
        if (name === 'maliciousConfidences') {
            let color = '';
            if (label === "0") {
                color = 'lime';
            } else if (label > 0 && label < 50) {
                color = 'green';
            } else if (label >= 50 && label < 75) {
                color = 'orange';
            } else if (label >= 75 && label <= 80) {
                color = 'crimson';
            } else if (label > 80 && label <= 100) {
                color = 'red';
            } else {
                color = 'purple';
            }
            backgroundColorArray.push(color);
            // For the rest - push from the random array of colors
        } else {
            backgroundColorArray = colors

        }
        colorScheme = colorScheme.filter(element => element !== item);
    })

    const chart = new Chart(canvas, {
        type: 'pie',
        plugins: [ChartDataLabels],
        data: {
            labels: labels,
            datasets: [
                {
                    //label: name,
                    backgroundColor: backgroundColorArray,
                    data: data,
                    //color: 'red',
                    borderWidth: 0,
                    borderColor: textColor,
                    weight: 600,
                }
            ]
        },
        options: {
            hover: {
                mode: null
            },
            responsive: false, // ✅ Disable responsiveness for fixed size
            maintainAspectRatio: false, // ✅ Allow custom height
            
            // legendCallback: function(chart) {
            //     var text = [];
            //     text.push('<ul class="0-legend">');
            //     var ds = chart.data.datasets[0];
            //     var sum = ds.data.reduce(function add(a, b) { return a + b; }, 0);
            //     for (var i=0; i<ds.data.length; i++) {
            //         text.push('<li>');
            //         var perc = Math.round(100*ds.data[i]/sum,0);
            //         text.push('<span style="background-color:' + ds.backgroundColor[i] + '">' + '</span>' + chart.data.labels[i] + ' ('+ds.data[i]+') ('+perc+'%)');
            //         text.push('</li>');
            //     }
            //     text.push('</ul>');
            //     return text.join("");
            // },
            
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        padding: 20,
                        color: textColor,
                        fontSize: 12,
                        borderWidth: 0,
                        /*
                        generateLabels: function(chart) {
                        var data = chart.data;
                        if (data.labels.length && data.datasets.length) {
                            return data.labels.map(function(label, i) {
                            var text = label;
                            if (text.length > 45) {
                                text = text.substring(0, 10) + '...';
                            }
                            return {
                                text: text,
                                fillStyle: data.datasets[0].backgroundColor[i],
                                strokeStyle: data.datasets[0].borderColor[i],
                                lineWidth: 2,
                                hidden: isNaN(data.datasets[0].data[i]) || chart.getDatasetMeta(0).data[i].hidden,
                                index: i
                            };
                            });
                        }
                        return [];
                        }
                        */
                    }
                },
                // Chart Title on top
                title: {
                    display: true,
                    text: name.replace('_', ' '),
                    padding: {
                        top: 15,
                        bottom: 10
                    },
                    color: textColor,
                    align: 'center',
                    fullSize: true,
                    font: {
                        weight: 'bold',
                        size: 16
                    },
                    position: 'top'
                },
                // When you hover on a datalabel, show count and stuff
                datalabels: {
                    display: true,
                    anchor: "center",
                    align: "center",
                    color: "#fff",
                    backgroundColor: "#000",
                    borderColor: "#fff",
                    borderWidth: 0,
                    borderRadius: 6,
                    font: {
                        weight: 'normal',
                        size: 12,
                    }
                }
            },
        }
    });
    return chart;
}

/**
 * Flexible Line Chart Creator
 * 
 * @param {string} title - Chart title
 * @param {string} parentDiv - ID of parent container element
 * @param {number} width - Chart width in pixels
 * @param {number} height - Chart height in pixels
 * @param {Array} labels - Array of x-axis labels
 * @param {Array} data - Array of dataset objects with 'label' and 'data' properties
 * @param {Object} options - Configuration options (optional)
 * 
 * Available options:
 * ==================
 * Chart Appearance:
 * - tension: number (0-1, default: 0.4) - Line curve smoothness
 * - pointRadius: number (default: 5) - Size of data points
 * - pointHoverRadius: number (default: 8) - Size of data points on hover
 * - borderWidth: number (default: 3) - Line thickness
 * - fill: boolean (default: false) - Fill area under line
 * - backgroundOpacity: string (default: '20') - Hex opacity for backgrounds
 * 
 * Animation:
 * - animationDuration: number (default: 1500) - Animation duration in ms
 * - animationEasing: string (default: 'easeInOutCubic') - Animation easing function
 * 
 * Axes:
 * - xAxisTitle: string (default: 'Time') - X-axis title (set to '' to hide)
 * - yAxisTitle: string (default: 'Value') - Y-axis title (set to '' to hide)
 * - xAxisRotation: object (default: {min: 0, max: 45}) - X-axis label rotation
 * - yAxisBeginAtZero: boolean (default: true) - Start Y-axis at zero
 * - yAxisPrecision: number (default: 0) - Decimal precision for Y-axis ticks
 * 
 * Legend:
 * - showLegend: boolean (default: true) - Show/hide legend
 * - legendPosition: string (default: 'top') - Legend position: 'top', 'bottom', 'left', 'right'
 * - usePointStyle: boolean (default: true) - Use point style in legend
 * 
 * Title:
 * - showTitle: boolean (default: true) - Show/hide chart title
 * - titlePadding: number (default: 20) - Title padding
 * 
 * Tooltip & Interaction:
 * - tooltipMode: string (default: 'index') - Tooltip mode: 'index', 'point', 'nearest'
 * - tooltipIntersect: boolean (default: false) - Tooltip intersection behavior
 * 
 * Grid:
 * - showGrid: boolean (default: true) - Show/hide grid lines
 * 
 * Colors:
 * - useCustomColors: boolean (default: false) - Use custom color palette
 * - customColors: Array (default: []) - Custom color array (if useCustomColors is true)
 * 
 * Point Styling:
 * - pointBorderColor: string (default: '#ffffff') - Point border color
 * - pointBorderWidth: number (default: 2) - Point border width
 * - pointHoverBorderWidth: number (default: 3) - Point border width on hover
 * 
 * Example usage:
 * ==============
 * createLineChart('My Chart', 'container', 800, 400, ['Jan', 'Feb'], 
 *   [{label: 'Sales', data: [100, 200]}], {
 *     xAxisTitle: 'Months',
 *     yAxisTitle: 'Revenue ($)',
 *     tension: 0.5,
 *     borderWidth: 4,
 *     useCustomColors: true,
 *     customColors: ['#ff6b6b', '#4ecdc4', '#45b7d1']
 *   }
 * );
 */
const createLineChart = (title, parentDiv, width, height, labels, data, options = {}) => {
    let parent = document.getElementById(parentDiv);
    let containerDiv = document.createElement('div');
    parent.appendChild(containerDiv);

    // Set explicit container size
    containerDiv.style.width = width + "px";
    containerDiv.style.height = height + "px";
    containerDiv.classList.add('relative'); // Prevent Tailwind conflict
    // Also make sure that overflox-x is handled if needed
    containerDiv.classList.add('overflow-x-auto', 'm-4');

    let canvas = document.createElement('canvas');
    canvas.id = title.replace(' ', '-').toLowerCase() + generateUniqueId(4);

    // ✅ Set the actual canvas dimensions before rendering
    canvas.width = width;
    canvas.height = height;

    containerDiv.appendChild(canvas);

    // Default configuration options
    const defaultOptions = {
        // Chart appearance
        tension: 0.4,
        pointRadius: 5,
        pointHoverRadius: 8,
        borderWidth: 3,
        fill: false,
        
        // Animation
        animationDuration: 1500,
        animationEasing: 'easeInOutCubic',
        
        // Axes
        xAxisTitle: 'Time',
        yAxisTitle: 'Value',
        xAxisRotation: { min: 0, max: 45 },
        yAxisBeginAtZero: true,
        yAxisPrecision: 0,
        
        // Legend
        showLegend: true,
        legendPosition: 'top',
        usePointStyle: true,
        
        // Title
        showTitle: true,
        titlePadding: 20,
        
        // Tooltip
        tooltipMode: 'index',
        tooltipIntersect: false,
        
        // Grid
        showGrid: true,
        
        // Colors
        useCustomColors: false,
        customColors: [],
        
        // Point styling
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointHoverBorderWidth: 3,
        
        // Background
        backgroundOpacity: '20' // Hex opacity for semi-transparent backgrounds
    };
    
    // Merge user options with defaults
    const config = { ...defaultOptions, ...options };

    let lineDataSets = [];

    const textColor = getCurrentChartColors().textColor;
    const gridColor = getCurrentChartColors().gridColor;
    
    data.forEach((dataset, index) => {
        // Use the label property directly from the dataset
        let label = dataset.label || `Dataset ${index + 1}`;

        // Determine color to use
        let color;
        if (config.useCustomColors && config.customColors.length > 0) {
            color = config.customColors[index % config.customColors.length];
        } else {
            color = colors[index % colors.length];
        }

        lineDataSets.push({
            label: label,
            data: dataset.data,
            borderColor: color,
            backgroundColor: color + config.backgroundOpacity,
            fill: config.fill,
            tension: config.tension,
            pointRadius: config.pointRadius,
            pointHoverRadius: config.pointHoverRadius,
            pointBackgroundColor: color,
            pointBorderColor: config.pointBorderColor,
            pointBorderWidth: config.pointBorderWidth,
            pointHoverBackgroundColor: color,
            pointHoverBorderColor: config.pointBorderColor,
            pointHoverBorderWidth: config.pointHoverBorderWidth,
            borderWidth: config.borderWidth
        });
    });

    const lineChart = new Chart(canvas, {
        type: 'line',
        data: {
            datasets: lineDataSets,
            labels: labels,
        },
        options: {
            responsive: false, // ✅ Disable responsiveness for fixed size
            maintainAspectRatio: false, // ✅ Allow custom height
            animation: {
                duration: config.animationDuration,
                easing: config.animationEasing
            },
            plugins: {
                legend: {
                    position: config.legendPosition,
                    display: config.showLegend,
                    labels: {
                        padding: 20,
                        color: textColor,
                        fontSize: 12,
                        borderWidth: 1,
                        usePointStyle: config.usePointStyle,
                        pointStyle: 'circle'
                    }
                },
                title: {
                    display: config.showTitle,
                    text: title,
                    color: textColor,
                    fontSize: 16,
                    padding: config.titlePadding
                },
                tooltip: {
                    mode: config.tooltipMode,
                    intersect: config.tooltipIntersect,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1
                }
            },
            interaction: {
                mode: config.tooltipMode,
                intersect: config.tooltipIntersect
            },
            scales: {
                x: {
                    title: {
                        display: !!config.xAxisTitle,
                        text: config.xAxisTitle,
                        color: textColor
                    },
                    ticks: {
                        color: textColor,
                        margin: 10,
                        maxRotation: config.xAxisRotation.max,
                        minRotation: config.xAxisRotation.min
                    },
                    grid: {
                        color: config.showGrid ? gridColor : 'transparent',
                        drawOnChartArea: config.showGrid,
                        drawTicks: config.showGrid
                    },
                },
                y: {
                    title: {
                        display: !!config.yAxisTitle,
                        text: config.yAxisTitle,
                        color: textColor
                    },
                    ticks: {
                        color: textColor,
                        margin: 10,
                        beginAtZero: config.yAxisBeginAtZero,
                        precision: config.yAxisPrecision
                    },
                    grid: {
                        color: config.showGrid ? gridColor : 'transparent',
                        drawOnChartArea: config.showGrid,
                        drawTicks: config.showGrid
                    },
                },
            },
        }        
    });
    lineChart.update();
}


// Gauge
const createGaugeChart = (title, parentDiv, width, height, min, max) => {
    let parent = document.getElementById(parentDiv);
    let containerDiv = document.createElement('div');
    parent.appendChild(containerDiv);

    // Set explicit container size
    containerDiv.style.width = width + "px";
    containerDiv.style.height = height + "px";
    containerDiv.classList.add('relative'); // Prevent Tailwind conflict

    let canvas = document.createElement('canvas');
    canvas.id = title.replace(' ', '-').toLowerCase() + generateUniqueId(4);

    // ✅ Set the actual canvas dimensions before rendering
    canvas.width = width;
    canvas.height = height;

    containerDiv.appendChild(canvas);

    const textColor = getCurrentChartColors().textColor;

    // Define the gauge chart data
    const remainder = max - min;
    
    const percentage = ((min / max) * 100).toFixed(0); // Calculate percentage with one decimal place

    // Create the gradient
    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, canvas.width, 0);  // Horizontal gradient
    gradient.addColorStop(0, 'lime');  // Green
    gradient.addColorStop(1, 'red');  // Red

    // Calculate how much of the gradient should be green or red based on the current value
    //const ratio = min / max;
    //const gradientColor = ratio > 0.5 ? gradient : '#00c853';  // Green if value is less than half of max

    // Function to generate gradient dynamically
    const getGradientFillHelper = (ctx, colors) => {
        let gradient = ctx.createLinearGradient(0, 0, width, 0); // Horizontal gradient
        let colorStops = colors.length - 1;
        colors.forEach((color, index) => {
            gradient.addColorStop(index / colorStops, color);
        });
        return gradient;
    };

    let gaugeColor;
    if (percentage >= 0 && percentage <= 25) {
        gaugeColor = getGradientFillHelper(ctx, ["lime", "green"]);
    } else if (percentage > 25 && percentage < 50) {
        gaugeColor = getGradientFillHelper(ctx, ["yellow", "green"]);
    } else if (percentage >= 50 && percentage < 75) {
        gaugeColor = getGradientFillHelper(ctx, ["orange", "yellow"]);
    } else if (percentage >= 75 && percentage <= 85) {
        gaugeColor = getGradientFillHelper(ctx, ["yellow", "crimson"]);
    } else if (percentage > 85 && percentage <= 100) {
        gaugeColor = getGradientFillHelper(ctx, ["crimson", "red"]);
    } else {
        gaugeColor = getGradientFillHelper(ctx, ["green", "lime"]);
    }

    const centerTextPlugin = {
        id: 'centerText',
        beforeDraw: function (chart) {
            let { width, height, ctx } = chart;
            let centerX = width / 2;
            let centerY = height / 2 + height * 0.10; // Adjusted for better centering
    
            ctx.save();
            ctx.font = `bold ${Math.floor(height / 13)}px Arial`; // Reduced font size
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = chart.options.plugins.title.color || '#111827';
            // First line (min/max)
            ctx.font = `bold ${Math.floor(height / 14)}px Arial`;
            ctx.fillText(`${min}/${max}`, centerX, centerY);

            // Second line (percentage) - slightly below the first line
            ctx.font = `bold ${Math.floor(height / 16)}px Arial`; // Slightly smaller
            ctx.fillText(`(${percentage}%)`, centerX, centerY + height * 0.06);
            ctx.restore();
        }
    };

    // Create the chart
    let chart = new Chart(canvas, {
        type: 'doughnut',
        plugins: [centerTextPlugin],
        data: {
            datasets: [
                {
                    data: [min, remainder],
                    backgroundColor: [gaugeColor, 'gray'],
                    borderWidth: 1,
                    borderColor: "rgba(0,0,0, 0.95)",
                    circumference: 360,  // Half-circle gauge
                    rotation: -Math.PI / 2,  // Start at the top
                    cutout: '75%',  // Make it a doughnut with a hole in the center
                }
            ]
        },
        options: {
            responsive: false, // ✅ Disable responsiveness for fixed size
            maintainAspectRatio: false, // ✅ Allow custom height
            animation: {
                animateRotate: true,
                animateScale: true
            },
            plugins: {
                title: {
                    display: true,
                    text: `${title}`,
                    font: { size: 16, weight: 'bold' },
                    color: textColor,
                }
            }
        }
    });

    return chart;
};


/* Donought Chart */
const createDonutChart = (title, parentDiv, width, height, labels, data, customColors = null) => {
    let parent = document.getElementById(parentDiv);
    let containerDiv = document.createElement('div');
    parent.appendChild(containerDiv);

    // Set explicit container size
    containerDiv.style.width = width + "px";
    containerDiv.style.height = height + "px";
    containerDiv.classList.add('relative'); // Prevent Tailwind conflict

    let canvas = document.createElement('canvas');
    canvas.id = title.replace(' ', '-').toLowerCase() + generateUniqueId(4);

    // ✅ Set the actual canvas dimensions before rendering
    canvas.width = width;
    canvas.height = height;

    containerDiv.appendChild(canvas);

    const textColor = getCurrentChartColors().textColor;

    let backgroundColorArray = [];
    
    // Use custom colors if provided, otherwise use default colors
    if (customColors && Array.isArray(customColors) && customColors.length > 0) {
        backgroundColorArray = customColors;
    } else {
        backgroundColorArray = colors;
    }

    let total = data.reduce((a, b) => a + b, 0);

    // Custom plugin to add text in the center
    const centerTextPlugin = {
        id: 'centerText',
        beforeDraw: function (chart) {
            let { width, height, ctx } = chart;
            
            // Get the chart area (excludes title, legend, padding)
            const chartArea = chart.chartArea;
            const centerX = (chartArea.left + chartArea.right) / 2;
            const centerY = (chartArea.top + chartArea.bottom) / 2;
    
            // Dynamically adjust font size based on chart area size
            const chartAreaHeight = chartArea.bottom - chartArea.top;
            let fontSize = Math.floor(chartAreaHeight / 7); // More proportional sizing
            ctx.font = `bold ${fontSize}px Arial`;
    
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = chart.options.plugins.title.color || '#111827';
            ctx.fillText(total.toLocaleString(), centerX, centerY); // Added number formatting
            ctx.restore();
        }
    };
    
    let chart = new Chart(canvas, {
        type: 'doughnut',
        plugins: [ChartDataLabels, centerTextPlugin],
        data: {
            labels: labels,
            datasets: [
                {
                    label: null,
                    backgroundColor: backgroundColorArray,
                    data: data,
                    borderWidth: 0,
                    borderColor: 'rgba(255,255,255, 0.95)',
                    weight: 600,
                }
            ]
        },
        options: {
            responsive: false, // ✅ Disable responsiveness for fixed size
            maintainAspectRatio: false, // ✅ Allow custom height
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        padding: 20,
                        color: textColor,
                        fontSize: 12,
                        borderWidth: 1,
                    }
                },
                // Chart Title on top
                title: {
                    display: true,
                    text: title,
                    padding: {
                        top: 15,
                        bottom: 10
                    },
                    color: textColor,
                    align: 'center',
                    fullSize: true,
                    font: {
                        weight: 'bold',
                        size: 16
                    },
                    position: 'top'
                },
                // When you hover on a datalabel, show count and stuff
                datalabels: {
                    display: true,
                    anchor: "center",
                    align: "center",
                    color: "#fff",
                    backgroundColor: "#000",
                    borderColor: "#fff",
                    borderWidth: 0,
                    borderRadius: 6,
                    font: {
                        weight: 'normal',
                        size: 12,
                    }
                },
                doughnutlabel: {
                    labels: [
                        {
                            text: `Total - ${data.reduce((a, b) => a + b, 0)}`,
                            font: {
                                size: 30,
                                family: 'Arial, Helvetica, sans-serif',
                                weight: 'bold'
                            },
                            backgroundColor: 'green',
                            color: textColor
                        }
                    ]
                }
            },
        }
    });
    return chart;
}

// Bar chart
const createBarChart = (title, parentDiv, width, height, labels, data) => {
    let parent = document.getElementById(parentDiv);
    let containerDiv = document.createElement('div');
    parent.appendChild(containerDiv);

    // Set explicit container size
    containerDiv.style.width = width + "px";
    containerDiv.style.height = height + "px";
    containerDiv.classList.add('relative'); // Prevent Tailwind conflict
    // Also make sure that overflox-x is handled if needed
    containerDiv.classList.add('overflow-x-auto', 'm-4');

    let canvas = document.createElement('canvas');
    canvas.id = title.replace(' ', '-').toLowerCase() + generateUniqueId(4);

    // ✅ Set the actual canvas dimensions before rendering
    canvas.width = width;
    canvas.height = height;

    containerDiv.appendChild(canvas);

    // Get the current theme
    const textColor = getCurrentChartColors().textColor;
    const gridColor = getCurrentChartColors().gridColor;

    let ctx = canvas.getContext('2d');
    let myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: title,
                data: data,
                backgroundColor: colors.slice(0, data.length),
                borderColor: colors.slice(0, data.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: false, // ✅ Disable responsiveness for fixed size
            maintainAspectRatio: false, // ✅ Allow custom height
            plugins: {
                legend: {
                    position: 'top',
                    display: false,
                    labels: {
                        padding: 12,
                        color: textColor,
                        fontSize: 12,
                        borderWidth: 1,
                    }
                },
                title: {
                    display: true,
                    text: title,
                    color: textColor,
                    fontSize: 20
                },
                datalabels: {
                    display: true,
                    anchor: "center",
                    align: "center",
                    color: "#fff",
                    backgroundColor: "#000",
                    borderColor: "#fff",
                    borderWidth: 0,
                    borderRadius: 6,
                    font: {
                        weight: 'normal',
                        size: 12,
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: textColor,
                        backgroundColor: gridColor,
                        font: {
                            size: 12,
                        }
                    },
                    grid: {
                        color: gridColor,
                        borderColor: textColor,
                        borderWidth: 1
                    }
                },
                x: {
                    beginAtZero: true,
                    ticks: {
                        color: textColor,
                        backgroundColor: gridColor,
                        font: {
                            size: 12,
                        }
                    },
                    grid: {
                        color: gridColor,
                        borderColor: textColor,
                        borderWidth: 1
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    myChart.update();
};
