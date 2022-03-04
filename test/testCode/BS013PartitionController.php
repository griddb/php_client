<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
if (file_exists('griddb_php_client.php')) {
    // File php wrapper is generated with SWIG 4.0.2 and below
    require_once('griddb_php_client.php');
}
require_once('config.php');
require_once('utility.php');

class BS013PartitionController extends TestCase {
    protected static $gridstore;
    protected static $containerNameCol = "PC_1";
    protected static $containerNameTS = "PC_2";

    public static function setUpBeforeClass(): void {
        $modifiable = true;
        $factory = \StoreFactory::getInstance();
        try {
            $storeInfo = ["host" => GRIDDB_NOTIFICATION_ADDRESS,
                          "port" => (int)GRIDDB_NOTIFICATION_PORT,
                          "clusterName" => GRIDDB_CLUSTER_NAME,
                          "username" => GRIDDB_USERNAME,
                          "password" => GRIDDB_PASSWORD];

            self::$gridstore = $factory->getStore($storeInfo);
            /**
             * Container schema
             */
            $columnInfoListCol = [["A", \Type::STRING],
                                  ["B", \Type::TIMESTAMP],
                                  ["a_9", \Type::BYTE],
                                  ["za", \Type::SHORT],
                                  ["1_a", \Type::INTEGER],
                                  ["F", \Type::BOOL],
                                  ["G", \Type::FLOAT],
                                  ["H", \Type::DOUBLE],
                                  ["I", \Type::BLOB]];

            $columnInfoListTS = [["A", \Type::TIMESTAMP],
                                 ["B", \Type::STRING],
                                 ["a_9", \Type::BYTE],
                                 ["za", \Type::SHORT],
                                 ["1_a", \Type::INTEGER],
                                 ["F", \Type::BOOL],
                                 ["G", \Type::FLOAT],
                                 ["H", \Type::DOUBLE],
                                 ["I", \Type::BLOB]];

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

            self::$gridstore->dropContainer(self::$containerNameCol);
            self::$gridstore->dropContainer(self::$containerNameTS);
            self::$gridstore->putContainer($containerInfoCol, $modifiable);
            self::$gridstore->putContainer($containerInfoTS, $modifiable);
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
    public function providerDataTestGetContainerCount() {
        return getTestDataFilter("test/resource/BS-013-PartitionController.csv", 0, 3);
    }

    /**
     * @dataProvider providerDataTestGetContainerCount
     */
    public function testGetContainerCount($testId, $partitionIndex, $start,
                                          $limit,$containerName,
                                          $hostname, $expectedOutput)
    {
        echo sprintf("Test case: %s \n", $testId);
        echo (" PartitionController::getContainerCount\n");
        $expectedOutputConvert = convertData($expectedOutput);

        try {
            $partitionController = self::$gridstore->partitionController;
            $retCount = $partitionController->getContainerCount(convertData($partitionIndex));

        } catch (\GSException $e) {
            echo("Throw $e\n");
            for ($i= 0; $i < $e->getErrorStackSize(); $i++) {
                echo("\n[$i]\n");
                echo($e->getErrorCode($i)."\n");
                echo($e->getLocation($i)."\n");
                echo($e->getErrorMessage($i)."\n");
            }
            $retCount = -1;
            $this->assertLessThanOrEqual($retCount, $expectedOutputConvert);
        } catch (\Exception $e1) {
            echo($e1."\n");
            $retCount = -1;
            $this->assertLessThanOrEqual($retCount, $expectedOutputConvert);
        }
        //Assert result
        $this->assertLessThanOrEqual($retCount, $expectedOutputConvert);
    }

    /**
     * Provider Data
     */
    public function providerDataTestGetContainerNames() {
        return getTestDataFilter("test/resource/BS-013-PartitionController.csv", 3, 12);
    }

    /**
     * @dataProvider providerDataTestGetContainerNames
     */
    public function testGetContainerNames($testId, $partitionIndex, $start,
                                          $limit,$containerName,
                                          $hostname, $expectedOutput)
    {
        echo sprintf("Test case: %s \n", $testId);
        echo (" PartitionController::getContainerNames\n");
        $expectedOutputConvert = convertData($expectedOutput);

        try {
            $partitionController = self::$gridstore->partitionController;
            $containerNames = $partitionController->getContainerNames(convertData($partitionIndex),
                                                                convertData($start),
                                                                convertData($limit));
            $length = sizeof($containerNames);
        } catch (\GSException $e) {
            echo("Throw $e\n");
            for ($i= 0; $i < $e->getErrorStackSize(); $i++) {
                echo("\n[$i]\n");
                echo($e->getErrorCode($i)."\n");
                echo($e->getLocation($i)."\n");
                echo($e->getErrorMessage($i)."\n");
            }
            $length = -1;
            $this->assertLessThanOrEqual($length, $expectedOutputConvert);
        } catch (\Exception $e1) {
            echo($e1."\n");
            $length = -1;
            $this->assertLessThanOrEqual($length, $expectedOutputConvert);
        }
        //Assert result
        $this->assertLessThanOrEqual($length, $expectedOutputConvert);
    }

    /**
     * Provider Data
     */
    public function providerDataTestGetPartitionIndexOfContainer() {
        return getTestDataFilter("test/resource/BS-013-PartitionController.csv", 13, 14);
    }

    /**
     * @dataProvider providerDataTestGetPartitionIndexOfContainer
     */
    public function testGetPartitionIndexOfContainer($testId, $partitionIndex,
                                                     $start, $limit,$containerName,
                                                     $hostname, $expectedOutput)
    {
        echo sprintf("Test case: %s \n", $testId);
        echo (" PartitionController::getPartitionIndexOfContainer\n");
        $expectedOutputConvert = convertData($expectedOutput);

        try {
            $partitionController = self::$gridstore->partitionController;
            $index = $partitionController->getPartitionIndexOfContainer(convertData($containerName));
        } catch (\GSException $e) {
            echo("Throw $e\n");
            for ($i= 0; $i < $e->getErrorStackSize(); $i++) {
                echo("\n[$i]\n");
                echo($e->getErrorCode($i)."\n");
                echo($e->getLocation($i)."\n");
                echo($e->getErrorMessage($i)."\n");
            }
            $index = -1;
            $this->assertLessThanOrEqual($index, $expectedOutputConvert);
        } catch (\Exception $e1) {
            echo($e1."\n");
            $index = -1;
            $this->assertLessThanOrEqual($index, $expectedOutputConvert);
        }
        //Assert result
        $this->assertLessThanOrEqual($index, $expectedOutputConvert);
    }
}
?>

