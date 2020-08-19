<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
require_once ('griddb_php_client.php');
require_once('config.php');
require_once('utility.php');

class BS015ContainerInfoSetGetSpec extends TestCase {
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
        return getTestData("test/resource/BS-015-ContainerInfo_set_get.csv");
    }

    /**
     * @dataProvider providerDataTest
     */
    public function testContainerInfoSetGet($testId, $containerType,
                                            $containerName, $containerType1,
                                            $rowKeyAssigned, $columnInfoList,
                                            $expirationInfo, $expectedOutput)
    {
        echo sprintf("Test case: %s put %s:\n", $testId, $containerType);
        echo sprintf(" Put container name: %s\n", $containerName);
        echo sprintf(" Expect has exception = %s\n", $expectedOutput);

        // Convert data test from string type in CSV to other data type
        $containerType  = convertData($containerType);
        $rowKeyAssigned = convertData($rowKeyAssigned);

        // Convert $expirationInfo string in CSV to array
        // For example: "[10;20;30]" => [10, 20, 30]
        $expirationInfo = str_replace(["[", "]"], ["", ""], $expirationInfo);
        $expirationInfo = explode(";", $expirationInfo);
        $hasException = "0";

        // Convert $columnInfoList string in CSV to array
        // For example: [[A_0:GS_TYPE_STRING];[A9:GS_TYPE_BOOL];[za:GS_TYPE_DOUBLE]]
        // => [[A_0,GS_TYPE_STRING],[A9,GS_TYPE_BOOL],[za,GS_TYPE_DOUBLE]]
        $columnInfoList =  str_replace(["[", "]"], ["", ""], $columnInfoList);
        $columnInfoList = explode(";", $columnInfoList);
        $columnInfoArray = [];
        for ($i = 0; $i < sizeof($columnInfoList); $i++) {
            $columnInfoArray[$i] = convertStrToRow($columnInfoList[$i]);
        }
        try {
            /**
             * Container schema
             */
            $propList = [["A_0", \Type::STRING],
                         ["A9", \Type::BOOL],
                         ["za", \Type::DOUBLE]];
            $expirationInfoObj = new \ExpirationInfo(convertData($expirationInfo[0]),
                                                     convertData($expirationInfo[1]),
                                                     convertData($expirationInfo[2]));
            $containerInfo = new \ContainerInfo(["name" => $containerName,
                                                 "columnInfoArray" => $propList,
                                                 "type" => $containerType,
                                                 "rowKey" => true]);
            // Check ContainerInfo.name
            $containerInfo->name = $containerName;
            $name = $containerInfo->name;
            $this->assertEquals($name, $containerName);

            // Check ContainerInfo.type
            $containerInfo->type = $containerType;
            $type = $containerInfo->type;
            $this->assertEquals($type, $containerType);

            // Check ContainerInfo.rowKey
            $containerInfo->rowKey = $rowKeyAssigned;
            $rowKeyAssign = $containerInfo->rowKey;
            $this->assertEquals($rowKeyAssign, $rowKeyAssigned);

            // Check ContainerInfo.expiration
            $containerInfo->expiration = $expirationInfoObj;
            $expirationInfoTmp = $containerInfo->expiration;
            $this->assertEquals($expirationInfoObj->divisionCount,
                                $expirationInfoTmp->divisionCount);
            $this->assertEquals($expirationInfoObj->unit,
                                $expirationInfoTmp->unit);
            $this->assertEquals($expirationInfoObj->time,
                                $expirationInfoTmp->time);

            // Check ContainerInfo.columnInfoArray
            $containerInfo->columnInfoArray = $columnInfoArray;
            $columnInfoArrayTmp = $containerInfo->columnInfoArray;
            $this->assertEquals($columnInfoArray, $columnInfoArrayTmp);
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

