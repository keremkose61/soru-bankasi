<?php
/**
 * ModerationService — AI-powered content moderation
 *
 * Priority order:
 *   1. OpenAI API  (if OPENAI_API_KEY is set)
 *   2. Gemini API  (if GEMINI_API_KEY is set)
 *   3. Keyword-based rule engine (always available fallback)
 *
 * Returns: ['status'=>'APPROVED|REVIEW|REJECTED', 'reason'=>string, 'confidence'=>int]
 */
class ModerationService {

    // ── Shared system prompt ─────────────────────────────────────────────────
    private static function buildPrompt(string $text): string {
        return <<<PROMPT
You are a strict content moderation system for a Turkish educational Q&A platform for students aged 13-18.

Analyze the following user-generated content and classify it.

Rules:
1. REJECTED if: profanity, insults, hate speech, sexual content, violence, threats, self-harm, spam/ads
2. APPROVED if: neutral, educational, normal question or answer
3. REVIEW if: suspicious, borderline, or unclear

Detect bypass attempts (e.g. sh*t, s3x, f**k, spacing tricks).
Content may be in Turkish or English.

Output ONLY valid JSON, no markdown, no extra text:
{"status":"APPROVED|REVIEW|REJECTED","reason":"one short English sentence","confidence":0-100}

Content:
"""
{$text}
"""
PROMPT;
    }

    // ── Turkish & English Keyword Lists ──────────────────────────────────────
    private static $rejectedKeywords = [
        // Turkish profanity / insults
        'aptal', 'salak', 'gerizekalı', 'mal', 'orospu', 'kahpe', 'göt', 'sik',
        'amk', 'amına', 'oç', 'piç', 'bok', 'boktan', 'sürtük', 'kaltak',
        'köpek', 'eşek', 'seni s', 'öldür', 'öldüreceğim',
        'kendini öldür', 'intihar', 'öl git', 'ölürsün',
        // English profanity / hate
        'fuck', 'shit', 'bitch', 'asshole', 'bastard', 'cunt', 'whore',
        'nigger', 'faggot', 'kill yourself', 'kys', 'go die',
        // Bypass attempts
        'f*ck', 'sh*t', 's3x', 'f**k', 'b*tch', 'a**',
        // Spam
        'click here', 'buy now', 'free money', 'earn $', 'work from home',
        'ücretsiz kazan', 'para kazan', 'tıkla kazan',
    ];

    private static $reviewKeywords = [
        // Mild / borderline Turkish
        'kötü', 'berbat', 'rezil', 'şerefsiz', 'namussuz', 'saçmalama',
        'ne biçim', 'cahil', 'zavallı', 'densiz',
        // Mild / borderline English
        'stupid', 'idiot', 'loser', 'dumb', 'moron', 'ugly', 'hate you',
        // Self-harm hints
        'kendime zarar', 'canıma kıy', 'hurt myself', 'self harm',
        // Suspicious links / spam
        'http://', 'https://', 'bit.ly', 't.me/', 'discord.gg',
        // Adult hints
        'seks', 'sex', 'porn', 'porno', 'adult',
    ];

    // ── Public Entry Point ───────────────────────────────────────────────────

    /**
     * Moderate content using configured AI provider, then keyword fallback.
     */
    public static function check(string $text): array {
        if (!defined('MODERATION_ENABLED') || !MODERATION_ENABLED) {
            return ['status' => 'APPROVED', 'reason' => 'Moderation disabled', 'confidence' => 100];
        }

        $text = trim($text);
        if (empty($text)) {
            return ['status' => 'APPROVED', 'reason' => 'Empty content', 'confidence' => 100];
        }

        // 1️⃣ OpenAI API (primary if key is set)
        if (defined('OPENAI_API_KEY') && !empty(OPENAI_API_KEY) && OPENAI_API_KEY !== 'YOUR_OPENAI_KEY_HERE') {
            $result = self::checkWithOpenAI($text);
            if ($result !== null) {
                return $result;
            }
        }

        // 2️⃣ Gemini API (secondary)
        if (defined('GEMINI_API_KEY') && !empty(GEMINI_API_KEY) && GEMINI_API_KEY !== 'YOUR_GEMINI_KEY_HERE') {
            $result = self::checkWithGemini($text);
            if ($result !== null) {
                return $result;
            }
        }

        // 3️⃣ Keyword fallback
        return self::checkWithKeywords($text);
    }

    // ── OpenAI API ───────────────────────────────────────────────────────────

    private static function checkWithOpenAI(string $text): ?array {
        $model   = defined('OPENAI_MODEL') ? OPENAI_MODEL : 'gpt-4o-mini';
        $payload = json_encode([
            'model'       => $model,
            'temperature' => 0.1,
            'max_tokens'  => 120,
            'messages'    => [
                [
                    'role'    => 'system',
                    'content' => 'You are a strict content moderation AI. Output ONLY valid JSON, no markdown, no extra text.',
                ],
                [
                    'role'    => 'user',
                    'content' => self::buildPrompt($text),
                ],
            ],
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . OPENAI_API_KEY,
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null; // Fall to next provider
        }

        $data = json_decode($response, true);
        $raw  = $data['choices'][0]['message']['content'] ?? '';

        return self::parseJsonResult($raw);
    }

    // ── Gemini API ───────────────────────────────────────────────────────────

    private static function checkWithGemini(string $text): ?array {
        $payload = json_encode([
            'contents'        => [['parts' => [['text' => self::buildPrompt($text)]]]],
            'generationConfig' => ['temperature' => 0.1, 'maxOutputTokens' => 120],
        ]);

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . GEMINI_API_KEY;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        $data = json_decode($response, true);
        $raw  = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return self::parseJsonResult($raw);
    }

    // ── Shared JSON Parser ───────────────────────────────────────────────────

    private static function parseJsonResult(string $raw): ?array {
        // Strip markdown code fences (```json ... ```)
        $raw = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $raw = preg_replace('/\s*```$/', '', $raw);

        $result = json_decode(trim($raw), true);
        if (!$result || !isset($result['status'])) {
            return null;
        }

        return [
            'status'     => strtoupper($result['status']),
            'reason'     => $result['reason'] ?? 'AI moderation result',
            'confidence' => (int)($result['confidence'] ?? 80),
        ];
    }

    // ── Keyword Fallback ─────────────────────────────────────────────────────

    private static function checkWithKeywords(string $text): array {
        $lower   = mb_strtolower($text, 'UTF-8');
        $compact = preg_replace('/\s+/', '', $lower);

        // Whole-word match for short keywords (≤2 chars) to avoid false positives
        $wordMatch = function(string $haystack, string $needle) use ($compact): bool {
            if (mb_strlen($needle) <= 2) {
                return (bool) preg_match('/\b' . preg_quote($needle, '/') . '\b/u', $haystack);
            }
            return str_contains($haystack, $needle) || str_contains($compact, str_replace(' ', '', $needle));
        };

        foreach (self::$rejectedKeywords as $kw) {
            if ($wordMatch($lower, $kw)) {
                return ['status' => 'REJECTED', 'reason' => "Prohibited keyword detected: '{$kw}'", 'confidence' => 90];
            }
        }

        foreach (self::$reviewKeywords as $kw) {
            if ($wordMatch($lower, $kw)) {
                return ['status' => 'REVIEW', 'reason' => "Suspicious keyword detected: '{$kw}'", 'confidence' => 70];
            }
        }

        if (preg_match('/(.)\1{5,}/', $text)) {
            return ['status' => 'REVIEW', 'reason' => 'Excessive character repetition (possible spam)', 'confidence' => 65];
        }
        if (mb_strlen($text) > 10 && mb_strtoupper($text) === $text && preg_match('/[A-ZÜÖÇŞİĞ]{5,}/', $text)) {
            return ['status' => 'REVIEW', 'reason' => 'All-caps message flagged for review', 'confidence' => 55];
        }

        return ['status' => 'APPROVED', 'reason' => 'Content passed keyword moderation', 'confidence' => 85];
    }

    // ── DB Logging ───────────────────────────────────────────────────────────

    public static function log(\PDO $db, string $contentType, int $contentId, array $result): void {
        try {
            $stmt = $db->prepare(
                "INSERT INTO moderation_log (content_type, content_id, status, reason, confidence)
                 VALUES (:type, :id, :status, :reason, :confidence)"
            );
            $stmt->execute([
                ':type'       => $contentType,
                ':id'         => $contentId,
                ':status'     => strtolower($result['status']),
                ':reason'     => $result['reason'],
                ':confidence' => $result['confidence'],
            ]);
        } catch (\Exception $e) {
            // Silent fail — logging should never break the app
        }
    }
}
