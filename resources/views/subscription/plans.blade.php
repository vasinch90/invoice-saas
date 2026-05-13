<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">แผนการใช้งาน</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto px-4">

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($isSubscribed)
            <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-6 flex justify-between items-center">
                <div>
                    <p class="font-medium text-blue-800">คุณกำลังใช้งาน Pro Plan</p>
                    <p class="text-sm text-blue-600">จัดการ subscription และดูใบเสร็จได้ที่ Billing Portal</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('subscription.billing') }}"
                       class="bg-blue-600 text-white px-4 py-2 rounded text-sm">Billing Portal</a>
                    <form method="POST" action="{{ route('subscription.cancel') }}"
                          onsubmit="return confirm('ยืนยันการยกเลิก?')">
                        @csrf
                        <button class="border border-red-300 text-red-600 px-4 py-2 rounded text-sm">
                            ยกเลิก
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-6">
            <!-- Free Plan -->
            <div class="bg-white rounded-lg border p-6">
                <h3 class="text-lg font-semibold mb-1">Free</h3>
                <p class="text-3xl font-bold mb-4">฿0 <span class="text-sm font-normal text-gray-500">/เดือน</span></p>
                <ul class="space-y-2 text-sm text-gray-600 mb-6">
                    <li>✓ Invoice สูงสุด 5 ใบ/เดือน</li>
                    <li>✓ ลูกค้าสูงสุด 3 ราย</li>
                    <li>✓ PDF export</li>
                    <li class="text-gray-400">✗ ไม่มี Email อัตโนมัติ</li>
                </ul>
                <div class="bg-gray-100 text-gray-500 text-center py-2 rounded text-sm">
                    แผนปัจจุบัน
                </div>
            </div>

            <!-- Pro Plan -->
            <div class="bg-white rounded-lg border-2 border-blue-500 p-6 relative">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-blue-500 text-white
                             text-xs px-3 py-1 rounded-full">แนะนำ</span>
                <h3 class="text-lg font-semibold mb-1">Pro</h3>
                <p class="text-3xl font-bold mb-4">฿299 <span class="text-sm font-normal text-gray-500">/เดือน</span></p>
                <ul class="space-y-2 text-sm text-gray-600 mb-6">
                    <li>✓ Invoice ไม่จำกัด</li>
                    <li>✓ ลูกค้าไม่จำกัด</li>
                    <li>✓ PDF export</li>
                    <li>✓ Email อัตโนมัติ</li>
                    <li>✓ ทดลองใช้ฟรี 14 วัน</li>
                </ul>
                @if(!$isSubscribed)
                <form method="POST" action="{{ route('subscription.checkout') }}">
                    @csrf
                    <input type="hidden" name="price_id" value="price_1TWCo3IIt8nUJso852sOaXUz">
                    <button type="submit"
                            class="w-full bg-blue-600 text-white py-2 rounded font-medium">
                        เริ่มทดลองใช้ฟรี 14 วัน
                    </button>
                </form>
                @else
                <div class="bg-blue-50 text-blue-700 text-center py-2 rounded text-sm font-medium">
                    แผนปัจจุบัน
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>