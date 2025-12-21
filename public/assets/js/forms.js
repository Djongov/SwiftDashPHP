
class Stopwatch {
    constructor(timerElement) {
        this.timer = timerElement;
        this.offset = 0;
        this.clock = 0;
        this.interval = null;
        this.timer.innerHTML = '0s';
    }

    start() {
        if (!this.interval) {
            this.offset = Date.now();
            this.interval = setInterval(() => this.update(), 100);
        }
    }

    stop() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
    }

    reset() {
        this.clock = 0;
        this.render();
    }

    update() {
        this.clock += this.delta();
        this.render();
    }

    render() {
        this.timer.innerHTML = (this.clock / 1000).toFixed(2) + 's';
    }

    delta() {
        const now = Date.now();
        const d = now - this.offset;
        this.offset = now;
        return d;
    }
}

// This is the fucntion that will create the modal for the form submission if confirm class on the form exists
const generateModal = (text, id) => {
    let buttonYesText = 'Yes, I\'m sure';
    if (lang === 'bg') {
        buttonYesText = 'Да, сигурен съм';
    }
    let noButton    = 'No, cancel';
    if (lang === 'bg') {
        noButton = 'Не, откажи';
    }
    
    // Create the html element div
    const modalDiv = document.createElement('div');
    // The modal HTML
    const html = `<div id="${id}-mass-delete-modal" tabindex="-1" class="fixed inset-0 flex items-center justify-center z-50" aria-hidden="true">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto overflow-auto">
            <div class="relative p-4 max-w-md w-full mx-auto bg-white border border-gray-400 dark:border-gray-300 rounded-lg shadow dark:bg-gray-700">
                <button id="${id}-x-cancel" type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="${id}-modal">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="p-6 text-center">
                    <svg aria-hidden="true" class="mx-auto mb-4 w-14 h-14 text-gray-400 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 id="${id}-modal-text" class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">${text}</h3>
                    <button id="${id}-submit" data-modal-toggle="${id}-modal" type="submit" class="my-2 text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                        ${buttonYesText}
                    </button>
                    <button id="${id}-cancel" data-modal-toggle="${id}-modal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">${noButton}</button>
                </div>
            </div>
        </div>
    </div>`;
    // Append
    modalDiv.innerHTML += html;
    // Return
    return modalDiv;
}

// This function handles the form submission, takes care of the result div where data return is displayed
const handleFormFetch = (form, currentEvent, resultType) => {
    const resultDiv = document.getElementById(`${form.id}-result`);
    
    if (resultDiv?.classList.contains('generic-form-submit-div')) {
        resultDiv.remove();
    }
    
    let newResultDiv = createFormResultDiv(form);
    form.parentNode.insertBefore(newResultDiv, form.nextSibling);
    //const initialClasses = currentEvent.submitter.className;
    const initialSubmitName = currentEvent.submitter.innerText;
    currentEvent.submitter.innerHTML = `
    <div role="status">
        <svg aria-hidden="false" class="inline w-6 h-6 text-gray-50 dark:text-white animate-spin fill-${theme}-600 dark:fill-${theme}-500" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
        </svg>
    </div>
    `;
    let stopwatch;

    // Initialize the stopwatch if data-stopwatch is set
    if (form.hasAttribute('data-stopwatch')) {
        // Remove any existing stopwatch div
        const existingStopwatchDiv = form.querySelector(`#${form.getAttribute('data-stopwatch')}-stopwatch`);
        if (existingStopwatchDiv) {
            existingStopwatchDiv.remove();
        }

        // Create a new div for the stopwatch
        const stopwatchDiv = document.createElement('div');
        stopwatchDiv.id = `${form.getAttribute('data-stopwatch')}-stopwatch`;
        stopwatchDiv.classList.add('flex', 'items-center', 'text-md', 'text-gray-700', 'dark:text-gray-200', 'font-semibold', 'm-2');

        // Clock SVG
        const clockSvg = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 stroke-gray-900 dark:stroke-gray-200">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>`;

        // Span for '0s' text
        const timeSpan = document.createElement('span');
        timeSpan.classList.add('ml-1');
        timeSpan.textContent = '0s';

        // Append SVG and span to stopwatchDiv
        stopwatchDiv.innerHTML = clockSvg;
        stopwatchDiv.appendChild(timeSpan);

        // Append stopwatchDiv to form
        form.appendChild(stopwatchDiv);

        // Initialize Stopwatch class with the span element
        stopwatch = new Stopwatch(timeSpan);

        // Reset the stopwatch
        stopwatch.reset();

        // Start the stopwatch
        stopwatch.start();
    }


    // Disable the submit button to prevent multiple submissions
    currentEvent.submitter.disabled = true;

    // Pick up the data from the form
    const formData = new FormData(form);

    // If you still want to add specific values for checkboxes, you can do it like this
    const toggleCheckboxes = form.querySelectorAll('input[type="checkbox"]');
    if (toggleCheckboxes.length > 0) {
        toggleCheckboxes.forEach(checkbox => {
            let checkboxValue = checkbox.checked ? 1 : 0;
            formData.set(checkbox.name, checkboxValue);
            console.log(`Setting ${checkbox.name} to ${checkboxValue}`);
        });
    }

    // Let's deal with grouped checkboxes
    const checkboxesInGroups = form.querySelectorAll('[class*="checkbox-group-"]');
    checkboxesInGroups.forEach(checkbox => {
        const groupName = checkbox.classList[checkbox.classList.length - 1];
        const checkboxesPerGroup = form.querySelectorAll(`.${groupName}`);
        checkboxesPerGroup.forEach(groupCheckbox => {
            if (groupCheckbox.checked) {
                formData.set(groupCheckbox.name, groupCheckbox.value);
            }
        });

        // If none of the checkboxes in the group is checked
        const checkedCheckboxes = form.querySelectorAll(`.${groupName}:checked`);
        if (checkedCheckboxes.length === 0) {
            formData.delete(checkboxesInGroups[0].name);
        }
    });

    // Form method and CSRF token
    const formMethod = form.getAttribute('data-method');
    const csrfToken = form.querySelector('input[name="csrf_token"]').value;

    const fetchOptions = {
        method: formMethod,
        headers: {
            'secretheader': 'badass',
            'X-CSRF-TOKEN': csrfToken
        },
        redirect: 'manual'
    };
    
    // Let's see if data-headers attribute is set on the form
    const customHeaders = form.getAttribute('data-headers');

    if (customHeaders) {
        // Parse the custom headers
        const headers = JSON.parse(customHeaders);
        // Add them to the fetch options
        for (const [key, value] of Object.entries(headers)) {
            fetchOptions.headers[key] = value;
        }
    }

    // Adjusting based on method:
    if (formMethod === 'POST') {
        // Check if the form contains any file inputs
        const hasFileInputs = form.querySelector('input[type="file"]') !== null;
        
        if (hasFileInputs) {
            // For POST with file uploads, use FormData (multipart/form-data)
            fetchOptions.body = formData;
        } else {
            // For POST without files, use URL-encoded format
            fetchOptions.headers['Content-Type'] = 'application/x-www-form-urlencoded';
            fetchOptions.body = new URLSearchParams(formData);
        }
    } else if (formMethod === 'PUT') {
        // For PUT, handle with JSON, but no file uploads (as per your original logic)
        fetchOptions.headers['Content-Type'] = 'application/json';

        // Convert form data to a JavaScript object
        const formDataObject = {};
        formData.forEach((value, key) => {
            formDataObject[key] = value;
        });

        fetchOptions.body = JSON.stringify(formDataObject);
    } else if (formMethod === 'GET') {
        // For DELETE and GET, handle URL-encoded data (no files)
        const data = new URLSearchParams(formData);
        fetchOptions.body = data;
    } else if (formMethod === 'DELETE') {
        // Let's be compliant and not have any body for DELETE requests
        fetchOptions.body = null;
    } else {
        // For other methods (like PATCH), use FormData (including file uploads)
        fetchOptions.body = formData;
    }
    // If the form method is GET, and formData is empty, we need to remove the body
    if (formMethod === 'GET' && fetchOptions.body.size <= 1) {
        delete fetchOptions.body;
    }

    // Fetch function
    fetch(form.action, fetchOptions)
        // Handle response
        .then(response => {
            // We need to stop the stopwatch
            if (stopwatch) {
                stopwatch.stop();
            }
            
            currentEvent.submitter.disabled = false;
            const contentType = response.headers.get("content-type");
            // If response is redirect (0) or 403 return by the server, usually token expired, reload the page
            if (response.status === 0 || response.status === 403) {
                if (response.type === 'opaqueredirect') {
                    // redirect to the desired page response.url
                    location.reload(response.url);
                } else {
                    // Handle fetch interruption
                    newResultDiv.innerHTML = '<p class="font-semibold text-red-500">Fetch interrupted. Refreshing page</p>';
                    location.reload();
                }
            } else if (response.status === 405) {
                //window.alert(`Receiving HTTP Status 405 means that you might have a misconfiguration on the server not accepting verbs such as ${formMethod}`);
                return response.text();
            } else {
                if (contentType && contentType.indexOf("application/json") === -1) {
                    return response.text();
                }
                return response.json();
            }
            console.log(resultType);
            // Hanle after the response comes
        }).then(response => {
            currentEvent.submitter.innerText = initialSubmitName;
            newResultDiv.innerHTML = '';
            if (typeof response === 'undefined') {
                console.log(response);
                return;
            }
            if (response.redirect) {
                window.location.href = response.redirect;
                return; // Exit the function after redirecting
            }
            // If the response is of type text()
            if (typeof response === 'string') {
                if (resultType === 'html') {
                    newResultDiv.innerHTML = `${response}`;
                } else {
                    newResultDiv.innerText = response;
                }
                // Let's search the response for table and if we find a table, get the table id
                const tablesArray = newResultDiv.querySelectorAll('table');
                // If there are any tables in the result, there is a good chance that they are datagrids and will need initialization
                if (tablesArray.length > 0) {
                    tablesArray.forEach(table => {
                        // get the table id
                        const tableId = table.getAttribute('id');
                        const dataTable = drawDataGrid(tableId);
                        buildDataGridFilters(dataTable, tableId, []);
                        // On every re-draw, rebuild them
                        dataTable.on('draw', () => {
                            console.log(`redraw occured`);
                            buildDataGridFilters(dataTable, tableId, []);
                        });
                        initializeAllDataGrids();
                    })
                }
                
                // Initialize any AGGrid instances that were loaded via fetch
                if (typeof window.initializeAGGrids === 'function') {
                    console.log('forms.js: Calling initializeAGGrids after form fetch');
                    window.initializeAGGrids(newResultDiv);
                } else {
                    console.log('forms.js: AGGrid initialization function not available');
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
                    /*
                    let td = currentEvent.target.parentNode.parentNode;
                    let tr = td.parentNode;
                    tr.parentNode.removeChild(tr);
                    */
                }
                // If there were copy buttons in the response, let's initiate them
                copyToClipboard();
                // If we have html coming in, it could hold other forms, so let's initiate them. So let's find if there is a form.generic selector in the response
                initiateGenericForms();
                return;
            }
            // Return the button text to the initial state, remove the loading spinner
            // handle error from the front end api processing endpoint
            if (response.error) {
                newResultDiv.classList.add('text-red-500', 'font-semibold', 'ml-0');
                newResultDiv.innerHTML = `<p class="text-red-500 font-semibold ml-0">${response.error}</p>`;
                return;
            }
            // handle error from the remote api processing endpoint
            if (response.result !== 'success') {
                // if response is [object Object] it means it's an error
                console.log(response.data);
                newResultDiv.classList.add('text-red-500', 'font-semibold', 'ml-0');
                newResultDiv.innerHTML = `<p class="text-red-500 font-semibold ml-0">${response.data}</p>`;
            } else {
                // Now successful respones from the API
                // If data-reload is on the form, instruct to reload the page
                if (form.getAttribute("data-reload") === "true") {
                    location.reload();
                    // Otherwise display the returned data
                } else if (form.getAttribute("data-redirect")) {
                    location.href = form.getAttribute("data-redirect");
                    // If data-delete-current-row, delete the current <tr> element
                } else if (form.getAttribute("data-delete-current-row")) {
                    // Now find the closest tr and delete it
                    currentEvent.target.closest("tr").remove();
                    /*
                    let td = currentEvent.target.parentNode.parentNode;
                    let tr = td.parentNode;
                    tr.parentNode.removeChild(tr);
                    */
                } else {
                    console.log(`result type is ${typeof response.data}`)
                    const result = (typeof response.data === 'object') ? JSON.stringify(response.data) : response.data;
                    // If the result type is html, display the response as html
                    if (resultType === 'html') {
                        if (typeof response.data === 'object') {
                            newResultDiv.innerHTML = `<pre class="text-sm font-mono text-gray-800 dark:text-gray-400 whitespace-pre-wrap">${result}</pre>`;
                        } else {
                            newResultDiv.innerHTML = `<p class="font-semibold text-green-500 ml-2">${result}</p>`;
                        }
                        return;
                    }
                    newResultDiv.innerText = result;
                }
            }
            // Catch errors
        }).catch(error => {
            if (error === null) {
                newResultDiv.innerHTML = `<p class="font-semibold text-red-500">An unknown error occured</p>`;
            } else {
                // Handle other errors, including network errors
                console.error('Error:', error);
                // Display the error message in newResultDiv
                newResultDiv.innerHTML = `<p class="font-semibold text-red-500">An error occurred: ${error.message}</p>`;
            }
        });
}

// This function handles form submission when confirm class exists
const handleFormSubmitWithConfirm = (currentEvent, form, initialSubmitName, resultType) => {
    // Create a random Id for the modal
    const randomId = generateUniqueId(4);
    // See if we are passing text for the modal via the form data-confirm
    const confirmText = form.getAttribute('data-confirm');
    // If we are set it, if not default to Are you sure?
    const modalText = (confirmText) ? form.getAttribute("data-confirm") : 'Are you sure?';
    // Generate the modal
    const modal = generateModal(modalText, randomId);
    // Insert the modal after the form
    form.parentNode.insertBefore(modal, form.nextSibling);
    // Let's get the buttons of the modal so we can attach event listeners
    const confirmButton = document.getElementById(`${randomId}-submit`);
    const cancelButton = document.getElementById(`${randomId}-cancel`);
    const xCancelButton = document.getElementById(`${randomId}-x-cancel`);

    if (form.classList.contains('double-confirm')) {
        // diable the submit button on the modal
        confirmButton.disabled = true;
        // create a new input field and add it to the modal
        const newInput = document.createElement("input");
        newInput.type = "text";
        newInput.classList.add('my-4', 'mx-auto', 'bg-gray-50', 'border', 'border-gray-300', 'outline-none', 'text-gray-900', 'text-sm', 'rounded-lg', 'focus:ring-red-500', 'focus:border-red-500', 'block', 'w-full', 'p-2.5', 'dark:bg-gray-700', 'dark:border-gray-600', 'dark:placeholder-gray-400', 'dark:text-white', 'dark:focus:ring-red-500', 'dark:focus:border-red-500');
        confirmButton.parentNode.insertBefore(newInput, confirmButton);

        // Find out the keyword to type
        const keyWord = (form.getAttribute('data-double-confirm-keyword')) ?? null;

        const inputParagraph = document.createElement('p');
        if (lang === 'en') {
            inputParagraph.innerHTML = `Type <span class="break-all">"${keyWord}"</span> to continue`;
        } else if (lang === 'bg') {
            inputParagraph.innerHTML = `Напишете <span class="break-all">"${keyWord}"</span> за да продължите`;
        }

        newInput.parentNode.insertBefore(inputParagraph, newInput);

        confirmButton.classList.remove('bg-red-600', 'hover:bg-red-800');
        confirmButton.classList.add('bg-gray-400', 'hover:bg-gray-600');

        newInput.addEventListener('input', () => {
            if (newInput.value === keyWord) {
                confirmButton.classList.remove('bg-gray-400', 'hover:bg-gray-600');
                confirmButton.classList.add('bg-red-600', 'hover:bg-red-800');
                confirmButton.disabled = false;
            } else {
                confirmButton.classList.remove('bg-red-600', 'hover:bg-red-800');
                confirmButton.classList.add('bg-gray-400', 'hover:bg-gray-600');
                confirmButton.disabled = true;
            }
        })

    }
    // There are two close buttons so put them in an array
    const cancelButtonsArray = [cancelButton, xCancelButton];
    // Hide the modal
    modal.classList.remove('hidden');
    // So on Yes click on the modal, remove the modal and start the fetch function
    confirmButton.addEventListener('click', () => {
        modal.remove();
        handleFormFetch(form, currentEvent, resultType)
    });
    // If user cancels the modal, again remove the modal and return to the initial button text
    cancelButtonsArray.forEach(button => {
        button.addEventListener('click', () => {
            modal.remove();
            currentEvent.submitter.innerText = initialSubmitName;
        })
    })
}

/* Search within selects. For this to work the search input needs to be just before the select */
const searchInputs = document.querySelectorAll('input.filterSearch');

if (searchInputs.length > 0) {
    searchInputs.forEach(input => {
        input.addEventListener('input', () => {
            const searchValue = input.value.toLowerCase();
            const select = input.parentNode.querySelector('select');
            const options = select.querySelectorAll('option');
            options.forEach(option => {
                if (option.value.toLowerCase().indexOf(searchValue) > -1) {
                    option.style.display = '';
                    // Also make sure that the option is selected
                    option.selected = true;
                } else {
                    option.style.display = 'none';
                }
            })
        })
    })
}


/* Generic forms submit functionality */
const initiateGenericForms = () => {
    // Query all forms produced by the backend
    const genericForms = document.querySelectorAll('form.generic-form');

    // If there are any forms
    if (genericForms.length === 0) {
        return;
    }
    // Loop through each
    genericForms.forEach(form => {
        // if data-trigger is set on the form and is false, we skip all javascript functionality
        if (form.getAttribute('data-trigger') === 'false') {
            return;
        }
        // Let's search for checkbox groups in the form. They all start with checkbox-group- followed by a random string. Let's try to catch each group
        const checkboxesInGroups = form.querySelectorAll('[class*="checkbox-group-"]');

        checkboxesInGroups.forEach(checkbox => {
            const groupName = checkbox.classList[checkbox.classList.length - 1];

            const checkboxesPerGroup = form.querySelectorAll(`.${groupName}`);

            checkboxesPerGroup.forEach(groupCheckbox => {
                groupCheckbox.addEventListener('change', () => {
                    // Uncheck other checkboxes in the same group
                    checkboxesPerGroup.forEach(otherCheckbox => {
                        if (otherCheckbox !== groupCheckbox && otherCheckbox.type === 'checkbox') {
                            otherCheckbox.checked = false;
                        }
                    });
                });
            });
        });

        // let's check if the form has an id, if not generate one
        if (!form.id) {
            const formId = generateUniqueId(4);
            form.id = formId;
        }

        // Check if the event listener is already attached
        if (!form.hasAttribute('data-submit-listener')) {
            // Attach submit event
            form.addEventListener('submit', (event) => {
                // Prevent normal submitting only if target is not _blank
                if (form.getAttribute('target') !== '_blank') {
                    event.preventDefault();
                }
                // Disable the submitter as someone can click multiple times sending multiple requests
                //event.submitter.disabled = true;
                // Check the result type (text or html) if declared in the form
                let resultType = form.getAttribute('data-result');
                // Remember the initial button text
                const initialButtonText = event.submitter.innerText;
                // Now turn the button text to a spinner
                // If there is a confirm required (via confirm class)
                if (form.classList.contains('confirm')) {
                    // use the handle with confirm function
                    handleFormSubmitWithConfirm(event, form, initialButtonText, resultType);
                } else {
                    // Otherwise just proceed with the fetch
                    handleFormFetch(form, event, resultType);
                }
            });
            // Mark the form to indicate that the listener is attached
            form.setAttribute('data-submit-listener', 'true');
        }
    });
}

const createFormResultDiv = (form) => {
    // Let's create the resultDiv where we will show the result of the form submission
    let resultDiv = document.createElement('div');
    const resultDivId = `${form.id}-result`;
    resultDiv.id = resultDivId;
    resultDiv.classList.add('text-gray-900', 'dark:text-gray-300', 'generic-form-submit-div', 'break-all');
    return resultDiv;
}

const handleFetchDataResponse = (response, form) => {
    // So the response is a Promise, so let's resolve it
    response.then(response => {
        // Let's get the result div
        const resultDiv = document.getElementById(`${form.id}-result`);
        // If the result div is already there, empty it
        if (resultDiv) {
            resultDiv.remove();
        }
        // Create a new result div
        let newResultDiv = createFormResultDiv(form);
        form.parentNode.insertBefore(newResultDiv, form.nextSibling);
        // If the response is a string
        if (typeof response === 'string') {
            // If the result type is html, display the response as html
            if (form.getAttribute('data-result') === 'html') {
                newResultDiv.innerHTML = response;
            } else {
                newResultDiv.innerText = response;
            }
            // If there is a reload attribute on the form, reload the page
            if (form.getAttribute('data-reload') === 'true') {
                location.reload();
            } else if (form.getAttribute('data-redirect')) {
                location.href = form.getAttribute('data-redirect');
            }
            // If there are any tables in the result, there is a good chance that they are datagrids and will need initialization
            const tablesArray = newResultDiv.querySelectorAll('table');
            if (tablesArray.length > 0) {
                tablesArray.forEach(table => {
                    const tableId = table.getAttribute('id');
                    const dataTable = drawDataGrid(tableId);
                    buildDataGridFilters(dataTable, tableId, []);
                    dataTable.on('draw', () => {
                        buildDataGridFilters(dataTable, tableId, []);
                    });
                    initializeAllDataGrids();
                })
            }
            
            // Initialize any AGGrid instances that were loaded via fetch
            if (typeof window.initializeAGGrids === 'function') {
                console.log('forms.js: Calling initializeAGGrids after fetch data response');
                window.initializeAGGrids(newResultDiv);
            } else {
                console.log('forms.js: AGGrid initialization function not available');
            }
            
            // If there were copy buttons in the response, let's initiate them
            copyToClipboard();
            // If we have html coming in, it could hold other forms, so let's initiate them. So let's find if there is a form.generic selector in the response
            initiateGenericForms();
        }
    })
}

initiateGenericForms();

const selectSubmitterForms = document.querySelectorAll('form.select-submitter');

if (selectSubmitterForms.length > 0) {
    selectSubmitterForms.forEach(form => {
        form.addEventListener('change', async (event) => {
            event.preventDefault();
            const loader = createLoader(form, generateUniqueId(4));
            form.insertAdjacentElement("afterend", loader);
            await fetchData(form);
            loader.remove();
        });
    });
}
