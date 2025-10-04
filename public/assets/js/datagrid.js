/* DataGrid DataTable

Here are the functions that are for DataGrid display. Using DataTables 1.12.1 library

REFACTORED STRUCTURE:
- All functionality is now modular and callable
- Use initializeDataGridActions(tableId) to initialize all actions for a specific table
- Use initializeAllDataGrids() to initialize all existing tables with class 'datagrid'
- Perfect for tables loaded via fetch/AJAX - just call initializeDataGridActions(tableId) after loading

Main functions:
- initializeEditButtons(tableId): Initialize edit button functionality
- initializeCheckboxes(tableId): Initialize checkbox functionality  
- initializeMassDelete(tableId): Initialize mass delete functionality
- initializeDeleteButtons(tableId): Initialize individual delete buttons
- initializeSelectAll(tableId): Initialize select all functionality
- initializeDataGridActions(tableId): Initialize ALL actions for a table
- initializeAllDataGrids(): Initialize all existing DataGrid tables

*/

// Utility functions
const countAllCheckedCheckboxes = (tableId) => {
    const table = document.getElementById(tableId);
    if (!table) return 0;
    return table.querySelectorAll('tbody > tr > td > input[type="checkbox"]:checked').length;
}

const countVisibleRows = (tableId) => {
    const table = document.getElementById(tableId);
    if (!table) return 0;
    return table.querySelectorAll('tbody > tr:not([style*="display: none;"])').length;
}

const updateSelectedResults = (tableId, newValue) => {
    const selectedResults = document.getElementById(tableId + '-selected');
    if (selectedResults) {
        selectedResults.innerText = newValue;
    }
}

const updateFilteredResults = (tableId, newValue) => {
    const filteredResults = document.getElementById(tableId + '-filtered');
    if (filteredResults) {
        filteredResults.innerText = newValue;
    }
}

// Main function to initialize edit button functionality
const initializeEditButtons = (tableId) => {
    const table = document.getElementById(tableId);
    if (!table) return;
    const editButtons = table.querySelectorAll('button.edit');

    if (editButtons.length > 0) {
        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                const uniqueId = generateUniqueId(4);
                // Generate the modal
                let modal = editModal(uniqueId, button.dataset.id);
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
                
                let modalBody = document.getElementById(`${uniqueId}-body`);
                let responseStatus = 0;
                const deleteLoadingScreen = document.getElementById(tableId + '-delete-loading-screen');
                
                // Now let's fetch data from the API and populate the modalBody. We need to send the id and the table name
                const formData = new FormData();
                // The Edit button has a data-columns attribute that contains the columns we need to fetch, so we want to send them to the API along with the id and table name
                formData.append('columns', button.dataset.columns);
                formData.append('table', button.dataset.table);
                formData.append('id', button.dataset.id);
                formData.append('csrf_token', button.dataset.csrf);
                fetch('/api/datagrid/get-records', {
                    method: 'POST',
                    headers: {
                        'secretHeader': 'badass',
                        'X-CSRF-TOKEN': button.dataset.csrf
                    },
                    body: formData,
                    redirect: 'manual'
                }).then(response => {
                    if (response.status === 0 || response.status === 401 || response.status === 403) {
                        location.reload();
                    }
                    // If response is JSON, then we probably got an error because we expect html from this request
                    if (response.headers.get('content-type').includes('application/json')) {
                        // Let's disable the save button
                        saveButton.disabled = true;
                        response.json().then(errorData => {
                            modalBody.innerHTML = `<p class="ml-4 w-fit font-bold text-red-500">${errorData.data}</p>`;
                        }).catch(error => {
                            modalBody.innerHTML = `<p class="ml-4 w-fit font-bold text-red-500">Error occurred, please try again later.</p>`;
                        });
                    }
                    return response.text();
                }).then(text => {
                    modalBody.innerHTML = '';
                    if (deleteLoadingScreen) {
                        deleteLoadingScreen.classList.add('hidden');
                    }
                    modalBody.innerHTML = text;
                });
                // Now the edit button
                const initialButtonText = saveButton.textContent;
                saveButton.addEventListener('click', () => {
                    // transform the button text to a loader
                    saveButton.innerHTML = loaderString();
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
                    // Now let's send a fetch request to /api/process with form-data api-action and the apiAction value
                    fetch('/api/datagrid/update-records', {
                        method: 'POST',
                        headers: {
                            'secretHeader': 'badass',
                            'X-CSRF-TOKEN': formData.get('csrf_token')
                        },
                        body: formData,
                        redirect: 'manual'
                    }).then(response => {
                        responseStatus = response.status;
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
            })
        });
    }
}

// Initialize checkbox functionality
const initializeCheckboxes = (tableId, theme = 'blue') => {
    // Get the modal elements for the mass delete
    const massDeleteModalTriggerer = document.getElementById(tableId + '-mass-delete-modal-trigger');
    const massDeleteModalText = document.getElementById(tableId + '-mass-delete-modal-text');

    // Disable it after Flowbite's JS
    if (massDeleteModalTriggerer) {
        massDeleteModalTriggerer.disabled = true;
    }

    // Get all the checkboxes in the table
    const table = document.getElementById(tableId);
    if (!table) return;
    const allTableCheckboxes = table.querySelectorAll('tbody > tr > td > input[type=checkbox]');
    if (allTableCheckboxes.length > 0) {
        allTableCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                // make row focused, 2 parent nodes behind
                if (checkbox.checked) {
                    checkbox.parentNode.parentNode.classList.add(`bg-${theme}-500`, `dark:bg-${theme}-600`, 'text-white', 'dark:text-white');
                } else {
                    checkbox.parentNode.parentNode.classList.remove(`bg-${theme}-500`, `dark:bg-${theme}-600`, 'text-white', 'dark:text-white');
                }
                const allTableCheckedCheckboxes = countAllCheckedCheckboxes(tableId);
                updateSelectedResults(tableId, allTableCheckedCheckboxes);
                updateFilteredResults(tableId, countVisibleRows(tableId));
                if (massDeleteModalTriggerer && massDeleteModalText) {
                    if (lang === 'en') {
                        massDeleteModalText.innerText = 'Are you sure you want to delete ' + allTableCheckedCheckboxes + ' entries?';
                    } else if (lang === 'bg') {
                        massDeleteModalText.innerText = 'Искате ли да изтриете ' + allTableCheckedCheckboxes + ' записа?';
                    }
                    massDeleteModalTriggerer.disabled = allTableCheckedCheckboxes <= 0;
                }
            }, false);
        })
    }
}

// Initialize mass delete functionality
const initializeMassDelete = (tableId) => {
    const deleteLoadingScreen = document.getElementById(tableId + '-delete-loading-screen');
    const deleteLoadingScreenText = document.getElementById(tableId + '-delete-loading-screen-text');

    // Find the mass delete form, which is in the container and has a class of delete-selected-form
    const deleteForms = document.querySelectorAll(`#${tableId}-container > form.delete-selected-form`);
    console.log(`Found ${deleteForms.length} mass delete forms for table ${tableId}.`);
    if (deleteForms.length > 0) {
        deleteForms.forEach(form => {
            form.addEventListener('submit', (event) => {
                deleteLoadingScreen.classList.remove('hidden');
                event.preventDefault();
                const data = new URLSearchParams(new FormData(form));
                let responseStatus = 0;
                fetch('/api/datagrid/delete-records', {
                    method: 'post',
                    // Let's send this secret header
                    headers: {
                        'secretHeader': 'badass',
                        'X-CSRF-TOKEN': data.get('csrf_token')
                    },
                    body: data,
                    redirect: 'manual'
                }).then(response => {
                    responseStatus = response.status;
                    if (responseStatus === 0 || responseStatus === 401 || responseStatus === 403) {
                        location.reload();
                    }
                    return response.json();
                }).then(json => {
                    if (responseStatus > 400) {
                        deleteLoadingScreenText.innerHTML = `<p class="text-red-500 font-semibold">${json.data}</p>`;
                        return;
                    }
                    // Assuming we get here is all good, quickly display the success message and reload the page
                    deleteLoadingScreenText.innerHTML = `<p class="text-green-500 font-semibold z-50">${json.data}</p>`;
                    location.reload();
                });
            }, false);
        })
    }
}

// Initialize individual delete buttons
const initializeDeleteButtons = (tableId) => {
    let totalResults = document.getElementById(tableId + '-total');
    const table = document.getElementById(tableId);
    if (!table) return;
    const deleteButtons = table.querySelectorAll('tbody > tr > td > button.delete');

    if (deleteButtons.length > 0) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                // insert the modal after the table
                let modal = deleteModal(tableId, `Delete entry with id <b>${event.target.dataset.id}</b> in table <b>${event.target.dataset.table}</b>?`);
                // Insert the modal at the bottom of the first div after the body
                document.body.insertBefore(modal, document.body.firstChild);
                // Now show the modal
                modal.classList.remove('hidden');
                // Now let's disable scrolling on the rest of the page by adding overflow-hidden to the body
                document.body.classList.add('overflow-hidden');
                // Blur the body excluding the modal
                toggleBlur(modal);
                // First, the close button
                const closeXButton = document.getElementById(`${tableId}-x-button`);
                // The cancel button
                const cancelButton = document.getElementById(`${tableId}-close-button`);
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
                const deleteModalButton = document.getElementById(`${tableId}-delete`);
                let modalBody = document.getElementById(`${tableId}-body`);
                let responseStatus = 0;
                deleteModalButton.addEventListener('click', () => {
                    // transform the button text to a loader
                    deleteModalButton.innerHTML = loaderString();
                    // Build the body of the delete request
                    const formData = new URLSearchParams();
                    formData.append('id', event.target.dataset.id);
                    formData.append('table', event.target.dataset.table);
                    formData.append('csrf_token', event.target.dataset.csrf);
                    fetch('/api/datagrid/delete-records', {
                        method: 'POST',
                        headers: {
                            'secretHeader': 'badass',
                            'X-CSRF-TOKEN': event.target.dataset.csrf
                        },
                        body: formData,
                        redirect: 'manual'
                    }).then(response => {
                        responseStatus = response.status;
                        if (responseStatus === 403 || responseStatus === 401 || responseStatus === 0) {
                            modalBody.innerHTML = `<p class="text-red-500 font-semibold">${json.data}</p>`;
                            location.reload();
                        } else {
                            return response.json()
                        }
                    }).then(json => {
                        // Return the overflow of the body
                        document.body.classList.remove('overflow-hidden');
                        // Remove the blur by toggling the blur class
                        toggleBlur(modal);
                        if (responseStatus === 200) {
                            // if we are deleting from a table, Delete the closest row
                            const closestRow = button.closest('tr');
                            if (closestRow) {
                                closestRow.remove();
                            }
                            // Decrement the total results
                            totalResults.innerText = parseInt(totalResults.innerText) - 1;
                            // Decrease the filtered results if the row is visible
                            if (closestRow.style.display !== 'none') {
                                updateFilteredResults(tableId, countVisibleRows(tableId) - 1);
                            }
                            // Completely remove the modal
                            modal.remove();
                        } else {
                            deleteModalButton.textContent = 'Retry';
                            modalBody.innerHTML = `<p class="text-red-500 font-semibold">${json.data}</p>`;
                        }
                    }).catch(error => {
                        console.error('Error during fetch:', error);
                    })
                })
            }, false);
        });
    }
}

// Initialize select all functionality
const initializeSelectAll = (tableId, theme = 'blue') => {
    const massDeleteModalTriggerer = document.getElementById(tableId + '-mass-delete-modal-trigger');
    const massDeleteModalText = document.getElementById(tableId + '-mass-delete-modal-text');

    /* Check all checkboxes functionality */
    // Get the "All" checkbox in the table
    // So basically find the checkbox all checkbox and bind a click event on it
    $(document).on("change", `#${tableId} input.select-all`, function (event) {
        // Find all the visible checkboxes, jquery returns an object so we user Object.entries() to conver to array so we can forEach it and get through the individual rows in the table
        Object.entries($(`#${tableId} tbody tr:visible :checkbox`).prop('checked', this.checked).closest('tr')).forEach(([index, row]) => {
            // Weirdly jquery returns an extra key in the array that's not an html element, so it throws errors unless we do this check
            if (row instanceof Element) {
                // If select all checkbox is checked, change the color of the row
                if (event.target.checked) {
                    row.classList.add(`bg-${theme}-500`, `dark:bg-${theme}-600`, 'text-white', 'dark:text-white');
                } else {
                    // Else remove the extra classes
                    row.classList.remove(`bg-${theme}-500`, `dark:bg-${theme}-600`, 'text-white', 'dark:text-white');
                }
            }
        });
        // After this action, we need to update the "Selected" count, as well as tell the modal how many rows we have selected         
        const allTableCheckedCheckboxes = countAllCheckedCheckboxes(tableId);
        updateSelectedResults(tableId, allTableCheckedCheckboxes);
        if (massDeleteModalTriggerer && massDeleteModalText) {
            if (lang === 'en') {
                massDeleteModalText.innerText = 'Are you sure you want to delete ' + allTableCheckedCheckboxes + ' entries?';
            } else if (lang === 'bg') {
                massDeleteModalText.innerText = 'Искате ли да изтриете ' + allTableCheckedCheckboxes + ' записа?';
            }
            massDeleteModalTriggerer.disabled = (allTableCheckedCheckboxes > 0) ? false : true;
        }
    });
}

// Initialize all DataGrid functionalities for a specific table
const initializeDataGridActions = (tableId, theme = 'blue') => {
    updateFilteredResults(tableId, countVisibleRows(tableId));
    initializeEditButtons(tableId);
    initializeCheckboxes(tableId, theme);
    initializeMassDelete(tableId);
    initializeDeleteButtons(tableId);
    initializeSelectAll(tableId, theme);
}

// Initialize all existing DataGrid tables
const initializeAllDataGrids = () => {
    const tables = document.querySelectorAll(`table.datagrid`);
    
    if (tables.length > 0) {
        console.log(`Found ${tables.length} DataGrid tables to initialize.`);
        tables.forEach(table => {
            // Try to extract theme from table row classes or use default
            const firstRow = table.querySelector('tbody tr');
            let theme = 'blue'; // default theme
            if (firstRow) {
                const rowClasses = firstRow.className;
                const themeMatch = rowClasses.match(/focus:bg-(\w+)-500/);
                if (themeMatch) {
                    theme = themeMatch[1];
                }
            }
            initializeDataGridActions(table.id, theme);
            console.log(`Initialized DataGrid actions for table: ${table.id} with theme: ${theme}`);
        });
    }
}

const constructOptions = (dataLength, options) => {
    // Let's analyze the options passed to the function
    if (options === null) {
        options = {
            searching: true,
            ordering : true,
            order : [[0, 'desc']],
            paging : true,
            filters: true, // Default to true when options is null
            lengthMenu : [[25, 50, 100, -1], [25, 50, 100, "All"]],
            info: true,
            export: {
                csv: true,
                tsv: true
            }
        };
    }

    // Let's analyze the options passed to the function
    options.searching = options.searching ?? true;
    options.ordering = options.ordering ?? true;
    options.order = options.order ?? [[0, 'desc']];

    // Ensure `filters` remains true by default, only set to false if explicitly undefined
    options.filters = options.filters ?? true;

    // Sort the paging
    if (dataLength < 6 && options.paging === undefined) {
        options.paging = false;
    }

    if (options.lengthMenu === undefined) {
        let defaultLengthMenu = [[25, 50, 100, -1], [25, 50, 100, "All"]];
        if (dataLength >= 1000) {
            defaultLengthMenu[0].splice(-1, 0, 250, 500);
            defaultLengthMenu[1].splice(-1, 0, 250, 500);
        }
        if (dataLength >= 10000) {
            defaultLengthMenu[0].splice(-1, 0, 2500, 5000);
            defaultLengthMenu[1].splice(-1, 0, 2500, 5000);
        }
        options.lengthMenu = defaultLengthMenu;
    }

    // Set `info` to true if either paging or searching is enabled
    options.info = options.info ?? (options.paging || options.searching);
    
    // Now the export buttons
    if (options.export === undefined) {
        options.export = {
            csv : true,
            tsv : true
        };
    }
    return options;
}
const drawDataGrid = (id, options = null, theme = 'blue') => {
    // Let's analyze the options passed to the function
    options = constructOptions(0, options);

    // Let's check if the first th contains a checkbox, if it does, we disable ordering for the first column because it doesn't make sense to sort checkboxes
    const firstThContainsCheckbox = $(`#${id} thead th:first-child input[type="checkbox"]`).length > 0;

    const table = $(`#${id}`).DataTable({
        ordering: options.ordering,
        order: options.order,
        searching: options.searching,
        autoWidth: false,
        //scrollY: '60vh',
        info: options.info,
        paging: options.paging,
        lengthMenu: options.lengthMenu,
        columnDefs: [
            { orderable: !firstThContainsCheckbox, targets: 0 }, // Disable ordering for the first column
        ],
        initComplete: function () {
            // Remove the loading screen
            $(`#${id}-loading-table`).remove();
            // Create filter rows outside of header callback
            const filterRow = $('<tr class="filter-row"></tr>').appendTo(`#${id} thead`);
            // Get the API instance
            const api = this.api();
            // Append an empty <th> for each column in the header
            api.columns().every(function () {
                filterRow.append('<th></th>');
            });
            // Remove the hidden from the table to show it after the data is loaded
            document.getElementById(`${id}`).classList.remove('hidden');
            
            // Initialize all actions for this table after DataTable is ready
            initializeDataGridActions(id, theme);
        },
    });
    return table;
}


const drawDataGridFromData = (json, skeletonId, options = null, theme = 'blue') => {

    options = constructOptions(json.length, options);

    const tableWrapper = $('<div class="mx-2 overflow-auto max-h-[44rem]"></div>'); // Create a wrapper div for the table
    // Create the loading screen for the table
    const loadingScreen = tableLoadingScreen(skeletonId);
    // Append the loading screen before the table
    $(`#${skeletonId}`).before(loadingScreen);

    const dataArray = [];
    const headers = [];
    const uniqueKeys = new Set();
    json.forEach(obj => {
        // Iterate over the keys and values of each user's data
        Object.keys(obj).forEach(key => {
            // Check if the key is not already present in the uniqueKeys set
            if (!uniqueKeys.has(key)) {
                // If not, push the key to the headers array and add it to the uniqueKeys set
                headers.push({ name: key });
                uniqueKeys.add(key);
            }
        });

        // Initialize an array to store user data
        const tempArray = Object.values(obj);

        // Push the tempArray to the dataArray
        dataArray.push(tempArray);
    });

    const tableHeaders = headers.map(column => ({
        title: column.name
        //data: column.name
    }));

    // Create the table and add data
    const table = $(`#${skeletonId}`).DataTable({
        data: dataArray,
        columns: tableHeaders,
        paging: options.paging,
        info: options.info,
        lengthMenu: options.lengthMenu,
        ordering: options.ordering,
        order: options.order,
        searching: options.searching,
        createdRow: function (row, data, dataIndex) {
            $(row).attr('tabindex', dataIndex);
            $(row).addClass(`even:bg-gray-200 odd:bg-gray-100 dark:even:bg-gray-700 dark:odd:bg-gray-600 focus:bg-${theme}-500 dark:focus:bg-gray-500`);
        },
        // now let's apply a common set of classes to all <td>
        "columnDefs": [{
            "targets": "_all",
            "createdCell": function (td, cellData, rowData, row, col) {
                // Apply the common class names for each table cell
                $(td).addClass('px-4 py-2 text-sm text-gray-900 dark:text-gray-300 truncate max-w-xs');
                // Let's deal with long text and truncate it so it doesn't make our table extremely long
                if (td.innerHTML.length > 60) {
                    $(td).addClass('truncate');
                    $(td).addClass('overflow-hidden');
                    //$(td).addClass('max-w-2xl');
                    // And here only show 60 cahracters of the text
                    $(td).text(decodeHTMLEntities(cellData).substring(0, 120) + '...');
                    // Add the full text as a title so it's visible on hover
                    $(td).attr('title', decodeHTMLEntities(cellData));
                } else {
                    $(td).text(decodeHTMLEntities(cellData));
                }
            }
        }],
        initComplete: function () {
            document.getElementById(loadingScreen.id).remove();
            // Initialize all actions for this table after DataTable is ready
            initializeDataGridActions(skeletonId, theme);
        }
    });
    // Example of adding Tailwind CSS classes to style the thead
    $(`#${skeletonId} thead`).addClass('bg-gray-200 dark:bg-gray-700 sticky top-0 border-collapse');

    // Create filter rows outside of header callback
    // if ordering is enabled, add the ordering icons to the thead
    if (options.ordering === true) {   
        $(`#${skeletonId} thead tr:first-child th`).append(` <span class="text-xs text-${theme}-400 cursor-pointer">&#x25B2;&#x25BC;</span>`);
    }
    // now add those classes to the first row of the thead
    $(`#${skeletonId} thead tr:first-child th`).addClass('px-4 py-2 text-center text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider cursor-pointer');
    const filtersRow = $('<tr></tr>').insertAfter($(`#${skeletonId} thead tr`));
    if (options.filters) {
        tableHeaders.forEach(header => {
            filtersRow.append(`<th></th>`);
        });
    }
    $(`#${skeletonId}`).wrap(tableWrapper); // Wrap the table with the wrapper div

    // Now if the export buttons are enabled, let's add them
    const buttons = [];

    // Check for CSV export option
    if (options.export.csv === true) {
        buttons.push({
            type: 'csv',
            url: '/api/tools/export-csv'
        });
    }

    // Check for TSV export option
    if (options.export.tsv === true) {
        buttons.push({
            type: 'tsv',
            url: '/api/tools/export-tsv'
        });
    }

    // Create a container for the buttons
    const buttonContainer = document.createElement('div');
    buttonContainer.className = 'mt-4'; // Add margin-top for spacing

    // Dynamically create and append buttons to the buttonContainer
    buttons.forEach(button => {
        // Create a new button element
        const exportButton = document.createElement('button');
        exportButton.innerText = `Export ${button.type.toUpperCase()}`;
        exportButton.className = `p-2 ml-2 mt-2 text-gray-500 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600`;

        // Add a click event listener for exporting data
        exportButton.addEventListener('click', function () {
            // Create a FormData object and append the JSON data
            const form = new FormData();
            form.append('data', JSON.stringify(json));

            // Now let's append the csrf token, let's take it from the meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content || '';
            form.append('csrf_token', csrfToken);

            // Send POST request to the appropriate export URL (CSV/TSV)
            fetch(button.url, {
                method: 'POST',
                body: form,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => {
                // Check if the response is not okay (status not in the range 200-299)
                if (!response.ok) {
                    // Handle specific status codes
                    switch (response.status) {
                        case 401:
                            // Handle unauthorized access
                            alert('Unauthorized access. Please log in.');
                            break;
                        case 403:
                            // Handle forbidden access
                            alert('You do not have permission to access this resource.');
                            break;
                        case 404:
                            // Handle not found
                            alert('Requested resource not found.');
                            break;
                        case 500:
                            // Handle server error
                            alert('Server error. Please try again later.');
                            break;
                        default:
                            alert('An error occurred: ' + response.statusText);
                    }
                    throw new Error('Network response was not ok: ' + response.statusText);
                }

                // If the response is ok, proceed to process the blob
                return response.blob();
            })
            .then(blob => {
                // Create a temporary download link for the file
                const link = document.createElement('a');
                const url = window.URL.createObjectURL(blob);
                link.href = url;
                const currentDate = new Date().toISOString().replace(/:/g, '-');
                link.download = `export-${currentDate}.${button.type}`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            })
            .catch(error => console.error('Error exporting file:', error));
        });

        // Append the export button to the button container
        buttonContainer.appendChild(exportButton);
    });
    // Append the button container to the parent of the table's wrapper
    $(`#${skeletonId}`).parent().after(buttonContainer);
    
    return table;
};

/**
 * Builds select filters for a DataTable.
 *
 * @param {DataTable} table - The DataTable instance.
 * @param {string} tableId - The ID of the table element.
 * @param {Array} columnSkipArray - An optional array of column indexes to skip.
 * @returns {void} - Creates the DataTable filters. Does not return a value.
 */
const buildDataGridFilters = (table, tableId, columnSkipArray = [], enabled = true) => {
    if (!enabled) {
        return;
    }
    // Loop through each column of the DataTable
    table.columns().every(function (col) {
        if (columnSkipArray.includes(col)) {
            return;
        }
        const column = table.column(this, { search: 'applied' }); // Get the DataTable column object

        // Create a select element and append it to the appropriate table header cell. (1) in this case is the 2nd thead so it doesn't do it on the first where the column names are
        const select = $(`<select class="text-center m-1 p-1 text-gray-900 border border-${theme}-300 rounded bg-gray-50 focus:ring-${theme}-500 focus:border-${theme}-500 dark:bg-gray-700 dark:border-${theme}-600 dark:placeholder-gray-400 dark:text-white outline-none dark:focus:ring-${theme}-500 dark:focus:border-${theme}-500"><option value="">No filter</option></select>`)
            .appendTo($(`#${tableId} thead tr:eq(1) th`).eq(column.index()).empty())
            .on('change', function () {
                const val = $.fn.dataTable.util.escapeRegex(
                    $(this).val()
                );
                // Apply the selected filter value to the column and redraw the table
                column
                    .search(val ? '^' + val + '$' : '', true, false)
                    .draw();
            });

        // Calculate the maximum width for the select options
        let maxOptionWidth = Math.min(
            $(column.header()).outerWidth() || select.width(),
            150
        ); // You can adjust the maximum width as needed
        if (maxOptionWidth < 150) {
            maxOptionWidth = 150;
        }

        // Iterate through the unique values in the column, create options for the select element
        column.data().unique().sort().each(function (d, j) {
            if (d !== null) {
                let optionText = d;
                if (optionText.length > maxOptionWidth / 2)  {
                    if (optionText.length > maxOptionWidth / 2) {
                        // Truncate the option text if it's longer than the maxOptionWidth
                        optionText = optionText.substring(0, maxOptionWidth / 2) + '...';
                    } else {
                        optionText = optionText + '...';
                    }
                }
                // Append the option with the selected attribute if necessary
                select.append(`<option value="${d}" title="${d}">${optionText}</option>`);
            }
        });

        // Repopulate the select element based on the current search filter
        const currSearch = column.search();
        if (currSearch) {
            const searchValue = currSearch.substring(1, currSearch.length - 1);
            select.val(searchValue); // Set the selected value

            // Loop through the options and set the 'selected' attribute explicitly for the matched option
            select.find('option').each(function () {
                const optionValue = $(this).val();
                // We need to replace the special characters from searchValue as searchValue comes with escaped special characters from column.search(). We want to apply the selected prop for the currently selected option so it's visible what has been filtered right now
                if (optionValue === searchValue.replace(/\\/g, '')) {
                    $(this).prop('selected', true);
                } else {
                    $(this).prop('selected', false);
                }
            });
            // Mark the select as red to be seen as selected more easily
            select.addClass('border-red-500');
            select.addClass('dark:border-red-500');
        }

        // Target both rows in the thead and add the classes in one go
        $(`#${tableId} thead tr:eq(0), #${tableId} thead tr:eq(1) th`).addClass('px-4 py-2 text-center text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider');

    });
}

const createSkeletonTable = () => {
    // First decide the random ID
    const skeletonId = generateUniqueId(4);
    // Create the skeleton table
    const table = document.createElement('table');
    table.id = skeletonId;
    // Add some classes to the table
    table.classList.add('table-auto', 'text-center', 'mt-4', 'min-w-full');
    return table;
}

const tableLoadingScreen = (tableId) => {
    const loadingScreen = document.createElement('div');
    loadingScreen.id = `${tableId}-loading-table`;
    loadingScreen.classList.add('m-4', 'bg-gray-500', 'h-10', 'w-full', 'text-center', 'text-white');
    loadingScreen.innerHTML = `Data Loading... Please wait<svg class="inline mx-4 w-8 h-8 text-gray-200 dark:text-white animate-spin fill-blue-600 dark:fill-gray-500" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>`;
    return loadingScreen;
}

// Initialize existing DataGrids when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeAllDataGrids();
});