<?php

namespace SWouters\PostgreSQLTools;

use Exception;

class PostgreSQLConditionBuilder
{
    public static function buildCondition(
        array  $array,
        string $firstLevelOperator = 'AND',
        string $secondLevelOperator = 'OR',
        array  $comparisonOperators = [],
        array  $replaces = []
    ): array
    {
        self::verifyArray($array);
        self::verifyComparisonOperators($comparisonOperators);
        self::verifyReplaces($replaces);

        $cond = self::neutralCondition($firstLevelOperator);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (count($value) > 0) {
                    $cond .= " $firstLevelOperator (" . self::neutralCondition($secondLevelOperator);
                    foreach ($value as $n => $v) {
                        $cond .= " $secondLevelOperator " . self::buildComparator($comparisonOperators, $key, $v, $replaces, $n);
                    }
                    $cond .= ")";
                }
            } else {
                $cond .= " $firstLevelOperator " . self::buildComparator($comparisonOperators, $key, $value, $replaces);
            }
        }

        $params = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $n => $v) {
                    $params[self::formatParamKey($key, $n)] = $v;
                }
            } else {
                $params[self::formatParamKey($key)] = $value;
            }
        }

        return [$cond, $params];

    }


    private static function formatParamKey(string $key, int $n = null): string
    {
        $paramKey = preg_replace('/[^A-Za-z0-9_]/', '', $key);
        if ($n !== null) {
            $paramKey .= $n;
        }
        return $paramKey;
    }

    private static function verifyArray(array $array): void
    {
        foreach ($array as $key => $value)
        {
            if (!preg_match('/^[A-Za-z0-9_\.]+$/', $key)) {
                throw new Exception('Invalid key: ' . $key);
            }
        }
    }

    private static function buildComparator($comparisonOperators, $key, mixed $value, $replaces, $n = null): string
    {
        $key_sql = $replaces[$key] ?? $key;

        if ($value == 'NULL') {
            return "$key_sql IS NULL";
        }

        $comparator = $comparisonOperators[$key] ?? '=';
        $sqlParamKey = self::formatParamKey($key, $n);

        return "$key_sql$comparator:$sqlParamKey";
    }


    private static function neutralCondition(string $op): string
    {
        if ($op == 'AND') {
            return "1=1";
        }
        if ($op == 'OR') {
            return "1=0";
        }

        throw new Exception('Unknown operator : ' . $op);
    }

    private static function verifyComparisonOperators(array $comparisonOperators)
    {
        foreach ($comparisonOperators as $key => $value)
        {
            if (!in_array($value, ['=', '>', '<', '>=', '<=', '<>', '!=', 'LIKE', 'ILIKE', 'IN', 'NOT IN'])) {
                throw new Exception('Invalid operator: ' . $value);
            }
        }
    }

    private static function verifyReplaces(array $replaces)
    {
        foreach ($replaces as $key => $value)
        {
            if (!preg_match('/^[A-Za-z0-9_\.]+$/', $key)) {
                throw new Exception('Invalid replace key: ' . $key);
            }
        }
    }


}
