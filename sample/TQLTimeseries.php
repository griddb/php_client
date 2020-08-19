<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::getInstance();

    $containerName = "SamplePHP_TQLTimeseries";
    $rowCount = 4;
    $dateTimeStrList = ["2018-12-01T10:00:00.000Z", "2018-12-01T10:10:00.000Z", "2018-12-01T10:20:00.000Z", "2018-12-01T10:40:00.000Z"];
    $value1List = [1, 3, 2, 4];
    $value2List = [10.3, 5.7, 8.2, 4.5];
    $dateTimeObjList = [];

    $queryStr1 = "SELECT TIME_AVG(value1)";
    $queryStr2 = "SELECT TIME_NEXT(*, TIMESTAMP('2018-12-01T10:10:00.000Z'))";
    $queryStr3 = "SELECT TIME_INTERPOLATED(value1, TIMESTAMP('2018-12-01T10:30:00.000Z'))";

    try {
        // Get GridStore object
        $gridstore = $factory->getStore(["host" => $argv[1],
                        "port" => (int)$argv[2],
                        "clusterName" => $argv[3],
                        "username" => $argv[4],
                        "password" => $argv[5]]);

        // Create a timeseries
        $conInfo = new ContainerInfo(["name" => $containerName,
                                   "columnInfoArray" => [["date", Type::TIMESTAMP],
                                                ["value1", Type::INTEGER],
                                                ["value2", Type::DOUBLE]],
                                   "type" => ContainerType::TIME_SERIES]);

        $ts = $gridstore->putContainer($conInfo);
        echo("Sample data generation: Create Collection name=$containerName\n");
        $col1Name = $conInfo->columnInfoArray[0][0];
        $col2Name = $conInfo->columnInfoArray[1][0];
        $col3Name = $conInfo->columnInfoArray[2][0];
        echo("Sample data generation: column=($col1Name, $col2Name, $col3Name)\n");

        // Convert Datetime string to DateTime object
        $UTCTime = new DateTimeZone("UTC");
        for ($i = 0; $i < $rowCount; $i++) {
            $dateTimeObjList[$i] = new DateTime($dateTimeStrList[$i], $UTCTime);
        }

        // Register rows with multiple times
        for ($i = 0; $i < $rowCount; $i++) {
            $ts->put([$dateTimeObjList[$i], $value1List[$i], $value2List[$i]]);
            echo sprintf("Sample data generation: row $i = (%s, %d, %lf)\n", $dateTimeObjList[$i]->format('Y-m-d H:i:s.u'), $value1List[$i], $value2List[$i]);
        }
        echo("Sample data generation: Put Rows count=$rowCount\n");

        // Aggregation operations specific to time series
        // Get the container
        // (1)Get the container
        $ts1 = $gridstore->getContainer($containerName);
        if ($ts1 == null) {
            echo("ERROR Container not found. name=$containerName\n");
        }

        // weighted average TIME_AVG
        // (1)Execute aggregation operation in TQL
        echo("TQL query : $queryStr1\n");
        $query1 = $ts1->query($queryStr1);
        $rs1 = $query1->fetch();

        // (2)Get the result
        while ($rs1->hasNext()) {
            // (3)Get the result of the aggregation operation
            $aggregationResult = $rs1->next();
            $value = $aggregationResult->get(TYPE::DOUBLE);
            echo sprintf("TQL result: %lf\n", $value);
        }

        // Time series specific selection operation
        // TIME_NEXT
        //(1)Execute aggregation operation in TQL
        echo("TQL query : $queryStr2\n");
        $query2 = $ts1->query($queryStr2);
        $rs2 = $query2->fetch();

        // (2)Get the result
        while ($rs2->hasNext()) {
            $row2 = $rs2->next();
            echo sprintf("TQL result: row=(%s, %d, %lf)\n", $row2[0]->format('Y-m-d H:i:s.u'), $row2[1], $row2[2]);
        }

        // Time series specific interpolation operation
        // TIME_INTERPOLATED
        // (1)Execute aggregation operation in TQL
        echo("TQL query : $queryStr3\n");
        $query3 = $ts1->query($queryStr3);
        $rs3 = $query3->fetch();

        // (2)Get the result
        while ($rs3->hasNext()) {
            $row3 = $rs3->next();
            echo sprintf("TQL result: row=(%s, %d, %lf)\n", $row3[0]->format('Y-m-d H:i:s.u'), $row3[1], $row3[2]);
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