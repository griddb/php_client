<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::getInstance();

    try {
        // Get GridStore object
        $gridstore = $factory->getStore(["host" => $argv[1],
                        "port" => (int)$argv[2],
                        "clusterName" => $argv[3],
                        "username" => $argv[4],
                        "password" => $argv[5]]);

        // Get Timeseries
        // Reuse TimeSeries and data from sample 2
        $ts = $gridstore->getContainer("point01");

        // Create normal query to get all row where active = FAlSE and voltage > 50
        $query = $ts->query("select * from point01 where not active and voltage > 50");
        $rs = $query->fetch();

        // Get result
        while ($rs->hasNext()) {
            $data = $rs->next();
            $dateTime= $data[0];
            $gsTS = TimestampUtils::getTimeMillis($dateTime);
            // Perform aggregation query to get average value
            // during 10 minutes later and 10 minutes earlier from this point
            $aggCommand = "select AVG(voltage) from point01 where timestamp > TIMESTAMPADD(MINUTE, TO_TIMESTAMP_MS($gsTS), -10) AND timestamp < TIMESTAMPADD(MINUTE, TO_TIMESTAMP_MS($gsTS), 10)";
            $aggQuery = $ts->query($aggCommand);
            $aggRs = $aggQuery->fetch();
            while ($aggRs->hasNext()) {
                // Get aggregation result
                $aggResult = $aggRs->next();
                // Convert result to double and print out
                $voltage = $aggResult->get(TYPE::DOUBLE);
                echo sprintf("[Alert] DateTime=%s, Average voltage=%.1lf\n", $data[0]->format('Y-m-d H:i:s.u'), $voltage);
            }
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
