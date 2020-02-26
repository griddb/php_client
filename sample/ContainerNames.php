<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::get_default();

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

        // Get a list of container names
        // (1)Get partition controller and number of partitions
        $pc = $gridstore->get_partition_controller();
        $pcCount = $pc->get_partition_count();

        // (2)Loop by the number of partitions to get a list of container names
        for ($i = 0; $i < $pcCount; $i++) {
            $nameList = $pc->get_partition_container_names($i, 0);
            $nameCount = sizeof($nameList);
            for ($j = 0; $j < $nameCount; $j++) {
                echo("$nameList[$j]\n");
            }
        }
    } catch (GSException $e) {
        echo($e->what()."\n");
        echo($e->get_code()."\n");
    }
?>
