<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SiteController extends Controller
{
    public function index()
    {

        $branches = DB::table('branches')->get();
        

        $reviews = DB::table('review as r')
            ->join('users as u', 'r.user_id', '=', 'u.user_id')
            ->join('orders as o', 'r.order_id', '=', 'o.order_id')

            ->leftJoin('reservation as res', 'o.order_id', '=', 'res.order_id')
            ->leftJoin('tables as t', 'res.table_id', '=', 't.table_id')
            ->leftJoin('branches as b', 't.branch_id', '=', 'b.branch_id')
            
            ->select(
                'r.rating',
                'r.review_descrpt as comment', // (ใช้ชื่อคอลัมน์จริง)
                'r.created_at',
                'u.username',
                
                // (กันเหนียว) ถ้าหาชื่อสาขาไม่เจอ (เช่น Order สั่งกลับบ้าน)
                DB::raw("COALESCE(b.branch_name, 'สั่งกลับบ้าน/ไม่ระบุ') as branch_name")
            )
            ->orderBy('r.created_at', 'desc')
            ->limit(5)
            ->get();

        // 3. (แก้ไข) ส่งตัวแปร $reviews
        return view('index', [
            'branches' => $branches,
            'reviews' => $reviews
        ]);
    }
    //login and registration
    public function login()
    {
        
        return view('login'); 
    }

    public function register()
    {
        
        return view('register');
    }

    public function menu(int $branchId = 101) 
    {
        // 1. รับ Branch ID จาก URL (ตาม Route ที่กำหนด)
        $branches = DB::table('branches')->get();
        
        // 2. กำหนด Branch ID ที่จะแสดงผล
        // ถ้า $branchId เป็น 0 (หรือค่าที่ไม่ถูกต้อง) เราจะใช้ค่า Default 101
        $selectedBranchId = ($branchId == 0 || !$branches->contains('branch_id', $branchId)) ? 101 : $branchId;

        // 2. ดึงข้อมูลเมนูทั้งหมดสำหรับสาขาที่เลือก (ใช้ $selectedBranchId)
        $rawMenuData = DB::table('menus')
            // เลือกคอลัมน์ทั้งหมดจาก menus และเลือก stock_qty จาก inventory
            ->select('menus.*', 'inventory.stock_qty') 
            ->where('menus.branch_id', $selectedBranchId)
        
            // **JOIN ตาราง inventory**
            ->leftJoin('inventory', function($join) use ($selectedBranchId) {
                $join->on('menus.menu_id', '=', 'inventory.menu_id')
                    ->where('inventory.branch_id', '=', $selectedBranchId);
            })
            ->get();

        // 3. จัดกลุ่มข้อมูลตามประเภท (Meal, Snack, Drink)
        $groupedMenu = $rawMenuData->groupBy('menu_type');
        
        // 4. ส่งตัวแปร $groupedMenu ไปให้ View
        return view('menu', [
            'groupedMenu' => $groupedMenu,
            'branches' => $branches, // <-- **ส่งตัวแปรนี้**
            'selectedBranchId' => $selectedBranchId
        ]);
    }

    //booking-category
    public function branches() // <-- นี่คือฟังก์ชันใน Route Context
    {
        // 2. ดึงข้อมูลสาขาทั้งหมดจากฐานข้อมูล
        $branches = DB::table('branches')->get();

        // 3. ส่งตัวแปร $branches ไปที่ View
        // (เราใช้ 'booking.branches' เพราะไฟล์ของคุณอยู่ที่ resources/views/booking/branches.blade.php)
        return view('booking.branches', ['branches' => $branches]);
    }


    //review
    public function reviews()
    {
        // 1. ดึงรีวิวทั้งหมดมาโชว์ (เหมือนเดิม)
        // (Join ตาราง users เพื่อเอาชื่อคนรีวิว)
        $reviews = DB::table('review')
            ->join('users', 'review.user_id', '=', 'users.user_id')
            ->select(
                'review.rating', 
                'review.review_descrpt AS review_text',
                'review.created_at AS review_date',
                'users.first_name',
                'users.last_name',
                'review.order_id'
            )
            ->whereNotNull('review.order_id')
            ->orderBy('review.created_at', 'desc')
            ->get();

        // 2. ค้นหา "การจองที่รอรีวิว" (สำหรับ User ที่ล็อกอิน)
        $bookings_to_review = collect(); 

        if (Auth::check()) {
            $userId = Auth::id();
            
            // 2a. ค้นหา Order ที่ "เสร็จสิ้น" (completed)
            $completedOrders = DB::table('orders')
                ->join('reservation', 'orders.order_id', '=', 'reservation.order_id')
                ->where('orders.user_id', $userId)
                ->where('orders.order_status', 'completed')
                ->select('orders.order_id', 'reservation.start_time')
                ->groupBy('orders.order_id', 'reservation.start_time')
                ->get();
            
            if ($completedOrders->isNotEmpty()) {
                $reviewedOrderIds = DB::table('review')
                    ->where('user_id', $userId)
                    ->whereNotNull('order_id')
                    ->pluck('order_id');

                $bookings_to_review = $completedOrders->whereNotIn('order_id', $reviewedOrderIds);
            }
        }

        return view('reviews', [
            'reviews' => $reviews,
            'bookings_to_review' => $bookings_to_review 
        ]);
    }
    public function submitReview(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,order_id', 
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|min:10|max:500',
        ]);

        $userId = Auth::id();
        $orderId = $request->input('order_id');

        $validOrder = DB::table('orders')
            ->where('order_id', $orderId)
            ->where('user_id', $userId)
            ->where('order_status', 'completed')
            ->first();
        
        if (!$validOrder) {
            return back()->withInput()->withErrors(['message' => 'คุณไม่สามารถรีวิวการจองนี้ได้ เนื่องจากการจองยังไม่เสร็จสิ้น']);
        }

        try {
            DB::table('review')->insert([
                'user_id' => $userId,
                'order_id' => $orderId,
                // (ต้องใส่ค่าของ review_descrpt และ rating ให้ถูกต้องตาม schema)
                'review_descrpt' => $request->input('review_text'), // <-- ใช้ field ที่ถูกต้อง
                'rating' => $request->input('rating'),
                'created_at' => Carbon::now(),                  
                'updated_at' => Carbon::now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) { // 1062 = Duplicate entry
                return back()->withInput()->withErrors(['message' => 'คุณได้รีวิวการจองนี้ไปแล้ว']);
            }
            return back()->withInput()->withErrors(['message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }

        return redirect()->route('reviews')->with('success', 'ขอบคุณสำหรับรีวิวของคุณ!');
    }

    //order
    public function order()
    {

        return view('orders.order'); 
    }
    
    //points-category
    private $rewardStore = [
        'REDEEM_50' => [
            'id' => 'REDEEM_50',
            'points_required' => 100,
            'reward_descrpt' => 'ส่วนลด 50 บาท',
            'reward_value' => 50,
            'reward_discount' => 'baht',
            'duration_days' => 30, // (คูปองมีอายุ 30 วัน)
        ],
        'REDEEM_120' => [
            'id' => 'REDEEM_120',
            'points_required' => 650,
            'reward_descrpt' => 'ส่วนลด 120 บาท',
            'reward_value' => 120,
            'reward_discount' => 'baht',
            'duration_days' => 30,
        ],
        'REDEEM_10_PERCENT' => [
            'id' => 'REDEEM_10_PERCENT',
            'points_required' => 500,
            'reward_descrpt' => 'ส่วนลด 10%',
            'reward_value' => 10,
            'reward_discount' => 'percent',
            'duration_days' => 15,
        ],
    ];


    // (แก้ไข) 2. แก้ไขเมธอด pointsPage
    public function pointsPage()
    {
        $user = Auth::user(); 
        $currentPoints = $user->loyalty_points;
        
        // (แก้ไข) ดึง "คูปองที่แลกได้" จาก "ร้านค้า" ที่เราสร้าง
        $redeemableRewards = $this->rewardStore;
            
        // (เพิ่ม) ดึง "คูปองที่ User มีอยู่" (ที่ยังไม่หมดอายุและยังไม่ใช้)
        $myActiveCoupons = DB::table('reward')
            ->where('user_id', $user->user_id)
            ->where('reward_status', 'active')
            ->where('expired_date', '>=', now()->toDateString())
            ->orderBy('expired_date', 'asc')
            ->get();

        return view('points.points', [
            'currentPoints' => $currentPoints,
            'redeemableRewards' => $redeemableRewards, // <-- ส่ง "ร้านค้า" ไป
            'myActiveCoupons' => $myActiveCoupons, // <-- ส่ง "คูปองที่มี" ไป
        ]);
    }
    public function pointsHistoryPage()
    {
        $user = Auth::user(); 
        $currentPoints = $user->loyalty_points;
        
        // 1. ดึงประวัติ "ที่ได้รับ" (Received)
        $received_transactions = DB::table('reward_transaction')
            ->where('user_id', $user->user_id)
            ->where('transact_type', 'received') // <-- กรองเฉพาะที่ได้รับ
            ->orderBy('transact_date', 'desc') 
            ->get();

        // 2. ดึงประวัติ "ที่ใช้ไป" (Redeemed)
        $redeemed_transactions = DB::table('reward_transaction')
            ->where('user_id', $user->user_id)
            ->where('transact_type', 'redeemed') // <-- กรองเฉพาะที่ใช้ไป
            ->orderBy('transact_date', 'desc') 
            ->get();

        return view('points.point_transact', [
            'currentPoints' => $currentPoints,
            'received' => $received_transactions, // <-- ส่งตัวแปรชื่อ received
            'redeemed' => $redeemed_transactions, // <-- ส่งตัวแปรชื่อ redeemed
        ]);
    }

    public function dailyCheckin(Request $request)
    {
        // 1. ดึงข้อมูล User ที่ล็อกอินอยู่
        $user = Auth::user();

        // 2. กำหนดค่าแต้มที่จะเพิ่ม
        $pointsToAdd = 25;
        $description = 'เช็คอินรายวัน';

        $today = now()->startOfDay();

        // ค้นหาในประวัติว่า:
        // 1. ตรงกับ user_id นี้
        // 2. มี description ตรงกับ 'เช็คอินรายวัน'
        // 3. มีวันที่ (transact_date) มากกว่าหรือเท่ากับ (>=) เที่ยงคืนของวันนี้
        $alreadyCheckedIn = DB::table('reward_transaction')
            ->where('user_id', $user->user_id)
            ->where('transact_descrpt', $description)
            ->where('transact_date', '>=', $today)
            ->exists(); // (exists() คือการถามว่า "มีไหม" - เร็วมาก)

        if ($alreadyCheckedIn) {
            // ...ให้ออกจากฟังก์ชันทันที และส่ง Error กลับไป
            return redirect()->route('points.index')->with('error', 'คุณรับแต้มเช็คอินวันนี้ไปแล้ว');
        }

        try {
            // ขั้นตอนที่ 1: อัปเดต "กระเป๋าตังค์" (ตาราง users)
            DB::table('users')
                ->where('user_id', $user->user_id)
                ->increment('loyalty_points', $pointsToAdd);

            // ขั้นตอนที่ 2: บันทึก "ประวัติ" (ตาราง reward_transaction)
            DB::table('reward_transaction')->insert([
                'user_id' => $user->user_id,
                'transact_type' => 'received',
                'pts_change' => $pointsToAdd,
                'transact_descrpt' => $description,
                'transact_date' => now(),
            ]);

            // 3. กลับไปหน้าเดิม พร้อมข้อความ "สำเร็จ"
            return redirect()->route('points.index')->with('success', 'คุณได้รับ ' . $pointsToAdd . ' แต้ม!');

        } catch (\Exception $e) {
            // 4. หากเกิดข้อผิดพลาด (เช่น DB ล่ม)
            return redirect()->route('points.index')->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

    }

    public function redeemPoints(Request $request)
    {
        // 1. ดึงข้อมูลที่จำเป็น
        $user = Auth::user();
        $rewardId = $request->input('reward_id'); // (รับ ID จาก Form เช่น 'REDEEM_50')

        // 2. ค้นหาคูปองนี้จาก "ร้านค้า"
        if (!isset($this->rewardStore[$rewardId])) {
            return redirect()->route('points.index')->with('error', 'ไม่พบรายการคูปองที่ต้องการแลก');
        }
        $rewardToRedeem = $this->rewardStore[$rewardId];

        // 3. เริ่มต้น Transaction
        try {
            
            $result = DB::transaction(function () use ($user, $rewardToRedeem) {

                // 4. (Validation) ดึงแต้มผู้ใช้ล่าสุด (จำเป็นต้องดึงใหม่ใน transaction)
                $currentUserPoints = DB::table('users')
                                    ->where('user_id', $user->user_id)
                                    ->lockForUpdate() // (สำคัญ) ล็อคแถวนี้กัน User แลกซ้ำซ้อน
                                    ->value('loyalty_points');

                // 5. (Validation) เช็คแต้ม
                if ($currentUserPoints < $rewardToRedeem['points_required']) {
                    return ['success' => false, 'message' => 'คะแนนสะสมไม่เพียงพอ'];
                }

                // --- ถ้าแต้มพอ ---

                // 6. (Action 1) ลดแต้มผู้ใช้
                DB::table('users')
                    ->where('user_id', $user->user_id)
                    ->decrement('loyalty_points', $rewardToRedeem['points_required']);

                // 7. (Action 2) "สร้าง" คูปองใหม่ลงในตาราง 'reward'
                DB::table('reward')->insert([
                    'user_id' => $user->user_id,
                    'reward_descrpt' => $rewardToRedeem['reward_descrpt'],
                    'reward_type' => 'points', // (เพราะแลกจากแต้ม)
                    'reward_value' => $rewardToRedeem['reward_value'],
                    'reward_discount' => $rewardToRedeem['reward_discount'],
                    'reward_status' => 'active', // (สถานะ: พร้อมใช้)
                    'issued_date' => now(),
                    'expired_date' => now()->addDays($rewardToRedeem['duration_days']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 8. (Action 3) บันทึกประวัติการใช้แต้ม (Transaction Log)
                DB::table('reward_transaction')->insert([
                    'user_id' => $user->user_id,
                    'transact_type' => 'redeemed', // (ประเภท: แลกแต้ม)
                    'pts_change' => $rewardToRedeem['points_required'], // (ใช้แต้มไปเท่าไหร่)
                    'transact_descrpt' => 'แลกคูปอง: ' . $rewardToRedeem['reward_descrpt'],
                    'transact_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return ['success' => true, 'message' => 'แลกคูปองสำเร็จ!'];

            }); // สิ้นสุด Transaction

            // 9. Redirect ตามผลลัพธ์
            if ($result['success']) {
                return redirect()->route('points.index')->with('success', $result['message']);
            } else {
                return redirect()->route('points.index')->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            // 10. หากเกิด Error ร้ายแรง
            return redirect()->route('points.index')->with('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: ' . $e->getMessage());
        }
    }

    //cart
    public function cart()
    {
        
        return view('carts.cart'); 
    }
    
    
}