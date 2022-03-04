<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
if (file_exists('griddb_php_client.php')) {
    // File php wrapper is generated with SWIG 4.0.2 and below
    require_once('griddb_php_client.php');
}
require_once('config.php');
require_once('utility.php');

class BS009RowSetManualCommitSpec extends TestCase {
    protected static $gridstore;
    protected static $containerNameString = "col01String";
    protected static $containerNameInteger = "col01Integer";
    protected static $containerNameLong = "col01Long";
    protected static $containerNameTimestampTS = "col01Timestamp";

    public static function setUpBeforeClass(): void {
        $factory = \StoreFactory::getInstance();
        /**
         * Container schema
         */
        $columnInfoListString = [["A", \Type::STRING],
                                 ["a_9", \Type::BYTE],
                                 ["za", \Type::SHORT],
                                 ["F", \Type::BOOL],
                                 ["G", \Type::FLOAT],
                                 ["H", \Type::DOUBLE],
                                 ["I", \Type::BLOB]];

        $columnInfoListInteger = [["A", \Type::INTEGER],
                                  ["a_9", \Type::BYTE],
                                  ["za", \Type::SHORT],
                                  ["F", \Type::BOOL],
                                  ["G", \Type::FLOAT],
                                  ["H", \Type::DOUBLE],
                                  ["I", \Type::BLOB]];

        $columnInfoListLong= [["A", \Type::LONG],
                              ["a_9", \Type::BYTE],
                              ["za", \Type::SHORT],
                              ["F", \Type::BOOL],
                              ["G", \Type::FLOAT],
                              ["H", \Type::DOUBLE],
                              ["I", \Type::BLOB]];

        $columnInfoListTimestampTS = [["A", \Type::TIMESTAMP],
                                      ["a_9", \Type::BYTE],
                                      ["za", \Type::SHORT],
                                      ["F", \Type::BOOL],
                                      ["G", \Type::FLOAT],
                                      ["H", \Type::DOUBLE],
                                      ["I", \Type::BLOB]];
        try {
            $storeInfo = ["host" => GRIDDB_NOTIFICATION_ADDRESS,
                          "port" => (int)GRIDDB_NOTIFICATION_PORT,
                          "clusterName" => GRIDDB_CLUSTER_NAME,
                          "username" => GRIDDB_USERNAME,
                          "password" => GRIDDB_PASSWORD];

            self::$gridstore = $factory->getStore($storeInfo);

            // Create a collection container with rowkey is string
            $containerInfoString = new \ContainerInfo(["name" => self::$containerNameString,
                                                    "columnInfoArray" => $columnInfoListString,
                                                    "type" => \ContainerType::COLLECTION,
                                                    "rowKey" => true]);

            // Create a a collection container with rowkey is integer
            $containerInfoInteger = new \ContainerInfo(["name" => self::$containerNameInteger,
                                                   "columnInfoArray" => $columnInfoListInteger,
                                                   "type" => \ContainerType::COLLECTION,
                                                   "rowKey" => true]);

            // Create a collection container with rowkey is long
            $containerInfoLong = new \ContainerInfo(["name" => self::$containerNameLong,
                                                    "columnInfoArray" => $columnInfoListLong,
                                                    "type" => \ContainerType::COLLECTION,
                                                    "rowKey" => true]);

            // Create a a collection container with rowkey is timestamp
            $containerInfoTimestampTS = new \ContainerInfo(["name" => self::$containerNameTimestampTS,
                                                   "columnInfoArray" => $columnInfoListTimestampTS,
                                                   "type" => \ContainerType::TIME_SERIES,
                                                   "rowKey" => true]);

            self::$gridstore->dropContainer(self::$containerNameString);
            self::$gridstore->dropContainer(self::$containerNameInteger);
            self::$gridstore->dropContainer(self::$containerNameLong);
            self::$gridstore->dropContainer(self::$containerNameTimestampTS);

            self::$gridstore->putContainer($containerInfoString, true);
            self::$gridstore->putContainer($containerInfoInteger, true);
            self::$gridstore->putContainer($containerInfoLong, true);
            self::$gridstore->putContainer($containerInfoTimestampTS, true);
        } catch (\GSException $e){
            for ($i= 0; $i < $e->getErrorStackSize(); $i++) {
                echo("\n[$i]\n");
                echo($e->getErrorCode($i)."\n");
                echo($e->getLocation($i)."\n");
                echo($e->getErrorMessage($i)."\n");
            }
        } catch (\Exception $e1) {
            echo($e1."\n");
            $hasException = "1";
        }
    }

    public static function tearDownAfterClass(): void
    {
        // Delete container
        try {
            self::$gridstore->dropContainer(self::$containerNameString);
            self::$gridstore->dropContainer(self::$containerNameInteger);
            self::$gridstore->dropContainer(self::$containerNameLong);
            self::$gridstore->dropContainer(self::$containerNameTimestampTS);
        } catch (\GSException $e){
            for ($i= 0; $i < $e->getErrorStackSize(); $i++) {
                echo("\n[$i]\n");
                echo($e->getErrorCode($i)."\n");
                echo($e->getLocation($i)."\n");
                echo($e->getErrorMessage($i)."\n");
            }
        }
    }

    /**
     * Provider Data
     */
    public function providerDataTest() {
        return getTestData("test/resource/BS-009-RowSet_manual_commit.csv");
    }

    /**
     * @dataProvider providerDataTest
     */
    public function testRowSetManualCommit($testId, $containerType,
                                           $rowKeyType, $rowKey,
                                           $mType_STRING, $mType_BOOL,
                                           $mType_BYTE, $mType_SHORT,
                                           $mType_INTEGER, $mType_LONG,
                                           $mType_FLOAT, $mType_DOUBLE,
                                           $mType_BLOB, $expectedOutput)
    {
        echo sprintf("Test case: %s put %s:\n", $testId, $containerType);
        echo sprintf(" Put container with row key type %s, value %s\n", $rowKeyType, $rowKey);
        echo sprintf(" Expected has exception = %s\n", $expectedOutput);

        $queryStr = "Select *";
        $rowKey = convertData($rowKey);
        $mType_BOOL = convertData($mType_BOOL);
        $mType_BYTE = convertData($mType_BYTE);
        $mType_SHORT = convertData($mType_SHORT);
        $mType_FLOAT = convertData($mType_FLOAT);
        $mType_DOUBLE = convertData($mType_DOUBLE);
        $mType_BLOB = convertData($mType_BLOB);

        $hasException = "0";

        switch ($rowKeyType) {
            case "string":
                $containerName = self::$containerNameString;
                break;
            case "integer":
                $containerName = self::$containerNameInteger;
                break;
            case "long":
                $containerName = self::$containerNameLong;
                break;
            case "timestamp":
                $containerName = self::$containerNameTimestampTS;
                break;
            default:
                $containerName = self::$containerNameString;
        }
        try {
            $container = self::$gridstore->getContainer($containerName);
            $container->setAutoCommit(false);
            $row = [$rowKey, $mType_BYTE, $mType_SHORT, $mType_BOOL,
                    $mType_FLOAT, $mType_DOUBLE, $mType_BLOB];
            $container->put($row);
            $container->commit();
            $rowAfterPut = $container->get($rowKey);
            $query = $container->query($queryStr);
            $rowSet = $query->fetch(false);
            while ($rowSet->hasNext()) {
                $rowSet->next();
            }
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
        $this->assertEquals($expectedOutput, $hasException);
    }
}
?>

