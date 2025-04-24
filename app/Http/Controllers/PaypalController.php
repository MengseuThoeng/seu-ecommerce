<?php

namespace App\Http\Controllers;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use DB;

class PaypalController extends Controller
{
    public function payment()
    {
        $cart = Cart::where('user_id', auth()->user()->id)->where('order_id', null)->get();
        
        if ($cart->isEmpty()) {
            return redirect()->back()->with('error', 'Your cart is empty');
        }

        // Calculate total
        $total = 0;
        foreach ($cart as $item) {
            $total += $item->price * $item->quantity;
        }

        // Apply coupon if exists
        if(session('coupon')) {
            $total -= session('coupon')['value'];
        }

        // Update cart with order ID
        Cart::where('user_id', auth()->user()->id)
            ->where('order_id', null)
            ->update(['order_id' => session()->get('id')]);

        // Initialize PayPal
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();

        // Create order data
        $orderData = [
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('payment.success'),
                "cancel_url" => route('payment.cancel'),
                "brand_name" => config('app.name', 'Laravel'),
                "shipping_preference" => "NO_SHIPPING"
            ],
            "purchase_units" => [
                [
                    "reference_id" => 'ORD-'.strtoupper(uniqid()),
                    "description" => "Order from " . config('app.name', 'Laravel'),
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $total
                    ]
                ]
            ]
        ];

        // Create PayPal order
        $response = $provider->createOrder($orderData);

        // Redirect to PayPal if order created successfully
        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] == 'approve') {
                    return redirect()->away($link['href']);
                }
            }
        }

        return redirect()->back()->with('error', 'Something went wrong with PayPal');
    }

    public function cancel()
    {
        return redirect()->route('cart')->with('error', 'Your payment was canceled');
    }

    public function success(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        
        // Capture the order payment
        $response = $provider->capturePaymentOrder($request->token);

        // Process successful payment
        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            // Payment successful
            session()->flash('success', 'Payment successful! Thank you for your purchase.');
            session()->forget('cart');
            session()->forget('coupon');
            return redirect()->route('home');
        }

        return redirect()->back()->with('error', 'Something went wrong with your payment');
    }
}