<?php

namespace App\Http\Controllers\Api\Gateway\Stripe;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\StripeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use UnexpectedValueException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StripeWebHookController extends Controller
{
    protected $stripeService;
    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function intent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return Helper::jsonResponse(false, 'Validation failed', 422, $validator->errors());
        }

        try {
            $data = $validator->validated();
            $uid = Str::uuid();

            $paymentIntent = PaymentIntent::create([
                'amount'   => $data['price'] * 100,
                'currency' => 'usd',
                'metadata' => [
                    'order_id' => $uid,
                    'user_id' => auth('api')->user()->id
                ],
            ]);

            $data = [
                'client_secret' => $paymentIntent->client_secret
            ];

            return Helper::jsonResponse(true, 'Payment intent created successfully', 200, $data);
        } catch (ApiErrorException $e) {

            return Helper::jsonResponse(false, $e->getMessage(), 500, []);
        } catch (Exception $e) {

            return Helper::jsonResponse(false, $e->getMessage(), 500, []);
        }
    }


    public function webhooko(Request $request): JsonResponse
    {

        $payload        = $request->getContent();
        $sigHeader      = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (UnexpectedValueException $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 400, []);
        } catch (SignatureVerificationException $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 400, []);
        }

        //? Handle the event based on its type
        try {
            Log::info('Stripe event type', ['type' => $event->type]);

            switch ($event->type) {
                case 'checkout.session.completed':
                    $payment = Transaction::where('trx_id', $event->data->object->id)->first();
                    Log::info('Stripe event type', ['type' => $event->data->object->id]);
                    Log::info('payment obhect ' . $payment);


                    if (!$payment) {
                        Log::info('Stripe event type', ['type' => 'Transaction not found']);
                        return response()->json(['message' => 'Transaction not found'], 200);
                    }
                    $payment->update([
                        'status' => 'success'
                    ]);
                    $payment->order->update([
                        'status' => 'completed'
                    ]);
                    $payment->order->product->update([
                        'stock' => $payment->order->product->stock - $payment->order->quantity
                    ]);
                    // $payment->order->customer->notify(new \App\Notifications\OrderCompletedNotification($payment->order));
                    $payment->save();

                    $this->stripeService->success($event->data->object);
                    return Helper::jsonResponse(true, 'Payment successful', 200, []);
                case 'payment_intent.payment_failed':
                    $this->stripeService->failure($event->data->object);
                    return Helper::jsonResponse(true, 'Payment failed', 200, []);
                default:
                    return Helper::jsonResponse(true, 'Unhandled event type', 200, []);
            }
        } catch (Exception $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 500, []);
        }
    }
    public function webhook(Request $request): JsonResponse
    {

        $payload        = $request->getContent();
        $sigHeader      = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (UnexpectedValueException $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 400, []);
        } catch (SignatureVerificationException $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 400, []);
        }

        //? Handle the event based on its type
        try {
            Log::info('Stripe event type', ['type' => $event->type]);

            switch ($event->type) {
                case 'checkout.session.completed':

                    $session = $event->data->object;
                    Log::info('Checkout session received', ['id' => $session->id]);

                    $orderId = $session->metadata->order_id ?? null;

                    if (!$orderId) {
                        Log::error('Order ID missing in metadata');
                        return response()->json(['message' => 'Order ID missing'], 200);
                    }

                    $order = Order::find($orderId);

                    if (!$order) {
                        Log::error('Order not found');
                        return response()->json(['message' => 'Order not found'], 200);
                    }

                    // Prevent duplicate transaction
                    $existing = Transaction::where('trx_id', $session->id)->first();
                    if ($existing) {
                        return response()->json(['message' => 'Already processed'], 200);
                    }

                    $transaction = Transaction::firstOrCreate(
                        ['trx_id' => $session->id],
                        [
                            'order_id'  => $order->id,
                            'amount'    => $session->amount_total / 100,
                            'currency'  => $session->currency,
                            'type'      => 'payment',
                            'status'    => 'success',
                            'gateway'   => 'stripe',
                            'metadata'  => json_encode($session->metadata)
                        ]
                    );

                    if ($order->status !== 'completed') {
                        $order->update(['status' => 'completed']);
                        $order->product->decrement('stock', $order->quantity);
                        $order->save();
                    }
                    Log::info('Order updated to completed status');


                    return Helper::jsonResponse(true, 'Payment successful', 200, []);

                case 'payment_intent.payment_failed':
                    $this->stripeService->failure($event->data->object);
                    return Helper::jsonResponse(true, 'Payment failed', 200, []);
                default:
                    return Helper::jsonResponse(true, 'Unhandled event type', 200, []);
            }
        } catch (Exception $e) {
            Log::error('Stripe checkout.session.completed error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'session' => $e,
            ]);
            return Helper::jsonResponse(false, $e->getMessage(), 500, []);
        }
    }
}
