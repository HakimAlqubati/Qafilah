<?php

namespace App\Http\Controllers\DocsReports;

use App\Http\Controllers\Controller;
use App\Services\DocsReports\RequiredReportsService;

class RequiredReportsController extends Controller
{
    public function index(RequiredReportsService $service)
    {
        return view('docs-reports.required-reports', [
            'data' => $service->getReports(),
        ]);
    }
}
