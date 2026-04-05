<?php

namespace App\Controllers;

use Exception;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class PrintersController extends Controller
{
    public function index()
    {
        response()->json([
            'message' => 'PrintersController@index output'
        ]);
    }

    public function print()
    {
        /* Validating */
        $validatedData = request()->validate([
            'header' => 'array<array>',
            'articles' => 'array<array>',
            'totals' => 'array<array>',
            'footer' => 'array<array>',
        ]);

        try {
            /* Printer setup */
            /* $profile = CapabilityProfile::load('SP2000'); */
            $profile = CapabilityProfile::load('simple');
            $connector = new WindowsPrintConnector(_env('PRINTER_NAME', 'printer'));
            $printer = new Printer($connector, $profile);

            /* PRINT */
            /* Header */
            $printer->initialize();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            foreach ($validatedData['header'] as $item) {
                $printer->{$item['action']}(...$item['content']);
            }

            /* Articles */
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            foreach ($validatedData['articles'] as $item) {
                $qty = $item['quantity'] ?? '';
                $name = trim(strlen($item['name']) > 20 ? substr($item['name'] ?? '', 0, 20) : $item['name'] ?? ''); // Clipped product name (max 20 chars)
                $unitPrice = $item['unitPrice'] ?? '';
                $total = $item['total'] ?? '';

                // Format: "qty  product_name  unit_price  total"
                $line = sprintf("%-4s %-20s %8s %10s", $qty, $name, $unitPrice, $total);
                $printer->text($line . "\n");
            }

            /* Totals */
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            foreach ($validatedData['totals'] as $item) {
                $printer->{$item['action']}(...$item['content']);
            }

            /* Footer */
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            foreach ($validatedData['footer'] as $item) {
                $printer->{$item['action']}(...$item['content']);
            }

            /* Close */
            $printer->cut();
            $printer->pulse();
            $printer->close();
        } catch (Exception $e) {
            response()->json([
                'error' => $e->getMessage()
            ], 500);
        }

        response()->json([
            'message' => 'Listo!',
            'data' => $validatedData
        ]);
    }

    public function testPrint()
    {
        /* Validating */
        $validatedData = request()->validate([
            'header' => 'array<array>',
            'articles' => 'array<array>',
            'totals' => 'array<array>',
            'footer' => 'array<array>',
        ]);

        try {
            $printObject = [
                'header' => [],
                'articles' => [],
                'totals' => [],
                'footer' => []
            ];
            /* PRINT */
            /* Header */
            foreach ($validatedData['header'] as $item) {
                $printObject['header'][] = $item;
            }

            /* Articles */
            foreach ($validatedData['articles'] as $item) {
                $qty = $item['quantity'] ?? '';
                $name = trim(strlen($item['name']) > 20 ? substr($item['name'] ?? '', 0, 20) : $item['name'] ?? ''); // Clipped product name (max 20 chars)
                $unitPrice = $item['unitPrice'] ?? '';
                $total = $item['total'] ?? '';

                // Format: "qty  product_name  unit_price  total"
                $line = sprintf("%-4s %-20s %8s %10s", $qty, $name, $unitPrice, $total);
                $printObject['articles'][] = $line;
            }

            /* Totals */
            foreach ($validatedData['totals'] as $item) {
                $printObject['totals'][] = $item;
            }

            /* Footer */
            foreach ($validatedData['footer'] as $item) {
                $printObject['footer'][] = $item;
            }

            /* Close */
        } catch (Exception $e) {
            response()->json([
                'error' => $e->getMessage()
            ], 500);
        }

        response()->json([
            'message' => 'Listo!',
            'data' => $printObject
        ]);
    }

    public function getPrinterName() {
        response()->json([
            'printerName' => _env('PRINTER_NAME', 'printer')
        ]);
    }
}
