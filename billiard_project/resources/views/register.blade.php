@extends('layouts.app')
@section('title', 'ลงทะเบียน')
@section('content')
{{-- Container สำหรับจัดวางฟอร์มให้อยู่ตรงกลางจอตามดีไซน์ --}}
<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-lg p-8 bg-white shadow-xl rounded-lg border border-gray-200">
        
        <h1 class="text-2xl font-bold text-center mb-6 text-gray-800">Register for new account</h1>

        {{-- ฟอร์มลงทะเบียน: จะส่งข้อมูลไปที่ {{ route('register') }} --}}
        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- ********** ส่วนข้อมูลพื้นฐาน (จัดเรียงเป็น 2 คอลัมน์) ********** --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                {{-- 1. Email --}}
                <div class="col-span-1">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email address" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
                
                {{-- 2. Phone Number --}}
                <div class="col-span-1">
                    <label for="user_phone" class="block text-sm font-medium text-gray-700">Phone number</label>
                    <input type="text" id="user_phone" name="user_phone" placeholder="Enter your phone number" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
                
                {{-- 3. Username --}}
                <div class="col-span-1">
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
                
                {{-- 4. Date of Birth --}}
                <div class="col-span-1">
                    <label for="user_dob" class="block text-sm font-medium text-gray-700">Birthday</label>
                    <input type="date" id="user_dob" name="user_dob" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>

                {{-- 5. Password --}}
                <div class="col-span-1">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>

                {{-- 6. Confirm Password (สำหรับ Laravel Validation) --}}
                <div class="col-span-1">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            {{-- ปุ่มส่งข้อมูล --}}
            <div class="mt-6 text-center">
                <button type="submit" class="w-full md:w-auto px-6 py-2 bg-green-600 text-white font-semibold rounded-md shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Send information
                </button>
            </div>
        </form>
        
        <div class="mt-4 text-center">
             <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-green-600">
                Already have an account? Log in.
            </a>
        </div>
    </div>
</div>
@endsection