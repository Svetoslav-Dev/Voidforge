<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;

class ReceiptPdfService
{
    /**
     * Build a simple PDF receipt for the given order.
     */
    public function render(Order $order): string
    {
        $lines = $this->receiptLines($order);
        $pages = array_chunk($lines, 42);

        $objects = [];
        $pageObjectNumbers = [];
        $contentObjectNumbers = [];
        $nextObjectNumber = 3;

        foreach ($pages as $pageLines) {
            $pageObjectNumbers[] = $nextObjectNumber++;
            $contentObjectNumbers[] = $nextObjectNumber++;
        }

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $kids = implode(' ', array_map(fn (int $number): string => $number.' 0 R', $pageObjectNumbers));
        $objects[2] = '<< /Type /Pages /Kids ['.$kids.'] /Count '.count($pageObjectNumbers).' >>';

        foreach ($pages as $index => $pageLines) {
            $pageObject = $pageObjectNumbers[$index];
            $contentObject = $contentObjectNumbers[$index];
            $objects[$pageObject] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >> /Contents '.$contentObject.' 0 R >>';

            $contentStream = $this->pageStream($pageLines);
            $objects[$contentObject] = '<< /Length '.strlen($contentStream)." >>\nstream\n".$contentStream."\nendstream";
        }

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number." 0 obj\n".$body."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= 'xref'."\n";
        $pdf .= '0 '.(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        foreach (array_keys($objects) as $number) {
            $pdf .= str_pad((string) $offsets[$number], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        $pdf .= 'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n".$xrefOffset."\n%%EOF";

        return $pdf;
    }

    /**
     * @return list<string>
     */
    private function receiptLines(Order $order): array
    {
        $lines = [
            'Voidforge Receipt',
            'Order #'.$order->id,
            'Placed: '.(optional($order->placed_at)->format('F d, Y H:i') ?? $order->created_at->format('F d, Y H:i')),
            'Status: '.ucfirst(str_replace('_', ' ', $order->status)),
            '',
            'Customer',
            $order->customer_name,
            $order->customer_email,
        ];

        if ($order->customer_phone) {
            $lines[] = $order->customer_phone;
        }

        $lines[] = '';
        $lines[] = 'Shipping Address';
        $lines[] = $order->shipping_address_line_1;

        if ($order->shipping_address_line_2) {
            $lines[] = $order->shipping_address_line_2;
        }

        $lines[] = trim(implode(', ', array_filter([
            $order->shipping_city,
            $order->shipping_state,
            $order->shipping_postal_code,
            $order->shipping_country,
        ])));

        $lines[] = '';
        $lines[] = 'Shirts';

        foreach ($order->items as $item) {
            $lines[] = sprintf(
                '%s | Size %s | Qty %d | %0.2f EUR',
                $item->product_name,
                $item->product_size,
                $item->quantity,
                $item->line_total_cents / 100
            );
        }

        $lines[] = '';
        $lines[] = sprintf('Subtotal: %0.2f EUR', $order->subtotal_cents / 100);
        $lines[] = sprintf('Shipping: %0.2f EUR', $order->shipping_cents / 100);
        $lines[] = sprintf('Total: %0.2f EUR', $order->total_cents / 100);
        $lines[] = '';
        $lines[] = 'Payments';

        if ($order->payments->isEmpty()) {
            $lines[] = 'No completed payment recorded.';
        } else {
            foreach ($order->payments as $payment) {
                $lines[] = $this->paymentLine($payment);
            }
        }

        return $lines;
    }

    /**
     * @param  list<string>  $lines
     */
    private function pageStream(array $lines): string
    {
        $stream = "BT\n/F1 12 Tf\n50 792 Td\n14 TL\n";

        foreach ($lines as $index => $line) {
            if ($index === 0) {
                $stream .= '(' . $this->escape($line) . ") Tj\n";
                continue;
            }

            $stream .= 'T* (' . $this->escape($line) . ") Tj\n";
        }

        $stream .= 'ET';

        return $stream;
    }

    private function paymentLine(Payment $payment): string
    {
        return sprintf(
            '%s | %s | %s | %0.2f EUR',
            ucfirst($payment->provider),
            $payment->status,
            $payment->transaction_id,
            $payment->amount / 100
        );
    }

    private function escape(string $value): string
    {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('(', '\(', $value);
        $value = str_replace(')', '\)', $value);

        return preg_replace('/[^\x20-\x7E]/', '?', $value) ?? '';
    }
}
