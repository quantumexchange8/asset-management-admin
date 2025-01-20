<?php

namespace App\Exports;

use App\Models\TradeBrokerHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CommissionsExport implements FromCollection, WithHeadings
{
    private $query;

    public function __construct($query)
    {
        $this->query = $query; // Filtered query passed from the controller
    }

    public function collection()
    {
        // Get all records from the filtered query as a subquery
        $filteredData = $this->query->select('email', 'volume', 'date', 'status');

        // Join the filtered data with the users table
        $commissionData = DB::table(DB::raw("({$filteredData->toSql()}) as filtered_data")) // toSql() converts laravel query builder into raw SQL string
            ->mergeBindings($filteredData->getQuery()) // mergeBindings() is to merge parameters and its value. 
            ->leftJoin('users', DB::raw('users.email COLLATE utf8mb4_unicode_ci'), '=', DB::raw('filtered_data.email COLLATE utf8mb4_unicode_ci'))
            ->select(
                'users.name',           
                'filtered_data.*'                
            )
            ->get();

        return $commissionData;
    }


    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Lot Size',
            'Date',
            'Status',
            'Broker',
        ];
    }
}
