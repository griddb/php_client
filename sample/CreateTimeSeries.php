<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::get_default();

    $containerName = "SamplePHP_timeseries1";

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

        // Create a timeseries container
        $col = $gridstore->put_container(
            $containerName,
            array(array("date" => GS_TYPE_TIMESTAMP),
                  array("value" => GS_TYPE_DOUBLE)),
            GS_CONTAINER_TIME_SERIES
        );
        echo("Create Timeseries name=$containerName\n");
        echo("success!\n");
    } catch (GSException $e) {
        echo($e->what()."\n");
        echo($e->get_code()."\n");
    }
?>
