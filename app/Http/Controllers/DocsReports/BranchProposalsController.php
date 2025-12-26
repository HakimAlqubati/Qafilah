<?php

namespace App\Http\Controllers\DocsReports;

use App\Http\Controllers\Controller;
use App\Services\DocsReports\BranchProposalsService;

class BranchProposalsController extends Controller
{
    public function index(BranchProposalsService $service)
    {
        return view('docs-reports.branch-proposals', [
            'data' => $service->getProposals(),
        ]);
    }
}
