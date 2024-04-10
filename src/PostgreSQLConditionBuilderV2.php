<?php

namespace SWouters\PostgreSQLTools;

use Exception;

class PostgreSQLConditionBuilderV2
{
    public function buildCondition(
        array  $array,
        string $firstLevelOperator = 'AND',
        string $secondLevelOperator = 'OR',
        array  $comparisonOperators = [],
        array  $replaces = []
    ): array
    {
        $this->verifyArray($array);

        $cond = $this->neutralCondition($firstLevelOperator);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (count($value) > 0) {
                    $cond .= " $firstLevelOperator (" . $this->neutralCondition($secondLevelOperator);
                    foreach ($value as $n => $v) {
                        $cond .= " $secondLevelOperator " . $this->buildComparator($comparisonOperators, $key, $v, $replaces, $n);
                    }
                    $cond .= ")";
                }
            } else {
                $cond .= " $firstLevelOperator " . $this->buildComparator($comparisonOperators, $key, $value, $replaces);
            }
        }

        $params = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $n => $v) {
                    $params[$this->formatParamKey($key, $n)] = $v;
                }
            } else {
                $params[$this->formatParamKey($key)] = $value;
            }
        }

        return [$cond, $params];

    }

    private function formatParamKey(string $key, int $n = null): string
    {
        $paramKey = preg_replace('/[^A-Za-z0-9_]/', '', $key);
        if ($n !== null) {
            $paramKey .= $n;
        }
        return $paramKey;
    }

    private function verifyArray(array $array): void
    {
        foreach ($array as $key => $value)
        {
            if (!preg_match('/^[A-Za-z0-9_\.]+$/', $key)) {
                throw new Exception('Invalid key: ' . $key);
            }
        }
    }

    private function buildComparator($comparisonOperators, $key, mixed $value, $replaces, $n = null): string
    {
        $key_sql = $replaces[$key] ?? $key;

        if ($value == 'NULL') {
            return "$key_sql IS NULL";
        }

        $comparator = $comparisonOperators[$key] ?? '=';
        $sqlParamKey = $this->formatParamKey($key, $n);

        return "$key_sql$comparator:$sqlParamKey";
    }


    private function neutralCondition(string $op): string
    {
        if ($op == 'AND') {
            return "1=1";
        }
        if ($op == 'OR') {
            return "1=0";
        }

        throw new Exception('Unknown operator : ' . $op);
    }


}
