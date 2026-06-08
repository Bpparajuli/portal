<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssistantController extends Controller
{
    /**
     * Show the AI assistant page
     */
    public function index()
    {
        return view('ai.assistant');
    }

    /**
     * Process AI chat message
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $message = $request->input('message');
        $context = $request->input('context', 'general');

        try {
            // Use OpenAI API if configured, otherwise use smart pattern matching
            $apiKey = config('services.openai.key');

            if ($apiKey) {
                $response = $this->callOpenAI($message, $context);
            } else {
                $response = $this->smartReply($message, $context);
            }

            return response()->json([
                'success' => true,
                'message' => $response,
                'timestamp' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            Log::error('AI Assistant error: ' . $e->getMessage());

            return response()->json([
                'success' => true,
                'message' => $this->fallbackReply($message),
                'timestamp' => now()->toIso8601String(),
            ]);
        }
    }

    /**
     * Show AI settings page
     */
    public function settings()
    {
        $aiSettings = Setting::where('group', 'ai')->orderBy('key')->get();
        return view('ai.settings', compact('aiSettings'));
    }

    /**
     * Update AI settings
     */
    public function updateSettings(Request $request)
    {
        $keys = ['openai_key', 'ai_model', 'ai_temperature', 'ai_max_tokens', 'ai_enabled'];
        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(
                    ['key' => $key, 'group' => 'ai'],
                    ['value' => $request->input($key), 'type' => in_array($key, ['ai_enabled']) ? 'boolean' : 'string']
                );
            }
        }
        return redirect()->route('ai.settings')->with('success', 'AI settings updated.');
    }

    /**
     * Analyze document text
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:10000',
            'type' => 'required|in:agreement,application,general',
        ]);

        $text = $request->input('text');
        $type = $request->input('type');

        $analysis = [
            'word_count' => str_word_count($text),
            'char_count' => strlen($text),
            'sentence_count' => preg_match_all('/[.!?]+/', $text, $matches),
            'readability' => $this->calculateReadability($text),
            'key_points' => $this->extractKeyPoints($text),
            'suggestions' => $this->generateSuggestions($text, $type),
        ];

        return response()->json([
            'success' => true,
            'analysis' => $analysis,
        ]);
    }

    /**
     * Smart reply using pattern matching
     */
    private function smartReply(string $message, string $context): string
    {
        $message = strtolower($message);

        // Student/agent/user counts
        if (preg_match('/\b(how many|count|total)\b.*\b(student|agent|user|application|staff)\b/', $message, $m)) {
            $entity = $m[2] ?? 'student';
            $count = match($entity) {
                'student' => \App\Models\Student::count(),
                'agent' => \App\Models\User::where('role', 'agent')->count(),
                'staff' => \App\Models\User::where('role', 'staff')->count(),
                'user' => \App\Models\User::count(),
                'application' => \App\Models\Application::count(),
                default => \App\Models\Student::count(),
            };
            return "There are currently **{$count}** {$entity}(s) in the system.";
        }

        // Greetings
        if (preg_match('/\b(hi|hello|hey|good morning|good afternoon)\b/', $message)) {
            $greeting = str_contains($message, 'morning') ? 'Good morning'
                : (str_contains($message, 'afternoon') ? 'Good afternoon' : 'Hello');
            return "{$greeting}! I'm your AI assistant. I can help you with reports, statistics, document analysis, and general queries about the portal. How can I assist you today?";
        }

        // Help
        if (preg_match('/\b(help|what can you do|capabilities|features)\b/', $message)) {
            return "I can help you with:\n\n📊 **Reports**: Get statistics on students, applications, agents\n📄 **Document Analysis**: Review and analyze documents\n🔍 **Search**: Find students, applications, agents\n❓ **General**: Answer questions about the system\n\nJust ask me anything!";
        }

        // Document analysis
        if (preg_match('/\b(analyze|review|check)\b.*\b(document|agreement|file)\b/', $message)) {
            return "To analyze a document, please upload it to the student's document section and I can review it for completeness and flag any missing required fields.";
        }

        // Time/date
        if (preg_match('/\b(time|date|today)\b/', $message)) {
            return "The current date and time is **" . now()->format('l, F j, Y h:i A') . "**.";
        }

        // Application status
        if (preg_match('/\b(application|applications)\b.*\b(pending|approved|rejected|status)\b/', $message, $m)) {
            $statuses = \App\Models\Application::selectRaw("status, count(*) as cnt")->groupBy('status')->pluck('cnt', 'status');
            $lines = $statuses->map(fn($c, $s) => "- **" . ucfirst($s) . "**: {$c}")->implode("\n");
            return "Here are the application status counts:\n\n{$lines}";
        }

        // Recent activity
        if (preg_match('/\b(recent|latest|new)\b.*\b(activity|student|application)\b/', $message)) {
            $recent = \App\Models\Activity::latest()->take(5)->get();
            if ($recent->isEmpty()) return "No recent activity found.";
            $lines = $recent->map(fn($a) => "- " . $a->created_at->format('M d, H:i') . " - " . ($a->description ?? $a->type))->implode("\n");
            return "Here are the 5 most recent activities:\n\n{$lines}";
        }

        // Default smart response
        $responses = [
            "That's a great question! Based on current data, I'd recommend checking the reports section for detailed analytics.",
            "I understand your query. For specific actions like creating students or applications, please use the respective management sections.",
            "Thanks for your question! You can find more details in the dashboard or reports section.",
            "I'll help you with that. Let me suggest checking the relevant section for the most up-to-date information.",
        ];

        return $responses[array_rand($responses)];
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI(string $message, string $context): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.key'),
            'Content-Type' => 'application/json',
        ])->timeout(15)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an AI assistant for a student consultancy portal called "Idea Consultancy". Help with student management, applications, university information, and general queries. Be concise and professional.',
                ],
                ['role' => 'user', 'content' => $message],
            ],
            'max_tokens' => 500,
            'temperature' => 0.7,
        ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'] ?? $this->fallbackReply($message);
        }

        return $this->fallbackReply($message);
    }

    /**
     * Fallback reply
     */
    private function fallbackReply(string $message): string
    {
        return "I understand you're asking about: \"" . Str::limit($message, 100) . "\". I'm processing your request. For immediate assistance, please contact the support team or check the relevant section in the portal.";
    }

    /**
     * Calculate readability score
     */
    private function calculateReadability(string $text): array
    {
        $words = str_word_count($text);
        $sentences = max(1, preg_match_all('/[.!?]+/', $text, $matches));
        $syllables = $this->countSyllables($text);

        $score = 206.835 - 1.015 * ($words / $sentences) - 84.6 * ($syllables / $words);

        $level = match(true) {
            $score >= 90 => 'Very Easy',
            $score >= 80 => 'Easy',
            $score >= 70 => 'Fairly Easy',
            $score >= 60 => 'Standard',
            $score >= 50 => 'Fairly Difficult',
            $score >= 30 => 'Difficult',
            default => 'Very Difficult',
        };

        return [
            'score' => round($score, 1),
            'level' => $level,
            'words' => $words,
            'sentences' => $sentences,
        ];
    }

    /**
     * Count syllables (approximate)
     */
    private function countSyllables(string $text): int
    {
        $words = str_word_count(strtolower($text), 1);
        $count = 0;

        foreach ($words as $word) {
            $count += preg_match_all('/[aeiouy]+/', $word, $m);
            // Adjust for silent e
            if (strlen($word) > 2 && substr($word, -1) === 'e') {
                $count--;
            }
        }

        return max($count, 1);
    }

    /**
     * Extract key points from text
     */
    private function extractKeyPoints(string $text): array
    {
        $points = [];
        $sentences = preg_split('/[.!?]+/', $text);

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (strlen($sentence) > 20 && preg_match('/\b(important|key|must|required|critical|note|please)\b/i', $sentence)) {
                $points[] = $sentence;
            }
            if (count($points) >= 5) break;
        }

        return $points ?: ['No specific key points identified'];
    }

    /**
     * Generate suggestions based on text type
     */
    private function generateSuggestions(string $text, string $type): array
    {
        $suggestions = [];

        if ($type === 'agreement') {
            if (!preg_match('/\b(sign(ed|ature)?|date|agree|accept)\b/i', $text)) {
                $suggestions[] = 'Consider adding a signature section';
            }
            if (strlen($text) < 500) {
                $suggestions[] = 'The agreement text appears short - consider expanding key terms';
            }
        }

        if ($type === 'application') {
            if (!preg_match('/\b(education|qualification|degree)\b/i', $text)) {
                $suggestions[] = 'Include educational qualifications';
            }
        }

        $suggestions[] = 'Ensure all required fields are completed';
        $suggestions[] = 'Verify all dates and deadlines';

        return $suggestions;
    }
}
