<?php
    include('griddb_php_client.php');

    $factory = StoreFactory::getInstance();

    $containerName = "SamplePHP_RowExpiration";

    try {
        // Get GridStore object
        $gridstore = $factory->getStore(["host" => $argv[1],
                        "port" => (int)$argv[2],
                        "clusterName" => $argv[3],
                        "username" => $argv[4],
                        "password" => $argv[5]]);

        // Set row expiration release
        $timeProp = new ExpirationInfo(100, TimeUnit::DAY, 5);

        // Create a time series container
        $conInfo = new ContainerInfo(["name" => $containerName,
                                   "columnInfoArray" => [["date", Type::TIMESTAMP],
                                                ["value", Type::DOUBLE]],
                                   "type" => ContainerType::TIME_SERIES,
                                   "rowKey" => true,
                                   "expiration" => $timeProp]);
        $ts = $gridstore->putContainer($conInfo);

        // Display attribute name, type and rowKey for ContainerInfo
        $conInformation = $gridstore->getContainerInfo($containerName);
        echo("ContainerInfo: name = ".$conInformation->name.
                ", type = ".(($conInformation->type) ? "TIME_SERIES" : "COLLECTION").
                ", rowKey = ".(($conInformation->rowKey) ? "true" : "false")."\n");

        // Display atribute time, unit, divisionCount for ExpirationInfo
        $expirationInfo = $conInformation->expiration;
        echo("ExpirationInfo: time = ".$expirationInfo->time.
                " , unit = ".$expirationInfo->unit.
                ", divisionCount = ".$expirationInfo->divisionCount."\n");

        echo("Create TimeSeries & Set Row Expiration name = $containerName\n");
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
