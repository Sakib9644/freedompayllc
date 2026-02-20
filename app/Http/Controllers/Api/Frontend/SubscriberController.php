<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PdfRequest;
use App\Models\Subscriber;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubscriberController extends Controller
{
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email|unique:subscribers,email'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => "Invalid email address",
    //             'data' => $validator->errors(),
    //             'code' => 422
    //         ]);
    //     }

    //     try {
    //         $subscriber = Subscriber::firstOrCreate(['email' => $request->input('email')]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => "Subscriber created successfully",
    //             'data' => $subscriber,
    //             'code' => 200
    //         ]);
            
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //             'data' => [],
    //             'code' => 500
    //         ]);
    //     }
    // }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:subscribers,email'],
        ]);

        try {
            $subscriber = Subscriber::create([
                'email' => $validated['email'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscriber created successfully.',
                'data'    => $subscriber,
            ], 201);

        } catch (\Throwable $e) {

            Log::error('Subscriber creation failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function pdfGuide(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:subscribers,email'],
        ]);

        try {
            $subscriber = PdfRequest::create([
                'email' => $validated['email'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'PDF Link Send Successfully.',
                'data'    => $subscriber,
            ], 201);

        } catch (\Throwable $e) {

            Log::error('PDF Request creation failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
}

