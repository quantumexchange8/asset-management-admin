<?php

namespace App\Imports;

use App\Models\TradeBrokerHistory;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CommissionsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $broker_id;

    public function __construct($broker_id)
    {
        $this->broker_id = $broker_id;
    }

    public function model(array $row)
    {
        // Convert Excel serial date to Y-m-d H:i:s format
        $transactionDate = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['transaction_date']))->format('Y-m-d H:i:s');

        return new TradeBrokerHistory([
            'email' => $row['email'],
            //  'mt_login' => $row['mt_login'],
            'volume' => $row['lot_size'],
            'date' => $transactionDate,
            'broker_id' => $this->broker_id,
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'lot_size' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'transaction_date' => 'required'
        ];
    }

    public function customValidationMessages()
    {
        return [
            'email.required' => 'Email field is required',
            'email.email' => 'Wrong email format',
            'email.exists' => 'Email does not exist',

            'lot_size.required' => 'Lot size is required',
            'lot_size.regex' => 'Lot size must be a number with up to two decimal places',

            'transaction_date.required' => 'Transaction date is required',
        ];
    }
}
