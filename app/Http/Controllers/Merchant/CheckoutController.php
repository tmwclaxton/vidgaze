<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class CheckoutController extends Controller
{

    public function index() {
        return view('checkout/receipts', [
            'receipts' => Payment::query()->where('user_id', '=' , Auth::user()->id)->orderBy('created_at','desc')->get()
        ]);
    }

    public function session(Product $product) {

            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            header('Content-Type: application/json');

            $YOUR_DOMAIN = URL::to('/');
            //Stripe doesn't send receipts in test mode
            $checkout_session = \Stripe\Checkout\Session::create([
                'line_items' => [[
                    # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
                    'price' => $product->price_id,
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                "customer_email"=> Auth::user()->email,
                //        "customer_creation"=> "always",
                'success_url' => $YOUR_DOMAIN . '/checkout-success',
                'cancel_url' => $YOUR_DOMAIN . '/marketplace',
                'automatic_tax' => [
                    'enabled' => true,
                ],
                //        'metadata[product_id]'=>1 this metadata doesn't show up with charge object
            ]);
            //    echo($checkout_session);
            return redirect($checkout_session->url);
            //    header("HTTP/1.1 303 See Other");
            //    header("Location: " . $checkout_session->url);
        }
}
