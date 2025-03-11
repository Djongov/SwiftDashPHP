<?php declare(strict_types=1);

namespace Components;

use App\Security\CSRF;

class DBButton
{
    public static function editButton(string $dbTable, array $columns, int|string $id, string $text = 'Edit', int $width = 14, int $height = 14) : string
    {
        $csrfToken = CSRF::create();
        if ($text === 'Edit') {
            return '<button data-table="' . $dbTable . '" data-columns="' . implode(',', $columns) . '" data-csrf="' . $csrfToken . '" data-id="'. $id. '" type="button" class="edit-button ml-2 my-2 block border dark:border-gray-400 text-white dark:text-gray-100 bg-gray-500 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-400">' . $text . '</button>';
        } else {
            return '<button title="' . $text . '" data-table="' . $dbTable . '" data-columns="' . implode(',', $columns) . '" data-csrf="' . $csrfToken . '" data-id="'. $id. '" type="button" class="edit-button p-1 ml-2 my-2 block border dark:border-gray-400 text-white dark:text-gray-100 bg-gray-500 hover:bg-gray-600 focus:outline-none font-medium rounded-lg text-sm text-center dark:bg-gray-600 dark:hover:bg-gray-700"><svg xmlns="http://www.w3.org/2000/svg" width="' . $width. '" height="' . $height . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-edit">
                <path d="M12 20h9" />
                <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                </svg>
            </button>';
        }
    }
    public static function deleteButton($dbTable, int|string $id, string $text = 'Delete', ?string $confirmTextint = null) : string
    {
        if ($confirmTextint === null) {
            $confirmTextint = 'Are you sure you want to delete this record?';
        }
        $csrfToken = CSRF::create();
        if ($text === 'Delete') {
            return '<button data-table="' . $dbTable . '" data-csrf="' . $csrfToken . '" data-id="'. $id. '" data-confirm-message="' . $confirmTextint . '" type="button" class="delete-button ml-2 my-2 block border dark:border-gray-400 text-white dark:text-gray-100 bg-red-500 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-gray-400">' . $text . '</button>';
        } else {
            return '<button title="' . $text . '" data-table="' . $dbTable . '" data-csrf="' . $csrfToken . '" data-id="'. $id. '" data-confirm-message="' . $confirmTextint . '" type="button" class="delete-button p-1 ml-2 my-2 block focus:outline-none font-medium text-sm text-center">❌</button>';
        }
    }
}
