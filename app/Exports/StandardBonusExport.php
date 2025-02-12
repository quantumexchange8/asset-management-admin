<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StandardBonusExport implements FromCollection, WithHeadings
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
            ->orderByDesc('created_at')
            ->get();

        $result = array();
        foreach ($records as $record) {
            $result[] = array(
                'created_at' => Carbon::parse($record->created_at)->format('Y-m-d'),
                'name' => $record->user->name ?? '-',
                'email' => $record->user->email ?? '-',
                'client_name' => $record->subject_user->email ?? '-',
                'client_email' => $record->subject_user->email ?? '-',
                'broker' => $record->broker->name ?? '-',
                'bonus_type' => trans("public.$record->bonus_type"),
                'bonus_amount' => $record->bonus_amount,
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            trans('public.date'),
            trans('public.name'),
            trans('public.email'),
            trans('public.client_name'),
            trans('public.client_email'),
            trans('public.broker'),
            trans('public.type'),
            trans('public.amount') . ' ($)',
        ];
    }
}
