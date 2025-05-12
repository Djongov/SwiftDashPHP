/* Dark/Light Theme Changes */
const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
const themeToggleBtn = document.getElementById('theme-toggle');

const isSystemDark = () => window.matchMedia('(prefers-color-scheme: dark)').matches;

const getCurrentTheme = () => {
    const storedTheme = localStorage.getItem('color-theme');
    if (storedTheme) return storedTheme;
    localStorage.setItem('color-theme', isSystemDark() ? 'dark' : 'light');
    return isSystemDark() ? 'dark' : 'light';
};

const getCurrentChartColors = () => {

    const systemTheme = isSystemDark() ? 'dark' : 'light';
    const userTheme = getCurrentTheme();

    let textColor = "#111827";
    let gridColor = "rgba(168, 162, 155, 1)";

    if (systemTheme === 'dark' && userTheme === 'dark') {
        textColor = "#111827";
        gridColor = "rgba(168, 162, 155, 1)";
    }

    if (systemTheme === 'dark' && userTheme === 'light') {
        textColor = "#E5E7EB";
        gridColor = "rgba(64, 62, 60, 1)";
    }

    if (systemTheme === 'light' && userTheme === 'dark') {
        textColor = "#E5E7EB";
        gridColor = "rgba(64, 62, 60, 1)";
    }

    if (systemTheme === 'light' && userTheme === 'light') {
        textColor = "#111827";
        gridColor = "rgba(168, 162, 155, 1)";
    }

    return {
        textColor: textColor,
        gridColor: gridColor,
    };
}

const updateChartThemes = () => {
    Chart.helpers.each(Chart.instances, function (chart) {
        if (chart) {
            const options = chart.options;

            // Update legend text color
            if (options.plugins?.legend?.labels) {
                options.plugins.legend.labels.color = getCurrentChartColors().textColor;
            }

            // Update title text color
            if (options.plugins?.title) {
                options.plugins.title.color = getCurrentChartColors().textColor;
            }

            // Update axes colors (only if scales exist)
            if (options.scales) {
                if (options.scales.x) {
                    options.scales.x.ticks.color = getCurrentChartColors().textColor;
                }
                if (options.scales.y) {
                    options.scales.y.ticks.color = getCurrentChartColors().textColor;
                }
            }
            // Update grid line colors
            if (options.scales) {
                if (options.scales.x) {
                    options.scales.x.grid.color = getCurrentChartColors().gridColor;
                }
                if (options.scales.y) {
                    options.scales.y.grid.color = getCurrentChartColors().gridColor;
                }
            }

            try {
                chart.update();
                console.log(`Chart with ID ${chart.id} updated successfully.`);
            } catch (error) {
                console.error(`Error updating chart with ID ${chart.id}: ${error}`);
            }
        }
    });
};

// Function to set button state based on localStorage
const setButtonStateFromLocalStorage = () => {
    if (getCurrentTheme() === 'dark') {
        themeToggleDarkIcon.classList.add('hidden');
        themeToggleLightIcon.classList.remove('hidden');
        //document.documentElement.classList.add('dark');
    } else {
        themeToggleDarkIcon.classList.remove('hidden');
        themeToggleLightIcon.classList.add('hidden');
        //document.documentElement.classList.remove('dark');
    }
};

// Event listener for theme toggle button
if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', () => {
        // Toggle theme class
        document.documentElement.classList.toggle('dark');
        const newTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        localStorage.setItem('color-theme', newTheme);

        // Update button state and charts
        setButtonStateFromLocalStorage();
        updateChartThemes();
    });
}

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
    const newTheme = event.matches ? 'dark' : 'light';

    // Update localStorage
    localStorage.setItem('color-theme', newTheme);

    // Update the <html> class
    if (newTheme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    setButtonStateFromLocalStorage();
    updateChartThemes();
});


// Event listener for storage change in other tabs/windows
window.addEventListener('storage', (event) => {
    if (event.key === 'color-theme') {
        setButtonStateFromLocalStorage();
        updateChartThemes();
    }
});

// Initially set button state and update chart themes when the page loads
setButtonStateFromLocalStorage();
updateChartThemes();

// I want to set a constant called 'theme' that will be used across the script, its value needs to be taken from 'input[type="hidden"][name="theme"]' if there such an elememt, if not it needs to be 'sky'

// Initiate theme across the script
const themeInput = document.querySelector('input[type="hidden"][name="theme"]');
const theme = themeInput ? themeInput.value : 'sky';

/* Back Button */
const backButtons = document.querySelectorAll('.back-button');

if (backButtons.length > 0) {
    backButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (history.length > 1 || document.referrer) {
                history.back();
            } else {
                location.href = '/'
            }
        }, false)
    });
}

const generateUniqueId = (length) => {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const charactersLength = characters.length;
    let id = '';

    for (let i = 0; i < length; i++) {
        const randomIndex = Math.floor(Math.random() * charactersLength);
        id += characters.charAt(randomIndex);
    }

    return id;
};

const isJSONString = (string) => {
    try {
        JSON.parse(string);
        return true;
    } catch (error) {
        return false
    }
}

const decodeHTMLEntities = (text) => {
    let element = document.createElement('textarea');
    element.innerHTML = text;
    return element.value;
}

const escapeHtml = (input) => {
    return String(input).replace(/[&<>"'\/]/g, function (s) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            '/': '&#x2F;'
        }[s];
    });
}

function serialize(data) {
    return Object.keys(data).map(key => 
        encodeURIComponent(key) + '=' + encodeURIComponent(data[key])
    ).join('&');
}

function serializeForBackend(data) {
    // Convert your data object/array to a serialized format compatible with PHP
    // This could be a custom serialization function if you want to keep it simple
    return Object.entries(data).map(([key, value]) => {
        return `${key}:${typeof value === 'object' ? serializeForBackend(value) : value}`;
    }).join(';');
}
/* Scroll to top button */

// Actual snooth scroll to top function. /8 shows how smooth or quick it should do it
const scrollToTop = () => {
    const c = document.documentElement.scrollTop || document.body.scrollTop;
    if (c > 0) {
        window.requestAnimationFrame(scrollToTop);
        window.scrollTo(0, c - c / 8);
    }
};

// Scroll to top button and function
const backToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

//Get the button
let scrollToTopButton = document.getElementById("btn-back-to-top");

// When the user scrolls down 20px from the top of the document, show the button
window.onscroll = function () {
    scrollFunction();
};

const scrollFunction = () => {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        scrollToTopButton.style.display = "block";
    } else {
        scrollToTopButton.style.display = "none";
    }
}
// When the user clicks on the button, scroll to the top of the document
scrollToTopButton.addEventListener("click", backToTop);

/* Autload stuff */

const initializeAutoLoadedComponents = () => {
    const autoLoadParams = document.querySelectorAll('input[type="hidden"][name="autoload"]');

    if (autoLoadParams.length === 0) return;

    autoLoadParams.forEach(input => {
        const value = JSON.parse(input.value);
        const type = value.type;
        const data = value.data;

        switch (type) {
            case 'gaugechart':
                createGaugeChart(data.title, data.parentDiv, data.width, data.height, data.data[0], data.data[1]);
                break;

            case 'donutchart':
                createDonutChart(data.title, data.parentDiv, data.width, data.height, data.labels, data.data);
                break;

            case 'piechart':
                createPieChart(data.title, data.parentDiv, data.title, data.height, data.width, data.labels, data.data);
                break;

            case 'linechart':
                createLineChart(data.title, data.parentDiv, data.width, data.height, data.labels, data.datasets);
                break;

            case 'barchart':
                createBarChart(data.title, data.parentDiv, data.width, data.height, data.labels, data.data);
                break;

            case 'table':
                const table = createSkeletonTable();
                document.getElementById(value.parentDiv).appendChild(table);
                const dataGridTable = drawDataGridFromData(data, table.id, value.tableOptions || {});
                if (value.tableOptions === null || value.tableOptions?.filters) {
                    buildDataGridFilters(dataGridTable, table.id);
                }
                break;

            default:
                console.warn(`Unknown autoload type: ${type}`);
        }
    });
};

initializeAutoLoadedComponents();

// Handle the submitting of the IP address form if there is a get request with query paramter "ip". If it does, find a form with .generic-form class and simulate a click on the form submitter
const urlParams = new URLSearchParams(window.location.search);
const ip = urlParams.get('ip');
if (ip) {
    const form = document.querySelector('form.generic-form');
    // Also update the value of input with name "ip" to the ip address
    const ipInput = document.querySelector('input[name="ipAddress"]');
    ipInput.value = ip;

    // Find the submit button by tag name (button) or by class name (e.g., "submit-button-class")
    const submitButton = form.querySelector('button'); // By tag name
    // Or, if the button has a class name:
    // const submitButton = form.querySelector('.submit-button-class');
    if (submitButton) {
        submitButton.click();
    }
}

// Universal fetch function
async function fetchData(form, resultDivId = null, resultType = null) {
    const formMethod = form.getAttribute('method'); // Use getAttribute to get PUT/DELETE
    const csrfToken = (form.querySelector('input[name="csrf_token"]')) ? form.querySelector('input[name="csrf_token"]').value : null;
    
    const fetchOptions = {
        method: formMethod,
        headers: {
            'secretheader': 'badass',
            'X-CSRF-TOKEN': csrfToken
        },
        redirect: 'manual'
    };

    const formData = new FormData(form);

    if (formMethod === 'POST') {
        fetchOptions.body = formData;
    } else if (formMethod === 'PUT') {
        fetchOptions.headers['Content-Type'] = 'application/json';
        const formDataObject = {};
        formData.forEach((value, key) => {
            formDataObject[key] = value;
        });
        fetchOptions.body = JSON.stringify(formDataObject);
    } else if (formMethod === 'DELETE' || formMethod === 'GET') {
        fetchOptions.body = new URLSearchParams(formData);
    } else {
        fetchOptions.body = formData;
    }

    const response = await fetch(form.action, fetchOptions);
    const responseStatus = response.status;
    let responseData = null;

    if (response.status === 0 || response.status === 403) {
        if (response.type === 'opaqueredirect') {
            // redirect to the desired page response.url
            location.reload(response.url);
        } else {
            location.reload();
        }
    }

    if (form.getAttribute("data-reload") === "true") {
        location.reload();
        // Otherwise display the returned data
    } else if (form.getAttribute("data-redirect")) {
        location.href = form.getAttribute("data-redirect");
        // If data-delete-current-row, delete the current <tr> element
    } else if (form.getAttribute("data-delete-current-row")) {
        // Now find the closest tr and delete it
        currentEvent.target.closest("tr").remove();
    }

    const resultDiv = (resultDivId) ? document.getElementById(resultDivId) : document.getElementById(`${form.id}-result`);

    const contentType = response.headers.get('Content-Type') || '';

    if (contentType.includes('application/json')) {
        responseData = await response.json();
        let responseType = 'json'; // Mark it as JSON
    } else {
        responseData = await response.text();
    }



    let returnData = '';
    if (resultType === null) {
        if (responseStatus >= 400) {
            window.alert(`Error: ${responseData.data}`);
        }

        return;
    }
    if (responseType === 'json') {
        console.log("Handling JSON response");
        
        if (responseType === 'json' && responseData.result) {
            returnData = responseData.data || responseData;
        } else {
            returnData = responseData;
        }
        let text = 'green';
        if (responseStatus >= 400) {
            text = 'red';
        }
    } else {
        console.log("Handling text response");
        returnData = responseData;
        resultDiv.innerHTML = returnData;
    }

    if (resultType === 'text') {
        resultDiv.innerText = returnData;
    } else if (resultType === 'json') {
        resultDiv.innerHTML = `<p class="w-fit text-white bg-${text}-500 font-semibold p-1 border border-gray-900 rounded-md">${returnData}</p>`;
    } else {
        resultDiv.innerHTML = returnData;
    }
}

const createLoader = (parentDiv, id, text = null, hidden = false) => {
    // Create the container div
    const loaderDiv = document.createElement('div');
    loaderDiv.id = id;
    loaderDiv.classList.add('flex', 'flex-col', 'items-center', 'justify-center');

    if (hidden) {
        loaderDiv.classList.add('hidden');
    }

    // Create the loader div
    const loader = document.createElement('div');
    loader.role = 'status';
    loaderDiv.appendChild(loader);

    // Add the loading text
    if (text !== null) {
        const span = document.createElement('span');
        span.textContent = text;
        span.classList.add('text-sm', 'text-gray-900', 'dark:text-gray-100');
        loaderDiv.appendChild(span);
    }

    // Create the SVG element
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('aria-hidden', 'true');
    svg.classList.add(
        'text-gray-200',
        'animate-spin',
        'dark:text-gray-600',
        `fill-${theme}-500`
    );
    if (text === null) {
        svg.classList.add('h-5', 'w-5');
    } else {
        svg.classList.add('h-8', 'w-8');
    }
    svg.setAttribute('viewBox', '0 0 100 101');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');

    // Add the first path
    const path1 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    path1.setAttribute(
        'd',
        'M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z'
    );
    path1.setAttribute('fill', 'currentColor');
    svg.appendChild(path1);

    // Add the second path
    const path2 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    path2.setAttribute(
        'd',
        'M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z'
    );
    path2.setAttribute('fill', 'currentFill');
    svg.appendChild(path2);

    // Append the SVG to the loader
    loader.appendChild(svg);

    // Append the loader to the parent div
    parentDiv.appendChild(loaderDiv);

    return loaderDiv;
};

const loaderString = (text = null) => {
    let srOnlyClass = '';
    let marginTop = 'mt-2 ';
    if (text === null) {
        srOnlyClass = 'sr-only ';
        marginTop = '';
    }
    return `
    <div role="status" class="flex flex-col items-center justify-center">
        <span class="${srOnlyClass}text-black dark:text-white">Loading data...</span>
        <svg class="${marginTop}animate-spin h-5 w-5 text-black dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0
                    014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
    </div>
    `;
}

const editModal = (id, entryId, table) => {
    let html = `
     <form id="${id}-form">
        <!-- Main modal -->
        <div id="${id}-container" class="relative bg-gray-50 dark:bg-gray-700 md:max-w-2xl max-w-full max-h-full md:mx-auto mx-2 border border-gray-700 dark:border-gray-400 shadow overflow-auto">
            <!-- Modal header -->
            <div class="mx-4 flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Editing entry with id ${entryId} in table <u>${table}</u>
                </h3>
                <button id="${id}-x-button" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="${id}">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal content -->
            <div class="relative overflow-auto max-h-[44rem] rounded-lg dark:bg-gray-700 ">
                <!-- Modal body -->
                <div id="${id}-body" class="p-4 md:p-5 space-y-4 break-words">
                        <p class="text-base leading-relaxed text-gray-700 dark:text-gray-400"></p>
                </div>
            </div>
            <!-- Modal Result -->
            <div id="${id}-result" class="m-4"></div>
            <!-- Modal footer -->
            <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button id="${id}-edit" data-modal-hide="${id}" type="submit" class="text-white bg-${theme}-700 hover:bg-${theme}-800 focus:ring-4 focus:outline-none focus:ring-${theme}-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-${theme}-600 dark:hover:bg-${theme}-700 dark:focus:ring-${theme}-800">Edit</button>
                <button id="${id}-close-button" data-modal-hide="${id}" type="button" class="ms-3 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-${theme}-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">Cancel</button>
            </div>
        </div>
    </form>
    `;
    const modal = document.createElement('div');
    modal.id = id;
    // Add data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
    modal.classList.add('hidden', 'overflow-auto', 'fixed', 'top-0', 'right-0', 'left-0', 'z-50', 'justify-center', 'items-center', 'w-full', 'md:inset-0', 'h-[calc(100%-1rem)]', 'max-h-full', 'mt-4');
    modal.setAttribute('data-modal-backdrop', 'static');
    modal.setAttribute('tabindex', '-1');
    modal.setAttribute('aria-hidden', 'true');
    // Add the html to the modal
    modal.innerHTML = html;
    return modal;
}

const deleteModal = (id, confirmMessage) => {
    let html = `
    <!-- Main modal -->
        <div id="${id}-container" class="relative w-full max-w-2xl max-h-full mx-auto">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 border border-gray-700 dark:border-gray-400">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Delete 
                    </h3>
                    <button id="${id}-x-button" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="${id}">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div id="${id}-body" class="p-4 md:p-5 space-y-4 break-words">
                    <p class="text-base leading-relaxed text-gray-700 dark:text-gray-400">
                        ${confirmMessage}
                    </p>
                </div>
                <!-- Modal Result -->
                <div id="${id}-result" class="m-4"></div>
                <!-- Modal footer -->
                <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                    <button id="${id}-delete" data-modal-hide="${id}" type="button" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">Delete</button>
                    <button id="${id}-close-button" data-modal-hide="${id}" type="button" class="ms-3 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-red-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">Cancel</button>
                </div>
            </div>
        </div>
    `;
    const modal = document.createElement('div');
    modal.id = id;
    // Add data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
    modal.classList.add('hidden', 'overflow-y-hidden', 'overflow-x-hidden', 'fixed', 'top-0', 'right-0', 'left-0', 'z-50', 'justify-center', 'items-center', 'w-full', 'md:inset-0', 'h-[calc(100%-1rem)]', 'max-h-full', 'mt-12');
    modal.setAttribute('data-modal-backdrop', 'static');
    modal.setAttribute('tabindex', '-1');
    modal.setAttribute('aria-hidden', 'true');
    // Add the html to the modal
    modal.innerHTML = html;
    return modal;
}

const toggleBlur = (excludeElement) => {
    const addBlurClass = (element) => {
        // Apply the hardcoded class 'blur-sm' to the element
        element.classList.toggle('blur-sm');

        // Recursively process child elements
        for (const child of element.children) {
            addBlurClass(child);
        }
    };

    // Get all elements in the body
    const allElements = document.body.getElementsByTagName('*');

    // Iterate through the elements and apply the class conditionally
    for (const currentElement of allElements) {
        // Check if the element is not the one to exclude or its descendant
        if (currentElement !== excludeElement && !excludeElement.contains(currentElement)) {
            addBlurClass(currentElement);
        }
    }
}



// Edit button

const editButtons = document.querySelectorAll(`button.edit-button`);

if (editButtons.length > 0) {
    editButtons.forEach(button => {
        button.addEventListener('click', async (event) => {
            // First fetch the data from /api/datagrid/get-records
            const uniqueId = generateUniqueId(4);
            // Generate the modal
            let modal = editModal(uniqueId, button.dataset.id, button.dataset.table);
            // Insert the modal at the bottom of the first div after the body
            document.body.insertBefore(modal, document.body.firstChild);
            // Now show the modal
            modal.classList.remove('hidden');
            // Now let's disable scrolling on the rest of the page by adding overflow-hidden to the body
            document.body.classList.add('overflow-hidden');
            // Blur the body excluding the modal
            toggleBlur(modal);
            // Let's make some bindings
            const modalResult = document.getElementById(`${uniqueId}-result`);
            // First, the close button
            const closeXButton = document.getElementById(`${uniqueId}-x-button`);
            // The cancel button
            const cancelButton = document.getElementById(`${uniqueId}-close-button`);
            // Modal Save button
            const saveButton = document.getElementById(`${uniqueId}-edit`);
            const cancelButtonsArray = [closeXButton, cancelButton];

            // Function to close the modal
            const closeModal = () => {
                // Completely remove the modal
                modal.remove();
                // Return the overflow of the body
                document.body.classList.remove('overflow-hidden');
                // Remove the blur by toggling the blur class
                toggleBlur(modal);

                // Remove the Escape key listener once the modal is closed
                document.removeEventListener('keydown', handleEscapeKey);
            };

            // Add click listeners to the cancel buttons
            cancelButtonsArray.forEach(cancelButton => {
                cancelButton.addEventListener('click', closeModal);
            });

            // Function to handle the Escape key press
            const handleEscapeKey = (event) => {
                if (event.key === 'Escape') { // Check if the Escape key was pressed
                    closeModal();
                }
            };

            // Add the Escape key listener when the modal is shown
            document.addEventListener('keydown', handleEscapeKey);
            
            let modalBody = document.getElementById(`${uniqueId}-body`);
            createLoader(modalBody, `${uniqueId}-loader`, 'Loading data...');
            // Fetch the data
            const formData = new FormData();
            formData.append('table', button.dataset.table);
            formData.append('columns', button.dataset.columns);
            formData.append('id', button.dataset.id);
            formData.append('csrf_token', button.dataset.csrf);
            const getDataApi = (button.dataset.getApi) ? button.dataset.getApi : '/api/datagrid/get-records';
            const data = fetch(getDataApi, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': button.dataset.csrf,
                    'secretheader': 'badass'
                },
                body: formData
            }).then(response => response.text());
            modalBody.innerHTML = await data;
            let initialButtonText = saveButton.innerText;
            // Let's focus the cursor make the focus on the first input
            const firstInput = modalBody.querySelector('input');
            firstInput.focus();
            // Now let's make the save button work
            saveButton.addEventListener('click', async () => {
                // First, get the form data
                saveButton.innerHTML = '';
                createLoader(saveButton, `${uniqueId}-save-button-edit-loader`);
                // Prevent the form from submitting
                const modalForm = document.getElementById(`${uniqueId}-form`);
                modalForm.addEventListener('submit', (event) => {
                    event.preventDefault();
                });
                // Build the body of the update request. We need to go through all the inputs and get their values
                const formData = new FormData();
                // Loop through all of the modalBody inputs, textarea and select and save them to the formData
                const modalBodyInputs = modal.querySelectorAll(`input, textarea, select`);
                modalBodyInputs.forEach(input => {
                    let value = input.value;
                    // Check if the value is a valid number
                    if (!isNaN(value) && value.includes('.')) {
                        // Convert to float and format to a specific number of decimal places (e.g., 2)
                        value = parseFloat(value);
                    }
                    formData.append(input.name, value);
                });
                // Now let's take care of potential checkboxes
                const modalBodyCheckboxes = modal.querySelectorAll(`input[type=checkbox]`);
                // Loop through the checkboxes and if they are checked, we transmit the value as 1, else as 0
                modalBodyCheckboxes.forEach(checkbox => {
                    formData.append(checkbox.name, checkbox.checked ? 1 : 0);
                });
                let responseStatus = 0;
                formData.append('id', button.dataset.id);
                const editApi = (button.dataset.editApi) ? button.dataset.editApi : '/api/datagrid/update-records';
                // Now let's fetch the data
                fetch(editApi, {
                    method: 'POST',
                    headers: {
                        'secretHeader': 'badass',
                        'X-CSRF-TOKEN': formData.get('csrf_token')
                    },
                    body: formData,
                    redirect: 'manual'
                }).then(response => {
                    responseStatus = response.status;
                    console.log(responseStatus);
                    if (responseStatus === 403 || responseStatus === 401 || responseStatus === 0) {
                        modalBody.innerHTML = `<p class="text-red-500 font-semibold">Response not ok, refreshing</p>`;
                        location.reload();
                    } else {
                        // Check if the response is JSON or text/HTML
                        if (response.headers.get('content-type').includes('application/json')) {
                            return response.json().then(data => ({ data, isJson: true }));
                        } else {
                            return response.text().then(data => ({ data, isJson: false }));
                        }
                    }
                }).then(({ data, isJson }) => {
                    // If the response status is >= 400, handle it as an error
                    if (responseStatus >= 400) {
                        saveButton.innerText = 'Retry';
                        let errorMessage = isJson ? (data.data || JSON.stringify(data)) : data;
                        modalResult.innerHTML = `<p class="text-red-500 font-semibold">${errorMessage}</p>`;
                    } else {
                        saveButton.innerText = initialButtonText;
                
                        if (isJson) {
                            modalResult.innerHTML = `<p class="text-green-500 font-semibold">${data.data}</p>`;
                            location.reload();
                        } else {
                            modalResult.innerHTML = `<p class="text-red-500 font-semibold">${data}</p>`;
                        }
                    }
                }).catch(error => {
                    console.error('Error during fetch:', error);
                });                                           
            })
        });
    });
}

// Delete button

const deleteButtons = document.querySelectorAll(`button.delete-button`);

if (deleteButtons.length > 0) {
    deleteButtons.forEach(button => {
        button.addEventListener('click', async (event) => {
            // First fetch the data from /api/datagrid/get-records
            const uniqueId = generateUniqueId(4);
            // Get the confirm message
            const confirmMessage = button.dataset.confirmMessage || `Are you sure you want to delete entry with id ${button.dataset.id}?`;
            // Generate the modal
            let modal = deleteModal(uniqueId, confirmMessage);
            // Insert the modal at the bottom of the first div after the body
            document.body.insertBefore(modal, document.body.firstChild);
            // Now show the modal
            modal.classList.remove('hidden');
            // Now let's disable scrolling on the rest of the page by adding overflow-hidden to the body
            document.body.classList.add('overflow-hidden');
            // Blur the body excluding the modal
            toggleBlur(modal);
            // Let's make some bindings
            const modalResult = document.getElementById(`${uniqueId}-result`);
            // First, the close button
            const closeXButton = document.getElementById(`${uniqueId}-x-button`);
            // The cancel button
            const cancelButton = document.getElementById(`${uniqueId}-close-button`);
            // Modal Save button
            const saveButton = document.getElementById(`${uniqueId}-delete`);
            const cancelButtonsArray = [closeXButton, cancelButton];
            cancelButtonsArray.forEach(cancelButton => {
                cancelButton.addEventListener('click', () => {
                    // Completely remove the modal
                    modal.remove();
                    // Return the overflow of the body
                    document.body.classList.remove('overflow-hidden');
                    // Remove the blur by toggling the blur class
                    toggleBlur(modal);
                })
            })
            let initialButtonText = saveButton.innerText;
            // Now let's make the save button work
            saveButton.addEventListener('click', async () => {
                // First, get the form data
                saveButton.innerHTML = '';
                createLoader(saveButton, `${uniqueId}-save-button-delete-loader`);
                // Build the body of the update request. We need to go through all the inputs and get their values
                const formData = new FormData();
                formData.append('id', button.dataset.id);
                formData.append('csrf_token', button.dataset.csrf);
                formData.append('table', button.dataset.table);
                const deleteApi = (button.dataset.deleteApi) ? button.dataset.deleteApi : '/api/datagrid/delete-records';
                let responseStatus = 0;
                // Now let's fetch the data
                fetch(deleteApi, {
                    method: 'POST',
                    headers: {
                        'secretHeader': 'badass',
                        'X-CSRF-TOKEN': formData.get('csrf_token')
                    },
                    body: formData
                }).then(response => {
                    responseStatus = response.status;
                    if (responseStatus === 403 || responseStatus === 401 || responseStatus === 0) {
                        modalResult.innerHTML = `<p class="text-red-500 font-semibold">Response not ok, refreshing</p>`;
                        location.reload();
                    } else {
                        // Check if the response is JSON or text/HTML
                        if (response.headers.get('content-type').includes('application/json')) {
                            return response.json().then(data => ({ data, isJson: true }));
                        } else {
                            return response.text().then(data => ({ data, isJson: false }));
                        }
                    }
                }
                ).then(({ data, isJson }) => {
                    // If the response status is >= 400, handle it as an error
                    if (responseStatus >= 400) {
                        saveButton.innerText = 'Retry';
                        let errorMessage = isJson ? (data.data || JSON.stringify(data)) : data;
                        modalResult.innerHTML = `<p class="text-red-500 font-semibold">${errorMessage}</p>`;
                    } else {
                        saveButton.innerText = initialButtonText;
                
                        if (isJson) {
                            modalResult.innerHTML = `<p class="text-green-500-font-semibold">${data.data}</p>`;
                            location.reload();
                        } else {
                            modalResult.innerHTML = `<p class="text-red-500 font-semibold">${data}</p>`;
                        }
                    }
                }).catch(error => {
                    console.error('Error during fetch:', error);
                });
            })
        });
    });
}

const createModal = (id, title, submitButtonName, parentDiv, action) => {
    let html = `
     <form id="${id}-form" action="${action}" method="POST">
        <!-- Main modal -->
        <div id="${id}-container" class="relative bg-gray-50 dark:bg-gray-700 md:max-w-2xl max-w-full max-h-full md:mx-auto mx-2 border border-gray-700 dark:border-gray-400 shadow overflow-auto">
            <!-- Modal header -->
            <div class="mx-4 flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    ${title}
                </h3>
                <button id="${id}-x-button" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal content -->
            <div class="relative overflow-auto max-h-[44rem] rounded-lg dark:bg-gray-700">
                <div id="${id}-body" class="p-4 md:p-5 space-y-4 break-words">
                        <p class="text-base leading-relaxed text-gray-700 dark:text-gray-400"></p>
                </div>
            </div>
            <!-- Modal Result -->
            <div id="${id}-result" class="m-4"></div>
            <!-- Modal footer -->
            <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button id="${id}-edit" type="submit" class="text-white bg-${theme}-700 hover:bg-${theme}-800 focus:ring-4 focus:outline-none focus:ring-${theme}-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-${theme}-600 dark:hover:bg-${theme}-700 dark:focus:ring-${theme}-800">${submitButtonName}</button>
                <button id="${id}-close-button" type="button" class="ms-3 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-${theme}-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">Cancel</button>
            </div>
        </div>
    </form>
    `;

    const modal = document.createElement('div');
    modal.id = id;
    modal.classList.add('overflow-auto', 'fixed', 'top-0', 'right-0', 'left-0', 'z-50', 'justify-center', 'items-center', 'w-full', 'md:inset-0', 'h-[calc(100%-1rem)]', 'max-h-full', 'mt-4');
    modal.setAttribute('data-modal-backdrop', 'static');
    modal.setAttribute('tabindex', '-1');

    modal.innerHTML = html;
    parentDiv.appendChild(modal);

    // Return the modal element so event listeners can be added separately
    return modal;
};

const initializeModal = (modal, uniqueId) => {
    // Show the modal
    modal.classList.remove('hidden');

    // Disable scrolling on the rest of the page
    document.body.classList.add('overflow-hidden');

    // Blur the body excluding the modal
    toggleBlur(modal);

    // Get modal buttons
    const closeXButton = document.getElementById(`${uniqueId}-x-button`);
    const cancelButton = document.getElementById(`${uniqueId}-close-button`);
    const cancelButtonsArray = [closeXButton, cancelButton];

    // Function to close the modal
    const closeModal = () => {
        modal.remove(); // Remove modal from DOM
        document.body.classList.remove('overflow-hidden'); // Restore scrolling
        toggleBlur(modal); // Remove blur effect
        document.removeEventListener('keydown', handleEscapeKey); // Remove Escape key listener
    };

    // Function to handle the Escape key press
    const handleEscapeKey = (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    };

    // Add click listeners to cancel buttons
    cancelButtonsArray.forEach(button => {
        if (button) button.addEventListener('click', closeModal);
    });

    // Add Escape key listener
    document.addEventListener('keydown', handleEscapeKey);
};

// Cookie Consent modal
const cookieConsentLink = document.getElementById('cookie-consent-link');

if (cookieConsentLink) {
    cookieConsentLink.addEventListener('click', async (event) => {
        event.preventDefault();
        const modalId = 'cookie-consent-modal';
        const modal = createModal(modalId, 'Cookie Consent', 'Accept', document.body, '/api/cookie-consent');
        document.body.insertBefore(modal, document.body.firstChild);
        initializeModal(modal, modalId);
        // Add a loader until we populate the data
        let modalBody = document.getElementById(`${modalId}-body`);
        createLoader(modalBody, `${modalId}-loader`, 'Loading data...');
        // Now that we have the loader spinning, let's pick up the cookie data
        const modalForm = document.getElementById(`${modalId}-form`);
        // Add a input hidden field in the form - get-consent
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'get-consent';
        hiddenInput.value = 'true';
        modalForm.appendChild(hiddenInput);
        // Now let's fetch the data
        // ✅ Fetch and log the resolved data
        try {
            const cookieData = await handleCookieFetchData(modalForm);
            modalBody.innerHTML = cookieData.data;
            if (cookieData.data === 'no consent') {
                modalBody.innerHTML = `You have not consented to cookies use yet. Please click the Accept button on the cookie banner to consent.`;
            }
            if (cookieData.data === 'accept') {
                modalBody.innerHTML = `You have already consented to cookies use.`;
                // Let's offer the user to revoke the consent
                document.getElementById(`${modalId}-edit`).innerText = 'Revoke';
                //Switch the theme with red
                document.getElementById(`${modalId}-edit`).classList.remove(`bg-${theme}-700`, `hover:bg-${theme}-800`, `focus:ring-${theme}-300`, `dark:bg-${theme}-600`, `dark:hover:bg-${theme}-700`, `dark:focus:ring-${theme}-800`);
                document.getElementById(`${modalId}-edit`).classList.add('bg-red-700', 'hover:bg-red-800', 'focus:ring-red-300', 'dark:bg-red-600', 'dark:hover:bg-red-700', 'dark:focus:ring-red-800');
                // Let's create a hidden input field to revoke the consent delete-consent
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'delete-consent';
                hiddenInput.value = 'true';
                modalForm.appendChild(hiddenInput);
                // Remove the get-consent hideen input
                modalForm.removeChild(document.querySelector('input[name="get-consent"]'));
                modalForm.addEventListener('submit', (event) => {
                    event.preventDefault();
                    // Send the request to revoke the consent
                    handleCookieFetchData(modalForm).then(data => {
                        if (data.data === 'consent deleted') {
                            modalBody.innerHTML = `You have successfully revoked the consent to use cookies.`;
                            // reload
                            location.reload();
                        }
                    });
                });
            }
        } catch (error) {
            console.error("Error fetching cookie data:", error);
        }
    });
}

const handleCookieFetchData = async (formElement) => {
    try {
        const response = await fetch('/api/cookie-consent', {
            method: 'POST',
            body: new FormData(formElement),
        });
        if (!response.ok) {
            throw new Error('Failed to fetch data');
        }
        const data = await response.json();
        //console.log('Fetched Data:', data);  // Debugging log
        return data; // Ensure you return the data here
    } catch (error) {
        console.error('Error fetching data:', error);
        return null; // Return null if there's an error
    }
};

function attachTooltip(targetElement, tooltipContent) {
    // Create tooltip element
    const tooltip = document.createElement('div');
    tooltip.innerHTML = tooltipContent;
    tooltip.style.position = 'absolute';
    tooltip.style.padding = '5px 10px';
    tooltip.style.background = 'black';
    tooltip.style.color = 'white';
    tooltip.style.borderRadius = '4px';
    tooltip.style.fontSize = '12px';
    tooltip.style.pointerEvents = 'none';
    tooltip.style.display = 'none';
    tooltip.style.zIndex = '9999';
    document.body.appendChild(tooltip);

    // Event listeners
    targetElement.addEventListener('mouseover', (e) => {
        tooltip.style.left = (e.pageX + 10) + 'px';
        tooltip.style.top = (e.pageY + 10) + 'px';
        tooltip.style.display = 'block';
    });

    targetElement.addEventListener('mousemove', (e) => {
        tooltip.style.left = (e.pageX + 10) + 'px';
        tooltip.style.top = (e.pageY + 10) + 'px';
    });

    targetElement.addEventListener('mouseout', () => {
        tooltip.style.display = 'none';
    });
    }

    const tooltipElements = document.querySelectorAll('.tooltip');

    if (tooltipElements.length > 0) {
    tooltipElements.forEach((element) => {
        element.classList.add('mouse-pointer');
        attachTooltip(element, element.getAttribute('data-tooltip'));
    });
}

// Search functionality for products

// Get the search input element
const liveSearchInputs = Array.from(document.getElementsByClassName('live-search-input'));

if (liveSearchInputs.length > 0) {
    // Add event listener to search input
    liveSearchInputs.forEach(search => {
        search.addEventListener('input', ({ target }) => {
            // Get the search value and convert it to lowercase
            const searchValue = target.value.toLowerCase();
            // Get all product names
            const searchForTextInClass = search.dataset.searchfortextinclass;
            const productNames = document.querySelectorAll(searchForTextInClass);
            
            // Loop through each product name and check if it includes the search value
            productNames.forEach((product) => {
                // Get the parent element of the product name
                const parentElement = search.dataset.parentElement;
                let productCard = product.parentElement;
                while (productCard && !productCard.classList.contains(parentElement.replace('.', ''))) {
                    productCard = productCard.parentElement;
                }
                // Check if the product name includes the search value
                const isMatch = product.textContent.toLowerCase().includes(searchValue);
                // Toggle the hidden class based on the search value
                productCard.classList.toggle('hidden', !isMatch);
            });
        });
    })
}