<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::get_default();

    $containerName = "SamplePHP_TQLAggregation";
    $rowCount = 5;
    $nameList = array("notebook PC", "desktop PC", "keyboard", "mouse", "printer");
    $numberList = array(108, 72, 25, 45, 62);
    $rowList = array();

    $queryStr = "SELECT MAX(count)";
    $update = false;

    try{
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
        $col = $gridstore->put_container($containerName, array(array("id" => GS_TYPE_INTEGER),
                  array("productName" => GS_TYPE_STRING),
                  array("count" => GS_TYPE_INTEGER)),
                  GS_CONTAINER_COLLECTION);
        echo("Sample data generation: Create Collection name=$containerName\n");

        // Create and set row data
        for($i = 0; $i < $rowCount; $i++){
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

        // Search by TQL
        // (1)Get the container
        $col1 = $gridstore->get_container($containerName);
        if($col1 == NULL){
            echo("ERROR Container not found. name=$containerName\n");
        }

        // (2)Executing aggregation operation with TQL
        echo("TQL query : $queryStr\n");
        $query = $col1->query($queryStr);
        $rs = $query->fetch($update);

        // (3)Get the result
        while ($rs->has_next()){
        // (4)Get the result of the aggregation operation
            $aggregationResult = $rs->get_next_aggregation();
            $max = $aggregationResult->get_long();
            echo("TQL result: max=$max\n");
        }
        echo("success!\n");

    } catch(GSException $e){
        echo($e->what()."\n");
        echo($e->get_code()."\n");
    }
?>
