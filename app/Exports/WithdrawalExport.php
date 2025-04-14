<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WithdrawalExport implements FromCollection, WithHeadings
{
    private $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection(): Collection
    {
        $records = $this->query->get();

        $result = array();
        foreach ($records as $record) {
            $result[] = array(
                'name' => $record->user->name,
                'email' => $record->user->email,
                'transaction_number' => $record->transaction_number,
                'amount' => $record->amount,
                'transaction_charges' => $record->transaction_charges ?? '0.00',
                'transaction_amount' => $record->transaction_amount,
                'to_payment_account_name' => $record->to_payment_account_name ?? '-',
                'to_payment_account_no' => $record->to_payment_account_no ?? '-',
                'status' => $record->status,
                'approval_at' => $record->approval_at,
                'remarks' => $record->remarks ?? '-',
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            trans('public.name'),
            trans('public.email'),
            trans('public.transaction_number'),
            trans('public.amount') . ('$'),
            trans('public.fee') . '($)',
            trans('public.net_amount') . '($)',
            trans('public.payment_account'),
            trans('public.account_number'),
            trans('public.status'),
            trans('public.approved_at'),
            trans('public.remarks'),
        ];
    }
}
