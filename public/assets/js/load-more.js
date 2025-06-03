let currentPage = 1;
const limitPerPage = 15; // Match your PHP limit

document.addEventListener('DOMContentLoaded', function () {
    const loadMoreButton = document.getElementById('loadMoreButton');

    if (loadMoreButton) {
        loadMoreButton.addEventListener('click', function () {
            // change the button text to loading
            loadMoreButton.innerText = translate('loadingData');
            loadMoreProducts();
        });
    }
});

function loadMoreProducts() {
    const storeName = getStoreNameFromUrl();

    fetch(`/api/products/load-products?store=${storeName}&page=${currentPage + 1}&limit=${limitPerPage}`)
        .then(response => response.text())
        .then(html => {
            loadMoreButton.innerText = translate('loadMore'); // Reset button text
            const trimmed = html.trim();

            if (!trimmed) {
                // No more products
                document.getElementById('loadMoreButton').disabled = true;
                document.getElementById('loadMoreButton').innerText = translate('noMoreProducts');
                return;
            }

            const container = document.getElementById('products-container');
            if (container) {
                container.insertAdjacentHTML('beforeend', trimmed);
                currentPage += 1;
                initializeAutoLoadedComponents(); // Reinitialize any components that need it
                initiateGenericForms(); // Reinitialize forms
            }
        })
        .catch(error => {
            loadMoreButton.innerText = translate('error'); // Reset button text
            loadMoreButton.disabled = false; // Re-enable button
            loadMoreButton.classList.add('text-red-500');
            console.error('Failed to load products:', error);
        });
}

function getStoreNameFromUrl() {
    const pathParts = window.location.pathname.split('/');
    return pathParts[2]; // Assuming /products/{store}
}
