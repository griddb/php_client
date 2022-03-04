<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
if (file_exists('griddb_php_client.php')) {
    // File php wrapper is generated with SWIG 4.0.2 and below
    require_once('griddb_php_client.php');
}
require_once('config.php');
require_once('utility.php');

class BS018EnumSpec extends TestCase {
    public function testEnum()
    {
        echo("Test for Enum:\n Call all Enums");
        echo(" Expected can call all Enums\n");
        $hasException = "0";
        try {
            $enumVal = \Type::STRING;
            $enumVal = \Type::BOOL;
            $enumVal = \Type::BYTE;
            $enumVal = \Type::SHORT;
            $enumVal = \Type::INTEGER;
            $enumVal = \Type::LONG;
            $enumVal = \Type::FLOAT;
            $enumVal = \Type::DOUBLE;
            $enumVal = \Type::TIMESTAMP;
            $enumVal = \Type::BLOB;
            $enumVal = \ContainerType::COLLECTION;
            $enumVal = \ContainerType::TIME_SERIES;
            $enumVal = \IndexType::DEFAULT_TYPE;
            $enumVal = \IndexType::TREE;
            $enumVal = \IndexType::HASH;
            $enumVal = \RowSetType::CONTAINER_ROWS;
            $enumVal = \RowSetType::AGGREGATION_RESULT;
            $enumVal = \RowSetType::QUERY_ANALYSIS;
            $enumVal = \TimeUnit::YEAR;
            $enumVal = \TimeUnit::MONTH;
            $enumVal = \TimeUnit::DAY;
            $enumVal = \TimeUnit::HOUR;
            $enumVal = \TimeUnit::MINUTE;
            $enumVal = \TimeUnit::SECOND;
            $enumVal = \TimeUnit::MILLISECOND;
            $enumVal = \TypeOption::NULLABLE;
            $enumVal = \TypeOption::NOT_NULL;
        } catch (\GSException $e) {
            for ($i= 0; $i < $e->getErrorStackSize(); $i++) {
                echo("\n[$i]\n");
                echo($e->getErrorCode($i)."\n");
                echo($e->getLocation($i)."\n");
                echo($e->getErrorMessage($i)."\n");
            }
            $hasException = "1";
        } catch (\Exception $e1) {
            echo($e1."\n");
            $hasException = "1";
        }

        //Assert result
        $this->assertEquals($hasException, "0");
    }
}
?>

