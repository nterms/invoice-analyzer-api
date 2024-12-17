<?php

namespace App\Repositories;

use App\Models\InvoiceItem;
use App\Repositories\Traits\Repository;
use Illuminate\Database\Eloquent\Model;

class InvoiceItemRepository
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
    public function __construct(InvoiceItem $invoiceItem)
    {
        $this->model = $invoiceItem;
    }
}
