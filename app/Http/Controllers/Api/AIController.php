<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AIController extends Controller
{
    public function autocomplete(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        try {
$response = Http::withToken(env('OPENAI_API_KEY'))
    ->post('https://api.openai.com/v1/chat/completions', [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful coding assistant.'],
            ['role' => 'user', 'content' => "Complete this code:\n\n" . $request->code],
        ],
        'max_tokens' => 150,
        'temperature' => 0.2,
    ]);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'OpenAI API request failed',
                    'details' => $response->json(),
                ], 500);
            }

return response()->json([
    'suggestion' => $response->json()['choices'][0]['message']['content'] ?? '',
]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
