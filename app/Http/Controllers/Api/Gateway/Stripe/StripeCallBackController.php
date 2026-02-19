<?php

namespace App\Http\Controllers\Api\Gateway\Stripe;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Exception\ApiErrorException;

use App\Traits\ApiResponse;

class StripeCallBackController extends Controller
{
    use ApiResponse;
    public $redirectFail;
    public $redirectSuccess;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $this->redirectFail = env("APP_URL") . "/fail";
        $this->redirectSuccess = env("APP_URL") . "/success";
    }

    public function checkout(Request $request, $order_id)
    {

        try {
            $order = Order::find($order_id);
            if (!$order) {
                return $this->error([], 'Order not found', 404);
            }

            $successUrl = route('api.payment.stripe.success') . '?token={CHECKOUT_SESSION_ID}';
            $cancelUrl = route('api.payment.stripe.cancel') . '?token={CHECKOUT_SESSION_ID}';

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $order->product->title,
                        ],
                        'unit_amount' => $order->total_price * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'metadata' => [
                    'order_id' => $order->id,
                    'customer_email' => $order->customer->email,
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

            $data = [
                'checkout_url' => $session->url
            ];

            return Helper::jsonResponse(true, 'Checkout session created successfully', 200, $data);
        } catch (ModelNotFoundException $e) {

            Log::error($e->getMessage());
            return redirect()->to($this->redirectFail);
        } catch (ApiErrorException $e) {

            Log::error($e->getMessage());
            return redirect()->to($this->redirectFail);
        }
    }

    public function succesds(Request $request)
    {
        $validatedData = $request->validate([
            'token' => ['required', 'string']
        ]);

        try {
            $session = Session::retrieve($validatedData['token']);
            if ($session->payment_status === 'paid') {

                Transaction::create([
                    'order_id'   => $session->metadata['order_id'],
                    'amount'    => $session->amount_total / 100,
                    'currency'  => $session->currency,
                    'trx_id'    => $session->id,
                    'type'      => 'payment',
                    'status'    => 'pending',
                    'gateway'   => 'stripe',
                    'metadata'  => json_encode($session->metadata)
                ]);

                return redirect()->to($this->redirectSuccess);
            }

            if ($session->payment_status === 'unpaid' || $session->payment_status === 'no_payment_required') {
                return redirect()->to($this->redirectFail);
            }

            return redirect()->to($this->redirectFail);
        } catch (ApiErrorException $e) {

            Log::error($e->getMessage());
            return redirect()->to($this->redirectFail);
        } catch (ModelNotFoundException $e) {

            Log::error($e->getMessage());
            return redirect()->to($this->redirectFail);
        }
    }
    public function success(Request $request)
    {
        return redirect()->to($this->redirectSuccess);
    }


    public function failure(Request $request)
    {
        return redirect()->to($this->redirectFail);
    }
}
