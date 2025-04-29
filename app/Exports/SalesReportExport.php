<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use App\Models\Order;

class SalesReportExport implements FromCollection, WithHeadings
{
    protected $from;
    protected $to;
    protected $status;
    protected $no_invoice;

    public function __construct($from, $to, $status, $no_invoice)
    {
        $this->from       = $from;
        $this->to         = $to;
        $this->status     = $status;
        $this->no_invoice = $no_invoice;
    }

    public function collection(): Collection
    {
        $q = Order::with(['client', 'paymentForm', 'paymentMethod']);

        if ($this->from) {
            $q->where('created_at', '>=', $this->from);
        }
        if ($this->to) {
            $q->where('created_at', '<=', $this->to);
        }
        if ($this->status !== null && $this->status !== '') {
            $q->where('state', $this->status);
        }
        if ($this->no_invoice) {
            $q->where('no_invoice', 'like', "%{$this->no_invoice}%");
        }

        return $q->get()->map(function($o) {
            return [
                'ID'             => $o->id,
                'Fecha'          => $o->created_at->format('Y-m-d H:i'),
                'Factura'        => $o->bill_number ?: $o->no_invoice,
                'Cliente'        => optional($o->client)->razon_social ?: '-',
                'Total Pagado'   => number_format($o->total_paid, 2, '.', ''),
                'V. Impuestos'   => number_format($o->total_iva_inc - $o->total_iva_exc, 2, '.', ''),
                'IVA Exc.'       => number_format($o->total_iva_exc, 2, '.', ''),
                'Descuento'      => number_format($o->total_discount, 2, '.', ''),
                'Forma Pago'     => optional($o->paymentForm)->name ?: '-',
                'Método Pago'    => optional($o->paymentMethod)->name ?: '-',
                'Estado Dian'    => $o->status_dian ? 'Recibida' : 'No recibida',
                'Estado'         => $this->mapState($o->state),
            ];
        });
    }

    protected function mapState($s)
    {
        switch ($s) {
            case 2: return 'Facturado';
            case 4: return 'Facturar e imprimir';
            // …otros estados que necesites…
            default: return (string) $s;
        }
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha',
            'Factura',
            'Cliente',
            'Total Pagado',
            'V. Impuestos',
            'IVA Exc.',
            'Descuento',
            'Forma Pago',
            'Método Pago',
            'Estado Dian',
            'Estado',
        ];
    }
}
