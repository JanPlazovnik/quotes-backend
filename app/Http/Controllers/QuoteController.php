<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\Request;

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
}
