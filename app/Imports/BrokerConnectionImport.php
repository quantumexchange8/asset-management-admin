<?php

namespace App\Imports;

use App\Models\BrokerAccount;
use App\Models\BrokerConnection;
use App\Models\User;
use App\Services\RunningNumberService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class BrokerConnectionImport implements ToCollection, WithHeadingRow, WithValidation
{
    use Importable;

    private $broker_id;
    private $type;

    public function __construct($broker_id, $type)
    {
        $this->broker_id = $broker_id;
        $this->type = $type;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            $user = User::firstWhere('email', $row['email']);

            if ($user) {
                $existingConnection = BrokerConnection::where([
                    'user_id' => $user->id,
                    'broker_id' => $this->broker_id,
                    'broker_login' => $row['login'],
                    'status' => 'success',
                ])
                ->where('connection_type', '!=', 'withdrawal')
                ->first();

                $joinedDate = is_numeric($row['joined_date'])
                    ? Carbon::instance(Date::excelToDateTimeObject($row['joined_date']))->format('Y-m-d')
                    : Carbon::parse($row['joined_date'])->format('Y-m-d');

                if ($existingConnection && $this->type === 'deposit') {
                    BrokerConnection::create([
                        'user_id' => $user->id,
                        'broker_id' => $this->broker_id,
                        'broker_login' => $row['login'],
                        'capital_fund' => $row['amount'],
                        'connection_type' => 'top_up',
                        'connection_number' => RunningNumberService::getID('connection'),
                        'joined_at' => $joinedDate,
                        'status' => 'success'
                    ]);

                    $account = BrokerAccount::where([
                        'user_id' => $user->id,
                        'broker_id' => $this->broker_id,
                        'broker_login' => $row['login'],
                    ])
                    ->where('status', '!=', 'rejected')
                    ->first();

                    $account->broker_capital += $row['amount'];
                    $account->save();

                } elseif ($this->type === 'deposit') {
                    BrokerConnection::create([
                        'user_id' => $user->id,
                        'broker_id' => $this->broker_id,
                        'broker_login' => $row['login'],
                        'capital_fund' => $row['amount'],
                        'connection_type' => 'deposit',
                        'connection_number' => RunningNumberService::getID('connection'),
                        'joined_at' => $joinedDate,
                        'status' => 'success'
                    ]);

                    $account = BrokerAccount::where([
                        'user_id' => $user->id,
                        'broker_id' => $this->broker_id,
                        'broker_login' => $row['login'],
                    ])
                    ->where('status', '!=', 'rejected')
                    ->first();

                    $account->broker_capital += $row['amount'];
                    $account->save();

                } elseif ($this->type === 'withdrawal') {
                    BrokerConnection::create([
                        'user_id' => $user->id,
                        'broker_id' => $this->broker_id,
                        'broker_login' => $row['login'],
                        'capital_fund' => '-' . $row['amount'],
                        'connection_type' => 'withdrawal',
                        'connection_number' => RunningNumberService::getID('connection'),
                        'removed_at' => $joinedDate,
                        'status' => 'success'
                    ]);

                    $account = BrokerAccount::where([
                        'user_id' => $user->id,
                        'broker_id' => $this->broker_id,
                        'broker_login' => $row['login'],
                    ])
                    ->where('status', '!=', 'rejected')
                    ->first();

                    $account->broker_capital -= $row['amount'];
                    $account->save();

                } else {
                    BrokerConnection::create([
                        'user_id' => $user->id,
                        'broker_id' => $this->broker_id,
                        'broker_login' => $row['login'],
                        'capital_fund' => $row['amount'],
                        'connection_type' => 'initial_join',
                        'connection_number' => RunningNumberService::getID('connection'),
                        'joined_at' => $joinedDate,
                        'status' => 'success'
                    ]);

                    $account = BrokerAccount::where([
                        'user_id' => $user->id,
                        'broker_id' => $this->broker_id,
                        'broker_login' => $row['login'],
                    ])
                    ->where('status', '!=', 'rejected')
                    ->first();

                    $account->broker_capital += $row['amount'];
                    $account->save();
                }
            }
        }
    }
    
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'login' => 'required|exists:broker_accounts,broker_login',
            'joined_date' => 'required',
            'amount' => 'required|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'email.required' => trans('public.required_email_import'),
            'email.email' =>  trans('public.format_email_import'),
            'email.exists' =>  trans('public.exists_email_import'),
            'login.required' => trans('public.required_broker_login_import'),
            'login.exists' => trans('public.exists_broker_login_import'),
            'joined_date.required' => trans('public.required_joined_date_import'),
            'amount.required' => trans('public.required_amount_import'),
            'amount.numeric' => trans('public.numeric_amount_import')
        ];
    }
}
