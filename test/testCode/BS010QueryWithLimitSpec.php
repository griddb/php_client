<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
if (file_exists('griddb_php_client.php')) {
    // File php wrapper is generated with SWIG 4.0.2 and below
    require_once('griddb_php_client.php');
}
require_once('config.php');
require_once('utility.php');

class BS010QueryWithLimitSpec extends TestCase {
    protected static $gridstore;
    protected static $containerNameCol = "col01Collection";
    protected static $containerNameTS = "col01Timeseries";

    public static function setUpBeforeClass(): void {
        $factory = \StoreFactory::getInstance();
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

            self::$gridstore->putContainer($containerInfoCol, true);
            self::$gridstore->putContainer($containerInfoTS, true);
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
        }
    }

    /**
     * Provider Data
     */
    public function providerDataTest() {
        return getTestData("test/resource/BS-010-Query-with-limit.csv");
    }

    /**
     * @dataProvider providerDataTest
     */
    public function testQueryWithLimitSpec($testId, $containerType,
                                           $queryStr, $expectedOutput)
    {
        echo sprintf("Test case: %s put %s:\n", $testId, $containerType);
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
            $query->fetch(false);
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

