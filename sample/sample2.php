<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::get_default();

    $update = false;

    try{
        #Get GridStore object
        $gridstore = $factory->get_store(array("notificationAddress" => $argv[1],
                        "notificationPort" => $argv[2],
                        "clusterName" => $argv[3],
                        "user" => $argv[4],
                        "password" => $argv[5]
                    ));

        #Create ContainerInfo
        $conInfo = 

        #Create TimeSeries
        $ts = $gridstore->put_container("point01", array(array("timestamp" => GS_TYPE_TIMESTAMP),
                        array("active" => GS_TYPE_BOOL),	        
                        array("voltage" => GS_TYPE_DOUBLE)),
                        GS_CONTAINER_TIME_SERIES);

        #Create and set row data	
        $row = $ts->create_row();
        $row->set_field_by_timestamp(0, TimestampUtils::current());
        $row->set_field_by_bool(1, false);
        $row->set_field_by_double(2, 100);

        #Put row to timeseries with current timestamp
        $ts->put_row($row);

        #Create normal query for range of timestamp from 6 hours ago to now
        $query = $ts->query("select * where timestamp > TIMESTAMPADD(HOUR, NOW(), -6)");
        $rs = $query->fetch($update);

        #Get result
        $rrow = $ts->create_row();
        while ($rs->has_next()){
	        $rs->get_next($rrow);
	        $timestamp = $rrow->get_field_as_timestamp(0);
	        $active = $rrow->get_field_as_bool(1);
	        $voltage = $rrow->get_field_as_double(2);
	        echo 'Time='.$timestamp.' Active=';
	        echo $active ? 'true' : 'false';
	        echo ' Voltage='.$voltage, PHP_EOL;
        }
    } catch(Exception $e){
		echo $e->getMessage(), PHP_EOL;
    }
?>
