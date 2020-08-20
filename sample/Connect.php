<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::getInstance();

    try {
        // (1)Get GridStore object
        // Multicast method
        $gridstore = $factory->getStore(["host" => $argv[1],
                        "port" => (int)$argv[2],
                        "clusterName" => $argv[3],
                        "username" => $argv[4],
                        "password" => $argv[5]]);

        // Fixed list method
//        $gridstore = $factory->getStore(array("notificationMember" => $argv[1],
//                        "clusterName" => $argv[2],
//                        "username" => $argv[3],
//                        "password" => $argv[4]
//                    ));

        // Provider method
//        $gridstore = $factory->getStore(array("notificationProvider" => $argv[1],
//                        "clusterName" => $argv[2],
//                        "username" => $argv[3],
//                        "password" => $argv[4]
//                    ));

        // (2)When operations such as container creation and acquisition are performed, it is connected to the cluster.
        $gridstore->getContainer("containerName");
        echo("Connect to Cluster\n");
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