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
        $this->middleware('auth:api', ['except' => ['getQuote', 'getAllQuotes', 'getRandomQuote']]);
    }

    /**
     * Get the validator for quote requests
     * 
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function getQuoteValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'content' => 'required|string|max:255',
        ]);
    }

    /**
     * Get a quote by id
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Get all quotes paginated
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Get a random quote
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRandomQuote()
    {
        $quote = Quote::inRandomOrder()->first();

        return response()->json([
            'status' => 'success',
            'data' => $quote,
        ]);
    }

    /**
     * Create a new quote
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Update a quote
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Delete a quote
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Vote for a quote
     * 
     * @param Request $request
     * @param int $id
     * @param string $vote
     * @return \Illuminate\Http\JsonResponse
     */
    public function voteQuote(Request $request, $id, $type)
    {
        // Check if vote type is valid
        if (!in_array($type, ['upvote', 'downvote'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid vote type',
            ], 422);
        }

        $numericVote = $type === 'upvote' ? 1 : -1;

        // Fetch the quote
        $quote = Quote::find($id);

        // Check if quote exists
        if (!$quote) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quote not found',
            ], 404);
        }

        // Check if user is the author
        if ($quote->user_id === auth()->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot vote for your own quote',
            ], 401);
        }

        // Fetch the vote if it exists
        $userVote = $quote->votes()->where('user_id', auth()->user()->id)->first();

        // User has not voted before so we create a new vote
        if (!$userVote) {
            $userVote = $quote->votes()->create([
                'user_id' => auth()->user()->id,
                'type' => $numericVote,
            ]);
        }

        // User has voted before and the vote type is the same so we delete the vote
        elseif ($userVote && $userVote->type === $numericVote) {
            $userVote->delete();
        }

        // User has voted before and the vote type is different so we update the vote
        elseif ($userVote && $userVote->type !== $numericVote) {
            $userVote->type = $numericVote;
            $userVote->save();
        }

        return response()->json([], 204);
    }
}
