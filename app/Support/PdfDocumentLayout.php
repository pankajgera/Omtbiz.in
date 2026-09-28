<?php

namespace App\Support;

use DOMElement;
use Dompdf\Canvas;
use Dompdf\FontMetrics;
use Dompdf\FrameDecorator\AbstractFrameDecorator;

class PdfDocumentLayout
{
    public static function numberPage(int $pageNumber, int $pageCount, Canvas $canvas, FontMetrics $fonts): void
    {
        $text = "{$pageNumber}/{$pageCount}";
        $font = $fonts->getFont('DejaVu Sans');
        $size = 7.5;
        $x = $canvas->get_width() - 12 * 72 / 25.4 - $fonts->getTextWidth($text, $font, $size);
        $y = $canvas->get_height() - 8 * 72 / 25.4;
        $canvas->text($x, $y, $text, $font, $size, [0.325, 0.384, 0.455]);
    }

    public static function alignFinalFooter(AbstractFrameDecorator $frame, Canvas $canvas): void
    {
        $node = $frame->get_node();
        if (! $node instanceof DOMElement || ! $node->hasAttribute('data-last-page-footer')) {
            return;
        }

        // Pagination has already placed this non-repeating block on its final page.
        // Move it and its children down into the remaining space, above the 12mm margin.
        $bottom = $frame->get_position('y') + $frame->get_margin_height();
        $offset = $canvas->get_height() - 12 * 72 / 25.4 - $bottom;
        if ($offset > 0) {
            $frame->move(0, $offset);
        }
    }
}
