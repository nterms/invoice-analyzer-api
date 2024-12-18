<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Repositories\Traits\Repository;
use Illuminate\Database\Eloquent\Model;

class InvoiceRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Model
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct(Invoice $invoice)
    {
        $this->model = $invoice;
    }
}
