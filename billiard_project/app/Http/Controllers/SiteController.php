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
                'r.review_descrpt as comment',
                'r.created_at',
                'u.username',
                DB::raw("COALESCE(b.branch_name, 'สั่งกลับบ้าน/ไม่ระบุ') as branch_name")
            )
            ->orderBy('r.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('index', [
            'branches' => $branches,
            'reviews' => $reviews
        ]);
    }

    /*login and registration
    public function login()
    {
        
        return view('login'); 
    }

    public function register()
    {
        
        return view('register');
    }*/

    public function menu(int $branchId = 101) 
    {
        $branches = DB::table('branches')->get();
        
        $selectedBranchId = ($branchId == 0 || !$branches->contains('branch_id', $branchId)) ? 101 : $branchId;

        $rawMenuData = DB::table('menus')
            ->select('menus.*', 'inventory.stock_qty') 
            ->where('menus.branch_id', $selectedBranchId)
            ->leftJoin('inventory', function($join) use ($selectedBranchId) {
                $join->on('menus.menu_id', '=', 'inventory.menu_id')
                    ->where('inventory.branch_id', '=', $selectedBranchId);
            })
            ->get();

        $groupedMenu = $rawMenuData->groupBy('menu_type');
        
        return view('menu', [
            'groupedMenu' => $groupedMenu,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId
        ]);
    }

    //booking-category
    public function branches()
    {
        $branches = DB::table('branches')->get();
        return view('booking.branches', ['branches' => $branches]);
    }


    //review
    public function reviews()
    {
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

        $bookings_to_review = collect(); 

        if (Auth::check()) {
            $userId = Auth::id();

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
                'review_descrpt' => $request->input('review_text'),
                'rating' => $request->input('rating'),
                'created_at' => Carbon::now(),                  
                'updated_at' => Carbon::now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return back()->withInput()->withErrors(['message' => 'คุณได้รีวิวการจองนี้ไปแล้ว']);
            }
            return back()->withInput()->withErrors(['message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }

        return redirect()->route('reviews')->with('success', 'ขอบคุณสำหรับรีวิวของคุณ!');
    }
    
    //points-category
    private $rewardStore = [
        'REDEEM_50' => [
            'id' => 'REDEEM_50',
            'points_required' => 100,
            'reward_descrpt' => 'ส่วนลด 50 บาท',
            'reward_value' => 50,
            'reward_discount' => 'baht',
            'duration_days' => 30,
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
        'REDEEM_10_BAHT' => [
            'id' => 'REDEEM_10_BAHT',
            'points_required' => 1,
            'reward_descrpt' => 'ส่วนลด 10 บาท',
            'reward_value' => 10,
            'reward_discount' => 'baht',
            'duration_days' => 7,
        ],
    ];

    public function pointsPage()
    {
        $user = Auth::user(); 
        $currentPoints = $user->loyalty_points;
        $redeemableRewards = $this->rewardStore;
        $myActiveCoupons = DB::table('reward')
            ->where('user_id', $user->user_id)
            ->where('reward_status', 'active')
            ->where('expired_date', '>=', now()->toDateString())
            ->orderBy('expired_date', 'asc')
            ->get();

        return view('points.points', [
            'currentPoints' => $currentPoints,
            'redeemableRewards' => $redeemableRewards,
            'myActiveCoupons' => $myActiveCoupons,
        ]);
    }
    public function pointsHistoryPage()
    {
        $user = Auth::user(); 
        $currentPoints = $user->loyalty_points;
        $received_transactions = DB::table('reward_transaction')
            ->where('user_id', $user->user_id)
            ->where('transact_type', 'received')
            ->orderBy('transact_date', 'desc') 
            ->get();
        $redeemed_transactions = DB::table('reward_transaction')
            ->where('user_id', $user->user_id)
            ->where('transact_type', 'redeemed')
            ->orderBy('transact_date', 'desc') 
            ->get();

        return view('points.point_transact', [
            'currentPoints' => $currentPoints,
            'received' => $received_transactions,
            'redeemed' => $redeemed_transactions,
        ]);
    }

    public function dailyCheckin()
    {
        $user = Auth::user();
        $pointsToAdd = 25;
        $description = 'เช็คอินรายวัน';

        $today = now()->startOfDay();

        $alreadyCheckedIn = DB::table('reward_transaction')
            ->where('user_id', $user->user_id)
            ->where('transact_descrpt', $description)
            ->where('transact_date', '>=', $today)
            ->exists();

        if ($alreadyCheckedIn) {
            return redirect()->route('points.index')->with('error', 'คุณรับแต้มเช็คอินวันนี้ไปแล้ว');
        }

        try {
            DB::table('users')
                ->where('user_id', $user->user_id)
                ->increment('loyalty_points', $pointsToAdd);

            DB::table('reward_transaction')->insert([
                'user_id' => $user->user_id,
                'transact_type' => 'received',
                'pts_change' => $pointsToAdd,
                'transact_descrpt' => $description,
                'transact_date' => now(),
            ]);

            return redirect()->route('points.index')->with('success', 'คุณได้รับ ' . $pointsToAdd . ' แต้ม!');

        } catch (\Exception $e) {
            return redirect()->route('points.index')->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

    }

    public function redeemPoints(Request $request)
    {
        $user = Auth::user();
        $rewardId = $request->input('reward_id');

        if (!isset($this->rewardStore[$rewardId])) {
            return redirect()->route('points.index')->with('error', 'ไม่พบรายการคูปองที่ต้องการแลก');
        }
        $rewardToRedeem = $this->rewardStore[$rewardId];

        try {
            
            $result = DB::transaction(function () use ($user, $rewardToRedeem) {
                $currentUserPoints = DB::table('users')
                                    ->where('user_id', $user->user_id)
                                    ->lockForUpdate()
                                    ->value('loyalty_points');

                if ($currentUserPoints < $rewardToRedeem['points_required']) {
                    return ['success' => false, 'message' => 'คะแนนสะสมไม่เพียงพอ'];
                }

                DB::table('users')
                    ->where('user_id', $user->user_id)
                    ->decrement('loyalty_points', $rewardToRedeem['points_required']);

                DB::table('reward')->insert([
                    'user_id' => $user->user_id,
                    'reward_descrpt' => $rewardToRedeem['reward_descrpt'],
                    'reward_type' => 'points',
                    'reward_value' => $rewardToRedeem['reward_value'],
                    'reward_discount' => $rewardToRedeem['reward_discount'],
                    'reward_status' => 'active',
                    'issued_date' => now(),
                    'expired_date' => now()->addDays($rewardToRedeem['duration_days']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('reward_transaction')->insert([
                    'user_id' => $user->user_id,
                    'transact_type' => 'redeemed',
                    'pts_change' => $rewardToRedeem['points_required'],
                    'transact_descrpt' => 'แลกคูปอง: ' . $rewardToRedeem['reward_descrpt'],
                    'transact_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return ['success' => true, 'message' => 'แลกคูปองสำเร็จ!'];

            });

            if ($result['success']) {
                return redirect()->route('points.index')->with('success', $result['message']);
            } else {
                return redirect()->route('points.index')->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            return redirect()->route('points.index')->with('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: ' . $e->getMessage());
        }
    } 
    
}