<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // Returns the authenticated user
    public function me()
    {
        return response()->json([
            'status' => 'success',
            'data' => auth()->user()
        ]);
    }

    // Post a quote
    public function postQuote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'data' => $validator->errors(),
            ], 422);
        }

        $user = User::find(auth()->user()->id);
        $quote = $user->quotes()->create($request->only('content'));

        return response()->json([
            'status' => 'success',
            'data' => $quote,
        ]);
    }

    // Edit quote
    public function editQuote(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'data' => $validator->errors(),
            ], 422);
        }

        // Fetch the quote
        $quote = Quote::find($id);

        // Check if quote exists
        if (!$quote) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quote not found',
            ], 404);
        }

        // Check if user is the author of the quote
        if ($quote->user_id !== auth()->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        // Update the quote text
        $quote->content = $request->input('content');
        $quote->save();

        return response()->json([
            'status' => 'success',
            'data' => $quote,
        ]);
    }
}
