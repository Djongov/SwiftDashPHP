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
    private string $theme = COLOR_SCHEME;

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
        string $theme = COLOR_SCHEME, 
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
    public static function fromData(?string $title, array $data, string $theme = COLOR_SCHEME): string
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
    $enableSelectionJS = $this->enableSelection ? 'true' : 'false';
    $enableDeleteJS = $this->enableDelete ? 'true' : 'false';
    $rowSelectionMode = $this->enableSelection ? 'multiple' : 'single';

    // Generate button code based on enabled features
    $columnsStr = implode(',', $this->columns);
    $csrfToken = \App\Security\CSRF::create();
    
    // Sanitize grid ID for JavaScript variable names (replace hyphens and other invalid chars)
    $jsGridId = preg_replace('/\W/', '_', $this->gridId);
    
    $editButtonCode = $this->enableEdit
        ? 'buttonsHtml += this.createEditButton(params.data.id);'
        : '// Edit disabled';
        
    $deleteButtonCode = $this->enableDelete
        ? 'buttonsHtml += this.createDeleteButton(params.data.id);'
        : '// Delete disabled';

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
    
    // Calculate dynamic dimensions based on content
    $rowCount = count($this->data);
    $columnCount = count($this->columns);
    
    // Add action column to count if enabled
    if ($this->enableEdit || $this->enableDelete) {
        $columnCount++;
    }
    
    // Add selection column to count if enabled  
    if ($this->enableSelection) {
        $columnCount++;
    }
    
    // Dynamic height calculation
    $headerHeight = 56; // Header + filter row
    $rowHeight = 42; // Default AG Grid row height
    $paginationHeight = $rowCount > 100 ? 64 : 0; // Pagination bar if needed
    $minHeight = 300; // Minimum height to avoid too small grids
    $maxHeight = 700; // Maximum height to avoid too large grids
    
    $calculatedHeight = $headerHeight + ($rowCount * $rowHeight) + $paginationHeight + 40; // +40 for padding
    $dynamicHeight = max($minHeight, min($maxHeight, $calculatedHeight));
    
    // Dynamic width calculation  
    $baseColumnWidth = 150; // Base width per column
    $actionColumnWidth = 120; // Width for action column
    $selectionColumnWidth = 50; // Width for selection column
    $minWidth = 400; // Minimum width
    
    $calculatedWidth = ($columnCount - ($this->enableEdit || $this->enableDelete ? 1 : 0) - ($this->enableSelection ? 1 : 0)) * $baseColumnWidth;
    if ($this->enableEdit || $this->enableDelete) {
        $calculatedWidth += $actionColumnWidth;
    }
    if ($this->enableSelection) {
        $calculatedWidth += $selectionColumnWidth;
    }
    
    // Smart width logic: use full width if we have enough columns or data density
    $useFullWidth = false;
    
    // Use full width if we have many columns (6+ columns including actions)
    if ($columnCount >= 6) {
        $useFullWidth = true;
    }
    
    // Use full width if we have lots of data (50+ rows)
    if ($rowCount >= 50) {
        $useFullWidth = true;
    }
    
    // Use full width if calculated width would be close to full anyway (80%+ of 1200px)
    if ($calculatedWidth >= 960) {
        $useFullWidth = true;
    }
    
    // Determine final width
    if ($useFullWidth) {
        $dynamicWidth = '100%';
        $maxWidthStyle = '';
    } else {
        $dynamicWidth = max($minWidth, min(1200, $calculatedWidth)) . 'px';
        $maxWidthStyle = 'max-width: 100%;';
    }
    
    // Prepare container and grid classes based on width strategy
    $containerClass = $useFullWidth ? 'w-full' : 'mx-auto';
    $gridClass = $useFullWidth ? 'w-full' : 'mx-auto';
    $widthStyle = "width: {$dynamicWidth};";
    $minWidthStyle = $useFullWidth ? 'min-width: 100%;' : 'min-width: 400px;';
    
    $html .= <<<HTML
<div class="ag-grid-responsive-container mb-6 {$containerClass}">
    <div id="{$this->gridId}" class="ag-theme-alpine {$gridClass}" style="height: {$dynamicHeight}px; {$widthStyle} {$minWidthStyle}"></div>
</div>

<style nonce="1nL1n3JsRuN1192kwoko2k323WKE">
.ag-grid-responsive-container {
    overflow-x: auto;
    width: 100%;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .ag-grid-responsive-container #{$this->gridId} {
        width: 100% !important;
        min-width: 320px !important;
    }
}

@media (max-width: 480px) {
    .ag-grid-responsive-container #{$this->gridId} {
        font-size: 12px;
    }
    
    .ag-grid-responsive-container .ag-header-cell-text {
        font-size: 11px;
    }
}
</style>

<script nonce="1nL1n3JsRuN1192kwoko2k323WKE">
(function() {
    const initGrid_{$jsGridId} = () => {
        const eGridDiv = document.querySelector("#{$this->gridId}");
        if (!eGridDiv || !window.agGrid) {
            setTimeout(initGrid_{$jsGridId}, 100);
            return;
        }

        // Action cell renderer for edit/delete buttons
        class ActionCellRenderer {
            init(params) {
                this.eGui = document.createElement('div');
                this.eGui.className = 'flex items-center justify-center gap-1';
                
                let buttonsHtml = '';
                
                $editButtonCode
                
                $deleteButtonCode
                
                this.eGui.innerHTML = buttonsHtml;
                
                // Since buttons are created dynamically, we need to manually bind events
                setTimeout(() => {
                    const editBtn = this.eGui.querySelector('.aggrid-edit-button');
                    const deleteBtn = this.eGui.querySelector('.aggrid-delete-button');
                    
                    // For edit button - use event delegation to trigger main.js handler
                    if (editBtn) {
                        // Simply trigger a click event that main.js can catch through event delegation
                        // We'll add the button to DOM temporarily so main.js can handle it
                        editBtn.addEventListener('click', (event) => {
                            event.preventDefault();
                            event.stopPropagation();
                            event.stopImmediatePropagation(); // Prevent main.js edit handler from firing
                            this.triggerMainJSEdit(editBtn);
                        });
                    }
                    
                    // For delete button - use custom class to avoid main.js conflicts
                    if (deleteBtn) {
                        deleteBtn.addEventListener('click', (event) => {
                            event.preventDefault();
                            event.stopPropagation();
                            event.stopImmediatePropagation(); // Prevent any other handlers
                            this.triggerMainJSDelete(deleteBtn);
                        });
                    }
                }, 0);
            }
            
            createEditButton(id) {
                return `<button title="Edit" data-table="{$this->dbTable}" data-columns="{$columnsStr}" data-csrf="{$csrfToken}" data-id="\${id}" type="button" class="aggrid-edit-button inline-flex items-center justify-center w-8 h-8 text-white bg-gradient-to-r from-{$this->theme}-500 to-{$this->theme}-600 hover:from-{$this->theme}-600 hover:to-{$this->theme}-700 focus:ring-4 focus:outline-none focus:ring-{$this->theme}-300 dark:focus:ring-{$this->theme}-800 rounded-lg shadow-sm hover:shadow-md transform hover:scale-105 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </button>`;
            }
            
            createDeleteButton(id) {
                return `<button title="Delete" data-table="{$this->dbTable}" data-csrf="{$csrfToken}" data-id="\${id}" data-confirm-message="Are you sure you want to delete this record?" type="button" class="aggrid-delete-button inline-flex items-center justify-center w-8 h-8 text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 rounded-lg shadow-sm hover:shadow-md transform hover:scale-105 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>`;
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
                    
                    // NO BLUR for edit modals either - keep both modals completely blur-free
                    
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
                    
                    // Remove blur only if no other modals are open
                    const remainingModals = document.querySelectorAll('.fixed.inset-0:not(.hidden)');
                    if (remainingModals.length === 0) {
                        if (typeof toggleBlur === 'function') {
                            toggleBlur(modal);
                        }
                    }
                    
                    document.removeEventListener('keydown', handleEscapeKey);
                };

                // Function to close the edit modal (unique name to avoid conflicts)
                const closeEditModal = () => {
                    // Ensure modal is NOT set to aria-hidden before removing (to prevent focus warnings)
                    modal.removeAttribute('aria-hidden');
                    modal.setAttribute('aria-modal', 'false');
                    
                    // Completely remove the modal
                    modal.remove();
                    // Return the overflow of the body
                    document.body.classList.remove('overflow-hidden');
                    
                    // Remove the Escape key listener once the modal is closed
                    document.removeEventListener('keydown', handleEditEscapeKey);
                };

                // Add click listeners to the cancel buttons (simple and clean)
                cancelButtonsArray.forEach(cancelButton => {
                    cancelButton.addEventListener('click', closeEditModal);
                });

                // Function to handle the Escape key press for edit modal (unique name)
                const handleEditEscapeKey = (event) => {
                    if (event.key === 'Escape') { // Check if the Escape key was pressed
                        closeEditModal();
                    }
                };
                document.addEventListener('keydown', handleEditEscapeKey);
                
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
            
            setupDeleteModalBindings(modal, uniqueId, button) {
                const modalResult = document.getElementById(`\${uniqueId}-result`);
                const closeXButton = document.getElementById(`\${uniqueId}-x-button`);
                const cancelButton = document.getElementById(`\${uniqueId}-close-button`);
                const deleteButton = document.getElementById(`\${uniqueId}-delete`);
                const cancelButtonsArray = [closeXButton, cancelButton];

                // Function to close the delete modal (unique name to avoid conflicts)
                const closeDeleteModal = () => {
                    // Simple clean close - no aria manipulation needed since we never set aria-hidden
                    modal.remove();
                    document.body.classList.remove('overflow-hidden');
                    document.removeEventListener('keydown', handleDeleteEscapeKey);
                };

                // Add click listeners to cancel buttons (simple and clean like edit modal)
                cancelButtonsArray.forEach(cancelButton => {
                    cancelButton.addEventListener('click', closeDeleteModal);
                });


                // Handle escape key for delete modal (unique name)
                const handleDeleteEscapeKey = (event) => {
                    if (event.key === 'Escape') {
                        closeDeleteModal();
                    }
                };
                document.addEventListener('keydown', handleDeleteEscapeKey);
                
                // Set up delete button (adapted from the original delete logic)
                this.setupDeleteButton(modal, uniqueId, button, deleteButton, modalResult);
            }
            
            setupDeleteButton(modal, uniqueId, button, deleteButton, modalResult) {
                const initialButtonText = deleteButton.innerText;
                
                deleteButton.addEventListener('click', async (e) => {
                    e.preventDefault();
                    
                    // Prevent double clicks
                    if (deleteButton.disabled) return;
                    deleteButton.disabled = true;
                    
                    // Show loading
                    deleteButton.innerHTML = '';
                    if (typeof createLoader === 'function') {
                        createLoader(deleteButton, `\${uniqueId}-save-button-delete-loader`);
                    }
                    
                    const formData = new FormData();
                    formData.append('id', button.dataset.id);
                    formData.append('csrf_token', button.dataset.csrf);
                    formData.append('table', button.dataset.table);
                    
                    const deleteApi = button.dataset.deleteApi || '/api/datagrid/delete-records';
                    
                    try {
                        const response = await fetch(deleteApi, {
                            method: 'POST',
                            headers: {
                                'secretHeader': 'badass',
                                'X-CSRF-TOKEN': formData.get('csrf_token')
                            },
                            body: formData
                        });
                        
                        const responseStatus = response.status;
                        if (responseStatus === 403 || responseStatus === 401 || responseStatus === 0) {
                            modalResult.innerHTML = `<p class="text-red-500 font-semibold">Response not ok, refreshing</p>`;
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
                        
                        deleteButton.disabled = false; // Re-enable button
                        
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
                    } catch (error) {
                        deleteButton.disabled = false; // Re-enable button
                        console.error('Error during fetch:', error);
                        deleteButton.innerText = 'Retry';
                        modalResult.innerHTML = '<p class="text-red-500 font-semibold">Network error occurred</p>';
                    }
                });
            }
            
            triggerMainJSDelete(button) {
                // Prevent any loops by checking if we're already processing
                if (button.hasAttribute('data-processing-delete')) {
                    return;
                }
                
                button.setAttribute('data-processing-delete', 'true');
                
                // Create modal directly - don't use main.js deleteModal to avoid aria issues
                try {
                    // Check if required functions exist
                    if (typeof generateUniqueId !== 'function') {
                        alert('Delete functionality not available - required functions not found');
                        return;
                    }
                    
                    // Create delete modal directly with our own HTML (no aria-hidden)
                    const uniqueId = generateUniqueId(4);
                    const confirmMessage = button.dataset.confirmMessage || `Are you sure you want to delete entry with id \${button.dataset.id}?`;
                    
                    // Create modal HTML without aria-hidden
                    let modalHTML = `
                        <div id="\${uniqueId}-container" class="relative w-full max-w-2xl max-h-full mx-auto">
                            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 border border-gray-700 dark:border-gray-400">
                                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Delete</h3>
                                    <button id="\${uniqueId}-x-button" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                        </svg>
                                        <span class="sr-only">Close modal</span>
                                    </button>
                                </div>
                                <div id="\${uniqueId}-body" class="p-4 md:p-5 space-y-4 break-words">
                                    <p class="text-base leading-relaxed text-gray-700 dark:text-gray-400">\${confirmMessage}</p>
                                </div>
                                <div id="\${uniqueId}-result" class="m-4"></div>
                                <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                                    <button id="\${uniqueId}-delete" type="button" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">Delete</button>
                                    <button id="\${uniqueId}-close-button" type="button" class="ms-3 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-red-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">Cancel</button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Create modal element WITHOUT aria-hidden
                    const modal = document.createElement('div');
                    modal.id = uniqueId;
                    modal.classList.add('hidden', 'overflow-y-hidden', 'overflow-x-hidden', 'fixed', 'top-0', 'right-0', 'left-0', 'z-50', 'justify-center', 'items-center', 'w-full', 'md:inset-0', 'h-[calc(100%-1rem)]', 'max-h-full', 'mt-12');
                    modal.setAttribute('tabindex', '-1');
                    // DO NOT SET aria-hidden="true" - this causes the accessibility warnings
                    modal.setAttribute('aria-modal', 'true'); // Set proper modal attribute from the start
                    modal.innerHTML = modalHTML;
                    
                    // Insert and show modal
                    document.body.insertBefore(modal, document.body.firstChild);
                    modal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                    
                    // NO BLUR for delete modals - keep it simple like edit modals work perfectly
                    
                    // Set up modal bindings directly (same as edit button)
                    this.setupDeleteModalBindings(modal, uniqueId, button);
                    
                } catch (error) {
                    console.error('Error creating delete modal:', error);
                    alert('Error opening delete dialog');
                } finally {
                    // Clean up processing flag (same as edit button)
                    setTimeout(() => {
                        button.removeAttribute('data-processing-delete');
                    }, 100);
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

        // Smart auto-sizing based on content and container
        setTimeout(() => {
            const isFullWidth = eGridDiv.classList.contains('w-full');
            const gridWidth = eGridDiv.offsetWidth;
            const columnCount = gridApi.getColumns().length;
            
            if (isFullWidth) {
                // For full-width grids, balance between auto-sizing and filling space
                gridApi.autoSizeAllColumns();
                
                // Check if auto-sized columns are too narrow for the available space
                const columnsWidth = gridApi.getColumns().reduce((total, col) => {
                    return total + (col.getActualWidth() || 150);
                }, 0);
                
                // If columns take up less than 70% of available space, size to fit
                if (columnsWidth < gridWidth * 0.7) {
                    gridApi.sizeColumnsToFit();
                }
            } else {
                // For fixed-width grids, just auto-size to content
                gridApi.autoSizeAllColumns();
                
                // If content is much smaller than fixed width, allow some expansion
                const columnsWidth = gridApi.getColumns().reduce((total, col) => {
                    return total + (col.getActualWidth() || 150);
                }, 0);
                
                if (columnsWidth < gridWidth - 100) {
                    gridApi.sizeColumnsToFit();
                }
            }
        }, 100);
        
        // Handle window resize with smarter responsive behavior
        const resizeHandler = () => {
            setTimeout(() => {
                const isFullWidth = eGridDiv.classList.contains('w-full');
                if (isFullWidth) {
                    // Full-width grids should always try to fill available space
                    gridApi.sizeColumnsToFit();
                } else {
                    // Fixed-width grids maintain their column sizing unless very cramped
                    const gridWidth = eGridDiv.offsetWidth;
                    if (gridWidth < 600) {
                        gridApi.sizeColumnsToFit();
                    }
                }
            }, 50);
        };
        
        window.addEventListener('resize', resizeHandler);
        
        // Clean up resize listener when grid is destroyed
        eGridDiv.addEventListener('destroyed', () => {
            window.removeEventListener('resize', resizeHandler);
        });

        // Mass delete functionality
        const massDeleteEnabled = $enableSelectionJS && $enableDeleteJS;
        if (massDeleteEnabled) {
            document.getElementById('{$this->gridId}_massDelete')?.addEventListener('click', async () => {
                const selectedRows = gridApi.getSelectedRows();
                if (selectedRows.length === 0) return;
                
                const ids = selectedRows.map(row => row.id);
                if (confirm(`Are you sure you want to delete \${selectedRows.length} selected records?`)) {
                    const massDeleteButton = document.getElementById('{$this->gridId}_massDelete');
                    const originalText = massDeleteButton.textContent;
                    
                    // Show loading state
                    massDeleteButton.disabled = true;
                    massDeleteButton.textContent = 'Deleting...';
                    
                    try {
                        // Create FormData to match API expected format for mass delete
                        const formData = new FormData();
                        formData.append('deleteRecords', '{$this->dbTable}');
                        formData.append('csrf_token', '{$csrfToken}');
                        
                        // Send each ID as a separate 'row[]' parameter (PHP array format)
                        ids.forEach(id => {
                            formData.append('row[]', id);
                        });
                        
                        const response = await fetch('/api/datagrid/delete-records', {
                            method: 'POST',
                            headers: {
                                'secretHeader': 'badass',
                                'X-CSRF-TOKEN': '{$csrfToken}'
                            },
                            body: formData
                        });
                        
                        const responseStatus = response.status;
                        if (responseStatus === 403 || responseStatus === 401 || responseStatus === 0) {
                            alert('Authentication error, refreshing page');
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
                            let errorMessage = isJson ? (data.data || JSON.stringify(data)) : data;
                            alert('Delete failed: ' + errorMessage);
                        } else {
                            // Success - remove rows from grid
                            gridApi.applyTransaction({ remove: selectedRows });
                            let successMessage = isJson ? data.data : `\${selectedRows.length} records deleted successfully`;
                            alert(successMessage);
                        }
                        
                    } catch (error) {
                        console.error('Error during mass delete:', error);
                        alert('Network error during delete operation');
                    } finally {
                        // Reset button state
                        massDeleteButton.disabled = false;
                        massDeleteButton.textContent = originalText;
                    }
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
        document.addEventListener('DOMContentLoaded', initGrid_{$jsGridId});
    } else {
        initGrid_{$jsGridId}();
    }
})();
</script>

HTML;

    return $html;
}

}
