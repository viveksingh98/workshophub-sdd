<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Student;

/**
 * Minimal dependency-free PDF writer (Unit 45: waiver template with
 * variables + a signed-ready PDF per student). Produces a valid single-
 * or multi-page text PDF with Helvetica — no composer package needed.
 */
class WaiverPdf
{
    public function forStudent(Student $student): string
    {
        return $this->render($this->fill($this->template(), [
            '{{student_name}}' => $student->name,
            '{{student_contact}}' => $student->contact,
            '{{date}}' => now()->format('Y-m-d'),
            '{{studio_name}}' => Setting::map()['studio_name'],
            '{{owner_name}}' => Setting::map()['owner_name'],
        ]));
    }

    public function blank(): string
    {
        return $this->render($this->template());
    }

    public function template(): string
    {
        return Setting::map()['waiver_template'] ?? implode("\n", [
            '{{studio_name}} — Participation Waiver',
            '',
            'Student: {{student_name}}',
            'Contact: {{student_contact}}',
            'Date: {{date}}',
            '',
            'I acknowledge the studio safety rules explained before every',
            'tool-based class and confirm my emergency contact details are',
            'up to date with the studio.',
            '',
            'Signature: ______________________',
            '',
            'Owner: {{owner_name}}',
        ]);
    }

    private function fill(string $template, array $variables): string
    {
        return strtr($template, $variables);
    }

    private function render(string $text): string
    {
        $lines = explode("\n", str_replace("\r", '', strip_tags($text)));
        $pages = array_chunk($lines, 44);
        $objects = [];
        $kids = [];
        $next = 3; // 1 = catalog, 2 = pages root

        foreach ($pages as $pageLines) {
            $pageId = $next++;
            $contentId = $next++;
            $kids[] = "$pageId 0 R";

            $stream = "BT /F1 12 Tf 56 780 Td 16 TL\n";
            foreach ($pageLines as $line) {
                $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
                $stream .= "($escaped) Tj T*\n";
            }
            $stream .= 'ET';

            $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents $contentId 0 R /Resources << /Font << /F1 $next 0 R >> >> >>";
            $objects[$contentId] = "<< /Length ".strlen($stream)." >>\nstream\n$stream\nendstream";
        }

        $fontId = $next++;
        $objects[$fontId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.count($kids).' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "$id 0 obj\n$body\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $count = count($objects) + 1;
        $pdf .= "xref\n0 $count\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }
        $pdf .= "trailer\n<< /Size $count /Root 1 0 R >>\nstartxref\n$xrefPos\n%%EOF";

        return $pdf;
    }
}
