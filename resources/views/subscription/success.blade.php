<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">สมัคร Subscription สำเร็จ</h2>
    </x-slot>
    <div class="py-12 max-w-lg mx-auto px-4 text-center">
        <div class="bg-white rounded-lg shadow p-8">
            <div class="text-5xl mb-4">🎉</div>
            <h3 class="text-xl font-semibold mb-2">ยินดีต้อนรับสู่ Pro Plan!</h3>
            <p class="text-gray-600 mb-6">คุณสามารถใช้งานฟีเจอร์ทั้งหมดได้แล้ว</p>
            <a href="{{ route('invoices.index') }}"
               class="bg-blue-600 text-white px-6 py-2 rounded">เริ่มสร้าง Invoice</a>
        </div>
    </div>
</x-app-layout>