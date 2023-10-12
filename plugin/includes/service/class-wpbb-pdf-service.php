<?php
require_once plugin_dir_path(dirname(__FILE__)) . '../vendor/autoload.php';

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

class Wpbb_PDF_Service {
  /**
   * @param array $pdfParams - array of arrays
   * @return string
   *
   * $pdfParams = [
   *  [
   *    'content' => 'https://wpbb-static-designs.s3.amazonaws.com/12x16_bmb_cmyk_corner_light.pdf',
   *  ],
   *  [
   * 		'content' => '',
   * 		'orientation' => 'P',
   * 		'size' => [12, 16]
   * 	]
   * ]
   */
  public function merge_pdfs(array $pdfParams) {
    // Initiate FPDI
    $pdf = new Fpdi('P', 'in');

    // Add a page from the first and second PDFs
    foreach ($pdfParams as $pdfParam) {
      $content = $pdfParam['content'] ?? '';
      $orientation = $pdfParam['orientation'] ?? '';
      $size = $pdfParam['size'] ?? '';

      $this->addPdfPage($pdf, $content, $orientation, $size);
    }

    // Output the result as a string
    $outputPdfContent = $pdf->Output('S');
    return $outputPdfContent;
  }

  private function addPdfPage(
    $pdf,
    $content = '',
    $orientation = '',
    $size = ''
  ) {
    if (empty($content)) {
      return $pdf->AddPage($orientation, $size);
    }

    $pageId = $pdf->setSourceFile(StreamReader::createByString($content));
    $template = $pdf->importPage($pageId);

    // If no orientation or size is provided, derive them from the content
    if (empty($orientation) || empty($size)) {
      $sizeAndOrientation = $pdf->getTemplateSize($template);
      $orientation = empty($orientation)
        ? $sizeAndOrientation['orientation']
        : $orientation;
      $size = empty($size) ? $sizeAndOrientation : $size;
    }

    $pdf->AddPage($orientation, $size);
    $pdf->useTemplate($template);
  }
}
