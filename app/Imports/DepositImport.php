<?php

namespace App\Imports;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
class DepositImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $approvalDate = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['approval_date']))->format('Y-m-d H:i:s');
        return new Transaction([
            'user_id' => $row['user_id'],
            'transaction_type' => $row['transaction_type'],
            'transaction_number' => $row['transaction_number'],
            'to_payment_account_no' => $row['to_payment_account_no'],
            'amount' => $row['amount'],
            'transaction_charges' => $row['transaction_charges'],
            'from_currency' => $row['from_currency'],
            'to_currency' => $row['to_currency'],
            'transaction_amount' => $row['transaction_amount'],
            'fund_type' => $row['fund_type'],
            'status' => $row['status'],
            'category' => $row['category'],
            'approval_at' => $approvalDate,
        ]);
    }

    public function rules(): array{
        return [
            'user_id' => 'required|exists:users,id',
            'transaction_type' => 'required|in:deposit,withdrawal',
            'transaction_number' => 'required|unique:transactions,transaction_number',
            'to_payment_account_no' => 'required',
            'amount' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/', 
            'transaction_charges' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'from_currency' => 'required',
            'to_currency' => 'required',
            'transaction_amount' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'fund_type' => 'required|in:real_fund,demo_fund', 
            'status' => 'required|in:success,rejected', 
            'category' => 'required',
            'approval_date' => 'required',
        ];
    }

    public function customValidationMessages(){
        return [
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'User ID does not exists',

            'transaction_type.required' => 'Transaction type is required',
            'transaction_type.in' => 'Transaction type must be either deposit or withdrawal',

            'transaction_number.required' => 'Transaction number is required',
            'transaction_number.unique' => 'Transaction number has already been used',

            'to_payment_account_no.required' => 'To payment account number is required',
            
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a number',
            'amount.regex' => 'Amount must have at most 2 decimal places',

            'transaction_charges.required' => 'Transaction charges are required',
            'transaction_charges.numeric' => 'Transaction charges must be a number',
            'transaction_charges.regex' => 'Transaction charges must have at most 2 decimal places',

            'from_currency.required' => 'From currency is required',
            'to_currency.required' => 'To currency is required',

            'transaction_amount.required' => 'Transaction amount is required',
            'transaction_amount.numeric' => 'Transaction amount must be a number',
            'transaction_amount.regex' => 'Transaction amount must have at most 2 decimal places',

            'fund_type.required' => 'Fund type is required',
            'fund_type.in' => 'Fund type must be either real_fund or demo_fund',

            'status.required' => 'Status is required',
            'status.in' => 'Status must be either success or rejected',

            'category.required' => 'Category is required',
            'approval_date.required' => 'Approval date and time are required',
        ];
    }
}
