<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::get_default();

    $containerName = "SamplePHP_BlobData";

    try {
        // Get GridStore object
        $gridstore = $factory->get_store(array("notificationAddress" => $argv[1],
                        "notificationPort" => $argv[2],
                        "clusterName" => $argv[3],
                        "user" => $argv[4],
                        "password" => $argv[5]
                    ));

        // When operations such as container creation and acquisition are performed, it is connected to the cluster.
        $gridstore->get_container("containerName");
        echo("Connect to Cluster\n");

        // Create a collection container
        $col = $gridstore->put_container(
            $containerName,
            array(array("id" => GS_TYPE_INTEGER),
                  array("blob" => GS_TYPE_BLOB)),
            GS_CONTAINER_COLLECTION
        );
        echo("Create Collection name=$containerName\n");

        // Register string data
        // (1)Read string data file
        $blobString = file_get_contents('BlobData.php');

        // (2)Create and set row data
        $row = $col->create_row(); //Create row for refer
        $row->set_field_by_integer(0, 0);
        $row->set_field_by_blob(1, $blobString);

        // (3)Put row
        $col->put_row($row);
        echo("Put Row (Blob)\n");

        // Get string data file from row
        // (1)Create an empty Row object
        $row1 = $col->create_row();

        // (2)Specify row key and get row
        $col->get_row_by_integer(0, false, $row1);
        $blob = $row1->get_field_as_blob(1);
        echo("Get Row (Blob string data=$blob");
        echo("success!\n");
    } catch (GSException $e) {
        echo($e->what()."\n");
        echo($e->get_code()."\n");
    }
?>
