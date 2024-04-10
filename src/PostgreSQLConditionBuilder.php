<?php

namespace SWouters\PostgreSQLTools;

use Exception;

class PostgreSQLConditionBuilder
{
    public function buildCondition(
        array $array,
        string $op1 = 'AND',
        string $op2 = 'OR',
        array $tests = [],
        array $replaces = []
    ): string
    {
        $this->verifyArray($array);

        $cond = $this->neutralCondition($op1);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (count($value) > 0) {
                    $cond .= " $op1 (" . $this->neutralCondition($op2);
                    foreach ($value as $v) {
                        $v = PostgreSQLFormatter::formatValueForSql($v);
                        $cond .= " $op2 " . $this->buildComparator($tests, $key, $v, $replaces);
                    }
                    $cond .= ")";
                }
            } else {
                $value = PostgreSQLFormatter::formatValueForSql($value);
                $cond .= " $op1 " . $this->buildComparator($tests, $key, $value, $replaces);
            }
        }
        return $cond;
    }

    private function verifyArray(array $array): void
    {
        foreach ($array as $key => $value)
        {
            if (!preg_match('/^[A-Za-z0-9\._]+$/', $key)) {
                throw new Exception('Invalid key: ' . $key);
            }
        }
    }

//    public function buildLikeCondition(array $list_rows, string $pattern): string
//    {
//        $cond = " (1!=1 ";
//
//        foreach ($list_rows as $row) {
//            $cond .= "OR $row ILIKE $pattern ";
//        }
//        $cond .= ") ";
//
//        return $cond;
//    }

    private function buildComparator($tests, $key, $value, $replaces): string
    {
        $key_sql = $key;
        if (in_array($key, array_keys($replaces))) {
            $key_sql = $replaces[$key];
        }

        if (in_array($key, array_keys($tests))) {
            $comparator  = $tests[$key];
            if ($comparator == '?') {
                return "jsonb_exists($key_sql,$value)";
            } else {
                return "$key_sql " . $comparator . " $value";
            }
        } else {
            if ($value == 'NULL') {
                return "$key_sql IS NULL";
            } else {
                return "$key_sql=$value";
            }
        }
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
