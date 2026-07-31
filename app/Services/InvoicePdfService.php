<?php

declare(strict_types=1);

namespace App\Services;

require_once dirname(__DIR__) . '/Libraries/fpdf/fpdf.php';

use FPDF;

/**
 * Renders a branded invoice PDF using the vendored FPDF library (kept as-is
 * from the original project since it's a plain-PHP, permissively-licensed
 * library with no Composer/network dependency — just reorganized behind a
 * proper service class instead of being inlined into pdf.php with raw
 * unescaped $_POST-driven queries).
 */
final class InvoicePdfService
{
    public function roomInvoice(array $booking): string
    {
        $pdf = $this->baseDocument('ROOM BOOKING INVOICE');

        $rows = [
            'Booking Ref'   => $booking['booking_ref'],
            'Guest Name'    => $booking['guest_name'],
            'Phone'         => $booking['guest_phone'],
            'Room Type'     => $booking['room_type_name'] ?? '-',
            'Room Number'   => $booking['room_number'] ?? '-',
            'Check-in'      => $booking['check_in'],
            'Check-out'     => $booking['check_out'],
            'Nights'        => (string) $booking['nights'],
            'Guests'        => (string) $booking['guests_count'],
            'Room Subtotal' => money($booking['room_rate_snapshot'] * $booking['nights']),
            'Extras'        => money($booking['services_total']),
            'Discount'      => '-' . money($booking['discount_amount']),
            'Tax'           => money($booking['tax_amount']),
            'Total'         => money($booking['total_amount']),
            'Status'        => ucfirst($booking['status']),
        ];

        $this->renderRows($pdf, $rows);
        return $pdf->Output('', 'S');
    }

    public function eventInvoice(array $booking): string
    {
        $pdf = $this->baseDocument('EVENT BOOKING INVOICE');

        $rows = [
            'Booking Ref' => $booking['booking_ref'],
            'Guest Name'  => $booking['guest_name'],
            'Phone'       => $booking['guest_phone'],
            'Event Type'  => $booking['event_type_name'] ?? '-',
            'Date'        => $booking['event_date'],
            'Time'        => $booking['start_time'] . ' - ' . $booking['end_time'],
            'Guests'      => (string) $booking['guests_count'],
            'Total'       => money($booking['total_amount']),
            'Status'      => ucfirst($booking['status']),
        ];

        $this->renderRows($pdf, $rows);
        return $pdf->Output('', 'S');
    }

    private function baseDocument(string $title): FPDF
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 22);
        $pdf->Cell(190, 10, (string) setting('hotel_name', 'The Pacific Hotel'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 13);
        $pdf->Cell(190, 8, $title, 0, 1, 'C');
        $pdf->Ln(8);
        return $pdf;
    }

    private function renderRows(FPDF $pdf, array $rows): void
    {
        $pdf->SetFont('Arial', '', 12);
        foreach ($rows as $label => $value) {
            $pdf->Cell(60, 9, $label, 0, 0);
            $pdf->Cell(10, 9, ':', 0, 0);
            $pdf->Cell(120, 9, (string) $value, 0, 1);
        }
    }
}
