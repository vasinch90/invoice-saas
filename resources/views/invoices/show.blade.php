<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">{{ $invoice->invoice_number }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('invoices.edit', $invoice) }}"
                   class="border px-4 py-2 rounded text-sm">แก้ไข</a>
                <a href="{{ route('invoices.pdf', $invoice) }}"
                   class="bg-green-600 text-white px-4 py-2 rounded text-sm">ดาวน์โหลด PDF</a>
            </div>
        </div>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto px-4">
        <div class="bg-white rounded shadow p-6 space-y-6">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">ลูกค้า</p>
                    <p class="font-medium">{{ $invoice->client->name }}</p>
                    <p>{{ $invoice->client->email }}</p>
                    <p>{{ $invoice->client->address }}</p>
                </div>
                <div class="text-right">
                    <p class="text-gray-500">วันที่ออก</p>
                    <p>{{ $invoice->issue_date->format('d/m/Y') }}</p>
                    <p class="text-gray-500 mt-2">วันครบกำหนด</p>
                    <p>{{ $invoice->due_date->format('d/m/Y') }}</p>
                </div>
            </div>

            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">รายการ</th>
                        <th class="p-3 text-right">จำนวน</th>
                        <th class="p-3 text-right">ราคา/หน่วย</th>
                        <th class="p-3 text-right">รวม</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr class="border-t">
                        <td class="p-3">{{ $item->description }}</td>
                        <td class="p-3 text-right">{{ $item->quantity }}</td>
                        <td class="p-3 text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="p-3 text-right">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t">
                        <td colspan="3" class="p-3 text-right text-gray-500">Subtotal</td>
                        <td class="p-3 text-right">{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="p-3 text-right text-gray-500">
                            ภาษี ({{ $invoice->tax_rate }}%)
                        </td>
                        <td class="p-3 text-right">{{ number_format($invoice->tax_amount, 2) }}</td>
                    </tr>
                    <tr class="border-t font-semibold">
                        <td colspan="3" class="p-3 text-right">ยอดรวมทั้งหมด</td>
                        <td class="p-3 text-right">{{ number_format($invoice->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            @if($invoice->notes)
            <div>
                <p class="text-gray-500 text-sm">หมายเหตุ</p>
                <p class="text-sm">{{ $invoice->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>