<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
require_once ('griddb_php_client.php');
require_once('config.php');
require_once('utility.php');

class BS021KeywordParametersSpec extends TestCase {
    // Test keyword parameter with valid input
    public function testKeywordParametersWithValidInput()
    {
        echo("Test case for hash parameters with valid input\n");
        echo(" Expected has exception = 0\n");
        $hasException = "0";
        try {
            $factory = \StoreFactory::getInstance();
            $storeInfo = ["host" => GRIDDB_NOTIFICATION_ADDRESS,
                          "port" => (int)GRIDDB_NOTIFICATION_PORT,
                          "clusterName" => GRIDDB_CLUSTER_NAME,
                          "username" => GRIDDB_USERNAME,
                          "password" => GRIDDB_PASSWORD];

            $gridstore = $factory->getStore($storeInfo);

            // Create a collection container with rowkey is string
            $containerInfo = new \ContainerInfo(["name" => "col01",
                                                 "columnInfoArray" => [["name", \Type::STRING],
                                                                       ["status", \Type::BOOL],
                                                                       ["count", \Type::LONG],
                                                                       ["lob", \Type::BLOB]],
                                                 "type" => \ContainerType::COLLECTION,
                                                 "rowKey" => true]);

            $gridstore->dropContainer("col01");
            $col = $gridstore->putContainer($containerInfo, true);
            $col->setAutoCommit(false);
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
        $this->assertEquals("0", $hasException);
    }

    // Test keyword parameter with invalid input
    public function testKeywordParametersWithInvalidInput()
    {
        echo("Test case for hash parameters with invalid input\n");
        echo(" Expected has exception = 1\n");
        $hasException = "0";
        try {
            $factory = \StoreFactory::getInstance();
            $storeInfo = ["host" => GRIDDB_NOTIFICATION_ADDRESS,
                          "port" => (int)GRIDDB_NOTIFICATION_PORT,
                          "clusterName" => GRIDDB_CLUSTER_NAME,
                          "username" => GRIDDB_USERNAME,
                          "password" => GRIDDB_PASSWORD];

            $gridstore = $factory->getStore($storeInfo);

            // Create a collection container with invalid input
            $containerInfo = new \ContainerInfo(["col02",
                                                 [["name", \Type::STRING],
                                                  ["status", \Type::BOOL],
                                                  ["count", \Type::LONG],
                                                  ["lob", \Type::BLOB]],
                                                 \ContainerType::COLLECTION,
                                                 true]);
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
        $this->assertEquals("1", $hasException);
    }
}
?>

