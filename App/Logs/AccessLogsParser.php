<?php

declare(strict_types=1);

namespace App\Logs;

class AccessLogsParser
{
    /**
     * @var resource
     */
    protected $file;

    /**
     * @var string
     */
    protected $logPattern = '/^(?P<ip>\d+\.\d+\.\d+\.\d+) - - \[(?P<datetime>[^\]]+)\] "(?P<method>[A-Z]+) (?P<url>.*?) HTTP\/\d+\.\d+" (?P<status>\d{3}) (?P<size>\d+) "(?P<referrer>.*?)" "(?P<user_agent>.*?)"$/';

    /**
     * Create a new parser instance.
     *
     * @param mixed $file A valid file resource (opened via fopen())
     *
     * @throws InvalidArgumentException if not given a resource.
     */
    public function __construct($file)
    {
        if (!is_resource($file)) {
            throw new \InvalidArgumentException('A valid file resource is required.');
        }
        $this->file = $file;
    }

    /**
     * Parse the Access Log.
     *
     * @return array An array containing:
     *               - 'header_columns': the column headers.
     *               - 'parsed_data': the log data with headers as keys.
     *
     * @throws Exception 
     */
    public function parse(): array
    {
        // Reset file pointer to the beginning.
        rewind($this->file);

        $columns   = [];
        $dataLines = [];

        while (($line = fgets($this->file)) !== false) {
            $line = str_replace("\r", '', trim($line));
            if (empty($line)) {
                continue;
            }

            if (preg_match($this->logPattern, $line, $matches)) {
                // Keep only named matches if needed
                $matches = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                if (empty($columns)) {
                    $columns = array_keys($matches);
                }

                if (count($columns) === count($matches)) {
                    $dataLines[] = array_combine($columns, array_values($matches));
                }
            }
        }

        fclose($this->file);

        // Now Counts

        $countsArray = [];

        $requiredCountsFields = ['status', 'ip', 'method', 'url'];

        foreach ($requiredCountsFields as $field) {
            // Count values for each field
            $countsArray[$field] = array_count_values(array_column($dataLines, $field));
            
            // Slice to top 5 items (highest counts first)
            $countsArray[$field] = array_slice($countsArray[$field], 0, 5, true);
            
            // Sort by count (highest first)
            arsort($countsArray[$field]);
        }

        return [
            'header_columns' => $columns,
            'parsed_data' => $dataLines,
            'counts' => $countsArray
        ];
    }
}
