<?php

namespace App\Http\Controllers;

use App\Exports\TransactionExport;
use App\Imports\TransactionImport;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{

    public function getDepositHistory()
    {
        $depositHistory = Transaction::where('transaction_type', 'deposit')
            ->where('status', 'success')
            ->with('user')
            ->get();

        return Inertia::render('Transaction/History/DepositHistory', [
            'depositHistory' => $depositHistory,
        ]);
    }
    
    public function getWithdrawalHistory()
    {
        $withdrawalHistory = Transaction::where('transaction_type', 'withdrawal')
            ->where('status', 'success')
            ->with('user')
            ->get();

        return Inertia::render('Transaction/History/WithdrawalHistory', [
            'withdrawalHistory' => $withdrawalHistory,
        ]);
    }

    public function importDepositHistory(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:25000',
        ]);

        $file = $request->file('file');

        Excel::import(new TransactionImport, $file);

        return Redirect::route('transaction.history.getDepositHistory');
    }

    public function exportDepositHistory()
    {
        return Excel::download(new TransactionExport, 'depositHistory.xlsx');
    }
}
