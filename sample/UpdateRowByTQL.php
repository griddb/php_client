<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::get_default();

    $containerName = "SamplePHP_UpdateRowByTQL";
    $rowCount = 5;
    $nameList = array("notebook PC", "desktop PC", "keyboard", "mouse", "printer");
    $numberList = array(108, 72, 25, 45, 62);
    $rowList = array();

    $queryStr = "SELECT * WHERE id = 4";
    $update = true;

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
        $column0 = array("id" => GS_TYPE_INTEGER);
        $column1 = array("productName" => GS_TYPE_STRING);
        $column2 = array("count" => GS_TYPE_INTEGER);
        $columnInfolist = array($column0, $column1, $column2);
        $col = $gridstore->put_container($containerName, $columnInfolist, GS_CONTAINER_COLLECTION);
        echo("Sample data generation: Create Collection name=$containerName\n");

        // Get names for all columns
        foreach ($column0 as $key => $value) {
            $column0 = $key;
        };
        foreach ($column1 as $key => $value) {
            $column1 = $key;
        };
        foreach ($column2 as $key => $value) {
            $column2 = $key;
        };
        echo("Sample data generation:  column=($column0, $column1, $column2)\n");

        // Create and set row data
        for ($i = 0; $i < $rowCount; $i++) {
            // (1)Create an empty Row object
            $rowList[$i] = $col->create_row();

            // (2)Set the value in the Row object
            $rowList[$i]->set_field_by_integer(0, $i);
            $rowList[$i]->set_field_by_string(1, $nameList[$i]);
            $rowList[$i]->set_field_by_integer(2, $numberList[$i]);
            echo("Sample data generation: row=($i, $nameList[$i], $numberList[$i])\n");
            $col->put_row($rowList[$i]);
        }
        echo("Sample data generation: Put Rows count=$rowCount\n");


        // Update a row
        // (1)Get the container
        $col1 = $gridstore->get_container($containerName);
        if ($col1 == null) {
            echo("ERROR Container not found. name=$containerName\n");
        }

        // (2)Change auto commit mode to false
        $col1->set_auto_commit(false);

        // (3)Execute search with TQL
        $query = $col1->query($queryStr);
        $rs = $query->fetch($update);

        // (4)Get the result
        while ($rs->has_next()) {
            // Create an empty Row object
            $rrow = $col1->create_row();
            // Get the row
            $rs->get_next($rrow);
            // Change the value
            $rrow->set_field_by_integer(2, 325);
            // Update the row
            $rs->update_current($rrow);
        }

        // (5)Commit
        $col1->commit();
        echo("Update row id=4\n");
        echo("success!\n");
    } catch (GSException $e) {
        echo($e->what()."\n");
        echo($e->get_code()."\n");
    }
?>
