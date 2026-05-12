<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = auth()->user()->invoices()
            ->with('client')
            ->latest()
            ->paginate(10);
        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = auth()->user()->clients()->get();
        return view('invoices.create', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request)
    {
        $data     = $request->validated();
        $items    = $data['items'];
        $taxRate  = $data['tax_rate'] ?? 0;
        $subtotal = collect($items)->sum(fn($i) => $i['quantity'] * $i['unit_price']);
        $taxAmount = $subtotal * ($taxRate / 100);

        $invoice = auth()->user()->invoices()->create([
            'client_id'      => $data['client_id'],
            'invoice_number' => Invoice::generateNumber(),
            'status'         => 'draft',
            'issue_date'     => $data['issue_date'],
            'due_date'       => $data['due_date'],
            'tax_rate'       => $taxRate,
            'subtotal'       => $subtotal,
            'tax_amount'     => $taxAmount,
            'total'          => $subtotal + $taxAmount,
            'notes'          => $data['notes'] ?? null,
        ]);

        foreach ($items as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'amount'      => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'สร้าง Invoice เรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        abort_if($invoice->user_id !== auth()->id(), 403);
        $invoice->load('client', 'items');
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        abort_if($invoice->user_id !== auth()->id(), 403);
        $clients = auth()->user()->clients()->get();
        $invoice->load('items');
        return view('invoices.edit', compact('invoice', 'clients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        abort_if($invoice->user_id !== auth()->id(), 403);
        $data     = $request->validated();
        $items    = $data['items'];
        $taxRate  = $data['tax_rate'] ?? 0;
        $subtotal = collect($items)->sum(fn($i) => $i['quantity'] * $i['unit_price']);
        $taxAmount = $subtotal * ($taxRate / 100);

        $invoice->update([
            'client_id'  => $data['client_id'],
            'issue_date' => $data['issue_date'],
            'due_date'   => $data['due_date'],
            'tax_rate'   => $taxRate,
            'subtotal'   => $subtotal,
            'tax_amount' => $taxAmount,
            'total'      => $subtotal + $taxAmount,
            'notes'      => $data['notes'] ?? null,
        ]);

        $invoice->items()->delete();
        foreach ($items as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'amount'      => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'แก้ไข Invoice เรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        abort_if($invoice->user_id !== auth()->id(), 403);
        $invoice->delete();
        return redirect()->route('invoices.index')
            ->with('success', 'ลบ Invoice เรียบร้อยแล้ว');
    }

    public function pdf(Invoice $invoice)
    {
        abort_if($invoice->user_id !== auth()->id(), 403);
        $invoice->load('client', 'items');
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }
}
