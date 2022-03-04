<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
if (file_exists('griddb_php_client.php')) {
    // File php wrapper is generated with SWIG 4.0.2 and below
    require_once('griddb_php_client.php');
}
require_once('config.php');
require_once('utility.php');

class BS001ContainerBasicScenario extends TestCase {
    protected static $gridstore;

    public static function setUpBeforeClass(): void {
        $factory = \StoreFactory::getInstance();
        try {
            $storeInfo = ["host" => GRIDDB_NOTIFICATION_ADDRESS,
                          "port" => (int)GRIDDB_NOTIFICATION_PORT,
                          "clusterName" => GRIDDB_CLUSTER_NAME,
                          "username" => GRIDDB_USERNAME,
                          "password" => GRIDDB_PASSWORD];

            self::$gridstore = $factory->getStore($storeInfo);
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
        return getTestData("test/resource/BS-001-Container_basic_scenario.csv");
    }

    /**
     * @dataProvider providerDataTest
     */
    public function testContainerBasicScenario($testId, $containerType,
                                               $containerName, $rowKeyList,
                                               $stringVal, $boolVal, $doubleVal,
                                               $queryCommand, $expectedOutput)
    {
        echo sprintf("Test case: %s put %s:\n", $testId, $containerType);
        echo sprintf(" Expected has exception = %s\n", $expectedOutput);
        $modifiable = true;
        $rowKeyAssigned = true;

        // Convert data test from string type in CSV to other data type
        $stringVal = convertData($stringVal);
        $boolVal = convertData($boolVal);
        $doubleVal = convertData($doubleVal);
        $containerName = convertData($containerName);
        $containerName = convertExtraData($containerName);

        // Convert $rowKeyList string in CSV to array
        // For example: "[10;20;30]" => [10, 20, 30]
        if (!is_null($rowKeyList)) {
            $rowKeyListRep = str_replace(["[", "]"], ["", ""], $rowKeyList);
            $rowKeyList = explode(";", $rowKeyListRep);
        } else {
            $rowKeyList = [];
        }
        $hasException = "0";

        // Test putRow
        try {
            /**
             * Container schema
             */
            $propList = [["A_0", \Type::STRING],
                         ["A9", \Type::BOOL],
                         ["za", \Type::DOUBLE]];
            if ($containerType == "GS_CONTAINER_COLLECTION") {
                $containerType = \ContainerType::COLLECTION;
                $rowKey = ["a_9", \Type::INTEGER];
            } else {
                $containerType = \ContainerType::TIME_SERIES;
                $rowKey = ["a_9", \Type::TIMESTAMP];
            }

            // Insert row key property to the first element of columnInfoList property
            $columnInfoList = $propList;
            array_unshift($columnInfoList, $rowKey);
            $containerInfo = new \ContainerInfo(["name" => $containerName,
                                                 "columnInfoArray" => $columnInfoList,
                                                 "type" => $containerType,
                                                 "rowKey" => $rowKeyAssigned]);
            self::$gridstore->dropContainer($containerName);
            $container = self::$gridstore->putContainer($containerInfo,
                                                        $modifiable);
            for ($i = 0; $i < sizeof($rowKeyList); $i++) {
                $container->put([convertData($rowKeyList[$i]),
                                 $stringVal, $boolVal, $doubleVal]);
            }
            $query = $container->query($queryCommand);
            $query->fetch(false);
            self::$gridstore->dropContainer($containerName);
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

