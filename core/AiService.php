<?php
/**
 * AiService — AI-powered hints and teaching assistance
 *
 * Provides educational hints using OpenAI or Gemini.
 */
class AiService {

    private static function buildPrompt(string $title, string $description, ?string $userPrompt = null): string {
        $cleanDesc = strip_tags($description);
        $prompt = <<<PROMPT
Sen Türkiye'deki lise veya üniversite sınavlarına hazırlanan öğrencilere yardımcı olan uzman, sabırlı ve teşvik edici bir eğitmensin.
Sana bir soru başlığı ve içeriği verilecek.
Görevin, doğrudan cevabı veya son çözümü VERMEMEK. Bunun yerine, öğrencinin soruyu kendi başına çözebilmesi için yardımcı olmak.
Öğrenci sana soruyla ilgili özel bir şey sormuş olabilir. Eğer öyleyse, öğrencinin bu sorusunu yanıtla ancak hala çözümü doğrudan verme.

Çıktın HTML formatında (sadece <b>, <i>, <ul>, <li>, <br> kullanarak) temiz bir tasarımla olsun. Başka hiçbir açıklama ekleme.

Soru Başlığı: {$title}
Soru İçeriği: {$cleanDesc}

PROMPT;
        
        if ($userPrompt) {
            $cleanUserPrompt = strip_tags($userPrompt);
            $prompt .= "\nÖğrencinin Sana Sorusu/Mesajı: {$cleanUserPrompt}\n";
        } else {
            $prompt .= "\nÖğrenci özel bir soru sormadı. Lütfen soruya genel bir ipucu ve düşünme adımı ver.\n";
        }
        
        return $prompt;
    }

    public static function getHint(string $title, string $description, ?string $userPrompt = null): ?string {
        // 1️⃣ OpenAI API (primary)
        if (defined('OPENAI_API_KEY') && !empty(OPENAI_API_KEY) && OPENAI_API_KEY !== 'YOUR_OPENAI_KEY_HERE') {
            $result = self::getHintWithOpenAI($title, $description, $userPrompt);
            if ($result !== null) {
                return $result;
            }
        }

        // 2️⃣ Gemini API (secondary)
        if (defined('GEMINI_API_KEY') && !empty(GEMINI_API_KEY) && GEMINI_API_KEY !== 'YOUR_GEMINI_KEY_HERE') {
            $result = self::getHintWithGemini($title, $description, $userPrompt);
            if ($result !== null) {
                return $result;
            }
        }

        // 3️⃣ Fallback
        return "Şu an yapay zeka asistanına bağlanılamıyor. Lütfen soruyu tekrar gözden geçir ve temel kuralları hatırlamaya çalış. Başarabilirsin!";
    }

    private static function getHintWithOpenAI(string $title, string $description, ?string $userPrompt = null): ?string {
        $model   = defined('OPENAI_MODEL') ? OPENAI_MODEL : 'gpt-4o-mini';
        $payload = json_encode([
            'model'       => $model,
            'temperature' => 0.7,
            'max_tokens'  => 300,
            'messages'    => [
                [
                    'role'    => 'system',
                    'content' => 'Sen öğrencilere ipucu veren bir yapay zeka asistanısın. Çıktıların kısa, net ve HTML formatında olmalıdır. Markdown veya JSON kullanma.',
                ],
                [
                    'role'    => 'user',
                    'content' => self::buildPrompt($title, $description, $userPrompt),
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
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? null;
    }

    private static function getHintWithGemini(string $title, string $description, ?string $userPrompt = null): ?string {
        $payload = json_encode([
            'contents'        => [['parts' => [['text' => self::buildPrompt($title, $description, $userPrompt)]]]],
            'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 300],
        ]);

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . GEMINI_API_KEY;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        $data = json_decode($response, true);
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        
        if ($text) {
             // Basic markdown to HTML cleanup for gemini just in case
             $text = preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', $text);
             $text = preg_replace('/\*(.*?)\*/', '<i>$1</i>', $text);
             $text = nl2br($text);
        }

        return $text;
    }
}
