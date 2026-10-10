<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveChat extends Model
{
    public const IDLE_MINUTES = 30;

    protected $fillable = [
        'token',
        'name',
        'mobile',
        'question',
        'messages',
    ];

    protected function casts(): array
    {
        return [
            'messages' => 'array',
        ];
    }

    public function mobileDisplay(): string
    {
        return substr($this->mobile, 0, 5).' '.substr($this->mobile, 5);
    }

    /**
     * Conversation lines without the name and mobile the visitor typed to start.
     *
     * @return list<array{from: string, text: string}>
     */
    public function transcript(): array
    {
        $name = mb_strtolower(trim($this->name));
        $skippedName = false;
        $skippedMobile = false;
        $lines = [];

        foreach ($this->messages ?? [] as $message) {
            $text = trim((string) ($message['text'] ?? ''));
            $from = ($message['from'] ?? '') === 'visitor' ? 'visitor' : 'bot';
            $digits = preg_replace('/\D+/', '', $text) ?? '';

            if ($from === 'visitor' && ! $skippedName && mb_strtolower($text) === $name) {
                $skippedName = true;

                continue;
            }

            if ($from === 'visitor' && ! $skippedMobile && $digits === $this->mobile) {
                $skippedMobile = true;

                continue;
            }

            if ($from === 'bot' && str_contains($text, 'What would you like to know')) {
                continue;
            }

            if ($text === '') {
                continue;
            }

            $lines[] = ['from' => $from, 'text' => $text];
        }

        return $lines;
    }

    /**
     * @return list<array{question: string, answer: ?string}>
     */
    public function exchanges(): array
    {
        $pairs = [];
        $pending = null;

        foreach ($this->transcript() as $line) {
            if ($line['from'] === 'visitor') {
                if ($pending) {
                    $pairs[] = $pending;
                }
                $pending = ['question' => $line['text'], 'answer' => null];

                continue;
            }

            if ($pending) {
                $pending['answer'] = $line['text'];
                $pairs[] = $pending;
                $pending = null;
            }
        }

        if ($pending) {
            $pairs[] = $pending;
        }

        return $pairs;
    }
}
