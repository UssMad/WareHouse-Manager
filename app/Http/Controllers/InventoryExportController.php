<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryExportController extends Controller
{
    private const FIRST_DATA_ROW = 5;

    /**
     * Download a complete, owner-scoped inventory report as a polished Excel workbook.
     */
    public function __invoke(Request $request): StreamedResponse
    {
        $user = $request->user();
        $categories = Category::query()
            ->where('user_id', $user->id)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('StockManager')
            ->setTitle('Export complet du stock')
            ->setSubject('Produits, catégories et mouvements de stock')
            ->setDescription('Export généré depuis StockManager.');

        $summary = $spreadsheet->getActiveSheet();
        $summary->setTitle('Résumé');
        $activeProducts = $spreadsheet->createSheet();
        $movements = $spreadsheet->createSheet();
        $clients = $spreadsheet->createSheet();
        $archives = $spreadsheet->createSheet();
        $categorySheet = $spreadsheet->createSheet();

        $activeEndRow = $this->fillActiveProducts($activeProducts, $user->id);
        $movementEndRow = $this->fillMovements($movements, $user->id);
        $clientEndRow = $this->fillClients($clients, $user->id);
        $archiveEndRow = $this->fillArchivedProducts($archives, $user->id);
        $this->fillCategories($categorySheet, $categories);
        $this->fillSummary($summary, $user->name, $activeEndRow, $movementEndRow, $clientEndRow, $archiveEndRow);

        $spreadsheet->setActiveSheetIndex(0);
        $fileName = 'StockManager_export_'.now()->format('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet): void {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate',
        ]);
    }

    private function fillSummary(Worksheet $sheet, string $ownerName, int $activeEndRow, int $movementEndRow, int $clientEndRow, int $archiveEndRow): void
    {
        $sheet->setShowGridlines(false);
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'StockManager — Export complet de l’entrepôt');
        $sheet->setCellValue('A3', 'Préparé pour '.$ownerName);
        $sheet->setCellValue('D3', 'Généré le');
        $sheet->setCellValue('E3', Date::PHPToExcel(now()));
        $sheet->getStyle('A2:F2')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 16, 'bold' => true, 'color' => ['rgb' => '0F172A']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('A3:E3')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 10, 'italic' => true, 'color' => ['rgb' => '64748B']],
        ]);
        $sheet->getStyle('E3')->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');
        $sheet->getStyle('A2:F3')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('FFCBD5E1'));

        $activeRange = "'Produits actifs'!\$A\$".self::FIRST_DATA_ROW.":\$A\$$activeEndRow";
        $stockRange = "'Produits actifs'!\$F\$".self::FIRST_DATA_ROW.":\$F\$$activeEndRow";
        $priceRange = "'Produits actifs'!\$E\$".self::FIRST_DATA_ROW.":\$E\$$activeEndRow";
        $statusRange = "'Produits actifs'!\$H\$".self::FIRST_DATA_ROW.":\$H\$$activeEndRow";
        $movementRange = "'Mouvements'!\$A\$".self::FIRST_DATA_ROW.":\$A\$$movementEndRow";
        $clientRange = "'Clients'!\$A\$".self::FIRST_DATA_ROW.":\$A\$$clientEndRow";
        $blacklistRange = "'Clients'!\$I\$".self::FIRST_DATA_ROW.":\$I\$$clientEndRow";
        $archiveRange = "'Archives'!\$A\$".self::FIRST_DATA_ROW.":\$A\$$archiveEndRow";

        $sheet->fromArray([
            ['Indicateur', 'Valeur', '', 'Indicateur', 'Valeur'],
            ['Produits actifs', null, '', 'Unités en stock', null],
            ['Valeur du stock', null, '', 'Alertes stock', null],
            ['Mouvements enregistrés', null, '', 'Produits archivés', null],
            ['Clients enregistrés', null, '', 'Clients blacklistés', null],
        ], null, 'A5');
        $sheet->setCellValue('B6', "=COUNTA($activeRange)");
        $sheet->setCellValue('E6', "=SUM($stockRange)");
        $sheet->setCellValue('B7', "=SUMPRODUCT($priceRange,$stockRange)");
        $sheet->setCellValue('E7', "=COUNTIFS($statusRange,\"<>Disponible\",$activeRange,\"<>\")");
        $sheet->setCellValue('B8', "=COUNTA($movementRange)");
        $sheet->setCellValue('E8', "=COUNTA($archiveRange)");
        $sheet->setCellValue('B9', "=COUNTA($clientRange)");
        $sheet->setCellValue('E9', "=COUNTIFS($blacklistRange,\"Blacklisté\",$clientRange,\"<>\")");

        $sheet->getStyle('A5:E5')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '0F172A']],
            'font' => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A6:E9')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 11, 'color' => ['rgb' => '1E293B']],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'E2E8F0']]],
        ]);
        $sheet->getStyle('B6:B9')->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => '0E7490']]]);
        $sheet->getStyle('E6:E9')->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => '0E7490']]]);
        $sheet->getStyle('B7')->getNumberFormat()->setFormatCode('#,##0.00 [$MAD]');
        $sheet->getStyle('B6:B9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E6:E9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(4);
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getRowDimension(2)->setRowHeight(28);
        $sheet->getSheetView()->setZoomScale(95);
        $sheet->setSelectedCell('A2');
    }

    private function fillActiveProducts(Worksheet $sheet, int $userId): int
    {
        $headers = ['Référence', 'Produit', 'Catégorie', 'Description', 'Prix unitaire (DH)', 'Stock', 'Seuil alerte', 'Statut', 'Créé le'];
        $this->startDataSheet($sheet, 'Produits actifs', 'Référentiel des produits actuellement disponibles dans l’entrepôt.', $headers, [16, 28, 22, 34, 18, 12, 14, 17, 15]);
        $row = self::FIRST_DATA_ROW;

        Product::query()->where('user_id', $userId)->with('category')->orderBy('id')->chunkById(500, function (Collection $products) use ($sheet, &$row): void {
            foreach ($products as $product) {
                $sheet->fromArray([[
                    $product->reference,
                    $product->name,
                    $product->category?->name ?? 'Sans catégorie',
                    $product->description,
                    (float) $product->unit_price,
                    $product->stock_quantity,
                    $product->alert_threshold,
                    $product->stock_status,
                    Date::PHPToExcel($product->created_at),
                ]], null, "A$row");
                $row++;
            }
        });

        return $this->finishDataSheet($sheet, $row - 1, count($headers), ['E' => '#,##0.00 [$MAD]', 'F:G' => '#,##0', 'I' => 'dd/mm/yyyy']);
    }

    private function fillMovements(Worksheet $sheet, int $userId): int
    {
        $headers = ['Date et heure', 'Référence', 'Produit', 'Catégorie', 'Client', 'Type', 'Quantité', 'Fournisseur', 'Motif', 'Note'];
        $this->startDataSheet($sheet, 'Mouvements', 'Historique complet des entrées, sorties et retours de stock.', $headers, [19, 16, 28, 22, 28, 14, 12, 26, 26, 40]);
        $row = self::FIRST_DATA_ROW;

        StockMovement::query()
            ->where('user_id', $userId)
            ->with(['product' => fn ($query) => $query->withTrashed()->with('category'), 'client:id,reference,name'])
            ->orderBy('id')
            ->chunkById(500, function (Collection $stockMovements) use ($sheet, &$row): void {
                foreach ($stockMovements as $movement) {
                    $product = $movement->product;
                    $sheet->fromArray([[
                        Date::PHPToExcel($movement->occurred_at),
                        $product?->reference ?? 'Produit supprimé',
                        $product?->name ?? 'Produit supprimé',
                        $product?->category?->name ?? '—',
                        $movement->client ? $movement->client->reference.' — '.$movement->client->name : '—',
                        $movement->is_return ? 'Retour client' : ($movement->type === 'entry' ? 'Entrée' : 'Sortie'),
                        $movement->quantity,
                        $movement->supplier,
                        $movement->reason,
                        $movement->note,
                    ]], null, "A$row");
                    $row++;
                }
            });

        return $this->finishDataSheet($sheet, $row - 1, count($headers), ['A' => 'dd/mm/yyyy hh:mm', 'G' => '#,##0']);
    }

    private function fillArchivedProducts(Worksheet $sheet, int $userId): int
    {
        $headers = ['Référence', 'Produit', 'Catégorie', 'Prix unitaire (DH)', 'Stock final', 'Seuil alerte', 'Archivé le'];
        $this->startDataSheet($sheet, 'Archives', 'Produits archivés, conservés pour garder une trace de l’activité.', $headers, [16, 28, 22, 18, 14, 14, 18]);
        $row = self::FIRST_DATA_ROW;

        Product::onlyTrashed()->where('user_id', $userId)->with('category')->orderBy('id')->chunkById(500, function (Collection $products) use ($sheet, &$row): void {
            foreach ($products as $product) {
                $sheet->fromArray([[
                    $product->reference,
                    $product->name,
                    $product->category?->name ?? 'Sans catégorie',
                    (float) $product->unit_price,
                    $product->stock_quantity,
                    $product->alert_threshold,
                    Date::PHPToExcel($product->deleted_at),
                ]], null, "A$row");
                $row++;
            }
        });

        return $this->finishDataSheet($sheet, $row - 1, count($headers), ['D' => '#,##0.00 [$MAD]', 'E:F' => '#,##0', 'G' => 'dd/mm/yyyy hh:mm']);
    }

    private function fillClients(Worksheet $sheet, int $userId): int
    {
        $headers = ['Référence', 'Type', 'Client', 'Contact', 'Téléphone', 'Email', 'Opérations', 'Retours', 'Statut', 'Créé le'];
        $this->startDataSheet($sheet, 'Clients', 'Répertoire clients avec les indicateurs calculés depuis l’historique des opérations.', $headers, [16, 16, 28, 24, 18, 30, 14, 12, 15, 15]);
        $row = self::FIRST_DATA_ROW;

        Client::query()
            ->where('user_id', $userId)
            ->withCount('movements')
            ->withCount(['movements as returns_count' => fn ($query) => $query->where('is_return', true)])
            ->orderBy('name')
            ->chunkById(500, function (Collection $clients) use ($sheet, &$row): void {
                foreach ($clients as $client) {
                    $sheet->fromArray([[
                        $client->reference,
                        $client->client_type === 'company' ? 'Entreprise' : 'Individuel',
                        $client->name,
                        $client->contact_name,
                        $client->phone,
                        $client->email,
                        $client->movements_count,
                        $client->returns_count,
                        $client->is_blacklisted ? 'Blacklisté' : 'Actif',
                        Date::PHPToExcel($client->created_at),
                    ]], null, "A$row");
                    $row++;
                }
            });

        return $this->finishDataSheet($sheet, $row - 1, count($headers), ['G:H' => '#,##0', 'J' => 'dd/mm/yyyy']);
    }

    /** @param Collection<int, Category> $categories */
    private function fillCategories(Worksheet $sheet, Collection $categories): void
    {
        $headers = ['Catégorie', 'Description', 'Produits actifs', 'Créée le'];
        $this->startDataSheet($sheet, 'Catégories', 'Structure de classement utilisée pour les produits actifs.', $headers, [28, 45, 18, 16]);
        $row = self::FIRST_DATA_ROW;

        foreach ($categories as $category) {
            $sheet->fromArray([[
                $category->name,
                $category->description,
                $category->products_count,
                Date::PHPToExcel($category->created_at),
            ]], null, "A$row");
            $row++;
        }

        $this->finishDataSheet($sheet, $row - 1, count($headers), ['C' => '#,##0', 'D' => 'dd/mm/yyyy']);
    }

    /** @param array<int, string> $headers @param array<int, int> $widths */
    private function startDataSheet(Worksheet $sheet, string $title, string $subtitle, array $headers, array $widths): void
    {
        $sheet->setTitle($title);
        $sheet->setShowGridlines(false);
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->mergeCells("A1:$lastColumn".'1');
        $sheet->mergeCells("A2:$lastColumn".'2');
        $sheet->setCellValue('A1', $title);
        $sheet->setCellValue('A2', $subtitle);
        $sheet->fromArray([$headers], null, 'A4');
        $sheet->getStyle("A1:$lastColumn".'1')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 15, 'bold' => true, 'color' => ['rgb' => '0F172A']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A2:$lastColumn".'2')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 10, 'italic' => true, 'color' => ['rgb' => '64748B']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A4:$lastColumn".'4')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '0F172A']],
            'font' => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0891B2']]],
        ]);
        foreach ($widths as $index => $width) {
            $sheet->getColumnDimensionByColumn($index + 1)->setWidth($width);
        }
        $sheet->getRowDimension(1)->setRowHeight(27);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getRowDimension(4)->setRowHeight(28);
        $sheet->freezePane('A5');
        $sheet->getSheetView()->setZoomScale(90);
    }

    /** @param array<string, string> $numberFormats */
    private function finishDataSheet(Worksheet $sheet, int $lastRow, int $columnCount, array $numberFormats): int
    {
        $lastColumn = Coordinate::stringFromColumnIndex($columnCount);
        $dataEndRow = max(self::FIRST_DATA_ROW, $lastRow);

        if ($lastRow >= self::FIRST_DATA_ROW) {
            $sheet->setAutoFilter("A4:$lastColumn$lastRow");
            $sheet->getStyle("A5:$lastColumn$lastRow")->applyFromArray([
                'font' => ['name' => 'Arial', 'size' => 10, 'color' => ['rgb' => '1E293B']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['bottom' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'E2E8F0']]],
            ]);
            for ($row = self::FIRST_DATA_ROW; $row <= $lastRow; $row += 2) {
                $sheet->getStyle("A$row:$lastColumn$row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }
        }

        foreach ($numberFormats as $columns => $format) {
            foreach (explode(':', $columns) as $column) {
                $sheet->getStyle("$column".self::FIRST_DATA_ROW.":$column$dataEndRow")->getNumberFormat()->setFormatCode($format);
            }
        }
        if ($columnCount >= 4) {
            $sheet->getStyle("D5:D$dataEndRow")->getAlignment()->setWrapText(true);
        }
        if ($columnCount >= 9) {
            $sheet->getStyle("I5:I$dataEndRow")->getAlignment()->setWrapText(true);
        }
        if ($columnCount >= 10) {
            $sheet->getStyle("J5:J$dataEndRow")->getAlignment()->setWrapText(true);
        }
        $sheet->setSelectedCell('A1');

        return $dataEndRow;
    }
}
