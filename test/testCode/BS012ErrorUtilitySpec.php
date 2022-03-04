<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
if (file_exists('griddb_php_client.php')) {
    // File php wrapper is generated with SWIG 4.0.2 and below
    require_once('griddb_php_client.php');
}
require_once('config.php');
require_once('utility.php');

class BS012ErrorUtilitySpec extends TestCase {
    protected static $gridstore;
    public function testErrorUtility()
    {
        echo("Test exception functions:\n");
        echo (" Expected has exception = 1\n");
        $hasException = "0";
        $factory = \StoreFactory::getInstance();

        try {
            $storeInfo = ["host" => GRIDDB_NOTIFICATION_ADDRESS,
                          "port" => (int)GRIDDB_NOTIFICATION_PORT,
                          "clusterName" => GRIDDB_CLUSTER_NAME,
                          "username" => GRIDDB_USERNAME,
                          "password" => GRIDDB_PASSWORD];

            self::$gridstore = $factory->getStore($storeInfo);
            self::$gridstore->dropContainer("ER_col");

            /**
             * Container schema
             */
            $columnInfoList = [["A", \Type::DOUBLE],
                               ["B", \Type::STRING],
                               ["a_9", \Type::BYTE],
                               ["za", \Type::SHORT],
                               ["1_a", \Type::INTEGER],
                               ["F", \Type::BOOL],
                               ["G", \Type::FLOAT],
                               ["H", \Type::TIMESTAMP],
                               ["I", \Type::BLOB]];
            // Create a collection container
            $containerInfo = new \ContainerInfo(["name" => "ER_col",
                                                 "columnInfoArray" => $columnInfoList,
                                                 "type" => \ContainerType::COLLECTION,
                                                 "rowKey" => true]);
            self::$gridstore->putContainer($containerInfo, true);
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

