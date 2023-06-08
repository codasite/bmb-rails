<?php
require_once plugin_dir_path(dirname(__FILE__)) . '../vendor/autoload.php';

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

class Wp_Bracket_Builder_PDF_Service {

	public function test_service() {
		return 'test';
	}

	public function merge_from_string($firstPdfContent, $secondPdfContent): string {
		// Initiate FPDI
		$pdf = new Fpdi();

		// Add a page from the first and second PDFs
		$this->addPageFromPdf($pdf, $firstPdfContent);
		$this->addPageFromPdf($pdf, $secondPdfContent);

		// Output the result as a string
		$outputPdfContent = $pdf->Output('S');
		return $outputPdfContent;
	}

	private function addPageFromPdf($pdf, $content) {
		$pageId = $pdf->setSourceFile(StreamReader::createByString($content));
		$template = $pdf->importPage($pageId);
		$size = $pdf->getTemplateSize($template);

		$pdf->AddPage($size['orientation'], $size);
		$pdf->useTemplate($template);
	}
}
