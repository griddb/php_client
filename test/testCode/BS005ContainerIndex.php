<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
require_once ('griddb_php_client.php');
require_once('config.php');
require_once('utility.php');

class BS005ContainerIndex extends TestCase {
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
        return getTestData("test/resource/BS-005-Container_index.csv");
    }

    /**
     * @dataProvider providerDataTest
     */
    public function testContainerIndex($testId, $containerType, $columnInfo,
                                        $indexType, $expectedOutput)
    {
        echo sprintf("Test case: %s put %s:\n", $testId, $containerType);
        echo sprintf(" Expected has exception = %s\n", $expectedOutput);

        $modifiable = true;
        $rowKeyAssigned = true;

        // Convert data test from string type in CSV to other data type
        $indexType = convertData($indexType);
        if (strpos($columnInfo, ":") !== false) {
            $columnInfo = convertStrToRow($columnInfo);
        } else {
            $columnInfo = [$columnInfo];
        }
        $hasException = "0";

        try {
            if ($containerType == "GS_CONTAINER_COLLECTION") {
                $containerName = "col01Collection";
                $columnInfoList = [["A", \Type::STRING],
                                   ["B", \Type::TIMESTAMP],
                                   ["Z0", \Type::BYTE],
                                   ["D", \Type::SHORT],
                                   ["1_a", \Type::INTEGER],
                                   ["F", \Type::BOOL],
                                   ["G", \Type::FLOAT],
                                   ["H", \Type::DOUBLE],
                                   ["I", \Type::BLOB]];
                $containerType = \ContainerType::COLLECTION;
            } else {
                $containerName = "col01Timeseries";
                $columnInfoList = [["A", \Type::TIMESTAMP],
                                   ["B", \Type::STRING],
                                   ["Z0", \Type::BYTE],
                                   ["D", \Type::SHORT],
                                   ["1_a", \Type::INTEGER],
                                   ["F", \Type::BOOL],
                                   ["G", \Type::FLOAT],
                                   ["H", \Type::DOUBLE],
                                   ["I", \Type::BLOB]];
                $containerType = \ContainerType::TIME_SERIES;
            }
            $containerInfo = new \ContainerInfo(["name" => $containerName,
                                                 "columnInfoArray" => $columnInfoList,
                                                 "type" => $containerType,
                                                 "rowKey" => $rowKeyAssigned]);
            self::$gridstore->dropContainer($containerName);
            $container = self::$gridstore->putContainer($containerInfo,
                                                        $modifiable);

            // If index type = "" -> it means not set value for index type
            //-> default type will be set for index type
            if ($indexType == "") {
                $container->createIndex($columnInfo[0]);
                $container->dropIndex($columnInfo[0]);
            } else {
                $container->createIndex($columnInfo[0], $indexType);
                $container->dropIndex($columnInfo[0], $indexType);
            }
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

