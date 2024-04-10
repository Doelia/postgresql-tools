<?php

namespace SWouters\PostgreSQLTools;

class PostgreSQLFormatter
{
    public static function escapeString(string $value): string
    {
        return str_replace("'", "''", $value);
    }

    public static function formatValueForSql(mixed $value): string
    {
        if (is_array($value)) {
            return "";
        }

        if ($value === true) {
            return 'TRUE';
        }

        if ($value === false) {
            return 'FALSE';
        }

        if ($value === null) {
            return "NULL";
        }

        if ($value == 'NOW()') {
            return $value;
        }

        $value = self::escapeString($value);
        $value = "'$value'";

        return $value;
    }



}
