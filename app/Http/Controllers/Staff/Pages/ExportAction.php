<?php

namespace App\Http\Controllers\Staff\Pages;

use App\Exports\PagesExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportAction extends Controller
{
    public function __invoke()
    {
        $now = Carbon::now()->format('Y-m-d_H-i-s');
        return Excel::download(new PagesExport(), "お知らせ一覧_{$now}.csv");
    }
}
