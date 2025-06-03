
document.getElementById('start-grouped-products').addEventListener('click', () => {
    showGroupedProductsModal();
});

function showGroupedProductsModal() {
    // Prevent duplicate modal
    if (document.getElementById('grouped-products-modal')) {
        document.getElementById('grouped-products-modal').classList.remove('hidden');
        return;
    }

    const modalHTML = `
    <div id="grouped-products-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-2xl max-h-full mx-auto z-60">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 border border-gray-700 dark:border-gray-400">
                <!-- Start form -->
                <form id="grouped-products-form" class="space-y-4">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        ${translate('groupedProductsTitle')}
                    </h3>
                    <button type="button" id="close-grouped-products-modal" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="p-4 md:p-5 space-y-4">
                    <p class="text-base text-gray-700 dark:text-gray-300">${translate('groupedProductsDescription')}</p>

                    <!-- Product group name input -->
                    <div class="mb-4">
                        <label for="group-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">${translate('groupName')}</label>
                        <input type="text" id="group-name" name="group_name" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="${translate('groupNamePlaceholder')}" />
                    </div>
                    
                    <!-- Add button -->
                    <button id="add-product-button" type="button" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        ${translate('groupNameAddProductButton')}
                    </button>
                    <!-- Placeholder for added products -->
                    <ul id="grouped-products-list" class="space-y-2"></ul>
                </div>

                <!-- Modal footer -->
                <div class="flex items-center justify-start p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                    <button id="submit-group" type="submit" disabled class="bg-blue-500 opacity-50 cursor-not-allowed text-white font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        ${translate('groupNameCreateButton')}
                    </button>
                </div>
            </div>
        </div>
        </form>
    </div>
    `;

    document.getElementById('grouped-products-modal-wrapper').innerHTML = modalHTML;

    // Form submission logic
    document.getElementById('grouped-products-form').addEventListener('submit', (e) => {
        e.preventDefault();

        // Target the button and change it to loading state
        const submitBtn = e.target.querySelector('#submit-group');

        submitBtn.innerHTML = loaderString();

        submitBtn.disabled = true; // Disable the button to prevent multiple submissions

        // Gather the input values
        const formData = new FormData(e.target); // Serialize form data
    
        // Now send the data to the server
        fetch('/api/products/grouped-products', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error: ${response.status} ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.result === 'success') {
                // Close modal
                document.getElementById('grouped-products-modal').classList.add('hidden');
                // Optionally, refresh the page or update the UI to reflect the new group
                location.reload();
            } else {
                alert(JSON.stringify(data));
            }
        })
        .catch(error => {
            console.error(error.message);
            // Reset the button to its original state
            submitBtn.innerHTML = translate('groupNameCreateButton');
            submitBtn.disabled = false; // Re-enable the button
            alert(error.message);
        });
    });

    // Close modal on "X"
    document.getElementById('close-grouped-products-modal').addEventListener('click', () => {
        document.getElementById('grouped-products-modal').classList.add('hidden');
    });

    // Block background close
    document.getElementById('grouped-products-modal').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) {
            // Do nothing
        }
    });

    // Handle Add Product (+)
    document.getElementById('add-product-button').addEventListener('click', () => {
        const list = document.getElementById('grouped-products-list');

        const item = document.createElement('li');
        item.className = 'bg-gray-100 dark:bg-gray-800 p-2 rounded space-y-2';

        const inputId = 'product-search-' + Date.now();

        item.innerHTML = `
            <div class="flex items-center space-x-2">
                <input id="${inputId}" type="text" placeholder="${translate('groupedProductsSearchPlaceholder')}" 
                    class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                <button type="button" class="remove-product text-red-600 hover:text-red-800 text-sm">${translate('remove')}</button>
            </div>
            <ul class="search-results bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded max-h-40 overflow-y-auto hidden"></ul>
        `;

        list.appendChild(item);
        updateSubmitState();

        // Remove button logic
        item.querySelector('.remove-product').addEventListener('click', () => {
            item.remove();
            updateSubmitState();
        });

        // Search input logic
        const input = item.querySelector('input');
        const resultsList = item.querySelector('.search-results');

        let debounceTimeout;

        input.addEventListener('input', () => {
            clearTimeout(debounceTimeout);
            const query = input.value.trim();
            if (!query) {
                resultsList.classList.add('hidden');
                resultsList.innerHTML = '';
                return;
            }

            // Gather all existing product_ids[] and build an exclude parameter with those ids
            const existingProductIds = Array.from(document.querySelectorAll('input[name="product_ids[]"]'))
                .map(input => input.value)
                .join(',');

            debounceTimeout = setTimeout(() => {
                fetch('/api/products/search', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ query, exclude: existingProductIds })
                })
                .then(res => res.text())
                .then(html => {
                    resultsList.innerHTML = html;
                    resultsList.classList.toggle('hidden', html.trim() === '');

                    // Attach click handlers to each result <a>
                    resultsList.querySelectorAll('a.product-search-result').forEach(a => {
                        a.addEventListener('click', e => {
                            e.preventDefault();

                            const productUrl = a.getAttribute('href');
                            const productId = productUrl.split('/').pop();
                            let productTitle = a.querySelector('p.text-sm.font-medium')?.innerText.trim();
                            // Limit the title length to 50 characters
                            if (productTitle && productTitle.length > 50) {
                                productTitle = productTitle.substring(0, 50) + '...';
                            }
                            const productImage = a.querySelector('img')?.getAttribute('src');
                            const productStore = a.querySelector('.store')?.innerText.trim();
                            const productPrice = a.querySelector('.price')?.innerText.trim();
                            const productPriceEur = a.querySelector('.price-eur')?.innerText.trim();

                            // Clear results
                            resultsList.innerHTML = '';
                            resultsList.classList.add('hidden');

                            // Hide input and store productId
                            input.classList.add('hidden');

                            // Add a hidden input for form submit
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'product_ids[]';
                            hiddenInput.value = productId;
                            input.parentElement.appendChild(hiddenInput);

                            // Add a preview block
                            const preview = document.createElement('div');
                            preview.className = 'mt-2 flex items-center space-x-4 overflow-auto';
                            preview.innerHTML = `
                                <img src="${productImage}" alt="${productTitle}" class="w-12 h-12 object-cover rounded border border-gray-300 dark:border-gray-600" />
                                <span class="text-gray-900 dark:text-white text-sm">${productTitle}</span> <span class="text-gray-600 dark:text-gray-400 text-md">(${productStore})</span> <span class="text-red-600 font-bold">${productPrice}</span> <span class="text-md font-bold text-red-600">${productPriceEur}</span>
                            `;
                            input.parentElement.appendChild(preview);

                            updateSubmitState();
                        });
                    });
                })
                .catch(err => {
                    console.error('Search failed:', err);
                    resultsList.classList.add('hidden');
                });
            }, 300);
        });



        // Hide suggestions on blur (with slight delay to allow click)
        input.addEventListener('blur', () => setTimeout(() => {
            resultsList.classList.add('hidden');
        }, 200));
    });


    function updateSubmitState() {
        const count = document.querySelectorAll('#grouped-products-list li').length;
        const submitBtn = document.getElementById('submit-group');
        if (count >= 2) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }
}

// Now the edit button in the product list should trigger this modal
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('edit-grouped')) {
        const groupId = e.target.getAttribute('data-group-id');
        fetch(`/api/products/grouped-products/${groupId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error: ${response.status} ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.result === 'success') {
                showEditGroupedProductsModal(data.data);
            } else {
                alert('Failed to fetch group data');
            }
        })
        .catch(error => {
            console.error(error);
            alert(error.message);
        });
    }
});

function showEditGroupedProductsModal(groupData) {
    // Remove any existing modal completely
    const existingModal = document.getElementById('grouped-products-modal');
    if (existingModal) {
        existingModal.remove();
    }

    const modalWrapper = document.getElementById('grouped-products-modal-wrapper');

    const modalElement = document.createElement('div');
    modalElement.id = 'grouped-products-modal';
    modalElement.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';

    modalElement.innerHTML = `
        <div class="relative w-full max-w-2xl max-h-full mx-auto z-60">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 border border-gray-700 dark:border-gray-400">
                <form id="grouped-products-form" class="space-y-4">
                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            ${translate('groupedEditGroupTitle')}
                        </h3>
                        <button type="button" id="close-grouped-products-modal" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>

                    <div class="p-4 md:p-5 space-y-4">
                        <input type="hidden" name="group_id" value="${groupData.group_id}">
                        <div class="mb-4">
                            <label for="group-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">${translate('groupName')}</label>
                            <input type="text" id="group-name" name="group_name" value="${groupData.name}" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                        </div>

                        <button id="add-product-button" type="button" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            ${translate('groupNameAddProductButton')}
                        </button>

                        <ul id="grouped-products-list" class="space-y-2">
                            ${Object.values(groupData.products).map(product => `
                                <li class="bg-gray-100 dark:bg-gray-800 p-2 rounded space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <input type="hidden" name="product_ids[]" value="${product.id}" />
                                        <img src="${product.image}" alt="${product.title}" class="w-12 h-12 object-cover rounded border border-gray-300 dark:border-gray-600" />
                                        <span class="text-gray-900 dark:text-white text-sm">${product.title}</span>
                                        <span class="text-gray-600 dark:text-gray-400 text-md">(${product.store})</span>
                                        <span class="text-red-600 font-bold">${product.price}</span>
                                        <span class="text-md font-bold text-red-600">${product.price_eur}</span>
                                        <button type="button" class="remove-product text-red-600 hover:text-red-800 text-sm">${translate('remove')}</button>
                                    </div>
                                </li>
                            `).join('')}
                        </ul>
                    </div>

                    <div class="flex items-center justify-start p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                        <button id="submit-group" type="submit" class="bg-blue-500 text-white font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            ${translate('groupedUppdateButton')}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;

    modalWrapper.appendChild(modalElement);

    // Close modal
    document.getElementById('close-grouped-products-modal').addEventListener('click', () => {
        document.getElementById('grouped-products-modal').remove();
    });

    // Remove product
    modalElement.querySelectorAll('.remove-product').forEach(button => {
        button.addEventListener('click', () => {
            button.closest('li').remove();
            updateSubmitState();
        });
    });

    // Form submit
    document.getElementById('grouped-products-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('#submit-group');

        submitBtn.innerHTML = loaderString();
        submitBtn.disabled = true;

        const formData = new FormData(form);
        const jsonData = {};

        formData.forEach((value, key) => {
            if (key.endsWith('[]')) {
                const cleanKey = key.slice(0, -2);
                if (!jsonData[cleanKey]) jsonData[cleanKey] = [];
                jsonData[cleanKey].push(value);
            } else {
                jsonData[key] = value;
            }
        });

        fetch('/api/products/grouped-products/' + groupData.group_id, {
            method: 'PUT',
            body: JSON.stringify(jsonData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.result === 'success') {
                document.getElementById('grouped-products-modal').remove();
                location.reload();
            } else {
                alert(JSON.stringify(data));
            }
        })
        .catch(err => {
            console.error(err);
            submitBtn.innerHTML = translate('groupNameUpdateButton');
            submitBtn.disabled = false;
            alert(err.message);
        });
    });

    // Handle Add Product (+)
    document.getElementById('add-product-button').addEventListener('click', () => {
        const list = document.getElementById('grouped-products-list');

        const item = document.createElement('li');
        item.className = 'bg-gray-100 dark:bg-gray-800 p-2 rounded space-y-2';

        const inputId = 'product-search-' + Date.now();

        item.innerHTML = `
            <div class="flex items-center space-x-2">
                <input id="${inputId}" type="text" placeholder="${translate('groupedProductsSearchPlaceholder')}" 
                    class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                <button type="button" class="remove-product text-red-600 hover:text-red-800 text-sm">${translate('remove')}</button>
            </div>
            <ul class="search-results bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded max-h-40 overflow-y-auto hidden"></ul>
        `;

        list.appendChild(item);
        updateSubmitState();

        // Remove button logic
        item.querySelector('.remove-product').addEventListener('click', () => {
            item.remove();
            updateSubmitState();
        });

        // Search input logic
        const input = item.querySelector('input');
        const resultsList = item.querySelector('.search-results');

        let debounceTimeout;

        input.addEventListener('input', () => {
            clearTimeout(debounceTimeout);
            const query = input.value.trim();
            if (!query) {
                resultsList.classList.add('hidden');
                resultsList.innerHTML = '';
                return;
            }

            // Gather all existing product_ids[] and build an exclude parameter with those ids
            const existingProductIds = Array.from(document.querySelectorAll('input[name="product_ids[]"]'))
                .map(input => input.value)
                .join(',');

            debounceTimeout = setTimeout(() => {
                fetch('/api/products/search', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ query, exclude: existingProductIds })
                })
                .then(res => res.text())
                .then(html => {
                    resultsList.innerHTML = html;
                    resultsList.classList.toggle('hidden', html.trim() === '');

                    // Attach click handlers to each result <a>
                    resultsList.querySelectorAll('a.product-search-result').forEach(a => {
                        a.addEventListener('click', e => {
                            e.preventDefault();

                            const productUrl = a.getAttribute('href');
                            const productId = productUrl.split('/').pop();
                            let productTitle = a.querySelector('p.text-sm.font-medium')?.innerText.trim();
                            // Limit the title length to 50 characters
                            if (productTitle && productTitle.length > 50) {
                                productTitle = productTitle.substring(0, 50) + '...';
                            }
                            const productImage = a.querySelector('img')?.getAttribute('src');
                            const productStore = a.querySelector('.store')?.innerText.trim();
                            const productPrice = a.querySelector('.price')?.innerText.trim();
                            const productPriceEur = a.querySelector('.price-eur')?.innerText.trim();

                            // Clear results
                            resultsList.innerHTML = '';
                            resultsList.classList.add('hidden');

                            // Hide input and store productId
                            input.classList.add('hidden');

                            // Add a hidden input for form submit
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'product_ids[]';
                            hiddenInput.value = productId;
                            input.parentElement.appendChild(hiddenInput);

                            // Add a preview block
                            const preview = document.createElement('div');
                            preview.className = 'mt-2 flex items-center space-x-4 overflow-auto';
                            preview.innerHTML = `
                                <img src="${productImage}" alt="${productTitle}" class="w-12 h-12 object-cover rounded border border-gray-300 dark:border-gray-600" />
                                <span class="text-gray-900 dark:text-white text-sm">${productTitle}</span> <span class="text-gray-600 dark:text-gray-400 text-md">(${productStore})</span> <span class="text-red-600 font-bold">${productPrice}</span> <span class="text-md font-bold text-red-600">${productPriceEur}</span>
                            `;
                            input.parentElement.appendChild(preview);

                            updateSubmitState();
                        });
                    });
                })
                .catch(err => {
                    console.error('Search failed:', err);
                    resultsList.classList.add('hidden');
                });
            }, 300);
        });



        // Hide suggestions on blur (with slight delay to allow click)
        input.addEventListener('blur', () => setTimeout(() => {
            resultsList.classList.add('hidden');
        }, 200));
    });

    function updateSubmitState() {
        const count = document.querySelectorAll('#grouped-products-list input[name="product_ids[]"]').length;
        const submitBtn = document.getElementById('submit-group');
        if (count >= 2) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    updateSubmitState();
}
