<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ShipmentOrderExport
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function export(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan TMS');

        // Headers
        $headers = [
            'order date',
            'order number',
            'Trip Number',
            'Mode Of Delivery',
            'MOT (Mode Of Transport)',
            'driver',
            'customer',
            'origin province',
            'origin kota-kec',
            'origin addres',
            'destination province',
            'destination kota-kec',
            'destination adress',
            'assigned date',
            'task accept date',
            'arrived date',
            'pod date',
            'status'
        ];

        // Format Header Style
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'name' => 'Arial',
                'size' => 9,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '0F172A'],
                ],
            ],
        ];

        // Write Headers
        foreach ($headers as $colIdx => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }
        
        $highestColumn = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(16);

        // Border and Alignment Style for Data Rows
        $dataStyle = [
            'font' => [
                'name' => 'Arial',
                'size' => 9,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'], // slate-200 light border
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ];

        $rowIdx = 2;
        foreach ($this->orders as $order) {
            $originKotaKec = implode(' - ', array_filter([$order->origin_city, $order->origin_district]));
            $destKotaKec = implode(' - ', array_filter([$order->destination_city, $order->destination_district]));

            $sheet->setCellValue('A' . $rowIdx, $order->order_date?->format('Y-m-d') ?: '-');
            $sheet->setCellValue('B' . $rowIdx, $order->order_number ?: '-');
            $sheet->setCellValue('C' . $rowIdx, $order->trip?->trip_number ?: '-');
            $sheet->setCellValue('D' . $rowIdx, $order->trip?->modeOfDelivery?->name ?: ($order->trip?->mod_id ?: '-'));
            $sheet->setCellValue('E' . $rowIdx, $order->trip?->modeOfTransport?->name ?: ($order->trip?->mot_id ?: '-'));
            $sheet->setCellValue('F' . $rowIdx, $order->trip?->driver?->name ?: '-');
            $sheet->setCellValue('G' . $rowIdx, $order->customer?->customer_name ?: '-');
            $sheet->setCellValue('H' . $rowIdx, $order->origin_province ?: '-');
            $sheet->setCellValue('I' . $rowIdx, $originKotaKec ?: '-');
            $sheet->setCellValue('J' . $rowIdx, $order->origin_address ?: '-');
            $sheet->setCellValue('K' . $rowIdx, $order->destination_province ?: '-');
            $sheet->setCellValue('L' . $rowIdx, $destKotaKec ?: '-');
            $sheet->setCellValue('M' . $rowIdx, $order->detail_address ?: '-');
            $sheet->setCellValue('N' . $rowIdx, $order->assigned_at?->toDateTimeString() ?: '-');
            $sheet->setCellValue('O' . $rowIdx, $order->accepted_at?->toDateTimeString() ?: '-');
            $sheet->setCellValue('P' . $rowIdx, $order->arrived_at?->toDateTimeString() ?: '-');
            $sheet->setCellValue('Q' . $rowIdx, $order->pod_received_at?->toDateTimeString() ?: '-');
            $sheet->setCellValue('R' . $rowIdx, $order->status ?: '-');

            $sheet->getRowDimension($rowIdx)->setRowHeight(16);
            $rowIdx++;
        }

        // Apply style to all data rows
        if ($rowIdx > 2) {
            $sheet->getStyle('A2:' . $highestColumn . ($rowIdx - 1))->applyFromArray($dataStyle);
        }

        // Auto-size columns
        foreach ($headers as $colIdx => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
