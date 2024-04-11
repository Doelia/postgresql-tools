# Postgresql Tools

A PHP library to help you to build complexes SQL queries for PostgreSQL.

## Installation

```bash
composer require swouters/postgresql-tools
```

## Usage

### Condition Builder

A tool to build easily complex conditions for SQL queries.

#### Signature

```php
// Array example
$array = [
    'age' => 30,
    'name' => ['John', 'Doe'],
];

// Signature
public function build(
    array  $array, // The array of conditions
    string $firstLevelOperator = 'AND', // The operator used between 1st level values (age=30 and ...)
    string $secondLevelOperator = 'OR', // The operator used if a value is an array (name=John OR name=Doe)
    array  $comparisonOperators = [], // Default is '='. You can set a specific operator for a key
    array  $replaces = [] // Replace the key used for the SQL column
): array
```

#### Build condition exemple

```php
use SWouters\PostgresqlTools\ConditionBuilder;

// name = "John Doe"
PostgreSQLConditionBuilder::buildCondition([
    'name' => 'John Doe',
]);

// Firstname=John AND Lastname=Doe
PostgreSQLConditionBuilder::buildCondition([
    'firstname' => 'John',
    'lastname' => 'Doe',
]);

// Firstname=John OR Lastname=Doe
PostgreSQLConditionBuilder::buildCondition([
        'firstname' => 'John',
        'lastname' => 'Doe',
], 'OR');

// Name=John OR Name=Doe
PostgreSQLConditionBuilder::buildCondition([
    'name' => ['John', 'Doe'],
]);

// (Name=John OR Name=Doe) AND Age=30
PostgreSQLConditionBuilder::buildCondition([
    'name' => ['John', 'Doe'],
    'age' => 30,
]);

// status != 'deleted'
PostgreSQLConditionBuilder::buildCondition([
    'status' => 'deleted',
], 'AND', 'OR', [
    'status' => '!='
]);

// age=30 AND (name!=John AND name!=Doe)
PostgreSQLConditionBuilder::buildCondition([
    'age' => 30,
    'name' => ['John', 'Doe'],
], 'AND', 'AND', [
    'name' => '!='
]);

// user.name=John
PostgreSQLConditionBuilder::buildCondition([
    'name' => 'John',
], 'AND', 'OR', [], [
    'name' => 'user.name'
]);

// created_at > '2024-03-01' AND created_at < '2024-03-02'
PostgreSQLConditionBuilder::buildCondition([
    'date_min' => '2024-03-01',
    'date_max' => '2024-03-02',
], 'AND', 'OR', [
    'date_min' => '>',
    'date_max' => '<',
], [
    'date_min' => 'created_at',
    'date_max' => 'created_at',
]);
```

#### Execute query (example with Doctrine DBAL)

```php
[$cond, $params] = PostgreSQLConditionBuilder::buildCondition([
    'name' => 'John Doe',
]);

$connexion->executeQuery("select * from users WHERE $cond", $params);
```
