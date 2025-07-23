<?php

declare(strict_types=1);

namespace App\Logs;

class IISLogParser
{
    /**
     * @var resource
     */
    protected $_file;

    /**
     * Create a new parser instance.
     *
     * @param mixed $_file A valid file resource (opened via fopen())
     *
     * @throws InvalidArgumentException if not given a resource.
     */
    public function __construct($_file)
    {
        if (!is_resource($_file)) {
            throw new \InvalidArgumentException('A valid file resource is required.');
        }
        $this->_file = $_file;
    }

    /**
     * Parse the IIS log file.
     *
     * The parser does the following:
     * - Finds the header line that starts with "#Fields:" and extracts the column names.
     * - Skips any other header lines (like those containing IIS version or other meta info).
     * - Reads data lines and maps each value to the corresponding header.
     *
     * @return array An array with:
     *               - 'header_columns': an array of column names.
     *               - 'parsed_data': an array of log rows as associative arrays.
     *
     * @throws Exception if the "#Fields:" header is not found.
     */
    public function parse(): array
    {
        // Reset file pointer to the beginning.
        rewind($this->_file);

        $columns   = [];
        $dataLines = [];

        // Read the file line by line.
        while (($line = fgets($this->_file)) !== false) {
            $line = trim($line);
            if ($line === '') {
                continue; // skip empty lines
            }
            // Check if this is the header line with column definitions.
            if (strpos($line, '#Fields:') === 0) {
                // Remove the "#Fields:" prefix and trim any extra whitespace.
                $headerLine = trim(substr($line, strlen('#Fields:')));
                // Split the header line on one or more whitespace characters.
                $columns = preg_split('/\s+/', $headerLine);
                // We got our columns—skip to the next line.
                continue;
            }

            // Skip any other header lines that start with "#"
            if (strpos($line, '#') === 0) {
                continue;
            }

            // Otherwise, it's a data line.
            $dataLines[] = $line;
        }

        // Make sure we found the "#Fields:" header.
        if (empty($columns)) {
            throw new \Exception("No '#Fields:' header found. This file may not be a valid IIS log.");
        }

        // Process each data line into an associative array.
        $parsedData = [];
        foreach ($dataLines as $dataLine) {
            // Split the line on one or more whitespace characters.
            $values = preg_split('/\s+/', $dataLine);
            $entry = [];
            foreach ($columns as $index => $column) {
                $entry[$column] = $values[$index] ?? null;
            }
            $parsedData[] = $entry;
        }

        // Now Counts

        $countsArray = [];

        $countsArray['totalRequests'] = count($parsedData);

        $countsArray['top5uris'] = array_count_values(array_column($parsedData, 'cs-uri-stem'));

        // Now make them top 5 as in cut the array for the top 5 but first sort it descending
        arsort($countsArray['top5uris']);
        $countsArray['top5uris'] = array_slice($countsArray['top5uris'], 0, 5);

        $countsArray['top5status'] = array_count_values(array_column($parsedData, 'sc-status'));
        arsort($countsArray['top5status']);
        $countsArray['top5status'] = array_slice($countsArray['top5status'], 0, 5, true);

        $countsArray['methods'] = array_count_values(array_column($parsedData, 'cs-method'));

        // For top IPs, we need to find if c-ip or CH-Connecting-IP is present to prevail
        $mainIpColumn = 'c-ip';

        foreach ($parsedData[0] as $key => $value) {
            if ($key === 'CF-Connecting-IP') {
                $mainIpColumn = 'CF-Connecting-IP';
                break;
            }
            // Add more checks here if needed, like Azure's Client-IP
        }

        $countsArray['top5ips'] = array_count_values(array_column($parsedData, $mainIpColumn));
        arsort($countsArray['top5ips']);
        $countsArray['top5ips'] = array_slice($countsArray['top5ips'], 0, 5);

        return [
            'headerColumns' => $columns,
            'prasedData'    => $parsedData,
            'counts'        => $countsArray
        ];
    }
}
