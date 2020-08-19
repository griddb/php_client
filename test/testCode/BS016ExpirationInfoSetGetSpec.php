<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
require_once ('griddb_php_client.php');
require_once('config.php');
require_once('utility.php');

class BS016ExpirationInfoSetGetSpec extends TestCase {
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
        }
    }

    /**
     * Provider Data
     */
    public function providerDataTest() {
        return getTestData("test/resource/BS-016-ExpirationInfo_set_get.csv");
    }

    /**
     * @dataProvider providerDataTest
     */
    public function testExpirationInfoSetGet($testId, $containerType,
                                            $rowExpirationTime,
                                            $rowExpirationTimeUnit,
                                            $expirationDivisionCount,
                                            $expectedOutput)
    {
        echo sprintf("Test case: %s:\n", $testId);
        echo sprintf(" Expect has exception = %s\n", $expectedOutput);

        // Convert data test from string type in CSV to other data type
        $containerType  = convertData($containerType);
        $rowExpirationTime = convertData($rowExpirationTime);
        $rowExpirationTimeUnit = convertData($rowExpirationTimeUnit);
        $expirationDivisionCount = convertData($expirationDivisionCount);
        $hasException = "0";

        try {
            $expirationInfoObj = new \ExpirationInfo(0, 0, 0);
            // Check ExpirationInfo.time
            $expirationInfoObj->time = $rowExpirationTime;
            $exTime = $expirationInfoObj->time;
            $this->assertEquals($exTime, $rowExpirationTime);

            // Check ExpirationInfo.unit
            $expirationInfoObj->unit = $rowExpirationTimeUnit;
            $exTimeUnit = $expirationInfoObj->unit;
            $this->assertEquals($exTimeUnit, $rowExpirationTimeUnit);

            // Check ExpirationInfo.divisionCount
            $expirationInfoObj->divisionCount = $expirationDivisionCount;
            $exDivisionCount = $expirationInfoObj->divisionCount;
            $this->assertEquals($exDivisionCount, $expirationDivisionCount);
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

