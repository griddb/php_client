<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::get_default();

    $containerName = "SamplePHP_TQLTimeseries";
    $rowCount = 4;
    $dateList = array("2018-12-01T10:00:00.000Z", "2018-12-01T10:10:00.000Z", "2018-12-01T10:20:00.000Z", "2018-12-01T10:40:00.000Z");
    $value1List = array(1, 3, 2, 4);
    $value2List = array(10.3, 5.7, 8.2, 4.5);
    $rowList = array();
    $timestamp = array();

    $queryStr1 = "SELECT TIME_AVG(value1)";
    $queryStr2 = "SELECT TIME_NEXT(*, TIMESTAMP('2018-12-01T10:10:00.000Z'))";
    $queryStr3 = "SELECT TIME_INTERPOLATED(value1, TIMESTAMP('2018-12-01T10:30:00.000Z'))";

    $update = false;
    $GS_TIME_STRING_SIZE_MAX = 32;

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
        $columnDate = array("date" => GS_TYPE_TIMESTAMP);
        $columnValue1 = array("value1" => GS_TYPE_INTEGER);
        $columnValue2 = array("value2" => GS_TYPE_DOUBLE);
        $columnInfolist = array($columnDate, $columnValue1, $columnValue2);
        $ts = $gridstore->put_container($containerName, $columnInfolist, GS_CONTAINER_TIME_SERIES);
        echo("Sample data generation: Create Collection name=$containerName\n");

        // Get names for all columns
        foreach ($columnDate as $key => $value) {
            $column0 = $key;
        };
        foreach ($columnValue1 as $key => $value) {
            $column1 = $key;
        };
        foreach ($columnValue2 as $key => $value) {
            $column2 = $key;
        };
        echo("Sample data generation:  column=($column0, $column1, $column2)\n");

        // Create and set row data
        for ($i = 0; $i < $rowCount; $i++) {
            // (1)Create an empty Row object
            $rowList[$i] = $ts->create_row();

            // (2)Parse time string to timestamp
            $timestamp[$i] = TimestampUtils::parse($dateList[$i]);

            // (3)Set column value
            $rowList[$i]->set_field_by_timestamp(0, $timestamp[$i]);
            $rowList[$i]->set_field_by_integer(1, $value1List[$i]);
            $rowList[$i]->set_field_by_double(2, $value2List[$i]);
            echo sprintf("Sample data generation:  row=(%s, %d, %lf)\n", $dateList[$i], $value1List[$i], $value2List[$i]);
            $ts->put_row($rowList[$i]);
        }
        echo("Sample data generation: Put Rows count=$rowCount\n");

        // Aggregation operations specific to time series
        // Get the container
        $ts1 = $gridstore->get_container($containerName);
        if ($ts1 == null) {
            echo("ERROR Container not found. name=$containerName\n");
        }

        // weighted average TIME_AVG
        // (1)Execute aggregation operation in TQL
        echo("TQL query : $queryStr1\n");
        $query = $ts1->query($queryStr1);
        $rs = $query->fetch($update);

        // (2)Get the result
        while ($rs->has_next()) {
            // (3)Get the result of the aggregation operation
            $aggregationResult = $rs->get_next_aggregation();
            $value = $aggregationResult->get_double();
            echo sprintf("TQL result: %lf\n", $value);
        }

        // Time series specific selection operation
        // TIME_NEXT
        //(1)Execute aggregation operation in TQL
        echo("TQL query : $queryStr2\n");
        $query = $ts1->query($queryStr2);
        $rs = $query->fetch($update);

        // (2)Get the result
        while ($rs->has_next()) {
            // Create empty row
            $rrow = $ts1->create_row();
            // Get a row
            $rs->get_next($rrow);
            // Get value
            $date = $rrow->get_field_as_timestamp(0);
            $value1 = $rrow->get_field_as_integer(1);
            $value2 = $rrow->get_field_as_double(2);
            $buf = TimestampUtils::format_time($date, $GS_TIME_STRING_SIZE_MAX);
            echo sprintf("TQL result: row=(%s, %d, %lf)\n", $buf, $value1, $value2);
        }

        // Time series specific interpolation operation
        // TIME_INTERPOLATED
        // (1)Execute aggregation operation in TQL
        echo("TQL query : $queryStr3\n");
        $query = $ts1->query($queryStr3);
        $rs = $query->fetch($update);

        // (2)Get the result
        while ($rs->has_next()) {
            // Create empty row
            $rrow = $ts1->create_row();
            // Get a row
            $rs->get_next($rrow);
            // Get value
            $date = $rrow->get_field_as_timestamp(0);
            $value1 = $rrow->get_field_as_integer(1);
            $value2 = $rrow->get_field_as_double(2);
            $buf = TimestampUtils::format_time($date, $GS_TIME_STRING_SIZE_MAX);
            echo sprintf("TQL result: row=(%s, %d, %lf)\n", $buf, $value1, $value2);
        }
        echo("success!\n");
    } catch (GSException $e) {
        echo($e->what()."\n");
        echo($e->get_code()."\n");
    }
?>
