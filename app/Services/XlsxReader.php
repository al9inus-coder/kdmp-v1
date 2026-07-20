<?php

namespace App\Services;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class XlsxReader
{
    /**
     * @return array<int, array<int, string|null>>
     */
    public function readRows(string $filePath): array
    {
        $zip = new ZipArchive();

        if ($zip->open($filePath) !== true) {
            throw new RuntimeException('File Excel tidak dapat dibuka.');
        }

        $sheetPath = $this->resolveFirstSheetPath($zip);
        $sharedStrings = $this->loadSharedStrings($zip);
        $sheetXml = $zip->getFromName($sheetPath);
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('Sheet utama tidak ditemukan di file Excel.');
        }

        $sheet = @simplexml_load_string($sheetXml);

        if (!$sheet instanceof SimpleXMLElement) {
            throw new RuntimeException('Format sheet Excel tidak valid.');
        }

        $rows = [];
        $sheetData = $sheet->sheetData ?? null;

        if (!$sheetData) {
            return $rows;
        }

        foreach ($sheetData->row as $row) {
            $cells = [];

            foreach ($row->c as $cell) {
                $cellRef = (string) ($cell['r'] ?? '');
                $columnIndex = $this->columnToIndex($cellRef);
                $cells[$columnIndex] = $this->resolveCellValue($cell, $sharedStrings);
            }

            if ($cells === []) {
                continue;
            }

            ksort($cells);
            $maxIndex = (int) array_key_last($cells);
            $normalizedRow = [];

            for ($i = 0; $i <= $maxIndex; $i++) {
                $normalizedRow[] = isset($cells[$i]) ? trim((string) $cells[$i]) : null;
            }

            $rows[] = $normalizedRow;
        }

        return $rows;
    }

    private function resolveFirstSheetPath(ZipArchive $zip): string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relsXml === false) {
            return 'xl/worksheets/sheet1.xml';
        }

        $workbook = @simplexml_load_string($workbookXml);
        $rels = @simplexml_load_string($relsXml);

        if (!$workbook instanceof SimpleXMLElement || !$rels instanceof SimpleXMLElement) {
            return 'xl/worksheets/sheet1.xml';
        }

        $workbookNs = $workbook->getNamespaces(true);
        $relsNs = $rels->getNamespaces(true);

        if (isset($workbookNs[''])) {
            $workbook->registerXPathNamespace('x', $workbookNs['']);
        }

        if (isset($workbookNs['r'])) {
            $workbook->registerXPathNamespace('r', $workbookNs['r']);
        }

        if (isset($relsNs[''])) {
            $rels->registerXPathNamespace('r', $relsNs['']);
        }

        $firstSheet = $workbook->xpath('//x:sheets/x:sheet[1]');

        if (!is_array($firstSheet) || !isset($firstSheet[0])) {
            return 'xl/worksheets/sheet1.xml';
        }

        $relationId = (string) ($firstSheet[0]->attributes($workbookNs['r'] ?? null)['id'] ?? '');

        if ($relationId === '') {
            return 'xl/worksheets/sheet1.xml';
        }

        $relationships = $rels->xpath('//r:Relationship');

        if (!is_array($relationships)) {
            return 'xl/worksheets/sheet1.xml';
        }

        foreach ($relationships as $relationship) {
            $id = (string) ($relationship['Id'] ?? '');

            if ($id !== $relationId) {
                continue;
            }

            $target = (string) ($relationship['Target'] ?? '');

            if ($target === '') {
                break;
            }

            $target = ltrim($target, '/');

            if (!str_starts_with($target, 'xl/')) {
                $target = 'xl/'.$target;
            }

            return $target;
        }

        return 'xl/worksheets/sheet1.xml';
    }

    /**
     * @return array<int, string>
     */
    private function loadSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $sharedStringsXml = @simplexml_load_string($xml);

        if (!$sharedStringsXml instanceof SimpleXMLElement) {
            return [];
        }

        $strings = [];

        foreach ($sharedStringsXml->si as $si) {
            if (isset($si->t)) {
                $strings[] = (string) $si->t;
                continue;
            }

            $text = '';
            foreach ($si->r as $run) {
                $text .= (string) ($run->t ?? '');
            }
            $strings[] = $text;
        }

        return $strings;
    }

    /**
     * @param array<int, string> $sharedStrings
     */
    private function resolveCellValue(SimpleXMLElement $cell, array $sharedStrings): ?string
    {
        $type = (string) ($cell['t'] ?? '');

        if ($type === 'inlineStr') {
            return isset($cell->is->t) ? (string) $cell->is->t : null;
        }

        $rawValue = isset($cell->v) ? (string) $cell->v : null;

        if ($rawValue === null) {
            return null;
        }

        if ($type === 's') {
            $index = (int) $rawValue;
            return $sharedStrings[$index] ?? null;
        }

        return $rawValue;
    }

    private function columnToIndex(string $cellRef): int
    {
        if (!preg_match('/^[A-Z]+/i', $cellRef, $matches)) {
            return 0;
        }

        $column = strtoupper($matches[0]);
        $index = 0;

        for ($i = 0, $len = strlen($column); $i < $len; $i++) {
            $index = ($index * 26) + (ord($column[$i]) - 64);
        }

        return max(0, $index - 1);
    }
}
