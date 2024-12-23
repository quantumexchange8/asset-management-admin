<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Transaction::with('user')
        ->where('status', 'success')
        ->where('transaction_type', 'deposit')
        ->get([
            'transaction_number',
            'user_id',
            'amount',
            'fund_type',
            'status',
            'approval_at',
        ]);
    }

    public function headings(): array
    {
        return [
            'Transaction Number',
            'Name',
            'Amount(USD)',
            'Fund Type',
            'Status',
            'ApprovalAt',
        ];
    }

    public function map($transaction): array 
    {
        return [
            $transaction->transaction_number,
            $transaction->user->name,
            $transaction->amount,
            $transaction->fund_type,
            $transaction->status,
            $transaction->approval_at,
        ];
    }
      

}
