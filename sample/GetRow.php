<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::getInstance();

    $containerName = "SamplePHP_GetRow";

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
        echo("Create Collection name=$containerName\n");

        //Register the row
        $col->put([0, "notebook PC", 108]);

        // Get a row
        // (1)Get the container
        $col1 = $gridstore->getContainer($containerName);
        if ($col == null) {
            echo("ERROR Container not found. name=$containerName\n");
        }

        // (2)Get the value from row
        $row = $col1->get(0);
        echo("Get Row (id = $row[0], productName = $row[1], count = $row[2])\n");
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
