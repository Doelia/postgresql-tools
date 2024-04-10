<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use SWouters\PostgreSQLTools\PostgreSQLConditionBuilder;

class PostgreSQLConditionBuilderTest extends TestCase
{

    public function testCleanValue()
    {
        $builder = new PostgreSQLConditionBuilder();

        $this->assertEquals("1=1 AND description='c''est l''été'", $builder->buildCondition([
            'description' => "c'est l'été",
        ]));
    }

    public function testFormatValue()
    {
        $builder = new PostgreSQLConditionBuilder();

        $this->assertEquals("1=1 AND id='1'", $builder->buildCondition([
            'id' => 1,
        ]));

        $this->assertEquals("1=1 AND created_at=NOW()", $builder->buildCondition([
            'created_at' => 'NOW()',
        ]));

        $this->assertEquals("1=1 AND created_at IS NULL", $builder->buildCondition([
            'created_at' => null,
        ]));

        $this->assertEquals("1=1 AND enabled=TRUE", $builder->buildCondition([
            'enabled' => true,
        ]));

        $this->assertEquals("1=1 AND enabled=FALSE", $builder->buildCondition([
            'enabled' => false,
        ]));
    }

    public function testBuildCondition()
    {

        $builder = new PostgreSQLConditionBuilder();

        $this->assertEquals("1=1 AND id='1'", $builder->buildCondition([
            'id' => 1,
        ]));

        $this->assertEquals("1=1 AND id='1' AND name='John'", $builder->buildCondition([
            'id' => 1,
            'name' => 'John',
        ]));

        $this->assertEquals("1=1 AND (1=0 OR id='1' OR id='2')", $builder->buildCondition([
            'id' => [1, 2],
        ]));

        $this->assertEquals("1=1 AND (1=0 OR id='1' OR id='2') AND name='John'", $builder->buildCondition([
            'id' => [1, 2],
            'name' => 'John',
        ]));

        $this->assertEquals("1=0 OR firstname='John' OR lastname='John'", $builder->buildCondition([
            'firstname' => 'John',
            'lastname' => 'John',
        ], 'OR'));

        $this->assertEquals("1=1 AND (1=0 OR id != '1' OR id != '2')", $builder->buildCondition([
            'id' => [1, 2],
        ], 'AND', 'OR', [
            'id' => '!=',
        ]));

        $this->assertEquals("1=1 AND created_at > '2024-03-01' AND created_at < '2024-03-02'", $builder->buildCondition([
            'date_min' => '2024-03-01',
            'date_max' => '2024-03-02',
        ], 'AND', 'OR', [
            'date_min' => '>',
            'date_max' => '<',
        ], [
            'date_min' => 'created_at',
            'date_max' => 'created_at',
        ]));

    }

    public function testBadCondition()
    {
        $builder = new PostgreSQLConditionBuilder();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid key');

        $builder->buildCondition([
            "id'" => 1,
        ]);

    }

}
