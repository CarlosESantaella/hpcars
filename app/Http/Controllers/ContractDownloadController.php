<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Reservation;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Symfony\Component\HttpFoundation\Response;

class ContractDownloadController extends Controller
{
    public function download(Contract $contract): Response
    {
        $contract->loadMissing('reservation.client');

        if (! $contract->pdf_path || ! Storage::disk('public')->exists($contract->pdf_path)) {
            abort(404, 'PDF del contrato no encontrado');
        }

        $clientName = $contract->reservation->client->name ?? 'cliente';
        $filename = 'contrato_' . str_replace(' ', '_', $clientName) . '_' . $contract->created_at->format('Y-m-d') . '.pdf';

        return Storage::disk('public')->download($contract->pdf_path, $filename);
    }

    /**
     * Genera el PDF del contrato usando la plantilla base y los datos de la reserva.
     *
     * @param  array<string, mixed>  $checklist  Datos del checklist de entrega
     */
    public static function generatePdf(Reservation $reservation, array $checklist = []): string
    {
        $reservation->loadMissing(['client', 'vehicle']);
        $client = $reservation->client;
        $vehicle = $reservation->vehicle;

        $templatePath = public_path('assets/docs/contrato.pdf');

        if (! file_exists($templatePath)) {
            throw new \RuntimeException('Plantilla de contrato no encontrada');
        }

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($templatePath);

        // --- Pagina 1: Cabecera del contrato con datos del cliente ---
        $templateId = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($templateId);

        // Fecha (italica para coincidir con la plantilla)
        $pdf->SetFont('Helvetica', 'I', 11);
        $now = now();
        $pdf->SetXY(94, 41.5);
        $pdf->Write(0, self::toLatin($now->format('d')));
        $pdf->SetXY(104, 41.5);
        $pdf->Write(0, self::toLatin($now->format('m')));
        $pdf->SetXY(117, 41.5);
        $pdf->Write(0, self::toLatin($now->format('Y')));

        // Datos del cliente (fuente normal)
        $pdf->SetFont('Helvetica', '', 10);

        // Nombre del cliente
        $pdf->SetXY(43, 85);
        $pdf->Write(0, self::toLatin($client->name));

        // Nombre de empresa (mercantil)
        $pdf->SetXY(74, 90);
        $pdf->Write(0, self::toLatin($client->company_name));

        // NIF/CIF
        $pdf->SetXY(152, 90);
        $pdf->Write(0, self::toLatin($client->dni));

        // Domicilio
        $pdf->SetXY(96, 94.5);
        $pdf->Write(0, self::toLatin($client->address));


        // --- Pagina 2: Cláusulas con datos de duración ---
        $templateId = $pdf->importPage(2);
        $pdf->AddPage();
        $pdf->useTemplate($templateId);

        $rentalDays = $reservation->start_date->diffInDays($reservation->end_date);
        $pdf->SetFont('Helvetica', '', 10);

        // Duración: "será de ____ DÍA" (cláusula 2.1)
        $pdf->SetXY(102, 89);
        $pdf->Write(0, self::toLatin((string) $rentalDays));

        $templateId = $pdf->importPage(3);
        $pdf->AddPage();
        $pdf->useTemplate($templateId);

        $templateId = $pdf->importPage(4);
        $pdf->AddPage();
        $pdf->useTemplate($templateId);

        // --- Pagina 5: Anexo I - Datos del vehículo, cliente y alquiler ---
        $templateId = $pdf->importPage(5);
        $pdf->AddPage();
        $pdf->useTemplate($templateId);
        $pdf->SetFont('Helvetica', '', 9);

        // Vehículo (columna derecha)
        $pdf->SetXY(122, 49);
        $pdf->Write(0, self::toLatin($vehicle->brand));

        $pdf->SetXY(122, 54);
        $pdf->Write(0, self::toLatin($vehicle->model));

        $pdf->SetXY(124, 60);
        $pdf->Write(0, self::toLatin($vehicle->plate));

        $startKm = $reservation->start_mileage ?? $vehicle->current_mileage;
        $pdf->SetXY(127, 65);
        $pdf->Write(0, number_format($startKm, 0, ',', '.'));

        // Arrendatario (columna izquierda)
        $pdf->SetXY(25, 93);
        $pdf->Write(0, self::toLatin($client->name));

        $pdf->SetXY(38, 97.5);
        $pdf->Write(0, self::toLatin($client->dni));

        $pdf->SetXY(32, 102);
        $pdf->Write(0, self::toLatin($client->phone));

        $pdf->SetXY(35, 106.5);
        $pdf->Write(0, self::toLatin($client->email));

        // Datos del alquiler - Fecha entrega (día, mes, año por separado)
        $pdf->SetXY(63, 141);
        $pdf->Write(0, self::toLatin($reservation->start_date->format('d')));
        $pdf->SetXY(68, 141);
        $pdf->Write(0, self::toLatin($reservation->start_date->format('m')));
        $pdf->SetXY(73, 141);
        $pdf->Write(0, self::toLatin($reservation->start_date->format('Y')));

        // Fecha devolución (día, mes, año por separado)
        $pdf->SetXY(70, 158);
        $pdf->Write(0, self::toLatin($reservation->end_date->format('d')));
        $pdf->SetXY(79, 158);
        $pdf->Write(0, self::toLatin($reservation->end_date->format('m')));
        $pdf->SetXY(86, 158);
        $pdf->Write(0, self::toLatin($reservation->end_date->format('Y')));

        $pdf->SetXY(60, 166.5);
        $pdf->Write(0, self::toLatin((string) $rentalDays));

        // Combustible a la entrega (del checklist si está disponible)
        $fuelLabels = [
            'E' => 'Reserva',
            '1/4' => '1/4',
            '1/2' => '1/2',
            '3/4' => '3/4',
            'full' => 'Lleno',
        ];
        $fuelValue = $checklist['combustible'] ?? '';
        if (isset($fuelLabels[$fuelValue])) {
            $pdf->SetXY(69, 175);
            $pdf->Write(0, self::toLatin($fuelLabels[$fuelValue]));
        }

        // --- Pagina 6: Liquidación y Devolución ---
        $templateId = $pdf->importPage(6);
        $pdf->AddPage();
        $pdf->useTemplate($templateId);
        $pdf->SetFont('Helvetica', '', 9);

        // Fecha devolución (sección DEVOLUCIÓN, parte inferior derecha)
        $pdf->SetXY(130, 248);
        $pdf->Write(0, self::toLatin($reservation->end_date->format('d/m/Y')));

        // --- Pagina 7: Checklist Entrega ---
        if ($pageCount >= 7) {
            $templateId = $pdf->importPage(7);
            $pdf->AddPage();
            $pdf->useTemplate($templateId);
            $pdf->SetFont('Helvetica', '', 10);

            // Cliente y fecha
            $pdf->SetXY(59, 98);
            $pdf->Write(0, self::toLatin($client->name));

            $pdf->SetXY(138, 98);
            $pdf->Write(0, self::toLatin($reservation->start_date->format('d/m/Y')));

            // Vehiculo y KMS
            $pdf->SetXY(64, 107);
            $pdf->Write(0, self::toLatin($vehicle->fullName() . ' - ' . $vehicle->plate));

            if ($reservation->start_mileage) {
                $pdf->SetXY(166, 108);
                $pdf->Write(0, number_format($reservation->start_mileage, 0, ',', '.'));
            }

            // --- Marcas del checklist ---
            $pdf->SetFont('Helvetica', 'B', 12);

            // Golpes: checkboxes a la derecha del diagrama del coche
            $golpes = $checklist['golpes'] ?? 'sin_golpes';
            if ($golpes === 'sin_golpes') {
                $pdf->SetXY(90, 128);
                $pdf->Write(0, 'X');
            } else {
                $pdf->SetXY(140, 128);
                $pdf->Write(0, 'X');
            }

            // Limpieza
            $pdf->SetFont('Helvetica', 'B', 12);
            $limpieza = $checklist['limpieza'] ?? 'limpio';
            if ($limpieza === 'limpio') {
                $pdf->SetXY(90, 166);
                $pdf->Write(0, 'X');
            } else {
                $pdf->SetXY(90, 172);
                $pdf->Write(0, 'X');
            }

            // Equipamiento
            $equip = $checklist['equipamiento'] ?? [];
            if (in_array('triangulos', $equip)) {
                $pdf->SetXY(40, 199);
                $pdf->Write(0, 'X');
            }
            if (in_array('chaleco', $equip)) {
                $pdf->SetXY(84, 199);
                $pdf->Write(0, 'X');
            }
            if (in_array('llave_ruedas', $equip)) {
                $pdf->SetXY(122, 199);
                $pdf->Write(0, 'X');
            }
            if (in_array('compresor_gato', $equip)) {
                $pdf->SetXY(40, 206);
                $pdf->Write(0, 'X');
            }

            // Baliza V16 y Otros en la misma linea que compresor
            $otrosText = '';
            if (in_array('baliza_v16', $equip)) {
                $otrosText .= 'Baliza V16';
            }
            $equipOtros = $checklist['equipamiento_otros'] ?? '';
            if ($equipOtros) {
                $otrosText .= ($otrosText ? ', ' : '') . $equipOtros;
            }
            if ($otrosText) {
                $pdf->SetXY(89, 206);
                $pdf->Write(0, 'X');
                $pdf->SetFont('Helvetica', '', 8);
                $pdf->SetXY(109, 206);
                $pdf->Write(0, self::toLatin($otrosText));
            }

            // Combustible (seccion ENTREGA - indicador de nivel)
            $pdf->SetFont('Helvetica', 'B', 12);
            $fuel = $checklist['combustible'] ?? '';
            $fuelPositions = [
                'E' => 84,
                '1/4' => 98,
                '1/2' => 114,
                '3/4' => 131,
                'full' => 148,
            ];
            if (isset($fuelPositions[$fuel])) {
                $pdf->SetXY($fuelPositions[$fuel], 224);
                $pdf->Write(0, 'X');
            }
        }

        $templateId = $pdf->importPage(8);
        $pdf->AddPage();
        $pdf->useTemplate($templateId);

        // Paginas restantes
        for ($i = 9; $i <= $pageCount; $i++) {
            $templateId = $pdf->importPage($i);
            $pdf->AddPage();
            $pdf->useTemplate($templateId);
        }

        // Guardar en storage
        $path = 'contracts/contrato_reserva_' . $reservation->id . '_' . now()->format('Ymd_His') . '.pdf';
        Storage::disk('public')->put($path, $pdf->Output('S'));

        return $path;
    }

    private static function toLatin(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
    }

    private static function spanishMonth(int $month): string
    {
        return match ($month) {
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre',
        };
    }
}
