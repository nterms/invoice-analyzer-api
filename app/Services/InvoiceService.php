<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\InvoiceRepository;
use App\Repositories\InvoiceItemRepository;
use App\Services\AWS\TextractService;
use Carbon\Carbon;
use Exception;

class InvoiceService
{
    protected $textractService;
    protected $invoiceRepository;
    protected $invoiceItemRepository;

    /**
     * Constructor
     * 
     * @param \App\Services\AWS\TextractService $textractService
     * @param \App\Repositories\InvoiceRepository $invoiceRepository
     * @param \App\Repositories\InvoiceItemRepository $invoiceItemRepository
     */
    public function __construct(TextractService $textractService, InvoiceRepository $invoiceRepository, InvoiceItemRepository $invoiceItemRepository)
    {
        $this->textractService = $textractService;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceItemRepository = $invoiceItemRepository;
    }

    /**
     * Import invoices from the given file and store in DB
     * 
     * @param string $filePath
     * @param \App\Models\User $user
     */
    public function importFromFile(string $filePath, User $user)
    {
        $expenseDocuments = $this->textractService->getExpenseDocuments($filePath);
        $invoiceCount = 0;

        if (!empty($expenseDocuments)) {
            foreach ($expenseDocuments as $doc) {
                $invoiceData = [
                    'user_id' => $user->id,
                    'invoice_number' => null,
                    'total_amount' => null,
                    'purchase_date' => null,
                ];

                if (!empty($doc['SummaryFields'])) {
                    foreach ($doc['SummaryFields'] as $field) {
                        if (isset($field['Type']['Text'], $field['ValueDetection']['Text'])) {
                            if ($field['Type']['Text'] == 'INVOICE_RECEIPT_ID') {
                                $invoiceData['invoice_number'] = $field['ValueDetection']['Text'];
                            }
                            if ($field['Type']['Text'] == 'TOTAL') {
                                $invoiceData['total_amount'] = str_replace([',', ' '], '', $field['ValueDetection']['Text']);
                            }
                            if ($field['Type']['Text'] == 'ORDER_DATE') {
                                $invoiceData['purchase_date'] = Carbon::parse($field['ValueDetection']['Text']);
                            }
                        }
                    }
                }

                if (!empty($invoiceData['invoice_number'])) {
                    $invoice = $this->invoiceRepository->create($invoiceData);
                    $invoiceCount++;
                }

                if (isset($invoice) && !empty($doc['LineItemGroups'])) {
                    foreach ($doc['LineItemGroups'] as $itemGroup) {
                        if (!empty($itemGroup['LineItems'])) {
                            foreach ($itemGroup['LineItems'] as $line) {
                                $invoiceItemData = [
                                    'invoice_id' => $invoice->id,
                                    'item_name' => null,
                                    'quantity' => null,
                                    'price_per_unit' => null,
                                    'total_price' => null,
                                ];

                                if (!empty($line['LineItemExpenseFields'])) {
                                    foreach ($line['LineItemExpenseFields'] as $field) {
                                        if (isset($field['Type']['Text'], $field['ValueDetection']['Text'])) {
                                            if ($field['Type']['Text'] == 'ITEM') {
                                                $invoiceData['item_name'] = $field['ValueDetection']['Text'];
                                            }
                                            if ($field['Type']['Text'] == 'QUANTITY') {
                                                $invoiceData['item_name'] = str_replace([',', ' '], '', $field['ValueDetection']['Text']);
                                            }
                                            if ($field['Type']['Text'] == 'UNIT_PRICE') {
                                                $invoiceData['price_per_unit'] = str_replace([',', ' '], '', $field['ValueDetection']['Text']);
                                            }
                                            if ($field['Type']['Text'] == 'PRICE') {
                                                $invoiceData['total_price'] = str_replace([',', ' '], '', $field['ValueDetection']['Text']);
                                            }
                                        }
                                    }
                                }

                                if (isset($invoiceItemData['item_name'])) {
                                    $this->invoiceItemRepository->create($invoiceItemData);
                                }
                            }
                        }
                    }
                }
            }
        } else {
            throw new Exception("Error parsing the given file.");
        }

        return $invoiceCount;
    }

    public function getAllInvoices() {
        return $this->invoiceRepository->getAll();
    }
}
