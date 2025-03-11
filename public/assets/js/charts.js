
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
    /*
    let colorScheme = [
        "#25CCF7","#FD7272","#54a0ff","#00d2d3",
        "#1abc9c","#2ecc71","#3498db","#9b59b6","#34495e",
        "#16a085","#27ae60","#2980b9","#8e44ad","#2c3e50",
        "#f1c40f","#e67e22","#e74c3c","#ecf0f1","#95a5a6",
        "#f39c12","#d35400","#c0392b","#bdc3c7","#7f8c8d",
        "#55efc4","#81ecec","#74b9ff","#a29bfe","#dfe6e9",
        "#00b894","#00cec9","#0984e3","#6c5ce7","#ffeaa7",
        "#fab1a0","#ff7675","#fd79a8","#fdcb6e","#e17055",
        "#d63031","#feca57","#5f27cd","#54a0ff","#01a3a4"
    ];
    */
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
        //console.log('Assigning color ' + item + ' to chart ' + name);
        colorScheme = colorScheme.filter(element => element !== item);
    })

    //const ctx = canvas.getContext('2d');

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

const doughnutChart = (name, parentNodeId, height, width, labels, data) => {
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
    let theme = getCurrentTheme();

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

    //const ctx = canvas.getContext('2d');

    let chart = new Chart(canvas, {
        type: 'doughnut',
        plugins: [ChartDataLabels],
        data: {
            labels: labels,
            datasets: [
                {
                    label: name,
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

// Gauge
const createGauge = (parentDiv, title, width, height, currentValue, maxValue) => {

    const percentage = (currentValue / maxValue) * 100;

    const container = document.getElementById(parentDiv);

    const gaugeContainer = document.createElement('div');

    gaugeContainer.classList.add('flex', 'flex-col', 'items-center');

    const titleElement = document.createElement('div');
    titleElement.classList.add('gauge-title', 'font-semibold');
    titleElement.textContent = title;

    const gaugeElement = document.createElement('div');
    gaugeElement.classList.add('gauge');
    gaugeElement.style.width = `${width}px`;
    gaugeElement.style.height = `${height}px`;

    const gaugeFill = document.createElement('div');
    gaugeFill.classList.add('gauge-fill');

    const gaugeText = document.createElement('div');
    gaugeText.classList.add('gauge-text');
    gaugeText.textContent = `${currentValue} / ${maxValue}`;

    gaugeElement.appendChild(gaugeFill);
    gaugeElement.appendChild(gaugeText);

    container.appendChild(gaugeContainer);

    gaugeContainer.appendChild(titleElement);
    gaugeContainer.appendChild(gaugeElement);

    if (percentage >= 100) {
        gaugeFill.style.background = `conic-gradient(#FF0000 0% 100%, #FF0000 100% 100%)`;
    } else if (percentage >= 75) {
        gaugeFill.style.background = `conic-gradient(#FF0000 0% ${percentage}%, #FF0000 ${percentage}% 100%)`;
    } else if (percentage >= 50) {
        gaugeFill.style.background = `conic-gradient(#FFA500 0% ${percentage}%, #FFA500 ${percentage}% 100%)`;
    } else if (percentage >= 25) {
        gaugeFill.style.background = `conic-gradient(#FFD700 0% ${percentage}%, #FFD700 ${percentage}% 100%)`;
    } else {
        gaugeFill.style.background = `conic-gradient(#4CAF50 0% ${percentage}%, #4CAF50 ${percentage}% 100%)`;
    }
}

const gauge = (title, parentNodeId, width, height, labels, deita) => {
    let parentDiv = document.getElementById(parentNodeId);
    let containerDiv = document.createElement('div');
    parentDiv.appendChild(containerDiv);
    containerDiv.classList.add('m-4');
    let canvas = document.createElement('canvas');
    containerDiv.appendChild(canvas);
    canvas.id = `canvas-${generateUniqueId(4)}`;
    canvas.height = height;
    canvas.width = width;

    // Find out the theme - dark or light
    let theme = getCurrentTheme();

    const titleColor = (theme === 'dark') ? 'white' : 'black';

    // data for the gauge chart
    // you can supply your own values here
    // max is the Gauge's maximum value
    var data = {
        value: deita[0],
        max: deita[1],
        label: "used"
    };

    let backgroundColor = ''

    if (deita[0] === deita[1]) {
        backgroundColor = 'red'
    } else {
        backgroundColor = ['green', 'gray'];
    }

    // Chart.js chart's configuration
    // We are using a Doughnut type chart to 
    // get a Gauge format chart 
    // This is approach is fine and actually flexible
    // to get beautiful Gauge charts out of it
    const config = {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: [data.value, data.max - data.value],
                backgroundColor: backgroundColor,
                borderWidth: 0
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: true,
            cutoutPercentage: 85,
            rotation: -90,
            circumference: 180,
            tooltips: {
                enabled: false
            },
            legend: {
                display: false
            },
            animation: {
                animateRotate: true,
                animateScale: false
            },
            title: {
                display: true,
                text: title,
                fontSize: 16
            },
            plugins: {
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
                doughnutlabel: {
                    labels: [
                        {
                            text: title,
                            font: {
                                size: 30,
                                family: 'Arial, Helvetica, sans-serif',
                                weight: 'bold'
                            },
                            backgroundColor: 'green',
                            color: titleColor
                        }
                    ]
                },
            }
        }
    };

    // Create the chart
    let gaugeChart = new Chart(canvas, config);
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
