<?php

namespace App\Http\Controllers\DocsReports;

use App\Http\Controllers\Controller;
use App\Services\DocsReports\DefaultUnitReportService;

class DefaultUnitReportController extends Controller
{
    public function index(DefaultUnitReportService $service)
    {
        return view('docs-reports.default-unit-report', [
            'data' => $service->getReport(),
        ]);
    }
}
