<?php
    if (file_exists('griddb_php_client.php')) {
        // File php wrapper is generated with SWIG 4.0.2 and below
        include_once('griddb_php_client.php');
    }

    $factory = StoreFactory::getInstance();

    $containerName = "SamplePHP_Index";

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

        $gridstore->putContainer($conInfo);
        echo("Create Collection name=$containerName\n");

        // Get the container
        $col = $gridstore->getContainer($containerName);
        if ($col == null) {
            echo("ERROR Container not found. name=$containerName\n");
        }

        // Create an index
        $col->createIndex("count", IndexType::HASH);
        echo("Create Index\n");
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
