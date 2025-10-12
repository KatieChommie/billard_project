<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {

        return view('index');
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
    public function menu()
    {

        return view('menu');
    }

    //booking-category
    public function branches()
    {

        return view('booking.branches');
    }
    public function table()
    {

        return view('booking.table');
    }
    public function reservation()
    {

        return view('booking.reservation');
    }

    //review
    public function reviews()
    {
    
        return view('reviews'); 
    }

    //order
    public function order()
    {

        return view('orders.order'); 
    }
    
    //points-category
    public function points()
    {
   
        return view('points.points'); 
    }
    public function point_transact()
    {
        
        return view('points.point_transact'); 
    }

    //cart
    public function cart()
    {
        
        return view('carts.cart'); 
    }
    
    
}