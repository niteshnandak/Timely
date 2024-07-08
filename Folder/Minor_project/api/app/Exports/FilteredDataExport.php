<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;
// use Illuminate\Support\Facades\View;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class FilteredDataExport implements FromView {

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('reports.report', [
            'filteredData' => $this->data
        ]);
    }
}

