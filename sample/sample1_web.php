<?php
    include('griddb_php_client.php');

    $data = "";
    if (isset($_POST["address"]) && isset($_POST["port"]) && isset($_POST["cluster"]) &&
        isset($_POST["user"]) && isset($_POST["password"])) {
    $address  = $_POST["address"];
    $port     = $_POST["port"];
    $cluster  = $_POST["cluster"];
    $user     = $_POST["user"];
    $password = $_POST["password"];

    $factory = StoreFactory::getInstance();

    $blob = pack('C*', 65, 66, 67, 68, 69, 70, 71, 72, 73, 74);
    $update = true;

    try {
        // Get GridStore object
        $gridstore = $factory->getStore(["host" => $address,
                        "port" => (int)$port,
                        "clusterName" => $cluster,
                        "username" => $user,
                        "password" => $password]);

        // Create a collection container
        $conInfo = new ContainerInfo(["name" => "col01",
                                   "columnInfoArray" => [["name", Type::STRING],
                                                        ["status", Type::BOOL],
                                                        ["count", Type::LONG],
                                                        ["lob", Type::BLOB]],
                                   "type" => ContainerType::COLLECTION,
                                   "rowKey" => true]);
        $gridstore->dropContainer("col01");
        $col = $gridstore->putContainer($conInfo);

        // Change auto commit mode to false
        $col->setAutoCommit(false);

        // Set an index on the Row-key Column
        $col->createIndex("name");

        // Set an index on the Column
        $col->createIndex("count");

        // Put row: RowKey is "name01"
        $ret = $col->put(["name01", false, 1, $blob]);
        // Remove row with RowKey "name01"
        $col->remove("name01");

        // Put row: RowKey is "name02"
        $col->put(["name02", false, 1, $blob]);
        $col-> commit();

        $mArray = $col->get("name02");

        // Create normal query
        $query = $col->query("select * where name = 'name02'");

        // Execute query
        $rs = $query->fetch($update);
        while ($rs->hasNext()) {
            $row = $rs->next();
            $row[2] = $row[2] + 1;
            $data .= "Person: name=$row[0] status=".($row[1] ? "true" : "false")
                            ." count=$row[2] lob=$row[3]\n";

            // Update row
            $rs->update($row);
        }

        // End transction
        $col->commit();
    } catch (GSException $e) {
        for ($i= 0; $i < $e->getErrorStackSize(); $i++) {
            $data = "\n[$i]\n";
            $data = $e->getErrorCode($i)."\n";
            $data = $e->getLocation($i)."\n";
            $data = $e->getErrorMessage($i)."\n";
        }
    }
}
?>

<html>
   <body>

      <form action = "<?php $_PHP_SELF ?>" method = "POST">
         address:&nbsp;&nbsp;&nbsp;                                       <input type = "text" size="10" name = "address" /> <br><br>
         port:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type = "text" size="10" name = "port" /> <br><br>
         cluster:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;                           <input type = "text" size="10" name = "cluster" /> <br><br>
         user:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type = "text" size="10" name = "user" /> <br><br>
         password:                                                        <input type = "password" size="10" name = "password" /> <br><br>
                                                                          <br>
                                                                          <input type = "submit" value="submit" style="height:30px; width:90px" />
      </form>

      <p> <?php echo $data ?> </p>

   </body>
</html>
