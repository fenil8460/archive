<?php

namespace App\Imports;

use App\Models\Url;
use Maatwebsite\Excel\Concerns\ToModel;

class UrlsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        dd($row[1]);

        return new Url([
            //
            'url' => $row[1],
        ]);
    }
}
