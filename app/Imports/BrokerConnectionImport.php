<?php

namespace App\Imports;

use App\Models\BrokerConnection;
use App\Models\User;
use App\Services\RunningNumberService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class BrokerConnectionImport implements ToCollection, WithHeadingRow
{
    use Importable;

    private $broker_id;

    public function __construct($broker_id)
    {
        $this->broker_id = $broker_id;
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
                    'broker_login' => $row['login'],
                    'status' => 'active'
                ])->first();

                $joinedDate = is_numeric($row['joined_date'])
                    ? Carbon::instance(Date::excelToDateTimeObject($row['joined_date']))->format('Y-m-d')
                    : Carbon::parse($row['joined_date'])->format('Y-m-d');

                if ($existingConnection) {
                    BrokerConnection::create([
                        'user_id' => $user->id,
                        'broker_id' => $this->broker_id,
                        'broker_login' => $row['login'],
                        'capital_fund' => $row['amount'],
                        'connection_type' => 'top_up',
                        'connection_number' => RunningNumberService::getID('connection'),
                        'joined_at' => $joinedDate,
                        'status' => 'active'
                    ]);
                } else {
                    BrokerConnection::create([
                        'user_id' => $user->id,
                        'broker_id' => $this->broker_id,
                        'broker_login' => $row['login'],
                        'capital_fund' => $row['amount'],
                        'connection_type' => 'initial_join',
                        'connection_number' => RunningNumberService::getID('connection'),
                        'joined_at' => $joinedDate,
                        'status' => 'active'
                    ]);
                }
            }
        }
    }
}
