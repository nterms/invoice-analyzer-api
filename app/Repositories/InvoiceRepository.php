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

    /**
     * Get all models.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->model->orderBy('created_at', 'DESC')->get();
    }
}
