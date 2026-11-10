<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class PdfService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function download(string $view, array $data, string $filename, string $paper = 'a4'): Response
    {
        return Pdf::loadView($view, $data + ['pdf' => true])
            ->setPaper($paper, 'portrait')
            ->download($filename);
    }
}
