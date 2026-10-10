<?php

namespace App\Support;

class LiveChatScript
{
    /**
     * @return array<string, array{label: string, answer: string, link: array{label: string, url: string}}>
     */
    public static function questions(): array
    {
        return [
            'website' => [
                'label' => 'What does a website cost?',
                'answer' => 'A business website starts at ₹14,999. Share what you need and the team will suggest the right plan.',
                'link' => ['label' => 'See website pricing', 'url' => route('pricing')],
            ],
            'time' => [
                'label' => 'How long does a website take?',
                'answer' => 'Most business websites are ready in 5 to 15 working days after a short briefing.',
                'link' => ['label' => 'How we build websites', 'url' => route('services.show', 'web-development')],
            ],
            'store' => [
                'label' => 'Can you build an online store?',
                'answer' => 'Yes. We build catalogue, payment and order systems for kirana shops, retail and growing stores.',
                'link' => ['label' => 'See online store service', 'url' => route('services.show', 'ecommerce')],
            ],
            'marketing' => [
                'label' => 'Do you handle marketing?',
                'answer' => 'Yes. Regular, festival and complete growth plans are available. A short call is enough to pick the right one.',
                'link' => ['label' => 'See marketing packages', 'url' => route('marketing-packages')],
            ],
            'whatsapp' => [
                'label' => 'I want to talk on WhatsApp',
                'answer' => 'Your details are saved. Tap below and the team will continue with you on WhatsApp.',
                'link' => ['label' => 'Open WhatsApp', 'url' => whatsapp_url()],
            ],
        ];
    }
}
