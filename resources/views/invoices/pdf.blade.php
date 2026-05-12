<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #333; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        h1 { font-size: 24px; color: #1d4ed8; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f3f4f6; padding: 8px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        .total-row td { font-weight: bold; border-top: 2px solid #333; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>INVOICE</h1>
            <p>{{ $invoice->invoice_number }}</p>
        </div>
        <div class="text-right">
            <p>วันที่ออก: {{ $invoice->issue_date->format('d/m/Y') }}</p>
            <p>วันครบกำหนด: {{ $invoice->due_date->format('d/m/Y') }}</p>
        </div>
    </div>
    <div>
        <strong>ลูกค้า:</strong>
        <p>{{ $invoice->client->name }}</p>
        <p>{{ $invoice->client->email }}</p>
        <p>{{ $invoice->client->address }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>รายการ</th>
                <th class="text-right">จำนวน</th>
                <th class="text-right">ราคา/หน่วย</th>
                <th class="text-right">รวม</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3" class="text-right">Subtotal</td>
                <td class="text-right">{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right">ภาษี ({{ $invoice->tax_rate }}%)</td>
                <td class="text-right">{{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="text-right">ยอดรวมทั้งหมด</td>
                <td class="text-right">{{ number_format($invoice->total, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @if($invoice->notes)
    <div style="margin-top: 30px;">
        <strong>หมายเหตุ:</strong>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif
</body>
</html>