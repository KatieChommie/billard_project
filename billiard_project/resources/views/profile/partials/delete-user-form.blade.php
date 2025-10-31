<form method="post" action="{{ route('profile.destroy') }}" class="p-6">
    @csrf
    @method('delete')

    <h3>ลบบัญชีผู้ใช้งาน</h3>
    <p>เมื่อบัญชีของคุณถูกลบ ข้อมูลทั้งหมดจะถูกลบอย่างถาวร กรุณาป้อนรหัสผ่านเพื่อยืนยันการลบบัญชี</p>

    {{-- ช่องป้อนรหัสผ่านเพื่อยืนยัน --}}
    <div class="input-group">
        <input 
            id="password" 
            name="password" 
            type="password" 
            placeholder="รหัสผ่าน" 
            required
        >
    </div>

    {{-- แสดงข้อผิดพลาด Validation --}}
    @error('password', 'userDeletion')
        <p class="error-message">{{ $message }}</p>
    @enderror

    <button type="submit" class="delete-btn">ลบบัญชีอย่างถาวร</button>
</form>