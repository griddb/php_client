<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
if (file_exists('griddb_php_client.php')) {
    // File php wrapper is generated with SWIG 4.0.2 and below
    require_once('griddb_php_client.php');
}
require_once('config.php');
require_once('utility.php');

class BS007AggregationWithDoubleTimestamp extends TestCase {
    protected static $factory;
    protected static $gridstore;
    protected static $containerNameCol = "col01Collection";
    protected static $containerNameTS = "col01Timeseries";

    public static function setUpBeforeClass(): void {
        $factory = \StoreFactory::getInstance();

        // Container schema
        $columnInfoListCol = [["A", \Type::STRING],
                              ["za", \Type::TIMESTAMP],
                              ["1_a", \Type::BYTE],
                              ["B", \Type::SHORT],
                              ["H", \Type::INTEGER],
                              ["F", \Type::BOOL],
                              ["G", \Type::FLOAT],
                              ["a_9", \Type::DOUBLE],
                              ["I", \Type::BLOB]];

        $columnInfoListTS = [["za", \Type::TIMESTAMP],
                             ["B", \Type::STRING],
                             ["1_a", \Type::BYTE],
                             ["C", \Type::SHORT],
                             ["H", \Type::INTEGER],
                             ["F", \Type::BOOL],
                             ["G", \Type::FLOAT],
                             ["a_9", \Type::DOUBLE],
                             ["I", \Type::BLOB]];

        $dateTimeStrList = ["2018-12-01T10:00:00.000Z",
                            "2018-12-01T10:10:00.000Z",
                            "2018-12-01T10:20:00.000Z"];
        $strList = ["row01", "row02", "row03"];
        $doubleList = [10.3, 5.7, 8.2];
        $rowCount = 3;

        try {
            $storeInfo = ["host" => GRIDDB_NOTIFICATION_ADDRESS,
                          "port" => (int)GRIDDB_NOTIFICATION_PORT,
                          "clusterName" => GRIDDB_CLUSTER_NAME,
                          "username" => GRIDDB_USERNAME,
                          "password" => GRIDDB_PASSWORD];

            self::$gridstore = $factory->getStore($storeInfo);
            self::$gridstore->dropContainer(self::$containerNameCol);
            self::$gridstore->dropContainer(self::$containerNameTS);

            // Create a collection container
            $containerInfoCol = new \ContainerInfo(["name" => self::$containerNameCol,
                                                    "columnInfoArray" => $columnInfoListCol,
                                                    "type" => \ContainerType::COLLECTION,
                                                    "rowKey" => true]);
            // Create a timeseries container
            $containerInfoTS = new \ContainerInfo(["name" => self::$containerNameTS,
                                                   "columnInfoArray" => $columnInfoListTS,
                                                   "type" => \ContainerType::TIME_SERIES,
                                                   "rowKey" => true]);

            $containerCol = self::$gridstore->putContainer($containerInfoCol, true);
            $containerTS = self::$gridstore->putContainer($containerInfoTS, true);

            // Convert Datetime string to DateTime object
            $dateTimeObjList = [];
            for ($i = 0; $i < $rowCount; $i++) {
                $dateTimeObjList[$i] = convertStrToDateTime($dateTimeStrList[$i]);
            }
            // Put row with multiple times
            for ($i = 0; $i < $rowCount; $i++) {
                // For row key is string
                $containerCol->put([$strList[$i], $dateTimeObjList[$i],
                                    127, 32767, 2147483647, true,
                                    1.1, $doubleList[$i] , "abc"]);
                // For row key is timestamp
                $containerTS->put([$dateTimeObjList[$i], $strList[$i],
                                   127, 32767, 2147483647, true,
                                   1.1, $doubleList[$i], "abc"]);
            }
        } catch (\GSException $e){
            for ($i= 0; $i < $e->getErrorStackSize(); $i++) {
                echo("\n[$i]\n");
                echo($e->getErrorCode($i)."\n");
                echo($e->getLocation($i)."\n");
                echo($e->getErrorMessage($i)."\n");
            }
        } catch (\Exception $e1) {
            echo($e1."\n");
        }
    }

    public static function tearDownAfterClass(): void
    {
        // Delete container
        try {
            self::$gridstore->dropContainer(self::$containerNameCol);
            self::$gridstore->dropContainer(self::$containerNameTS);
        } catch (\GSException $e){
            for ($i= 0; $i < $e->getErrorStackSize(); $i++) {
                echo("\n[$i]\n");
                echo($e->getErrorCode($i)."\n");
                echo($e->getLocation($i)."\n");
                echo($e->getErrorMessage($i)."\n");
            }
        } catch (\Exception $e1) {
            echo($e1."\n");
        }
    }

    /**
     * Provider Data
     */
    public function providerDataTest() {
        return getTestData("test/resource/BS-007-Aggregation_with_double_timestamp.csv");
    }

    /**
     * @dataProvider providerDataTest
     */
    public function testAggregation($testId, $containerType,
                                        $queryStr, $expectedOutput)
    {

        echo sprintf("Test case: %s:\n", $testId);
        echo sprintf(" Expected has exception = %s\n", $expectedOutput);
        if ($containerType == "GS_CONTAINER_COLLECTION") {
            $containerName = self::$containerNameCol;
        } else {
            $containerName = self::$containerNameTS;
        }
        $hasException = "0";

        try {
            $container = self::$gridstore->getContainer($containerName);
            $query = $container->query($queryStr);
            // Query fetch
            $rs = $query->fetch();
            // Get the result
            while ($rs->hasNext()) {
                // Get the result of the aggregation operation
                $aggregationResult = $rs->next();
                if (strpos($queryStr, "a_9") !== false) {
                    $value = $aggregationResult->get(\TYPE::DOUBLE);
                    echo sprintf(" TQL result: %lf\n", $value);
                }
                if (strpos($queryStr, "za") !== false){
                    $value = $aggregationResult->get(\TYPE::TIMESTAMP);
                    echo sprintf(" TQL result: %s\n", $value->format('Y-m-d H:i:s.u'));
                }
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

