<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportInvoiceRequest;
use App\Models\Invoice;
use App\Services\InvoiceService;

class InvoiceController extends Controller
{
    protected $invoiceService;

    /**
     * Constructor
     * 
     * @param \App\Services\InvoiceService $invoiceService
     */
    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Returns a list of invoices.
     */
    public function index()
    {
        return response($this->invoiceService->getAllInvoices());
    }

    /**
     * Import invoice files and store data
     */
    public function import(ImportInvoiceRequest $request)
    {
        // Get the uploaded file (validated at ImportInvoiceRequest)
        $file = $request->file('file');
        $invoiceCount = 0;

        try {
            // Parse the file and insert the detected invoice data to invoice and invoice_items tables
            $invoiceCount = $this->invoiceService->importFromFile($file->getRealPath(), auth()->user());
        } catch (\Throwable $th) {
            logger()->error($th->getMessage());
            logger()->info($th->getTraceAsString());
            return response([
                'error' => 'Error importing invoice data.',
            ], 422);
        }

        return response([
            'message' => $invoiceCount > 0 ? $invoiceCount . ' invoices imported.' : 'No invoices found in file uploaded.',
        ]);
    }
}
