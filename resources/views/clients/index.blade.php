<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">ลูกค้าทั้งหมด</h2>
            <a href="{{ route('clients.create') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded">+ เพิ่มลูกค้า</a>
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
                    <th class="p-3">ชื่อ</th>
                    <th class="p-3">อีเมล</th>
                    <th class="p-3">เบอร์โทร</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($clients as $client)
                <tr class="border-t">
                    <td class="p-3">{{ $client->name }}</td>
                    <td class="p-3">{{ $client->email }}</td>
                    <td class="p-3">{{ $client->phone }}</td>
                    <td class="p-3 flex gap-2">
                        <a href="{{ route('clients.edit', $client) }}"
                           class="text-blue-600">แก้ไข</a>
                        <form method="POST"
                              action="{{ route('clients.destroy', $client) }}"
                              onsubmit="return confirm('ยืนยันการลบ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600">ลบ</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $clients->links() }}</div>
    </div>
</x-app-layout>