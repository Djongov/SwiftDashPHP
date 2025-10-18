<?php

declare(strict_types=1);

namespace Components;

use App\Security\CSRF;

class DBButton
{
    public static function createButton(string $dbTable, array $columns, string $text = 'Add New', $theme = COLOR_SCHEME): string
    {
        $csrfToken = CSRF::create();
        if ($text === 'Add New') {
            return '<button data-table="' . $dbTable . '" data-columns="' . implode(',', $columns) . '" data-csrf="' . $csrfToken . '" type="button" class="add-button create-button inline-flex items-center gap-1.5 px-3 py-2 text-white bg-gradient-to-r from-' . $theme . '-500 to-' . $theme . '-600 hover:from-' . $theme . '-600 hover:to-' . $theme . '-700 focus:ring-4 focus:outline-none focus:ring-' . $theme . '-300 dark:focus:ring-' . $theme . '-800 font-medium rounded-lg text-sm shadow-sm hover:shadow-md transform hover:scale-105 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                ' . $text . '
            </button>';
        } else {
            return '<button title="' . $text . '" data-table="' . $dbTable . '" data-columns="' . implode(',', $columns) . '" data-csrf="' . $csrfToken . '" type="button" class="create-button inline-flex items-center justify-center w-8 h-8 text-white bg-gradient-to-r from-' . $theme . '-500 to-' . $theme . '-600 hover:from-' . $theme . '-600 hover:to-' . $theme . '-700 focus:ring-4 focus:outline-none focus:ring-' . $theme . '-300 dark:focus:ring-' . $theme . '-800 rounded-lg shadow-sm hover:shadow-md transform hover:scale-105 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>';
        }
    }
    public static function editButton(string $dbTable, array $columns, int|string $id, $theme = COLOR_SCHEME, string $text = 'Edit'): string
    {
        $csrfToken = CSRF::create();
        if ($text === 'Edit') {
            return '<button data-table="' . $dbTable . '" data-columns="' . implode(',', $columns) . '" data-csrf="' . $csrfToken . '" data-id="' . $id . '" type="button" class="edit-button inline-flex items-center gap-1.5 px-3 py-2 text-white bg-gradient-to-r from-' . $theme . '-500 to-' . $theme . '-600 hover:from-' . $theme . '-600 hover:to-' . $theme . '-700 focus:ring-4 focus:outline-none focus:ring-' . $theme . '-300 dark:focus:ring-' . $theme . '-800 font-medium rounded-lg text-sm shadow-sm hover:shadow-md transform hover:scale-105 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                ' . $text . '
            </button>';
        } else {
            return '<button title="' . $text . '" data-table="' . $dbTable . '" data-columns="' . implode(',', $columns) . '" data-csrf="' . $csrfToken . '" data-id="' . $id . '" type="button" class="edit-button inline-flex items-center justify-center w-8 h-8 text-white bg-gradient-to-r from-' . $theme . '-500 to-' . $theme . '-600 hover:from-' . $theme . '-600 hover:to-' . $theme . '-700 focus:ring-4 focus:outline-none focus:ring-' . $theme . '-300 dark:focus:ring-' . $theme . '-800 rounded-lg shadow-sm hover:shadow-md transform hover:scale-105 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </button>';
        }
    }
    public static function deleteButton($dbTable, int|string $id, string $text = 'Delete', ?string $confirmTextint = null): string
    {
        if ($confirmTextint === null) {
            $confirmTextint = 'Are you sure you want to delete this record?';
        }
        $csrfToken = CSRF::create();
        if ($text === 'Delete') {
            return '<button data-table="' . $dbTable . '" data-csrf="' . $csrfToken . '" data-id="' . $id . '" data-confirm-message="' . $confirmTextint . '" type="button" class="delete-button inline-flex items-center gap-1.5 px-3 py-2 text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm shadow-sm hover:shadow-md transform hover:scale-105 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                ' . $text . '
            </button>';
        } else {
            return '<button title="' . $text . '" data-table="' . $dbTable . '" data-csrf="' . $csrfToken . '" data-id="' . $id . '" data-confirm-message="' . $confirmTextint . '" type="button" class="delete-button inline-flex items-center justify-center w-8 h-8 text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 rounded-lg shadow-sm hover:shadow-md transform hover:scale-105 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>';
        }
    }
}
