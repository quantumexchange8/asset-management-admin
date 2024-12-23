<?php

namespace App\Imports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\ToModel;

class TransactionImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Transaction([
            'user_id' => $row[0],
            'transaction_type' => $row[1],
            'transaction_number' => $row[2],
            'to_payment_account_no' => $row[3],
            'amount' => $row[4],
            'transaction_charges' => $row[5],
            'from_currency' => $row[6],
            'to_currency' => $row[7],
            'transaction_amount' => $row[8],
            'fund_type' => $row[9],
            'status' => $row[10],
            'category' => $row[11],
            'approval_at' => now(),
        ]);
    }
}
