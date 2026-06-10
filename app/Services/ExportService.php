<?php
namespace App\Services;

class ExportService
{
    /**
     * Generate a CSV stream response from an array of data.
     *
     * @param  array  $headers   CSV column headers.
     * @param  array  $rows      Array of arrays (row data).
     * @param  string $filename  Download filename.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function toCsv(array $headers, array $rows, string $filename = 'export.csv'): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $callback = function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
