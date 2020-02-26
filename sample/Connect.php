<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::get_default();

    try {
        // (1)Get GridStore object
        // Multicast method
        $gridstore = $factory->get_store(array("notificationAddress" => $argv[1],
                        "notificationPort" => $argv[2],
                        "clusterName" => $argv[3],
                        "user" => $argv[4],
                        "password" => $argv[5]
                    ));

        // Fixed list method
        //$gridstore = $factory->get_store(array("notificationMember" => $argv[1],
        //                "clusterName" => $argv[2],
        //                "user" => $argv[3],
        //                "password" => $argv[4]
        //            ));

        // Provider method
        //$gridstore = $factory->get_store(array("notificationProvider" => $argv[1],
        //                "clusterName" => $argv[2],
        //                "user" => $argv[3],
        //                "password" => $argv[4]
        //            ));

        // (2)When operations such as container creation and acquisition are performed, it is connected to the cluster.
        $gridstore->get_container("containerName");
        echo("Connect to Cluster\n");
        echo("success!\n");
    } catch (GSException $e) {
        echo($e->what()."\n");
        echo($e->get_code()."\n");
    }
?>
