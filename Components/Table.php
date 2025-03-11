<?php declare(strict_types=1);

namespace Components;

use Components\Alerts;
use App\Utilities\General;

class Table
{
    public static function simple(array $columns, array $data) : string
    {
        $html = '';
        if (!$data) {
            $html .= Alerts::danger('No data for this table.');
            return $html;
        }
        $html .= '<div class="w-full my-4 overflow-auto max-h-[44rem]">';
            $html .= '<table class="mx-auto my-4 table-auto w-max-sm border boreder-black dark:border-gray-400 text-center bg-gray-100 ' . DARK_COLOR_SCHEME_CLASS . '">';
                if ($columns) {
                    $html .= '<thead class="bg-gray-200">';
                        $html .= '<tr>';
                            foreach ($columns as $column) {
                                $html .= '<th class="max-w-lg border px-4 py-2">' . $column . '</th>';
                            }
                        $html .= '</tr>';
                    $html .= '</thead>';
                }
                $html .= '<tbody>';
                    if (General::isAssocArray($data)) {
                        foreach ($data as $a => $b) {
                            $html .= '<tr>';
                                if (is_string($b)) {
                                    $html .= '<td class="max-w-lg border px-4 py-2 break-words">' . $a . '</td>';
                                }
                                if (is_string($b)) {
                                    $html .= '<td class="max-w-lg border px-4 py-2 break-words">' . $b . '</td>';
                                }
                            $html .= '</tr>';
                        }
                    } elseif (General::isMultiDimensionalArray($data)) {
                        foreach ($data as $index => $array) {
                            $html .= '<tr>';
                                foreach ($array as $key => $value) {
                                    $html .= '<td class="max-w-lg border px-4 py-2 break-words">' . $value . '</td>';
                                }
                            $html .= '</tr>';
                        }  
                    } else {
                        foreach ($data as $row) {
                            $html .= '<tr>';
                                if (is_string($row)) {
                                    $html .= '<td class="max-w-lg border px-4 py-2 break-words">' . $row . '</td>';
                                }
                            $html .= '</tr>';
                        }
                    }
                $html .= '</tbody>';
            $html .= '</table>';
        $html .= '</div>';
        return $html;
    }
    public static function auto(array $data) : string
    {
        $columnArray = [];
        // Let's check if the data is flat or assoc array
        if (General::isAssocArray($data)) {
            // If it is assoc array then we can extract the columns
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $column => $columnValue) {
                        array_push($columnArray, $column);
                    }
                }
            }
        }
        if (General::isMultiDimensionalArray($data)) {
            foreach ($data as $index => $array) {
                foreach ($array as $column => $value) {
                    array_push($columnArray, $column);
                }
            }
        }
        return self::simple($columnArray, $data);
    }
}