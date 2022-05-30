<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\FromCollection;
use DB;
class Ventas_export implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DB::table('ventas')->get();
    }
}
