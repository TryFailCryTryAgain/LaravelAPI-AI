<?php

namespace App\Http\Controllers;

use App\Models\ChatHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ChatHistoryController extends Controller
{
    public function chat(Request $request)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'message' => 'required|string',
            'session_id' => 'nullable|string'
        ]);

        $user = Auth::user();
        
        $session_id = $request->session_id ?? Str::uuid()->toString();

        $chat_history = ChatHistory::where('session_id', $session_id)
            ->orderBy('created_at', 'asc')
            ->get(['user_message', 'bot_response']);

        // Prepare chat context
        $context = "";
        foreach ($chat_history as $chat) {
            $context .= "User: " . $chat->user_message . "\nBot: " . $chat->bot_response . "\n";
        }
        
        // Append new message to the context
        $full_prompt = $context . "User: " . $request->message . "\nBot:";

        // Send request to LLM
        $response = Http::post('http://localhost:11434/api/generate', [
            'model' => 'mistral',
            'prompt' => $full_prompt,
            'stream' => false
        ]);

        $bot_response = data_get($response->json(), 'response', 'No response from the AI');

        // Store the conversation in the database
        ChatHistory::create([
            'user_id' => $user->id,
            'session_id' => $session_id,
            'user_message' => $request->message,
            'bot_response' => $bot_response,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'session_id' => $session_id,
            'message' => $bot_response
        ]);
    }
}