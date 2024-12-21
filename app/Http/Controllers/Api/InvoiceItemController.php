<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceItemRequest;
use App\Http\Requests\UpdateInvoiceItemRequest;
use App\Models\InvoiceItem;
use App\Services\InvoiceService;

class InvoiceItemController extends Controller
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
     * Display a listing of the resource.
     * 
     * @param int $invoiceId ID of the invoice to list the items
     */
    public function index($invoiceId)
    {
        return response($this->invoiceService->getInvoiceItems($invoiceId));
    }
}
