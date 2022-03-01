<?php
    if (file_exists('griddb_php_client.php')) {
        // File php wrapper is generated with SWIG 4.0.2 and below
        include_once('griddb_php_client.php');
    }

    $factory = StoreFactory::getInstance();

    try {
        // Get GridStore object
        $gridstore = $factory->getStore(["host" => $argv[1],
                        "port" => (int)$argv[2],
                        "clusterName" => $argv[3],
                        "username" => $argv[4],
                        "password" => $argv[5]]);

        // Create a time series container
        $conInfo = new ContainerInfo(["name" => "point01",
                                   "columnInfoArray" => [["timestamp", Type::TIMESTAMP],
                                                        ["active", Type::BOOL],
                                                        ["voltage", Type::DOUBLE]],
                                   "type" => ContainerType::TIME_SERIES,
                                   "rowKey" => true]);

        $ts = $gridstore->putContainer($conInfo);

        // Put row to timeseries with current timestamp
        $now = new DateTime("now", new DateTimeZone("UTC"));
        $ts->put([$now, false, 100]);

        // Create normal query for range of timestamp from 6 hours ago to now
        $query = $ts->query("select * where timestamp > TIMESTAMPADD(HOUR, NOW(), -6)");
        $rs = $query->fetch();

        while ($rs->hasNext()) {
            $data = $rs->next();
            $dateTime = $data[0]->format("Y-m-d H:i:s.u");
            $active = $data[1] ? "true" : "false";
            $voltage = $data[2];
            echo("Time=$dateTime Active=$active Voltage =$voltage\n");
        }

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
