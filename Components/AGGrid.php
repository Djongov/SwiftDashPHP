<?php

declare(strict_types=1);

namespace Components;

class AGGrid
{
    private string $gridId;
    private array $columns = [];
    private array $data = [];
    private static bool $resourcesLoaded = false;
    private static int $instanceCount = 0;
    private bool $enableEdit = false;
    private bool $enableDelete = false;
    private bool $enableSelection = false;
    private string $dbTable = '';
    private string $theme = 'blue';

    public function __construct(string $gridId = '')
    {
        self::$instanceCount++;
        $this->gridId = $gridId ?: 'agGrid_' . self::$instanceCount;
    }

    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }

    public function setData(array $data): void
    {
        // Sanitize all cell values to neutralize malicious HTML/JS
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = htmlspecialchars($value);
            }
        });

        $this->data = $data;
    }

    /**
     * Enable row selection functionality
     */
    public function enableSelection(bool $enable = true): void
    {
        $this->enableSelection = $enable;
    }

    /**
     * Enable edit functionality
     */
    public function enableEdit(bool $enable = true, string $dbTable = ''): void
    {
        $this->enableEdit = $enable;
        if ($dbTable) {
            $this->dbTable = $dbTable;
        }
    }

    /**
     * Enable delete functionality
     */
    public function enableDelete(bool $enable = true, string $dbTable = ''): void
    {
        $this->enableDelete = $enable;
        if ($dbTable) {
            $this->dbTable = $dbTable;
        }
    }

    /**
     * Set the theme for action buttons
     */
    public function setTheme(string $theme): void
    {
        $this->theme = $theme;
    }

    /**
     * Get the grid ID for external reference
     */
    public function getGridId(): string
    {
        return $this->gridId;
    }

    /**
     * Generate edit button HTML using DBButton component
     */
    private function generateEditButtonHTML(): string
    {
        if (empty($this->columns)) {
            $this->columns = ['id']; // fallback
        }
        return \Components\DBButton::editButton($this->dbTable, $this->columns, 'PLACEHOLDER_ID', $this->theme, '');
    }

    /**
     * Generate delete button HTML using DBButton component
     */
    private function generateDeleteButtonHTML(): string
    {
        return \Components\DBButton::deleteButton($this->dbTable, 'PLACEHOLDER_ID', '', 'Are you sure you want to delete this record?');
    }

    /**
     * Get escaped edit button HTML for JavaScript
     */
    private function getEscapedEditButtonHTML(): string
    {
        return addslashes($this->generateEditButtonHTML());
    }

    /**
     * Get escaped delete button HTML for JavaScript
     */
    private function getEscapedDeleteButtonHTML(): string
    {
        return addslashes($this->generateDeleteButtonHTML());
    }

    /**
     * Static method to render multiple grids in a container
     */
    public static function renderMultiple(array $grids, string $containerClass = 'grid gap-6'): string
    {
        $html = '<div class="' . htmlspecialchars($containerClass) . '">';
        
        foreach ($grids as $grid) {
            if ($grid instanceof self) {
                $html .= '<div class="ag-grid-container">' . $grid->render() . '</div>';
            }
        }
        
        $html .= '</div>';
        return $html;
    }

    /**
     * Static factory method similar to DataGrid::fromDBTable
     */
    public static function fromDBTable(
        string $dbTable, 
        ?string $title = null, 
        string $theme = 'blue', 
        bool $edit = true, 
        bool $delete = true, 
        string $orderBy = 'id', 
        string $sortBy = 'desc', 
        ?int $limit = null
    ): string {
        if ($sortBy !== 'asc' && $sortBy !== 'desc') {
            return \Components\Alerts::danger('Invalid sort order. Please use either "asc" or "desc"');
        }
        
        if (!$limit) {
            $limit = ini_get('max_input_vars');
        }
        
        $db = new \App\Database\DB();
        $pdo = $db->getConnection();
        
        try {
            $stmt = $pdo->query('SELECT * FROM ' . $dbTable . ' ORDER BY ' . $orderBy . ' ' . strtoupper($sortBy) . ' LIMIT ' . $limit);
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return \Components\Alerts::danger('Error fetching data from the database: ' . $e->getMessage());
        }

        if (!$data) {
            return \Components\Alerts::danger('No results for table "' . $dbTable . '"');
        }

        $grid = new self();
        $grid->setTheme($theme);
        
        // Extract columns from data
        $columns = array_keys($data[0] ?? []);
        $grid->setColumns($columns);
        $grid->setData($data);
        
        // Enable features based on parameters
        if ($edit || $delete) {
            $grid->enableSelection(true);
        }
        if ($edit) {
            $grid->enableEdit(true, $dbTable);
        }
        if ($delete) {
            $grid->enableDelete(true, $dbTable);
        }
        
        $html = '';
        if ($title) {
            $html .= \Components\Html::h2(htmlspecialchars($title, ENT_QUOTES, 'UTF-8'), true);
        }
        
        $html .= $grid->render();
        
        return $html;
    }

    /**
     * Static factory method similar to DataGrid::fromData
     */
    public static function fromData(?string $title, array $data, string $theme = 'blue'): string
    {
        if (empty($data)) {
            $noResultsText = ($title) ? 'No results for "' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"' : 'No results found';
            return \Components\Alerts::danger($noResultsText);
        }

        $grid = new self();
        $grid->setTheme($theme);
        
        // Extract columns from data
        $columns = array_keys($data[0] ?? []);
        $grid->setColumns($columns);
        $grid->setData($data);
        
        $html = '';
        if ($title) {
            $html .= \Components\Html::h2(htmlspecialchars($title, ENT_QUOTES, 'UTF-8'), true);
        }
        
        $html .= $grid->render();
        
        return $html;
    }


public function render(): string
{
    $columnDefs = [];
    
    // Add selection column if enabled
    if ($this->enableSelection) {
        $columnDefs[] = [
            'checkboxSelection' => true,
            'headerCheckboxSelection' => true,
            'width' => 50,
            'pinned' => 'left',
            'lockPosition' => true,
            'suppressHeaderMenuButton' => true,
            'sortable' => false,
            'filter' => false
        ];
    }

    // Add data columns with filters
    foreach ($this->columns as $col) {
        $columnDefs[] = [
            'field' => $col,
            'filter' => 'agTextColumnFilter',
            'floatingFilter' => true,
            'sortable' => true,
            'resizable' => true
        ];
    }

    // Add actions column if edit or delete is enabled
    if ($this->enableEdit || $this->enableDelete) {
        $columnDefs[] = [
            'headerName' => 'Actions',
            'field' => 'actions',
            'sortable' => false,
            'filter' => false,
            'pinned' => 'right',
            'width' => 120,
            'cellRenderer' => 'actionCellRenderer'
        ];
    }

    $columnsJson = json_encode($columnDefs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $dataJson = json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // Prepare JavaScript boolean values
    $enableEditJS = $this->enableEdit ? 'true' : 'false';
    $enableDeleteJS = $this->enableDelete ? 'true' : 'false';
    $enableSelectionJS = $this->enableSelection ? 'true' : 'false';
    $rowSelectionMode = $this->enableSelection ? 'multiple' : 'single';

    $html = '';
    
    // Only load resources once
    if (!self::$resourcesLoaded) {
        $html .= <<<HTML
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31.2.1/styles/ag-grid.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31.2.1/styles/ag-theme-alpine.css">
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community@31.2.1/dist/ag-grid-community.min.noStyle.js"></script>

HTML;
        self::$resourcesLoaded = true;
    }

    // Mass delete button (if selection and delete are enabled)
    $massDeleteButton = '';
    if ($this->enableSelection && $this->enableDelete) {
        $massDeleteButton = <<<HTML
<div class="mb-4">
    <button id="{$this->gridId}_massDelete"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
            disabled>
        Delete Selected (<span id="{$this->gridId}_selectedCount">0</span>)
    </button>
</div>
HTML;
    }

    $html .= $massDeleteButton;
    $html .= <<<HTML
<div id="{$this->gridId}" class="ag-theme-alpine h-[80vh] w-full mb-6"></div>

<script nonce="1nL1n3JsRuN1192kwoko2k323WKE">
(function() {
    const initGrid_{$this->gridId} = () => {
        const eGridDiv = document.querySelector("#{$this->gridId}");
        if (!eGridDiv || !window.agGrid) {
            setTimeout(initGrid_{$this->gridId}, 100);
            return;
        }

        // Action cell renderer for edit/delete buttons
        class ActionCellRenderer {
            init(params) {
                this.eGui = document.createElement('div');
                this.eGui.className = 'flex items-center justify-center gap-1';
                
                let buttonsHtml = '';
                
                if ($enableEditJS) {
                    buttonsHtml += `{$this->getEscapedEditButtonHTML()}`.replace(/PLACEHOLDER_ID/g, params.data.id);
                }
                
                if ($enableDeleteJS) {
                    buttonsHtml += `{$this->getEscapedDeleteButtonHTML()}`.replace(/PLACEHOLDER_ID/g, params.data.id);
                }
                
                this.eGui.innerHTML = buttonsHtml;
                
                // Since buttons are created dynamically, we need to manually bind events
                setTimeout(() => {
                    const editBtn = this.eGui.querySelector('.edit-button');
                    const deleteBtn = this.eGui.querySelector('.delete-button');
                    
                    // For edit button - use event delegation to trigger main.js handler
                    if (editBtn) {
                        // Simply trigger a click event that main.js can catch through event delegation
                        // We'll add the button to DOM temporarily so main.js can handle it
                        editBtn.addEventListener('click', (event) => {
                            event.stopPropagation();
                            // Create a temporary element that main.js will recognize and handle
                            this.triggerMainJSEdit(editBtn);
                        });
                    }
                    
                    // For delete button - use the working logic we already have
                    // Delete button should trigger main.js delete functionality
                    if (deleteBtn) {
                        deleteBtn.addEventListener('click', (event) => {
                            event.stopPropagation();
                            this.triggerMainJSDelete(deleteBtn);
                        });
                    }
                }, 0);
            }
            
            triggerMainJSEdit(button) {
                // Prevent any loops by checking if we're already processing
                if (button.hasAttribute('data-processing-edit')) {
                    return;
                }
                
                button.setAttribute('data-processing-edit', 'true');
                
                // Instead of calling initializeEditButtons and clicking the button,
                // let's create the modal directly using the same functions from main.js
                try {
                    // Check if required functions exist
                    if (typeof generateUniqueId !== 'function' || typeof editModal !== 'function') {
                        alert('Edit functionality not available - required functions not found');
                        return;
                    }
                    
                    // Create modal directly (same logic as main.js but without event listener conflicts)
                    const uniqueId = generateUniqueId(4);
                    let modal = editModal(uniqueId, button.dataset.id, button.dataset.table);
                    
                    // Insert and show modal
                    document.body.insertBefore(modal, document.body.firstChild);
                    modal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                    
                    if (typeof toggleBlur === 'function') {
                        toggleBlur(modal);
                    }
                    
                    // Set up modal bindings directly
                    this.setupModalBindings(modal, uniqueId, button);
                    
                } catch (error) {
                    console.error('Error opening edit modal:', error);
                    alert('Error opening edit modal');
                } finally {
                    // Always remove processing flag
                    button.removeAttribute('data-processing-edit');
                }
            }
            
            setupModalBindings(modal, uniqueId, button) {
                const modalResult = document.getElementById(`\${uniqueId}-result`);
                const closeXButton = document.getElementById(`\${uniqueId}-x-button`);
                const cancelButton = document.getElementById(`\${uniqueId}-close-button`);
                const saveButton = document.getElementById(`\${uniqueId}-edit`);
                const cancelButtonsArray = [closeXButton, cancelButton];

                // Function to close the modal
                const closeModal = () => {
                    modal.remove();
                    document.body.classList.remove('overflow-hidden');
                    if (typeof toggleBlur === 'function') {
                        toggleBlur(modal);
                    }
                    document.removeEventListener('keydown', handleEscapeKey);
                };

                // Add click listeners to cancel buttons
                cancelButtonsArray.forEach(cancelBtn => {
                    if (cancelBtn) {
                        cancelBtn.addEventListener('click', closeModal);
                    }
                });

                // Handle Escape key
                const handleEscapeKey = (event) => {
                    if (event.key === 'Escape') {
                        closeModal();
                    }
                };
                document.addEventListener('keydown', handleEscapeKey);
                
                // Load modal content
                let modalBody = document.getElementById(`\${uniqueId}-body`);
                if (typeof createLoader === 'function') {
                    createLoader(modalBody, `\${uniqueId}-loader`, 'Loading data...');
                }
                
                // Fetch data for the modal
                const formData = new FormData();
                formData.append('table', button.dataset.table);
                formData.append('columns', button.dataset.columns);
                formData.append('id', button.dataset.id);
                formData.append('csrf_token', button.dataset.csrf);
                const getDataApi = button.dataset.getApi || '/api/datagrid/get-records';
                
                fetch(getDataApi, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': button.dataset.csrf,
                        'secretheader': 'badass'
                    },
                    body: formData
                }).then(response => response.text()).then(data => {
                    modalBody.innerHTML = data;
                    
                    // Focus first input
                    const firstInput = modalBody.querySelector('input');
                    if (firstInput) {
                        firstInput.focus();
                    }
                    
                    // Set up save button
                    this.setupSaveButton(modal, uniqueId, button, saveButton, modalResult);
                }).catch(error => {
                    console.error('Error loading edit data:', error);
                    modalBody.innerHTML = '<p class="text-red-500">Error loading data</p>';
                });
            }
            
            setupSaveButton(modal, uniqueId, button, saveButton, modalResult) {
                const initialButtonText = saveButton.innerText;
                
                saveButton.addEventListener('click', async (e) => {
                    e.preventDefault();
                    
                    // Show loading
                    saveButton.innerHTML = '';
                    if (typeof createLoader === 'function') {
                        createLoader(saveButton, `\${uniqueId}-save-button-edit-loader`);
                    }
                    
                    // Prevent form submission
                    const modalForm = document.getElementById(`\${uniqueId}-form`);
                    if (modalForm) {
                        modalForm.addEventListener('submit', (event) => {
                            event.preventDefault();
                        });
                    }
                    
                    // Collect form data
                    const formData = new FormData();
                    const modalBodyInputs = modal.querySelectorAll('input, textarea, select');
                    modalBodyInputs.forEach(input => {
                        let value = input.value;
                        if (!isNaN(value) && value.includes('.')) {
                            value = parseFloat(value);
                        }
                        formData.append(input.name, value);
                    });
                    
                    // Handle checkboxes
                    const modalBodyCheckboxes = modal.querySelectorAll('input[type=checkbox]');
                    modalBodyCheckboxes.forEach(checkbox => {
                        formData.append(checkbox.name, checkbox.checked ? 1 : 0);
                    });
                    
                    formData.append('id', button.dataset.id);
                    const editApi = button.dataset.editApi || '/api/datagrid/update-records';
                    
                    try {
                        const response = await fetch(editApi, {
                            method: 'POST',
                            headers: {
                                'secretHeader': 'badass',
                                'X-CSRF-TOKEN': formData.get('csrf_token')
                            },
                            body: formData,
                            redirect: 'manual'
                        });
                        
                        const responseStatus = response.status;
                        if (responseStatus === 403 || responseStatus === 401 || responseStatus === 0) {
                            location.reload();
                            return;
                        }
                        
                        let data, isJson;
                        if (response.headers.get('content-type')?.includes('application/json')) {
                            data = await response.json();
                            isJson = true;
                        } else {
                            data = await response.text();
                            isJson = false;
                        }
                        
                        if (responseStatus >= 400) {
                            saveButton.innerText = 'Retry';
                            let errorMessage = isJson ? (data.data || JSON.stringify(data)) : data;
                            modalResult.innerHTML = `<p class="text-red-500 font-semibold">\${errorMessage}</p>`;
                        } else {
                            saveButton.innerText = initialButtonText;
                            if (isJson) {
                                modalResult.innerHTML = `<p class="text-green-500 font-semibold">\${data.data}</p>`;
                                location.reload();
                            } else {
                                modalResult.innerHTML = `<p class="text-red-500 font-semibold">\${data}</p>`;
                            }
                        }
                    } catch (error) {
                        console.error('Error during fetch:', error);
                        saveButton.innerText = 'Retry';
                        modalResult.innerHTML = '<p class="text-red-500 font-semibold">Network error occurred</p>';
                    }
                });
            }
            
            triggerMainJSDelete(button) {
                // Prevent any loops by checking if we're already processing
                if (button.hasAttribute('data-processing-delete')) {
                    console.log('Delete button already being processed, ignoring click');
                    return;
                }
                
                button.setAttribute('data-processing-delete', 'true');
                
                // Create modal directly using main.js functions (same as edit but for delete)
                try {
                    // Check if required functions exist
                    if (typeof generateUniqueId !== 'function' || typeof deleteModal !== 'function') {
                        alert('Delete functionality not available - required functions not found');
                        return;
                    }
                    
                    // Create delete modal directly (same logic as main.js)
                    const uniqueId = generateUniqueId(4);
                    const confirmMessage = button.dataset.confirmMessage || `Are you sure you want to delete entry with id \${button.dataset.id}?`;
                    let modal = deleteModal(uniqueId, confirmMessage);
                    
                    // Insert and show modal
                    document.body.insertBefore(modal, document.body.firstChild);
                    modal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                    
                    // Apply blur only if no other modals are open
                    const existingModals = document.querySelectorAll('.fixed.inset-0:not(.hidden)');
                    if (existingModals.length <= 1) { // Only this modal
                        if (typeof toggleBlur === 'function') {
                            toggleBlur(modal);
                        }
                    }
                    
                    // Get modal elements
                    const modalResult = document.getElementById(`\${uniqueId}-result`);
                    const closeXButton = document.getElementById(`\${uniqueId}-x-button`);
                    const cancelButton = document.getElementById(`\${uniqueId}-close-button`);
                    const deleteButton = document.getElementById(`\${uniqueId}-delete`);
                    
                    // Close modal function
                    const closeModal = () => {
                        modal.remove();
                        document.body.classList.remove('overflow-hidden');
                        
                        // Remove blur only if no other modals are open
                        const remainingModals = document.querySelectorAll('.fixed.inset-0:not(.hidden)');
                        if (remainingModals.length === 0) {
                            if (typeof toggleBlur === 'function') {
                                toggleBlur(modal);
                            }
                        }
                        
                        button.removeAttribute('data-processing-delete');
                    };
                    
                    // Bind close buttons
                    [closeXButton, cancelButton].forEach(btn => {
                        if (btn) btn.addEventListener('click', closeModal);
                    });
                    
                    // Handle delete button click
                    let initialButtonText = deleteButton.innerText;
                    deleteButton.addEventListener('click', async () => {
                        deleteButton.innerHTML = '';
                        createLoader(deleteButton, `\${uniqueId}-save-button-delete-loader`);
                        
                        const formData = new FormData();
                        formData.append('id', button.dataset.id);
                        formData.append('csrf_token', button.dataset.csrf);
                        formData.append('table', button.dataset.table);
                        
                        const deleteApi = button.dataset.deleteApi || '/api/datagrid/delete-records';
                        let responseStatus = 0;
                        
                        fetch(deleteApi, {
                            method: 'POST',
                            headers: {
                                'secretHeader': 'badass',
                                'X-CSRF-TOKEN': formData.get('csrf_token')
                            },
                            body: formData
                        })
                        .then(response => {
                            responseStatus = response.status;
                            if (responseStatus === 403 || responseStatus === 401 || responseStatus === 0) {
                                modalResult.innerHTML = `<p class="text-red-500 font-semibold">Response not ok, refreshing</p>`;
                                location.reload();
                            } else {
                                if (response.headers.get('content-type')?.includes('application/json')) {
                                    return response.json().then(data => ({ data, isJson: true }));
                                } else {
                                    return response.text().then(data => ({ data, isJson: false }));
                                }
                            }
                        })
                        .then(({ data, isJson }) => {
                            if (responseStatus >= 400) {
                                deleteButton.innerText = 'Retry';
                                let errorMessage = isJson ? (data.data || JSON.stringify(data)) : data;
                                modalResult.innerHTML = `<p class="text-red-500 font-semibold">\${errorMessage}</p>`;
                            } else {
                                deleteButton.innerText = initialButtonText;
                                
                                if (isJson) {
                                    modalResult.innerHTML = `<p class="text-green-500 font-semibold">\${data.data}</p>`;
                                    location.reload();
                                } else {
                                    modalResult.innerHTML = `<p class="text-red-500 font-semibold">\${data}</p>`;
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error during fetch:', error);
                            deleteButton.innerText = 'Retry';
                            modalResult.innerHTML = '<p class="text-red-500 font-semibold">Network error occurred</p>';
                        });
                    });
                    
                } catch (error) {
                    console.error('Error creating delete modal:', error);
                    alert('Error opening delete dialog');
                } finally {
                    // Clean up processing flag
                    setTimeout(() => {
                        button.removeAttribute('data-processing-delete');
                    }, 1000);
                }
            }
            
            getGui() {
                return this.eGui;
            }
        }

        const gridOptions = {
            columnDefs: $columnsJson,
            rowData: $dataJson,
            pagination: true,
            paginationPageSize: 100,
            animateRows: true,
            rowSelection: '$rowSelectionMode',
            suppressRowClickSelection: $enableSelectionJS,
            components: {
                actionCellRenderer: ActionCellRenderer
            },
            onSelectionChanged: (event) => {
                if ($enableSelectionJS) {
                    const selectedRows = event.api.getSelectedRows();
                    const count = selectedRows.length;
                    const countElement = document.getElementById('{$this->gridId}_selectedCount');
                    const deleteButton = document.getElementById('{$this->gridId}_massDelete');
                    
                    if (countElement) countElement.textContent = count;
                    if (deleteButton) deleteButton.disabled = count === 0;
                }
            }
        };
        
        const gridApi = window.agGrid.createGrid(eGridDiv, gridOptions);

        // Mass delete functionality
        const massDeleteEnabled = $enableSelectionJS && $enableDeleteJS;
        if (massDeleteEnabled) {
            document.getElementById('{$this->gridId}_massDelete')?.addEventListener('click', () => {
                const selectedRows = gridApi.getSelectedRows();
                if (selectedRows.length === 0) return;
                
                const ids = selectedRows.map(row => row.id);
                if (confirm(`Are you sure you want to delete \${selectedRows.length} selected records?`)) {
                    fetch('/api/admin/delete-records', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            table: '{$this->dbTable}',
                            ids: ids
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            gridApi.applyTransaction({ remove: selectedRows });
                            alert(`\${selectedRows.length} records deleted successfully`);
                        } else {
                            alert('Error deleting records: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error deleting records');
                    });
                }
            });
        }

        // Right-click to copy cell value
        eGridDiv.addEventListener("contextmenu", (event) => {
            event.preventDefault();
            const cell = event.target.closest(".ag-cell");
            if (!cell) return;

            const value = cell.textContent;
            navigator.clipboard.writeText(value).then(() => {
                cell.style.backgroundColor = "#c8ffc8";
                setTimeout(() => cell.style.backgroundColor = "", 300);
            }).catch(() => {
                cell.style.backgroundColor = "#ffcc99";
                setTimeout(() => cell.style.backgroundColor = "", 300);
            });
        });

        // Tailwind dark mode integration
        const updateGridTheme = () => {
            if (document.documentElement.classList.contains('dark')) {
                eGridDiv.classList.remove('ag-theme-alpine');
                eGridDiv.classList.add('ag-theme-alpine-dark');
            } else {
                eGridDiv.classList.remove('ag-theme-alpine-dark');
                eGridDiv.classList.add('ag-theme-alpine');
            }
        };

        updateGridTheme();
        new MutationObserver(updateGridTheme).observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGrid_{$this->gridId});
    } else {
        initGrid_{$this->gridId}();
    }
})();
</script>

HTML;

    return $html;
}

}
