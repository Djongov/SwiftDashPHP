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
        $this->gridId = $gridId ?: 'agGrid_' . self::$instanceCount . '_' . uniqid();
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
     * Generate JavaScript code to initialize AGGrids after AJAX content load
     * Call this after inserting AGGrid HTML via fetch/AJAX
     */
    public static function getInitializationScript(): string
    {
        return <<<HTML
<script nonce="1nL1n3JsRuN1192kwoko2k323WKE">
console.log('AGGrid: getInitializationScript executed');
// Initialize any AGGrid instances that were loaded via AJAX
if (typeof window.initializeAGGrids === 'function') {
    console.log('AGGrid: Calling initializeAGGrids from getInitializationScript');
    window.initializeAGGrids();
} else {
    console.warn('AGGrid: initializeAGGrids function not found. Make sure at least one AGGrid was loaded on the initial page.');
}
</script>
HTML;
    }

    /**
     * Generate JavaScript code to initialize a specific AGGrid by ID
     */
    public static function getSpecificInitializationScript(string $gridId): string
    {
        return <<<HTML
<script nonce="1nL1n3JsRuN1192kwoko2k323WKE">
console.log('AGGrid: getSpecificInitializationScript executed for: {$gridId}');
// Initialize specific AGGrid instance
if (typeof window.initializeAGGrid === 'function') {
    console.log('AGGrid: Calling initializeAGGrid for specific grid: {$gridId}');
    if (!window.initializeAGGrid('{$gridId}')) {
        console.warn('AGGrid: Failed to initialize specific grid: {$gridId}');
    }
} else {
    console.warn('AGGrid: initializeAGGrid function not found. Make sure at least one AGGrid was loaded on the initial page.');
}
</script>
HTML;
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

        // Normalize data structure for AGGrid compatibility
        $normalizedData = self::normalizeDataStructure($data);

        $grid = new self();
        $grid->setTheme($theme);
        
        // Extract columns from normalized data
        $columns = array_keys($normalizedData[0] ?? []);
        $grid->setColumns($columns);
        $grid->setData($normalizedData);
        
        $html = '';
        if ($title) {
            $html .= \Components\Html::h2(htmlspecialchars($title, ENT_QUOTES, 'UTF-8'), true);
        }
        
        $html .= $grid->render();
        
        return $html;
    }

    /**
     * Normalize complex array structures (like $_SERVER) for AGGrid display
     * Handles cases where array values might be mixed types (arrays, strings, etc.)
     */
    private static function normalizeDataStructure(array $data): array
    {
        // Check if data is already in tabular format (array of associative arrays)
        if (self::isTabularData($data)) {
            return $data;
        }
        
        // Handle associative arrays like $_SERVER, $_ENV, etc.
        if (self::isAssociativeArray($data)) {
            return self::convertAssociativeToTabular($data);
        }
        
        // Handle indexed arrays with mixed content
        return self::convertIndexedToTabular($data);
    }
    
    /**
     * Check if data is already in proper tabular format
     */
    private static function isTabularData(array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        
        // Check if first element is an associative array
        $firstElement = reset($data);
        return is_array($firstElement) && !self::isAssociativeArray($firstElement);
    }
    
    /**
     * Check if array is associative (not purely indexed)
     */
    private static function isAssociativeArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }
    
    /**
     * Convert associative array (like $_SERVER) to tabular format
     */
    private static function convertAssociativeToTabular(array $data): array
    {
        $tabularData = [];
        $maxColumns = 0;
        
        // First pass: determine the maximum number of columns needed
        foreach ($data as $key => $value) {
            $columns = self::flattenValue($value);
            $maxColumns = max($maxColumns, count($columns));
        }
        
        // Second pass: create rows with consistent column count
        foreach ($data as $key => $value) {
            $row = ['key' => $key];
            $flattenedValue = self::flattenValue($value);
            
            // Add value columns
            for ($i = 0; $i < $maxColumns; $i++) {
                $columnName = $maxColumns === 1 ? 'value' : 'column' . ($i + 1);
                $row[$columnName] = $flattenedValue[$i] ?? '';
            }
            
            $tabularData[] = $row;
        }
        
        return $tabularData;
    }
    
    /**
     * Convert indexed array to tabular format
     */
    private static function convertIndexedToTabular(array $data): array
    {
        $tabularData = [];
        $maxColumns = 0;
        
        // Determine max columns needed
        foreach ($data as $index => $value) {
            $columns = self::flattenValue($value);
            $maxColumns = max($maxColumns, count($columns));
        }
        
        // Create tabular structure
        foreach ($data as $index => $value) {
            $row = ['index' => $index];
            $flattenedValue = self::flattenValue($value);
            
            for ($i = 0; $i < $maxColumns; $i++) {
                $columnName = $maxColumns === 1 ? 'value' : 'column' . ($i + 1);
                $row[$columnName] = $flattenedValue[$i] ?? '';
            }
            
            $tabularData[] = $row;
        }
        
        return $tabularData;
    }
    
    /**
     * Flatten a value into an array of strings for display
     */
    private static function flattenValue($value): array
    {
        if (is_array($value)) {
            if (empty($value)) {
                return ['[empty array]'];
            }
            
            // For simple arrays, join with commas
            if (!self::isAssociativeArray($value)) {
                // If all elements are scalar, join them
                $allScalar = array_reduce($value, function($carry, $item) {
                    return $carry && is_scalar($item);
                }, true);
                
                if ($allScalar && count($value) <= 5) {
                    return [implode(', ', $value)];
                }
            }
            
            // For complex arrays, convert to JSON or key-value pairs
            if (count($value) <= 3 && self::isAssociativeArray($value)) {
                $pairs = [];
                foreach ($value as $k => $v) {
                    if (is_scalar($v)) {
                        $pairs[] = "$k: $v";
                    } else {
                        $pairs[] = "$k: " . json_encode($v);
                    }
                }
                return $pairs;
            }
            
            // For large or complex arrays, use JSON
            return [json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)];
        }
        
        if (is_object($value)) {
            return [json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)];
        }
        
        if (is_bool($value)) {
            return [$value ? 'true' : 'false'];
        }
        
        if (is_null($value)) {
            return ['null'];
        }
        
        return [(string)$value];
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
        
        // PREVENT DOUBLE INITIALIZATION - check if already initialized
        if (eGridDiv.hasAttribute('data-ag-grid-initialized')) {
            console.log('AGGrid: Grid {$this->gridId} already initialized, skipping');
            return;
        }
        
        console.log('AGGrid: Starting initialization for:', '{$this->gridId}');

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
        
        // CRITICAL: Check if grid is already created to prevent double initialization
        if (eGridDiv._agGridApi) {
            console.log('AGGrid: Grid already exists, destroying before recreating:', eGridDiv.id);
            try {
                eGridDiv._agGridApi.destroy();
                delete eGridDiv._agGridApi;
            } catch(e) {
                console.log('AGGrid: Error destroying existing grid:', e);
            }
        }
        
        // Double-check for any remaining AG-Grid DOM elements
        const existingAgElements = eGridDiv.querySelectorAll('.ag-root-wrapper, .ag-aria-description-container');
        if (existingAgElements.length > 0) {
            console.log('AGGrid: Found', existingAgElements.length, 'remaining AG-Grid elements, removing them');
            existingAgElements.forEach(el => el.remove());
        }
        
        // Mark as initialized BEFORE creating to prevent race conditions
        eGridDiv.setAttribute('data-ag-grid-initialized', 'true');
        
        console.log('AGGrid: Creating new grid for:', eGridDiv.id);
        const gridApi = window.agGrid.createGrid(eGridDiv, gridOptions);
        
        // Store the API reference for cleanup purposes
        eGridDiv._agGridApi = gridApi;
        console.log('AGGrid: Grid created successfully for:', eGridDiv.id);
        
        // Final check for duplicate content after creation
        setTimeout(() => {
            const rootWrappers = eGridDiv.querySelectorAll('.ag-root-wrapper');
            if (rootWrappers.length > 1) {
                console.error('AGGrid: CRITICAL - Multiple ag-root-wrapper elements detected after creation!', rootWrappers.length);
                // Remove all but the last one
                for (let i = 0; i < rootWrappers.length - 1; i++) {
                    console.log('AGGrid: Emergency removal of duplicate wrapper', i);
                    rootWrappers[i].remove();
                }
            }
        }, 100);

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

    // Register this grid for dynamic initialization
    if (!window.AGGridInstances) {
        console.log('AGGrid: Creating global AGGridInstances registry');
        window.AGGridInstances = new Map();
    }
    console.log('AGGrid: Registering grid for dynamic initialization:', '{$this->gridId}');
    console.log('AGGrid: JavaScript function name:', 'initGrid_{$jsGridId}');
    console.log('AGGrid: Registration happening at:', new Date().toISOString());
    window.AGGridInstances.set('{$this->gridId}', initGrid_{$jsGridId});
    console.log('AGGrid: Registry now contains:', Array.from(window.AGGridInstances.keys()));

    // Only auto-initialize if not in AJAX context (document ready state check)
    // For AJAX content, initializeAGGrids() will handle the initialization
    const gridElement = document.querySelector('#{$this->gridId}');
    if (gridElement && !gridElement.hasAttribute('data-ag-grid-initialized')) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initGrid_{$jsGridId});
        } else {
            // Add a small delay to ensure DOM is fully ready and avoid double initialization
            setTimeout(() => {
                const stillNotInitialized = !gridElement.hasAttribute('data-ag-grid-initialized');
                console.log('AGGrid: Auto-init check for {$this->gridId}, not initialized:', stillNotInitialized);
                if (stillNotInitialized) {
                    initGrid_{$jsGridId}();
                }
            }, 10);
        }
    } else {
        console.log('AGGrid: Skipping auto-init for {$this->gridId} - element not found or already initialized');
    }
})();

// Global function to initialize AGGrid instances dynamically (for AJAX-loaded content)
if (!window.initializeAGGrids) {
    console.log('AGGrid: Setting up global initializeAGGrids function');
    
    window.initializeAGGrids = function(containerElement = document) {
        console.log('AGGrid: initializeAGGrids called');
        console.log('AGGrid: Container element:', containerElement);
        console.log('AGGrid: Container element type:', containerElement ? containerElement.constructor.name : 'null');
        console.log('AGGrid: Container is document?', containerElement === document);
        
        if (containerElement && containerElement.innerHTML) {
            console.log('AGGrid: Container innerHTML (first 300 chars):', containerElement.innerHTML.substring(0, 300));
        }
        
        // Find all AGGrid containers in the specified element
        const gridContainers = containerElement.querySelectorAll('[id^="agGrid_"]');
        console.log('AGGrid: Found', gridContainers.length, 'grid containers');
        
        // If we're initializing AJAX-loaded content (not the whole document), optionally clean up existing grids
        if (containerElement !== document && gridContainers.length > 0) {
            console.log('AGGrid: AJAX content detected');
            
            // Enable cleanup to replace old grids with new ones
            const enableCleanup = true; // Set to false to allow multiple grids
            
            if (enableCleanup) {
                console.log('AGGrid: Performing selective cleanup of old AGGrid elements');
            
            // Remove containers that don't contain the new content
            const allContainers = document.querySelectorAll('.ag-grid-responsive-container');
            console.log('AGGrid: Found', allContainers.length, 'containers to check');
            allContainers.forEach((container, index) => {
                if (!containerElement.contains(container)) {
                    console.log('AGGrid: Removing old container', index);
                    container.remove();
                } else {
                    console.log('AGGrid: Keeping container', index, '- it contains new content');
                }
            });
            
            // Remove ALL grids that aren't in the new container - check both DOM and registry
            console.log('AGGrid: Checking registry for grids to remove');
            if (window.AGGridInstances) {
                const registryKeys = Array.from(window.AGGridInstances.keys());
                console.log('AGGrid: Registry contains:', registryKeys);
                
                registryKeys.forEach(gridId => {
                    const gridElement = document.getElementById(gridId);
                    if (gridElement) {
                        if (!containerElement.contains(gridElement)) {
                            console.log('AGGrid: Removing registry grid from DOM:', gridId);
                            // Remove the grid's container
                            const gridContainer = gridElement.closest('.ag-grid-responsive-container');
                            if (gridContainer) {
                                gridContainer.remove();
                            } else {
                                gridElement.remove();
                            }
                            // Remove from registry
                            window.AGGridInstances.delete(gridId);
                            console.log('AGGrid: Removed', gridId, 'from registry');
                        } else {
                            console.log('AGGrid: Keeping registry grid:', gridId, '- it is in new content');
                        }
                    } else {
                        console.log('AGGrid: Registry grid', gridId, 'not found in DOM - removing from registry');
                        window.AGGridInstances.delete(gridId);
                    }
                });
            }
            
            // Also check DOM for any remaining grids
            const allExistingGrids = document.querySelectorAll('[id^="agGrid_"]');
            console.log('AGGrid: Found', allExistingGrids.length, 'grid elements in DOM after registry cleanup');
            allExistingGrids.forEach((grid, index) => {
                if (!containerElement.contains(grid)) {
                    console.log('AGGrid: Removing remaining DOM grid element', index, ':', grid.id);
                    const gridContainer = grid.closest('.ag-grid-responsive-container');
                    if (gridContainer) {
                        gridContainer.remove();
                    } else {
                        grid.remove();
                    }
                } else {
                    console.log('AGGrid: Keeping DOM grid element', index, ':', grid.id, '- it is in new content');
                }
            });

            
                console.log('AGGrid: Finished removing existing grids');
                console.log('AGGrid: Registry now contains:', window.AGGridInstances ? Array.from(window.AGGridInstances.keys()) : 'none');
                
                // Advanced debugging to detect duplicate AG-Grid structures
                const remainingGrids = document.querySelectorAll('[id^="agGrid_"]');
                console.log('AGGrid: After cleanup - remaining grids in DOM:', remainingGrids.length);
                
                remainingGrids.forEach((grid, index) => {
                    const gridId = grid.id;
                    const agRootWrappers = grid.querySelectorAll('.ag-root-wrapper');
                    const visibleWrappers = Array.from(agRootWrappers).filter(wrapper => 
                        wrapper.offsetWidth > 0 && wrapper.offsetHeight > 0
                    );
                    
                    console.log('AGGrid: Grid', index, ':', gridId);
                    console.log('  - Total ag-root-wrapper elements:', agRootWrappers.length);
                    console.log('  - Visible ag-root-wrapper elements:', visibleWrappers.length);
                    console.log('  - Grid element visible:', grid.offsetWidth > 0 && grid.offsetHeight > 0);
                    
                    // If we have multiple ag-root-wrapper elements, this is the problem!
                    if (agRootWrappers.length > 1) {
                        console.warn('AGGrid: DUPLICATE DETECTED! Grid', gridId, 'has', agRootWrappers.length, 'ag-root-wrapper elements!');
                        
                        // Remove all but the last one (assuming the last one is the new one)
                        for (let i = 0; i < agRootWrappers.length - 1; i++) {
                            console.log('AGGrid: Removing duplicate ag-root-wrapper', i, 'from grid', gridId);
                            agRootWrappers[i].remove();
                        }
                        console.log('AGGrid: Removed', agRootWrappers.length - 1, 'duplicate ag-root-wrapper elements from', gridId);
                    }
                });
                
                const remainingContainers = document.querySelectorAll('.ag-grid-responsive-container');
                console.log('AGGrid: After cleanup - remaining containers:', remainingContainers.length);
            } else {
                console.log('AGGrid: Cleanup disabled - allowing multiple grids');
            }
        }
        
        // Log details about each found container
        for (let i = 0; i < gridContainers.length; i++) {
            const element = gridContainers[i];
            console.log('AGGrid: Container ' + i + ': ID=' + element.id + ', initialized=' + element.hasAttribute('data-ag-grid-initialized'));
            console.log('AGGrid: Container ' + i + ' parent:', element.parentElement ? element.parentElement.tagName + '.' + element.parentElement.className : 'no parent');
        }
        
        // Also check all grids in the entire document for comparison
        if (containerElement !== document) {
            const allGridContainers = document.querySelectorAll('[id^="agGrid_"]');
            console.log('AGGrid: For comparison - total grids in document:', allGridContainers.length);
            for (let i = 0; i < allGridContainers.length; i++) {
                const element = allGridContainers[i];
                console.log('AGGrid: Document grid ' + i + ': ID=' + element.id + ', initialized=' + element.hasAttribute('data-ag-grid-initialized'), ', hidden=' + element.hasAttribute('data-ag-grid-hidden'));
            }
        }
        
        gridContainers.forEach(gridElement => {
            const gridId = gridElement.id;
            console.log('AGGrid: Processing grid:', gridId);
            
            // CRITICAL: Before initializing, completely clear any existing AG-Grid content
            console.log('AGGrid: Clearing any existing AG-Grid content from element:', gridId);
            const existingAgContent = gridElement.querySelectorAll('.ag-root-wrapper, .ag-aria-description-container');
            if (existingAgContent.length > 0) {
                console.log('AGGrid: Found', existingAgContent.length, 'existing AG-Grid elements to remove');
                existingAgContent.forEach((element, index) => {
                    console.log('AGGrid: Removing existing element', index, ':', element.className);
                    element.remove();
                });
            }
            
            // Also destroy any existing grid API if available
            if (gridElement._agGridApi) {
                console.log('AGGrid: Destroying existing grid API for:', gridId);
                try {
                    gridElement._agGridApi.destroy();
                    delete gridElement._agGridApi;
                } catch (error) {
                    console.log('AGGrid: Error destroying existing API:', error);
                }
            }
            
            if (window.AGGridInstances && window.AGGridInstances.has(gridId)) {
                console.log('AGGrid: Found initialization function for:', gridId);
                const initFunction = window.AGGridInstances.get(gridId);
                
                // Only initialize if not already initialized
                if (!gridElement.hasAttribute('data-ag-grid-initialized')) {
                    console.log('AGGrid: Initializing grid:', gridId);
                    try {
                        initFunction();
                        gridElement.setAttribute('data-ag-grid-initialized', 'true');
                        console.log('AGGrid: Successfully initialized dynamically:', gridId);
                    } catch (error) {
                        console.error('AGGrid: Error initializing grid:', gridId, error);
                    }
                } else {
                    console.log('AGGrid: Grid already initialized:', gridId);
                }
            } else {
                console.warn('AGGrid: No initialization function found for:', gridId);
                console.log('AGGrid: Available instances:', window.AGGridInstances ? Array.from(window.AGGridInstances.keys()) : 'none');
                
                // The script tag didn't execute because innerHTML doesn't run scripts
                // Let's find and execute the script manually
                console.log('AGGrid: Looking for script tags to execute for:', gridId);
                const scriptTags = containerElement.querySelectorAll('script');
                console.log('AGGrid: Found', scriptTags.length, 'script tags in container');
                
                scriptTags.forEach((script, index) => {
                    console.log('AGGrid: Processing script tag', index);
                    if (script.innerHTML && script.innerHTML.includes(gridId)) {
                        console.log('AGGrid: Found script for grid', gridId, '- executing it');
                        try {
                            // Create a new script element and execute it
                            const newScript = document.createElement('script');
                            newScript.innerHTML = script.innerHTML;
                            if (script.nonce) newScript.nonce = script.nonce;
                            document.head.appendChild(newScript);
                            console.log('AGGrid: Script executed successfully for:', gridId);
                            
                            // Now try to initialize after a brief delay
                            setTimeout(() => {
                                if (window.AGGridInstances && window.AGGridInstances.has(gridId)) {
                                    console.log('AGGrid: Found registration after script execution:', gridId);
                                    const initFunction = window.AGGridInstances.get(gridId);
                                    if (!gridElement.hasAttribute('data-ag-grid-initialized')) {
                                        try {
                                            initFunction();
                                            gridElement.setAttribute('data-ag-grid-initialized', 'true');
                                            console.log('AGGrid: Successfully initialized grid after script execution:', gridId);
                                        } catch (error) {
                                            console.error('AGGrid: Error initializing grid after script execution:', gridId, error);
                                        }
                                    }
                                } else {
                                    console.error('AGGrid: Still no registration found after script execution:', gridId);
                                }
                            }, 50);
                        } catch (error) {
                            console.error('AGGrid: Error executing script for:', gridId, error);
                        }
                    }
                });
            }
        });
        
        console.log('AGGrid: Initialization complete. Total grids processed:', gridContainers.length);
        
        // FINAL DEBUG: Let's see what's actually visible to the user
        setTimeout(() => {
            const allVisibleGrids = [];
            document.querySelectorAll('[id^="agGrid_"]').forEach(grid => {
                const rect = grid.getBoundingClientRect();
                const isActuallyVisible = rect.width > 0 && rect.height > 0 && 
                                        grid.offsetParent !== null && 
                                        window.getComputedStyle(grid).display !== 'none' &&
                                        window.getComputedStyle(grid).visibility !== 'hidden' &&
                                        window.getComputedStyle(grid).opacity !== '0';
                if (isActuallyVisible) {
                    allVisibleGrids.push({
                        id: grid.id,
                        rect: rect,
                        parent: grid.parentElement ? grid.parentElement.className : 'no parent'
                    });
                }
            });
            console.log('AGGrid: ACTUALLY VISIBLE GRIDS COUNT:', allVisibleGrids.length);
            allVisibleGrids.forEach((grid, index) => {
                console.log('AGGrid: Visible grid', index, ':', grid.id, 'at', grid.rect.left + ',' + grid.rect.top, 'size', grid.rect.width + 'x' + grid.rect.height);
            });
        }, 500);
    };
    
    // Helper function to initialize a specific AGGrid by ID
    window.initializeAGGrid = function(gridId) {
        console.log('AGGrid: initializeAGGrid called for:', gridId);
        console.log('AGGrid: Available instances:', window.AGGridInstances ? Array.from(window.AGGridInstances.keys()) : 'none');
        
        if (window.AGGridInstances && window.AGGridInstances.has(gridId)) {
            console.log('AGGrid: Found initialization function for specific grid:', gridId);
            const gridElement = document.getElementById(gridId);
            const initFunction = window.AGGridInstances.get(gridId);
            
            if (gridElement && !gridElement.hasAttribute('data-ag-grid-initialized')) {
                console.log('AGGrid: Element found and not initialized, proceeding with initialization');
                try {
                    initFunction();
                    gridElement.setAttribute('data-ag-grid-initialized', 'true');
                    console.log('AGGrid: Successfully initialized specific grid:', gridId);
                    return true;
                } catch (error) {
                    console.error('AGGrid: Error initializing specific grid:', gridId, error);
                    return false;
                }
            } else {
                console.log('AGGrid: Element not found or already initialized for:', gridId);
            }
        } else {
            console.warn('AGGrid: No initialization function found for specific grid:', gridId);
        }
        return false;
    };
}
</script>

HTML;

    return $html;
}

}
