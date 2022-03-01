<?php
    if (file_exists('griddb_php_client.php')) {
        // File php wrapper is generated with SWIG 4.0.2 and below
        include_once('griddb_php_client.php');
    }

    $factory = StoreFactory::getInstance();

    $containerName = "SamplePHP_RemoveRowByRowKey";
    $rowCount = 5;
    $nameList = ["notebook PC", "desktop PC", "keyboard", "mouse", "printer"];
    $numberList = [108, 72, 25, 45, 62];

    try {
        // Get GridStore object
        $gridstore = $factory->getStore(["host" => $argv[1],
                        "port" => (int)$argv[2],
                        "clusterName" => $argv[3],
                        "username" => $argv[4],
                        "password" => $argv[5]]);

        // Create a collection
        $conInfo = new ContainerInfo(["name" => $containerName,
                                   "columnInfoArray" => [["id", Type::INTEGER],
                                                ["productName", Type::STRING],
                                                ["count", Type::INTEGER]],
                                   "type" => ContainerType::COLLECTION,
                                   "rowKey" => true]);

        $col = $gridstore->putContainer($conInfo);
        echo("Create Collection name=$containerName\n");

        //Register rows with multiple times
        for ($i = 0; $i < $rowCount; $i++) {
            $col->put([$i, $nameList[$i], $numberList[$i]]);
        }
        echo("Sample data generation: Put Rows count=$rowCount\n");

        // Get a row
        // (1)Get the container
        $col1 = $gridstore->getContainer($containerName);
        if ($col1 == null) {
            echo("ERROR Container not found. name=$containerName\n");
        }

        // (2)Delete row by specifying Row key
        $col1->remove(3);
        echo("Delete Row rowkey=3\n");
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
