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


public function render(): string
{
    // Use set filter for all columns
    $columnDefs = array_map(fn($col) => [
        'field' => $col,
        'filter' => 'agTextColumnFilter', // Community edition filter
        'floatingFilter' => true
    ], $this->columns);

    $columnsJson = json_encode($columnDefs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $dataJson = json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

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

    $html .= <<<HTML
<div id="{$this->gridId}" class="ag-theme-alpine h-[80vh] w-full mb-6"></div>

<script nonce="1nL1n3JsRuN1192kwoko2k323WKE">
(function() {
    // Use IIFE to avoid variable conflicts between multiple grids
    const initGrid_{$this->gridId} = () => {
        const eGridDiv = document.querySelector("#{$this->gridId}");
        if (!eGridDiv || !window.agGrid) {
            // Retry if AG Grid not loaded yet
            setTimeout(initGrid_{$this->gridId}, 100);
            return;
        }
        
        const gridOptions = {
            columnDefs: $columnsJson,
            rowData: $dataJson,
            pagination: true,
            paginationPageSize: 100,
            animateRows: true
        };
        
        const gridApi = window.agGrid.createGrid(eGridDiv, gridOptions);

        // Right-click to copy cell value
        eGridDiv.addEventListener("contextmenu", (event) => {
            event.preventDefault();
            const cell = event.target.closest(".ag-cell");
            if (!cell) return;

            const value = cell.textContent;
            navigator.clipboard.writeText(value).then(() => {
                cell.style.backgroundColor = "#c8ffc8"; // flash green
                setTimeout(() => cell.style.backgroundColor = "", 300);
            }).catch(() => {
                // Fallback for browsers without clipboard API
                cell.style.backgroundColor = "#ffcc99"; // flash orange
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

        // Initial theme setup
        updateGridTheme();

        // Observe class changes on <html> for dynamic switching
        new MutationObserver(updateGridTheme).observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
    };

    // Initialize when DOM is ready or immediately if already ready
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
