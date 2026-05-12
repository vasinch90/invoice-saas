<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">Invoice ทั้งหมด</h2>
            <a href="{{ route('invoices.create') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded">+ สร้าง Invoice</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-7xl mx-auto px-4">
        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        <table class="w-full bg-white rounded shadow text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="p-3">เลขที่</th>
                    <th class="p-3">ลูกค้า</th>
                    <th class="p-3">วันครบกำหนด</th>
                    <th class="p-3">ยอดรวม</th>
                    <th class="p-3">สถานะ</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr class="border-t">
                    <td class="p-3">{{ $invoice->invoice_number }}</td>
                    <td class="p-3">{{ $invoice->client->name }}</td>
                    <td class="p-3">{{ $invoice->due_date->format('d/m/Y') }}</td>
                    <td class="p-3">{{ number_format($invoice->total, 2) }}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs
                            @if($invoice->status === 'paid') bg-green-100 text-green-800
                            @elseif($invoice->status === 'sent') bg-blue-100 text-blue-800
                            @elseif($invoice->status === 'cancelled') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $invoice->status }}
                        </span>
                    </td>
                    <td class="p-3 flex gap-2">
                        <a href="{{ route('invoices.show', $invoice) }}"
                           class="text-blue-600">ดู</a>
                        <a href="{{ route('invoices.edit', $invoice) }}"
                           class="text-yellow-600">แก้ไข</a>
                        <a href="{{ route('invoices.pdf', $invoice) }}"
                           class="text-green-600">PDF</a>
                        <form method="POST"
                              action="{{ route('invoices.destroy', $invoice) }}"
                              onsubmit="return confirm('ยืนยันการลบ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600">ลบ</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-6 text-center text-gray-400">
                        ยังไม่มี Invoice
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $invoices->links() }}</div>
    </div>
</x-app-layout>