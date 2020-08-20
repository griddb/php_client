<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::getInstance();

    try {
        // Get GridStore object
        $gridstore = $factory->getStore(["host" => $argv[1],
                        "port" => (int)$argv[2],
                        "clusterName" => $argv[3],
                        "username" => $argv[4],
                        "password" => $argv[5]]);

        // Get an array of container names
        // (1)Get partition controller and number of partitions
        $pc = $gridstore->partitionController;
        $pcCount = $pc->partitionCount;

        //(2)Loop by the number of partitions to get an array of container names
        for ($i = 0; $i < $pcCount; $i++) {
            $arrayContainerNames = $pc->getContainerNames($i, 0, -1);
            $nameCount = sizeof($arrayContainerNames);
            for ($j = 0; $j < $nameCount; $j++) {
                echo("$arrayContainerNames[$j]\n");
            }
        }
        echo("success!\n");
    } catch (GSException $e) {
        for ($i= 0; $i < $e->getErrorStackSize(); $i++) {
            echo("\n[$i]\n");
            echo($e->getErrorCode($i)."\n");
            echo($e->getLocation($i)."\n");
            echo($e->getErrorMessage($i)."\n");
        }
    }
?>
