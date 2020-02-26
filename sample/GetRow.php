<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::get_default();

    $containerName = "SamplePHP_GetRow";
    $rowCount = 5;
    $nameList = array("notebook PC", "desktop PC", "keyboard", "mouse", "printer");
    $numberList = array(108, 72, 25, 45, 62);
    $rowList = array();
    $rowList1 = array();
    $id = array();
    $productName = array();
    $count = array();

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
                  array("productName" => GS_TYPE_STRING),
                  array("count" => GS_TYPE_INTEGER)),
            GS_CONTAINER_COLLECTION
        );
        echo("Sample data generation: Create Collection name=$containerName\n");

        // Create and set row data
        for ($i = 0; $i < $rowCount; $i++) {
            // (1)Create an empty Row object
            $rowList[$i] = $col->create_row();

            // (2)Set the value in the Row object
            $rowList[$i]->set_field_by_integer(0, $i);
            $rowList[$i]->set_field_by_string(1, $nameList[$i]);
            $rowList[$i]->set_field_by_integer(2, $numberList[$i]);
            $col->put_row($rowList[$i]);
        }
        echo("Sample data generation: Put Rows count=$rowCount\n");

        // Get a row
        // (1)Get the container
        $col1 = $gridstore->get_container($containerName);
        if ($col1 == null) {
            echo("ERROR Container not found. name=$containerName\n");
        }

        for ($i = 0; $i < $rowCount; $i++) {
            // (2)Create an empty Row object
            $rowList1[$i] = $col1->create_row();

            // (3)Specify row key and get row
            $col1->get_row_by_integer($i, false, $rowList1[$i]);

            //(4)Get value from Row
            $id[$i] = $rowList1[$i]->get_field_as_integer(0);
            $productName[$i] = $rowList1[$i]->get_field_as_string(1);
            $count[$i] = $rowList1[$i]->get_field_as_integer(2);
        }

        echo("Get Row (id=$id[0], productName=$productName[0], count=$count[0])\n");
        echo("success!\n");
    } catch (GSException $e) {
        echo($e->what()."\n");
        echo($e->get_code()."\n");
    }
?>
