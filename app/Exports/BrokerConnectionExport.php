<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BrokerConnectionExport implements FromCollection, WithHeadings
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
    * @return Collection
    */
    public function collection(): Collection
    {
        $records = $this->query
            ->orderByDesc('joined_at')
            ->get();

        $result = array();
        foreach ($records as $record) {
            $result[] = array(
                'joined_at' => Carbon::parse($record->joined_at)->format('Y-m-d'),
                'name' => $record->user->name ?? '-',
                'email' => $record->user->email ?? '-',
                'broker' => $record->broker->name ?? '-',
                'broker_login' => $record->broker_login,
                'connection_number' => $record->connection_number,
                'capital_fund' => $record->capital_fund,
                'status' => $record->status,
            );
        }

        return collect($result);
    }

    public function headings(): array
    {

        return [
            trans('public.join_date'),
            trans('public.name'),
            trans('public.email'),
            trans('public.broker'),
            trans('public.login'),
            trans('public.connection_number'),
            trans('public.fund') . ' ($)',
            trans('public.status'),
        ];
    }
}
