<?php

namespace App\Http\Controllers;

use App\Models\CampusSeason;
use App\Services\WooCommerceExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WooCommerceExportController extends Controller
{
    public function __invoke(Request $request, WooCommerceExportService $service): StreamedResponse
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'manager']), 403);

        $seasonId = $request->integer('season') ?: null;
        $filename = 'wc-product-export-' . now()->format('j-n-Y') . '-' . now()->getTimestampMs() . '.csv';

        return response()->streamDownload(
            fn() => print($service->generate($seasonId)),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }
}
