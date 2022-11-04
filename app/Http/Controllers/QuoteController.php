<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['getQuote', 'getAllQuotes']]);
    }

    public function getQuote($id)
    {
        // TODO: Fetch vote count
        $quote = Quote::with('user')->find($id);

        if (!$quote) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quote not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $quote,
        ]);
    }


    public function getAllQuotes(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        // TODO: Order by likes descending
        $quotes = Quote::with('user')->simplePaginate($limit, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'data' => $quotes
        ]);
    }

    public function getRandomQuote()
    {
        $quote = Quote::inRandomOrder()->first();

        return response()->json([
            'status' => 'success',
            'data' => $quote,
        ]);
    }

    // Post a quote
    public function postQuote(Request $request)
    {
        $validator = $this->getQuoteValidator($request);
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
        $validator = $this->getQuoteValidator($request);
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

    // Delete quote
    public function deleteQuote(Request $request, $id)
    {
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

        $quote->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Quote deleted',
        ]);
    }

    private function getQuoteValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'content' => 'required|string|max:255',
        ]);
    }
}
