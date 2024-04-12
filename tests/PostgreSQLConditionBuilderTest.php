<?php


use PHPUnit\Framework\TestCase;
use SWouters\PostgreSQLTools\PostgreSQLConditionBuilder;

class PostgreSQLConditionBuilderTest extends TestCase
{

    public function testBuildCondition()
    {
        $builder = new PostgreSQLConditionBuilder();

        $this->assertEquals("1=1 AND id=:id", $builder->buildCondition([
            'id' => 1,
        ])[0]);

        $this->assertEquals("1=1 AND u.id=:uid", $builder->buildCondition([
            'u.id' => 1,
        ])[0]);

        $this->assertEquals("1=1 AND id=:id AND name=:name", $builder->buildCondition([
            'id' => 1,
            'name' => 'John',
        ])[0]);

        $this->assertEquals("1=1 AND (1=0 OR id=:id0 OR id=:id1)", $builder->buildCondition([
            'id' => [1, 2],
        ])[0]);

        $this->assertEquals("1=1 AND (1=0 OR id=:id0 OR id=:id1) AND name=:name", $builder->buildCondition([
            'id' => [1, 2],
            'name' => 'John',
        ])[0]);

        $this->assertEquals("1=0 OR firstname=:firstname OR lastname=:lastname", $builder->buildCondition([
            'firstname' => 'John',
            'lastname' => 'John',
        ], 'OR')[0]);

        $this->assertEquals("1=1 AND (1=0 OR id!=:id0 OR id!=:id1)", $builder->buildCondition([
            'id' => [1, 2],
        ], 'AND', 'OR', [
            'id' => '!=',
        ])[0]);

        $this->assertEquals("1=1 AND created_at>:date_min AND created_at<:date_max", $builder->buildCondition([
            'date_min' => '2024-03-01',
            'date_max' => '2024-03-02',
        ], 'AND', 'OR', [
            'date_min' => '>',
            'date_max' => '<',
        ], [
            'date_min' => 'created_at',
            'date_max' => 'created_at',
        ])[0]);

        $this->assertEquals("1=1 AND created_at IS NULL", $builder->buildCondition([
            'created_at' => null,
        ])[0]);

        $this->assertEquals("1=1 AND created_at IS NOT NULL", $builder->buildCondition([
            'created_at' => null,
        ], 'AND', 'OR', [
            'created_at' => '!=',
        ])[0]);
    }

    public function testBuildConditionParamsWithReplaces()
    {
        $builder = new PostgreSQLConditionBuilder();

        [$cond, $params] = $builder->buildCondition([
            'u.id' => 1,
        ], 'AND', 'OR', [], [
            'u.id' => 's.id',
        ]);

        $this->assertEquals("1=1 AND s.id=:uid", $cond);
        $this->assertEquals(['uid' => 1], $params);
    }

    public function testBuildConditionParamsArray()
    {
        $builder = new PostgreSQLConditionBuilder();

        [$cond, $params] = $builder->buildCondition([
            'id' => [1, 2, 3],
        ]);

        $this->assertEquals("1=1 AND (1=0 OR id=:id0 OR id=:id1 OR id=:id2)", $cond);
        $this->assertEquals([
            'id0' => 1,
            'id1' => 2,
            'id2' => 3,
        ], $params);

    }

    public function testBadCondition()
    {
        $builder = new PostgreSQLConditionBuilder();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid key');

        $builder->buildCondition([
            "id'" => 1,
        ]);
    }

    public function testBadOperator()
    {
        $builder = new PostgreSQLConditionBuilder();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid operator');

        $builder->buildCondition([
            "id" => 1,
        ], 'AND', 'OR', [
            'id' => 'nono',
        ]);
    }

}
