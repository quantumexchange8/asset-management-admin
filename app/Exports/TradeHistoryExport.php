<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\BeforeSheet;

class TradeHistoryExport implements FromCollection, WithHeadings, WithEvents
{
    protected $query;
    protected $dateRangeLabel;

    public function __construct($query, $dateRangeLabel = null)
    {
        $this->query = $query;
        $this->dateRangeLabel = $dateRangeLabel;
    }

    /**
     * @return Collection
     */

    public function collection(): Collection
    {
        $records = $this->query
            ->orderByDesc('created_at')
            ->get();

        $result = array();
        foreach ($records as $record) {
            $result[] = array(
                'created_at' => Carbon::parse($record->created_at)->format('Y-m-d'),
                'name' => $record->user->name ?? '-',
                'email' => $record->user->email ?? '-',
                'broker_login' => $record->broker_login ?? '-',
                'volume' => $record->volume,
                'trade_net_profit' => $record->trade_net_profit,
            );
        }

        return collect($result);
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                // Insert the date label at A1
                $event->sheet->setCellValue('A1', $this->dateRangeLabel ?? '');
            },
        ];
    }

    public function headings(): array
    {
        return [
            trans('public.date'),
            trans('public.name'),
            trans('public.email'),
            trans('public.broker_login'),
            trans('public.lot_size'),
            trans('public.profit') . ' ($)',
        ];
    }
}
