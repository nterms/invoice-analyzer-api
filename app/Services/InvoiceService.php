<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\InvoiceRepository;
use App\Repositories\InvoiceItemRepository;
use App\Services\AWS\TextractService;
use App\Traits\ParsesData;
use Carbon\Carbon;
use Exception;

class InvoiceService
{
    use ParsesData;

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
        // Get invoice details parsed through AWS Textract service
        // Provided file is parsed to detect the invoice details with 
        // a confidense level and parsed data returned as ExpenseDocuments
        // Refer to: https://docs.aws.amazon.com/textract/latest/dg/API_ExpenseDocument.html
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

                // Examine ExpenseDocument SummaryFields to pick relavant data
                // Refer to: https://docs.aws.amazon.com/textract/latest/dg/API_ExpenseField.html
                if (!empty($doc['SummaryFields'])) {
                    foreach ($doc['SummaryFields'] as $field) {
                        // Check each field data to extract information
                        // Refer to: https://docs.aws.amazon.com/textract/latest/dg/API_ExpenseDetection.html
                        // Refer to: https://docs.aws.amazon.com/textract/latest/dg/invoices-receipts.html
                        if (isset($field['Type']['Text'], $field['ValueDetection']['Text'])) {
                            if ($field['Type']['Text'] == 'INVOICE_RECEIPT_ID') {
                                $invoiceData['invoice_number'] = $field['ValueDetection']['Text'];
                            }
                            if ($field['Type']['Text'] == 'TOTAL') {
                                $invoiceData['total_amount'] = $this->getFloatValue($field['ValueDetection']['Text']);
                            }
                            if ($field['Type']['Text'] == 'ORDER_DATE') {
                                $invoiceData['purchase_date'] = Carbon::parse($field['ValueDetection']['Text'])->format('Y-m-d');
                            }
                        }
                    }
                }

                // Save only if invoice number is available
                if (!empty($invoiceData['invoice_number'])) {
                    logger()->info($invoiceData);
                    $invoice = $this->invoiceRepository->create($invoiceData);
                    $invoiceCount++;
                }

                // In voice can have multiple tables of items which are identified as LineItemGroups
                // Refer to: https://docs.aws.amazon.com/textract/latest/dg/API_LineItemGroup.html
                if (isset($invoice) && !empty($doc['LineItemGroups'])) {
                    foreach ($doc['LineItemGroups'] as $itemGroup) {
                        // A roup has multiple LineItemFields
                        if (!empty($itemGroup['LineItems'])) {
                            foreach ($itemGroup['LineItems'] as $line) {
                                $invoiceItemData = [
                                    'invoice_id' => $invoice->id,
                                    'item_name' => null,
                                    'quantity' => null,
                                    'price_per_unit' => null,
                                    'total_price' => null,
                                ];

                                // Each LineItem has multiple LineItemExpenseFields
                                // Refer to: https://docs.aws.amazon.com/textract/latest/dg/API_ExpenseField.html
                                if (!empty($line['LineItemExpenseFields'])) {
                                    foreach ($line['LineItemExpenseFields'] as $field) {
                                        if (isset($field['Type']['Text'], $field['ValueDetection']['Text'])) {
                                            if ($field['Type']['Text'] == 'ITEM') {
                                                $invoiceItemData['item_name'] = $field['ValueDetection']['Text'];
                                            }
                                            if ($field['Type']['Text'] == 'QUANTITY') {
                                                $invoiceItemData['quantity'] = $this->getFloatValue($field['ValueDetection']['Text']);
                                            }
                                            if ($field['Type']['Text'] == 'UNIT_PRICE') {
                                                $invoiceItemData['price_per_unit'] = $this->getFloatValue($field['ValueDetection']['Text']);
                                            }
                                            if ($field['Type']['Text'] == 'PRICE') {
                                                $invoiceItemData['total_price'] = $this->getFloatValue($field['ValueDetection']['Text']);
                                            }
                                        }
                                    }
                                }
                                
                                if (isset($invoiceItemData['quantity'])) {
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

    public function getInvoiceItems($invoiceId)
    {
        return $this->invoiceItemRepository->getAllByInvoice($invoiceId);
    }
}
