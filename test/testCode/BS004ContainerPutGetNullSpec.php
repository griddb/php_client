<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
require_once ('griddb_php_client.php');
require_once('config.php');
require_once('utility.php');

class BS004ContainerPutGetNullSpec extends TestCase {
    protected static $gridstore;
    protected static $containerCol;
    protected static $containerTS;

    public static function setUpBeforeClass(): void {
        $factory = \StoreFactory::getInstance();
        /**
         * Container schema
         */
        $propListCol = [["A_0", \Type::STRING],
                        ["A9", \Type::BOOL],
                        ["za", \Type::DOUBLE]];

        $propListTS = [["A_0", \TYPE::TIMESTAMP],
                       ["A9", \TYPE::BOOL],
                       ["za", \TYPE::LONG]];
        try {
            $storeInfo = ["host" => GRIDDB_NOTIFICATION_ADDRESS,
                          "port" => (int)GRIDDB_NOTIFICATION_PORT,
                          "clusterName" => GRIDDB_CLUSTER_NAME,
                          "username" => GRIDDB_USERNAME,
                          "password" => GRIDDB_PASSWORD];

            self::$gridstore = $factory->getStore($storeInfo);
            self::$gridstore->dropContainer("col");
            self::$gridstore->dropContainer("ts");

            // Create a collection container
            $containerInfoCol = new \ContainerInfo(["name" => "col",
                                                    "columnInfoArray" => $propListCol,
                                                    "type" => \ContainerType::COLLECTION,
                                                    "rowKey" => true]);
            // Create a timeseries container
            $containerInfoTS = new \ContainerInfo(["name" => "ts",
                                                   "columnInfoArray" => $propListTS,
                                                   "type" => \ContainerType::TIME_SERIES,
                                                   "rowKey" => true]);

            self::$containerCol = self::$gridstore->putContainer($containerInfoCol, true);
            self::$containerTS = self::$gridstore->putContainer($containerInfoTS, true);
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

    // Test put null for all fields of row (except rowKey)
    // Collection container and timeseries container
    public function testPutRowNullExceptRowKey() {
        echo("Testcase: BS-004-Container_put_get_null-001:\n");
        echo(" Put null for all fields of row (except rowKey)\n");
        echo sprintf(" Expected has exception = 0\n");
        $hasException = "0";

        try {
            self::$containerCol->put(["name01", null, null]);

            $dateTimeStr = "9999-12-31T23:59:59.999Z";
            $dateTimeObj = convertData($dateTimeStr);
            self::$containerTS->put([$dateTimeObj, null, null]);

            $row = self::$containerCol->get("name01");
            $row = self::$containerTS->get($dateTimeObj);
        }  catch (\GSException $e) {
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

    // Test put null for all fields of row (include rowKey)
    // Collection container
    public function testPutRowNullContainerCol() {
        echo("Test case: BS-004-Container_put_get_null-002: Collection container:\n");
        echo(" Put null for all fields of row (include rowKey)\n");
        echo(" Expected has exception = 1\n");
        $hasException = "0";

        try {
            self::$containerCol->put([null, null, null]);
        }  catch (\GSException $e) {
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
        $this->assertEquals($hasException, "1");
    }

    // Test put null for all fields of row (include rowKey)
    // Timeseries container
    public function testPutRowNullContainerTS() {
        echo("Testcase: BS-004-Container_put_get_null-002: Timeseries container:\n");
        echo(" Put null for all fields of row (include rowKey)\n");
        echo (" Expected has exception = 1\n");
        $hasException = "0";

        try {
            self::$containerTS->put([null, null, null]);
        }  catch (\GSException $e) {
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
        $this->assertEquals($hasException, "1");
    }
}
?>

