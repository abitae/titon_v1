<?php

namespace App\Services\Purchases;

class SimplePdfBuilder
{
    /**
     * @param  list<string>  $lines
     */
    public function build(string $title, array $lines): string
    {
        $pages = [];
        $currentLines = [$title, ''];

        foreach ($lines as $line) {
            if (count($currentLines) >= 40) {
                $pages[] = $currentLines;
                $currentLines = [];
            }

            $currentLines[] = $line;
        }

        if ($currentLines !== []) {
            $pages[] = $currentLines;
        }

        $objects = [];
        $pageObjectIds = [];

        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';

        $pageIds = [];
        $contentIds = [];
        $fontId = 3 + (count($pages) * 2);

        foreach ($pages as $index => $pageLines) {
            $pageIds[] = 3 + ($index * 2);
            $contentIds[] = 4 + ($index * 2);
        }

        $objects[] = '<< /Type /Pages /Kids ['.collect($pageIds)->map(fn (int $id): string => $id.' 0 R')->implode(' ').'] /Count '.count($pages).' >>';

        foreach ($pages as $index => $pageLines) {
            $content = $this->pageContent($pageLines);
            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 '.$fontId.' 0 R >> >> /Contents '.$contentIds[$index].' 0 R >>';
            $objects[] = '<< /Length '.strlen($content)." >>\nstream\n".$content."\nendstream";
            $pageObjectIds[] = $pageIds[$index];
        }

        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        return $this->assemble($objects);
    }

    /**
     * @param  list<string>  $lines
     */
    protected function pageContent(array $lines): string
    {
        $content = "BT\n/F1 11 Tf\n50 760 Td\n";

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $content .= "0 -18 Td\n";
            }

            $content .= '('.$this->escape($line).") Tj\n";
        }

        return $content.'ET';
    }

    /**
     * @param  list<string>  $objects
     */
    protected function assemble(array $objects): string
    {
        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$object."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);

        $pdf .= 'xref'."\n";
        $pdf .= '0 '.(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        $pdf .= "trailer\n";
        $pdf .= '<< /Size '.(count($objects) + 1).' /Root 1 0 R >>'."\n";
        $pdf .= "startxref\n";
        $pdf .= $xrefOffset."\n";
        $pdf .= '%%EOF';

        return $pdf;
    }

    protected function escape(string $value): string
    {
        return str($value)
            ->replace('\\', '\\\\')
            ->replace('(', '\(')
            ->replace(')', '\)')
            ->limit(110, '')
            ->toString();
    }
}
