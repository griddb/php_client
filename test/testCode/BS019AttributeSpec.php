<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
if (file_exists('griddb_php_client.php')) {
    // File php wrapper is generated with SWIG 4.0.2 and below
    require_once('griddb_php_client.php');
}
require_once('config.php');
require_once('utility.php');

class BS019AttributeSpec extends TestCase {
    protected static $gridstore;

    public static function setUpBeforeClass(): void {
        $factory = \StoreFactory::getInstance();
        $containerNameList = ["NQR_Col", "NQR_TS", "Con_string", "Con_integer",
                              "Con_long", "Con_timestamp", "Con_timestamp_timeseries",
                              "Con_timeseries", "Con_collection"];
        $containerTypeList = ["string", "timestamp_timeseries", "string",
                              "integer", "long", "timestamp", "timestamp_timeseries",
                              "timestamp_timeseries", "string"];
        try {
            $storeInfo = ["host" => GRIDDB_NOTIFICATION_ADDRESS,
                          "port" => (int)GRIDDB_NOTIFICATION_PORT,
                          "clusterName" => GRIDDB_CLUSTER_NAME,
                          "username" => GRIDDB_USERNAME,
                          "password" => GRIDDB_PASSWORD];

            self::$gridstore = $factory->getStore($storeInfo);

            preSetupContainer(self::$gridstore, $containerNameList, $containerTypeList);
            $timeSeries = self::$gridstore->getContainer("NQR_TS");
            $timeSeries->setAutoCommit(false);
            $timeSeries->put([convertData("2018-12-01T10:10:00.000Z"),
                               "name01", true, 8, 90, 10, 0,
                               convertData("9999-12-31T23:59:59.999Z"),
                               23.98, 1211.9232, pack('C*', 65, 66)]);
            $timeSeries->put([convertData("2018-12-01T10:20:00.000Z"),
                               "name01", true, 8, 90, 10, 0,
                               convertData("9999-12-31T23:59:59.999Z"),
                               23.98, 1211.9232, pack('C*', 65, 66)]);
            $timeSeries->put([convertData("2018-12-01T10:30:00.000Z"),
                               "name01", true, 8, 90, 10, 0,
                               convertData("9999-12-31T23:59:59.999Z"),
                               23.98, 1211.9232, pack('C*', 65, 66)]);
            $timeSeries->put([convertData("2018-12-01T10:40:00.000Z"),
                               "name01", true, 8, 90, 10, 0,
                               convertData("9999-12-31T23:59:59.999Z"),
                               23.98, 1211.9232, pack('C*', 65, 66)]);
            $timeSeries->commit();
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

    public function createDropContainer($containerName, $containerInfo) {
        $hasException = "0";
        try {
            self::$gridstore->dropContainer($containerName);
            self::$gridstore->putContainer($containerInfo);
            self::$gridstore->dropContainer($containerName);
        } catch (\GSException $e){
            for ($i= 0; $i < $e->getErrorStackSize(); $i++) {
                echo("\n[$i]\n");
                echo($e->getErrorCode($i)."\n");
                echo($e->getLocation($i)."\n");
                echo($e->getErrorMessage($i)."\n");
                $hasException = "1";
            }
        } catch (\Exception $e1) {
            echo($e1."\n");
            $hasException = "1";
        }
        return $hasException;
    }

    /**
     * Provider Data
     */
    public function providerDataTest() {
        return getTestData("test/resource/UC-017-Attribute.csv");
    }

    /**
     * @dataProvider providerDataTest
     */
    public function testAttribute($testId, $queryStr, $containerName,
                                  $columnInfoList, $rowKeyAssigned,
                                  $rowExpirationTime, $rowExpirationTimeUnit,
                                  $divisionCount, $containerType,
                                  $attribute, $expectedOutput)
    {
        echo sprintf("Test case: %s\n", $testId);
        echo sprintf(" Expected has exception = %s\n", $expectedOutput);
        $exceedString = file_get_contents('test/resource/exceedSizeColName.txt');

        try {
            // Container schema:
            $propList = [["A_0", \Type::STRING], ["A9", \Type::BOOL], ["za", \Type::DOUBLE]];

            $propList0 = [["bnfb", \Type::TIMESTAMP], ["ccvb", \Type::INTEGER]];

            $propList1 = [["aaaaaaaaaaaaaa", \Type::STRING]];

            $propList2 = [["a1", \Type::STRING], ["a2", -1]];

            $propList3 = [["a1", \Type::STRING], ["a1", 20]];

            $propList4 = [["a1", \Type::STRING], ["a1", \Type::BOOL]];

            $propList5 = [["", \Type::STRING]];

            $propList6 = [["a1", \Type::STRING], ["a1", 1.1]];

            $propList7 = [["a1", \Type::STRING], [$exceedString, \Type::STRING]];

            $propList8 = [["a1", \Type::STRING], ["a 0", \Type::INTEGER]];

            $propList9 = [["a1", \Type::STRING], ["a_0", "GS_TYPE_STRING"]];

            $propList10 = [["a1", \Type::STRING], ["!", \Type::BOOL]];

            $propList11 = [["a1", \Type::STRING], ["/", \Type::BYTE]];

            $propList12 = [["a1", \Type::STRING], ["⇿", \Type::SHORT]];

            $propList13 = [["a1", \Type::STRING], ["1_a", \Type::INTEGER]];

            $propList14 = [["A_0", \Type::STRING]];

            $hasException = "0";
            $partitionController = self::$gridstore->partitionController;
            if ($containerName == "" || $containerName == NULL) {
                if ($containerType == "collection") {
                    $container = self::$gridstore->getContainer("NQR_Col");
                } else {
                    $container = self::$gridstore->getContainer("NQR_TS");
                }
            } else {
                $container = self::$gridstore->getContainer($containerName);
            }

            switch ($attribute) {
                case "container_type":
                    $type = $container->type;
                    break;
                case "container_type(set)":
                    $container->type = \ContainerType::COLLECTION;
                    break;
                case "PartitionInfo:partition_count":
                    $count = $partitionController->partitionCount;
                    break;
                case "PartitionInfo:partition_count(set)":
                    $partitionController->partitionCount = 2;
                    break;
                case "RowSet:type;RowSet:size":
                    $container = self::$gridstore->getContainer("NQR_Col");
                    $query = $container->query($queryStr);
                    $rowSet = $query->fetch();
                    $type = $rowSet->type;
                    $size = $rowSet->size;
                    break;
                case "RowSet:type;RowSet:size(set)":
                    $container = self::$gridstore->getContainer("NQR_Col");
                    $query = $container->query($queryStr);
                    $rowSet = $query->fetch();
                    $rowSet->type = 10; // dummy data
                    $rowSet->size = 2; // dummy data
                    break;
                case "ContainerInfo:name(set/get)":
                    if ($containerName == "16KB_string" ||
                            $containerName == "exceed_16KB_string") {
                        $containerNameTmp = convertExtraData($containerName);
                    } else {
                        $containerNameTmp = convertData($containerName);
                    }
                    $conInfo = new \ContainerInfo(["name" => "dummy",
                                                   "columnInfoArray" => [["name", \Type::STRING],
                                                                         ["status", \Type::BOOL],
                                                                         ["count", \Type::LONG],
                                                                         ["lob", \Type::BLOB]],
                                                   "type" => \ContainerType::COLLECTION,
                                                   "rowKey" => true]);
                    $name = $conInfo->name;
                    $conInfo->name = $containerNameTmp;
                    // Create/drop container
                    $hasException = $this->createDropContainer($containerNameTmp, $conInfo);
                    break;
                case "ContainerInfo:column_info_list(set/get)":
                    $propListStr = "\$propList = \$propList";
                    $propListStr.=$columnInfoList;
                    $propListStr.=";";
                    eval($propListStr);
                    $conInfo = new \ContainerInfo(["name" => "Con_collection",
                                                   "columnInfoArray" => [["name", \Type::STRING],
                                                                         ["status", \Type::BOOL],
                                                                         ["count", \Type::LONG],
                                                                         ["lob", \Type::BLOB]],
                                                   "type" => \ContainerType::COLLECTION,
                                                   "rowKey" => true]);
                    $infoList = $conInfo->columnInfoArray;
                    $conInfo->columnInfoArray = $propList;
                    // Create/drop Con_collection container
                    $hasException = $this->createDropContainer("Con_collection", $conInfo);
                    break;
                case "ContainerInfo:row_key(set/get)":
                    $rowKeyAssigned = convertData($rowKeyAssigned);
                    if ($rowKeyAssigned === 0) {
                        $rowKeyAssigned = false;
                    } else if ($rowKeyAssigned == 1) {
                        $rowKeyAssigned = true;
                    }
                    $conInfo = new \ContainerInfo(["name" => "dummy",
                                                   "columnInfoArray" => [["name", \Type::STRING],
                                                                         ["status", \Type::BOOL],
                                                                         ["count", \Type::LONG],
                                                                         ["lob", \Type::BLOB]],
                                                   "type" => \ContainerType::COLLECTION,
                                                   "rowKey" => true]);
                    $rowKey = $conInfo->rowKey;
                    $conInfo->rowKey = $rowKeyAssigned;
                    break;
                case  "ExpirationInfo:time(set/get)":
                    $rowExpirationTime = convertData($rowExpirationTime);
                    $expirationInfo = new \ExpirationInfo(100, \TimeUnit::MINUTE, 10); //dummy data
                    $time = $expirationInfo->time;
                    $expirationInfo->time = $rowExpirationTime;
                    $conInfo = new \ContainerInfo(["name" => "dummy",
                                                   "columnInfoArray" => [["bnfb", \Type::TIMESTAMP],
                                                                         ["status", \Type::BOOL],
                                                                         ["count", \Type::LONG],
                                                                         ["lob", \Type::BLOB]],
                                                   "type" => \ContainerType::TIME_SERIES,
                                                   "rowKey" => true,
                                                   "expiration" => $expirationInfo]);
                    $tmp = $conInfo->expiration;
                    $conInfo->expiration = $expirationInfo;
                    // Create/drop dummy container
                    $hasException = $this->createDropContainer("dummy", $conInfo);
                    break;
                case  "ExpirationInfo:unit(set/get)":
                    $rowExpirationTimeUnit = convertData($rowExpirationTimeUnit);
                    $expirationInfo = new \ExpirationInfo(100, \TimeUnit::MINUTE, 10); //dummy data
                    $time = $expirationInfo->unit;
                    $expirationInfo->unit = $rowExpirationTimeUnit;
                    $conInfo = new \ContainerInfo(["name" => "dummy",
                                                   "columnInfoArray" => [["bnfb", \Type::TIMESTAMP],
                                                                         ["status", \Type::BOOL],
                                                                         ["count", \Type::LONG],
                                                                         ["lob", \Type::BLOB]],
                                                   "type" => \ContainerType::TIME_SERIES,
                                                   "rowKey" => true,
                                                   "expiration" => $expirationInfo]);
                    // Create/drop dummy container
                    $hasException = $this->createDropContainer("dummy", $conInfo);
                    break;
                case  "ExpirationInfo:division_count(set/get)":
                    $divisionCount = convertData($divisionCount);
                    $expirationInfo = new \ExpirationInfo(100, \TimeUnit::MINUTE, 10); //dummy data
                    $tmp = $expirationInfo->divisionCount;
                    $expirationInfo->divisionCount = $divisionCount;
                    $conInfo = new \ContainerInfo(["name" => "dummy",
                                                   "columnInfoArray" => [["bnfb", \Type::TIMESTAMP],
                                                                         ["status", \Type::BOOL],
                                                                         ["count", \Type::LONG],
                                                                         ["lob", \Type::BLOB]],
                                                   "type" => \ContainerType::TIME_SERIES,
                                                   "rowKey" => true,
                                                   "expiration" => $expirationInfo]);
                    // Create/drop dummy container
                    $hasException = $this->createDropContainer("dummy", $conInfo);
                    break;
                case "ContainerInfo:type(set/get)":
                    $containerType = convertData($containerType);
                    $conInfo = new \ContainerInfo(["name" => "dummy",
                                                   "columnInfoArray" => [["bnfb", \Type::TIMESTAMP],
                                                                         ["status", \Type::BOOL],
                                                                         ["count", \Type::LONG],
                                                                         ["lob", \Type::BLOB]],
                                                   "type" => \ContainerType::TIME_SERIES,
                                                   "rowKey" => true]);
                    $tmp = $conInfo->type;
                    $conInfo->type = $containerType;
                    // Create/drop dummy container
                    $hasException = $this->createDropContainer("dummy", $conInfo);
                    break;
                default:
                    $hasException ="1";
                    break;
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

