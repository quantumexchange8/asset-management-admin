<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PendingDepositExport implements FromCollection, WithHeadings
{
    private $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection(): Collection
    {
        $filteredData = $this->query->get();

        $result = array();
        foreach ($filteredData as $data) {
            $result[] = array(
                'name' => $data->user->name,
                'email' => $data->user->email,
                'transaction_number' => $data->transaction_number,
                'amount' => $data->amount,
                'transaction_amount' => $data->transaction_amount,
                'transaction_charges' => $data->transaction_charges,
                'to_payment_account_no' => $data->to_payment_account_no,
                'transaction_type' => $data->transaction_type,
                'fund_type' => $data->fund_type,
                'status' => $data->status,
                'approval_at' => $data->approval_at,
                'remarks' => $data->remarks,
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Transaction Number',
            'Amount',
            'Transaction Amount',
            'Transaction Charges',
            'To Payment Account No',
            'Transaction Type',
            'Fund Type',
            'Status',
            'Approval Date',
            'Remarks',
        ];
    }
}
