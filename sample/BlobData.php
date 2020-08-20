<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::getInstance();

    $containerName = "SamplePHP_BlobData";

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
                                                ["blob", Type::BLOB]],
                                   "type" => ContainerType::COLLECTION,
                                   "rowKey" => true]);

        $col = $gridstore->putContainer($conInfo);
        echo("Create Collection name=$containerName\n");

        // Register string data
        // (1)Get contents of a file into a string
        $filename = "sample/BlobData.php";
        $handle = fopen($filename, "rb");
        $blobString = fread($handle, filesize($filename));
        fclose($handle);

        //(2)Register a row
        $col->put([0, $blobString]);
        echo("Put Row (Blob)\n");

        // Get string data file from row
        $row = $col->get(0);
        echo("Get Row (Blob content: \n$row[1]\nBlob size = ".strlen($row[1]).")\n");
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
