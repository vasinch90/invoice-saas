<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">สร้าง Invoice ใหม่</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto px-4">
        <form method="POST" action="{{ route('invoices.store') }}"
              class="bg-white p-6 rounded shadow space-y-6">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">ลูกค้า *</label>
                    <select name="client_id" class="w-full border rounded px-3 py-2" required>
                        <option value="">-- เลือกลูกค้า --</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}"
                                {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">ภาษี (%)</label>
                    <input type="number" name="tax_rate" value="{{ old('tax_rate', 7) }}"
                           step="0.01" min="0" max="100"
                           class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">วันที่ออก *</label>
                    <input type="date" name="issue_date"
                           value="{{ old('issue_date', date('Y-m-d')) }}"
                           class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">วันครบกำหนด *</label>
                    <input type="date" name="due_date"
                           value="{{ old('due_date') }}"
                           class="w-full border rounded px-3 py-2" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">รายการสินค้า/บริการ *</label>
                <table class="w-full text-sm" id="items-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-2 text-left">รายการ</th>
                            <th class="p-2 text-right w-24">จำนวน</th>
                            <th class="p-2 text-right w-32">ราคา/หน่วย</th>
                            <th class="p-2 text-right w-32">รวม</th>
                            <th class="p-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <tr class="item-row border-t">
                            <td class="p-2">
                                <input type="text" name="items[0][description]"
                                       class="w-full border rounded px-2 py-1" required>
                            </td>
                            <td class="p-2">
                                <input type="number" name="items[0][quantity]"
                                       value="1" min="0.01" step="0.01"
                                       class="w-full border rounded px-2 py-1 text-right qty" required>
                            </td>
                            <td class="p-2">
                                <input type="number" name="items[0][unit_price]"
                                       value="0" min="0" step="0.01"
                                       class="w-full border rounded px-2 py-1 text-right price" required>
                            </td>
                            <td class="p-2 text-right amount">0.00</td>
                            <td class="p-2"></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" onclick="addRow()"
                        class="mt-2 text-sm text-blue-600 border border-blue-300 px-3 py-1 rounded">
                    + เพิ่มรายการ
                </button>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">หมายเหตุ</label>
                <textarea name="notes" rows="3"
                          class="w-full border rounded px-3 py-2">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded">บันทึก</button>
                <a href="{{ route('invoices.index') }}"
                   class="px-6 py-2 border rounded">ยกเลิก</a>
            </div>
        </form>
    </div>

    <script>
    let rowIndex = 1;

    function addRow() {
        const tbody = document.getElementById('items-body');
        const row = document.createElement('tr');
        row.className = 'item-row border-t';
        row.innerHTML = `
            <td class="p-2">
                <input type="text" name="items[${rowIndex}][description]"
                       class="w-full border rounded px-2 py-1" required>
            </td>
            <td class="p-2">
                <input type="number" name="items[${rowIndex}][quantity]"
                       value="1" min="0.01" step="0.01"
                       class="w-full border rounded px-2 py-1 text-right qty" required>
            </td>
            <td class="p-2">
                <input type="number" name="items[${rowIndex}][unit_price]"
                       value="0" min="0" step="0.01"
                       class="w-full border rounded px-2 py-1 text-right price" required>
            </td>
            <td class="p-2 text-right amount">0.00</td>
            <td class="p-2">
                <button type="button" onclick="this.closest('tr').remove(); calcTotal()"
                        class="text-red-500 text-xs">ลบ</button>
            </td>
        `;
        tbody.appendChild(row);
        rowIndex++;
        bindEvents(row);
    }

    function bindEvents(row) {
        row.querySelectorAll('.qty, .price').forEach(input => {
            input.addEventListener('input', calcTotal);
        });
    }

    function calcTotal() {
        document.querySelectorAll('.item-row').forEach(row => {
            const qty   = parseFloat(row.querySelector('.qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.price')?.value) || 0;
            const amt   = row.querySelector('.amount');
            if (amt) amt.textContent = (qty * price).toFixed(2);
        });
    }

    document.querySelectorAll('.item-row').forEach(bindEvents);
    </script>
</x-app-layout>