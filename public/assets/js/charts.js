
/* Charts */

const createPieChart = (name, parentNodeId, canvasId, containerHeight, containerWidth, labels, data) => {
    let parentDiv = document.getElementById(parentNodeId);
    let containerDiv = document.createElement('div');
    parentDiv.appendChild(containerDiv);
    containerDiv.classList.add('w-80');
    containerDiv.style.height = containerHeight;
    containerDiv.style.width = containerWidth;
    let canvas = document.createElement('canvas');
    canvas.id = canvasId;
    containerDiv.appendChild(canvas);

    // Find out the theme - dark or light
    let theme = '';
    if (localStorage.getItem('color-theme')) {
        if (localStorage.getItem('color-theme') === 'dark') {
            theme = 'dark';
        } else {
            theme = 'light';
        }
    } else {
        theme = 'light';
    }

    const titleColor = (theme === 'dark') ? 'white' : 'black';

    const labelColor = (theme === 'dark') ? 'white' : 'black';

    let backgroundColorArray = [];

    let colorScheme = [];
    labels.forEach(label => {
        var item = colorScheme[Math.floor(Math.random() * colorScheme.length)];
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
            backgroundColorArray = [
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
                    borderColor: 'rgba(255,255,255, 0.95)',
                    weight: 600,
                }
            ]
        },
        options: {
            hover: {
                mode: null
            },
            responsive: true,
            maintainAspectRatio: true,
            /*
            legendCallback: function(chart) {
                var text = [];
                text.push('<ul class="0-legend">');
                var ds = chart.data.datasets[0];
                var sum = ds.data.reduce(function add(a, b) { return a + b; }, 0);
                for (var i=0; i<ds.data.length; i++) {
                    text.push('<li>');
                    var perc = Math.round(100*ds.data[i]/sum,0);
                    text.push('<span style="background-color:' + ds.backgroundColor[i] + '">' + '</span>' + chart.data.labels[i] + ' ('+ds.data[i]+') ('+perc+'%)');
                    text.push('</li>');
                }
                text.push('</ul>');
                return text.join("");
            },
            */
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        padding: 20,
                        color: labelColor,
                        fontSize: 12,
                        borderWidth: 1,
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
                    color: titleColor,
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
                    align: 'middle',
                    color: '#fff',
                    backgroundColor: '#000',
                    borderRadius: 3,
                    font: {
                        size: 11,
                        lineHeight: 1
                    },
                }
            },
        }
    });
    return chart;
}

// Line chart

const createLineChart = (title, parentDiv, width, height, labels, data) => {
    let parent = document.getElementById(parentDiv);
    let containerDiv = document.createElement('div');
    parent.appendChild(containerDiv);
    let canvas = document.createElement('canvas');
    containerDiv.appendChild(canvas);
    containerDiv.style.height = `${height}px`;
    containerDiv.style.width = `${width}px`;

    let lineDataSets = [];

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
    ]; // Array of colors

    // Find out the theme - dark or light
    let theme = getCurrentTheme();

    const textColor = (theme === 'dark') ? 'white' : 'black';
    const gridColor = (theme === 'dark') ? 'rgba(128, 128, 128, 0.65)' : 'rgba(128, 128, 128, 0.4)';

    
    data.forEach((array, index) => {
        let calculatedTarget = Object.entries(array)[0][1];

        // Get the color from the colors array based on the index. Uses the remainder operator (%) to cycle through the colors array and assign a color based on the index value.
        let color = colors[index % colors.length];

        lineDataSets.push({
            label: calculatedTarget,
            data: array['data'],
            borderColor: color,
            backgroundColor: color,
            fill: false,
            tension: 0.1
        });
    });

    const lineChart = new Chart(canvas, {
        type: 'line',
        data: {
            datasets: lineDataSets,
            labels: labels,
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                    display: true,
                    labels: {
                        padding: 20,
                        color: textColor,
                        fontSize: 12,
                        borderWidth: 1,
                    }
                },
                title: {
                    display: true,
                    text: title,
                    color: textColor,
                },
                tooltip: {
                    mode: 'index', // Show tooltip for all datasets at the same x-value
                    intersect: false // Ensures tooltip shows for all datasets, not just the hovered one
                }
            },
            interaction: {
                mode: 'index', // Ensures all dataset values for the same x-axis label are shown
                intersect: false
            },
            scales: {
                x: {
                    ticks: {
                        color: textColor, // Color for x-axis labels
                        margin: 10,
                    },
                    grid: {
                        color: gridColor, // Color for y-axis grid lines
                    },
                },
                y: {
                    ticks: {
                        color: textColor, // Color for y-axis labels
                        margin: 10,
                    },
                    grid: {
                        color: gridColor, // Color for y-axis grid lines
                    },
                },
            },
        }        
    });
    lineChart.update();
}


// Gauge
const gaugeChart = (title, parentDiv, width, height, min, max) => {
    let parent = document.getElementById(parentDiv);
    let containerDiv = document.createElement('div');
    containerDiv.classList.add('w-64');
    parent.appendChild(containerDiv);
    let canvas = document.createElement('canvas');
    containerDiv.appendChild(canvas);

    // Define the gauge chart data
    const remainder = max - min;
    
    const percentage = ((min / max) * 100).toFixed(1); // Calculate percentage with one decimal place

    // Create the gradient
    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, canvas.width, 0);  // Horizontal gradient
    gradient.addColorStop(0, 'lime');  // Green
    gradient.addColorStop(1, 'red');  // Red

    // Calculate how much of the gradient should be green or red based on the current value
    //const ratio = min / max;
    //const gradientColor = ratio > 0.5 ? gradient : '#00c853';  // Green if value is less than half of max

    let gaugeColor;
    if (percentage <= 33) {
        gaugeColor = '#00c853';  // Green
    } else if (percentage <= 55) {
        gaugeColor = '#ffeb3b';  // Yellow
    } else if (percentage <= 75) {
        gaugeColor = '#ff9800';  // Orange
    } else {
        gaugeColor = '#e53935';  // Red
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
                    backgroundColor: [gaugeColor, '#e0e0e0'],
                    borderWidth: 1,
                    circumference: 360,  // Half-circle gauge
                    rotation: -Math.PI / 2,  // Start at the top
                    cutout: '75%',  // Make it a doughnut with a hole in the center
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,  // Allow resizing
            animation: {
                animateRotate: true,
                animateScale: true
            },
            plugins: {
                title: {
                    display: true,
                    text: `${title} - ${min} out of ${max}`,
                    font: { size: 16, weight: 'bold' },
                    color: '#333',
                },
                // When you hover on a datalabel, show count and stuff
                datalabels: {
                    display: true,
                    align: 'middle',
                    color: '#fff',
                    backgroundColor: '#000',
                    borderRadius: 3,
                    font: {
                        size: 11,
                        lineHeight: 1
                    },
                }
            }
        }
    });

    return chart;
};


/* Donought Chart */
const donutChart = (title, parentNodeId, width, height, labels, data) => {
    let parentDiv = document.getElementById(parentNodeId);
    let containerDiv = document.createElement('div');
    parentDiv.appendChild(containerDiv);
    containerDiv.classList.add('w-80');
    containerDiv.style.height = height;
    containerDiv.style.width = width;
    let canvas = document.createElement('canvas');
    canvas.id = `canvas-${generateUniqueId(4)}`;
    containerDiv.appendChild(canvas);

    // Find out the theme - dark or light
    let theme = '';
    if (localStorage.getItem('color-theme')) {
        if (localStorage.getItem('color-theme') === 'dark') {
            theme = 'dark';
        } else {
            theme = 'light';
        }
    } else {
        theme = 'light';
    }

    const titleColor = (theme === 'dark') ? '#9ca3af' : '#111827';

    const labelColor = (theme === 'dark') ? '#9ca3af' : '#111827';

    let backgroundColorArray = [];
    let colorScheme = [];
    labels.forEach(label => {
        backgroundColorArray = [
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
        //console.log('Assigning color ' + item + ' to chart ' + name);
        colorScheme = colorScheme.filter(element => element !== item);
    })

    let total = data.reduce((a, b) => a + b, 0);

    // Custom plugin to add text in the center
    const centerTextPlugin = {
        id: 'centerText',
        beforeDraw: function (chart) {
            let { width, height, ctx } = chart;
            let centerX = width / 2;
            let centerY = height / 2 + height * 0.24; // Adjusted for better centering
    
            ctx.save();
            ctx.font = `bold ${Math.floor(height / 12)}px Arial`; // Reduced font size
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = chart.options.plugins.title.color || '#111827';
            ctx.fillText(total, centerX, centerY);
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
                    label: labels,
                    backgroundColor: backgroundColorArray,
                    data: data,
                    borderWidth: 0,
                    borderColor: 'rgba(255,255,255, 0.95)',
                    weight: 600,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        padding: 20,
                        color: labelColor,
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
                    color: titleColor,
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
                    align: 'middle',
                    color: '#fff',
                    backgroundColor: '#000',
                    borderRadius: 3,
                    font: {
                        size: 11,
                        lineHeight: 1
                    },
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
                            color: '#777'
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
    console.log(data);
    let parent = document.getElementById(parentDiv);
    let containerDiv = document.createElement('div');
    parent.appendChild(containerDiv);
    containerDiv.classList.add('w-80', 'overflow-auto', 'm-4');
    containerDiv.style.height = height;
    containerDiv.style.width = width;
    let canvas = document.createElement('canvas');
    // Canvas id will be derived from the title
    canvas.id = title.replace(' ', '-');
    containerDiv.appendChild(canvas);

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

    // Find the current theme
    let theme = getCurrentTheme();

    const textColor = (theme === 'dark') ? 'white' : 'black';

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
            responsive: false,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                    display: false,
                    labels: {
                        padding: 20,
                        color: textColor,
                        fontSize: 12,
                        borderWidth: 1,
                    }
                },
                title: {
                    display: true,
                    text: title,
                    color: textColor,
                },
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    myChart.update();
};
