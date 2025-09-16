<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;

function generateExcelFile($data, $headers, $filename) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set headers
    $column = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($column . '1', $header);
        $sheet->getColumnDimension($column)->setAutoSize(true);
        $column++;
    }
    
    // Set data
    $row = 2;
    foreach ($data as $rowData) {
        $column = 'A';
        foreach ($rowData as $value) {
            $sheet->setCellValue($column . $row, $value);
            $column++;
        }
        $row++;
    }
    
    // Style the header row
    $headerStyle = [
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'E0E0E0']
        ]
    ];
    $sheet->getStyle('A1:' . --$column . '1')->applyFromArray($headerStyle);
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}