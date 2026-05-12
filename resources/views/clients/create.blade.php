<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">เพิ่มลูกค้าใหม่</h2>
    </x-slot>
    <div class="py-6 max-w-2xl mx-auto px-4">
        <form method="POST" action="{{ route('clients.store') }}"
              class="bg-white p-6 rounded shadow space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">ชื่อ *</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full border rounded px-3 py-2" required>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">อีเมล</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">เบอร์โทร</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">ที่อยู่</label>
                <textarea name="address" rows="3"
                          class="w-full border rounded px-3 py-2">{{ old('address') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">เลขผู้เสียภาษี</label>
                <input type="text" name="tax_id" value="{{ old('tax_id') }}"
                       class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex gap-3">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded">บันทึก</button>
                <a href="{{ route('clients.index') }}"
                   class="px-6 py-2 border rounded">ยกเลิก</a>
            </div>
        </form>
    </div>
</x-app-layout>