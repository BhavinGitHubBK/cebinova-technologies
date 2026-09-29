<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'New';
    case Contacted = 'Contacted';
    case Qualified = 'Qualified';
    case ProposalSent = 'Proposal Sent';
    case Won = 'Won';
    case Lost = 'Lost';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function timestampColumn(): ?string
    {
        return match ($this) {
            self::Contacted => 'contacted_at',
            self::Qualified => 'qualified_at',
            self::ProposalSent => 'proposal_sent_at',
            self::Won => 'won_at',
            self::Lost => 'lost_at',
            self::New => null,
        };
    }
}
