<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    
    public function create($order_id, $branch_id)
    {
        $userId = Auth::id();

        $order = DB::table('orders')
                    ->where('order_id', $order_id)
                    ->where('user_id', $userId)
                    ->where('order_status', 'completed')
                    ->first();

        if (!$order) {
            return redirect()->route('user.dashboard')->withErrors(['message' => 'ไม่สามารถรีวิว Order นี้ได้']);
        }

        $existingReview = DB::table('review')
                            ->where('order_id', $order_id) 
                            ->where('user_id', $userId)
                            ->first();

        if ($existingReview) {
            return redirect()->route('user.dashboard')->withErrors(['message' => 'คุณได้รีวิว Order นี้ไปแล้ว']);
        }
      
        $branch = DB::table('branches')->where('branch_id', $branch_id)->first();
        if (!$branch) {
            return redirect()->route('user.dashboard')->withErrors(['message' => 'ไม่พบข้อมูลสาขา']);
        }
      
        return view('review.create', [
            'order_id' => $order_id,
            'branch' => $branch
        ]);
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        
        $request->validate([
            'order_id' => 'required|integer|exists:orders,order_id',
            'branch_id' => 'required|integer|exists:branches,branch_id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500', 
        ]);
        
        $orderId = $request->input('order_id');

        $order = DB::table('orders')
                    ->where('order_id', $orderId)
                    ->where('user_id', $userId)
                    ->where('order_status', 'completed')
                    ->first();
        
        if (!$order) {
            return back()->withInput()->withErrors(['message' => 'คุณไม่มีสิทธิ์รีวิว Order นี้']);
        }

        $existingReview = DB::table('review')
                            ->where('order_id', $orderId)
                            ->where('user_id', $userId)
                            ->first();
        
        if ($existingReview) {
            return redirect()->route('user.dashboard')->withErrors(['message' => 'คุณได้รีวิว Order นี้ไปแล้ว']);
        }

        try {
            DB::table('review')->insert([
                'order_id' => $orderId,
                'user_id' => $userId,
                'rating' => $request->input('rating'),
                'review_descrpt' => $request->input('comment'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('user.dashboard')->with('success', 'ขอบคุณสำหรับรีวิวครับ!');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
}