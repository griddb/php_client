<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::getInstance();

    $containerName = "SamplePHP_Info";

    try {
        // Get GridStore object
        $gridstore = $factory->getStore(["host" => $argv[1],
                        "port" => (int)$argv[2],
                        "clusterName" => $argv[3],
                        "username" => $argv[4],
                        "password" => $argv[5]]);

        // Create a collection container
        $conInfo = new ContainerInfo(["name" => $containerName,
                                   "columnInfoArray" => [["id", Type::INTEGER],
                                                ["productName", Type::STRING],
                                                ["count", Type::INTEGER]],
                                   "type" => ContainerType::COLLECTION,
                                   "rowKey" => true]);

        $col = $gridstore->putContainer($conInfo);
        echo("Sample data generation: Create Collection name=$containerName\n");

        // Get container information
        // (1)Get container information
        $containerInfo = $gridstore->getContainerInfo($containerName);

        // (2)Display container information
        echo("Get containerInfo:\n    name =".$containerInfo->name."\n");

        if ($containerInfo->type == ContainerType::COLLECTION) {
            echo("    type=Collection\n");
        } else {
            echo("    type=Timeseries\n");
        }

        echo("    rowKeyAssigned=". (($containerInfo->rowKey) ? "true" : "false")."\n");

        $count = sizeof($containerInfo->columnInfoArray);
        echo("    columnCount=$count\n");

        for ($i = 0; $i < $count; $i++) {
            echo("    column (".$containerInfo->columnInfoArray[$i][0].", ".$containerInfo->columnInfoArray[$i][1].")\n");
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
