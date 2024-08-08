<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\InvoiceFilter;
use App\Http\Requests\InvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Currency;
use App\Models\Invoice;

/**
 *
 */
class InvoiceController extends Controller
{
    /**
     * @var string
     */
    protected string $model = Invoice::class;

    protected $filter = InvoiceFilter::class;

    protected array $with = ['currency'];

    public function __construct()
    {
        parent::__construct([
            'currency_id' => Currency::class,
        ]);
    }

    protected string $request = InvoiceRequest::class;

    /**
     * Display a paginated list of invoices with related order and transactions.
     *
     * @param \App\Filters\InvoiceFilter $filter The filter instance to apply on the invoices.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection An HTTP resource collection containing the paginated list of invoices with related order and transactions.
     */
    public function invoices(InvoiceFilter $filter): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $invoices = Invoice::with('transactions')
            ->orderBy('id', 'desc')
            ->filter($filter)
            ->paginate(request()->per_page ?? 10);
        return InvoiceResource::collection($invoices);
    }

    /**
     * Get transactions and related order information for a specific invoice.
     *
     * @param \App\Models\Invoice $invoice The invoice instance for which to retrieve transactions and related order information.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing transactions and related order information.
     */
    public function getTransactions(Invoice $invoice)
    {
        $response = [
            'transactions' => $invoice->transactions->load('payment_method_setting:id,label'),
            'order' => $invoice->order()->get(['uid', 'updated_at'])->first(),
        ];
        return response()->json($response);
    }
}
