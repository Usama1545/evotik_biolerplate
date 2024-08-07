<?php

namespace App\Enums;

enum IssueReportEnum: string
{
    case IGNORE = 'ignore';
    case NEW = 'new';
    case FURTHER_DEVELOPMENT = 'further_development';
    case Resolved = 'resolved';
    case ASAP = 'asap';


    public static function options(): array
    {
        $cases = static::cases();
        foreach ($cases as $case) {
            $dropdowns[] = ['text' => __($case->value), 'value' => $case->value];
        }

        return $dropdowns;
    }
}
