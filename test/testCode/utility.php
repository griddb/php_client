<?php
function getTestData($filePath) {
    $file = file_get_contents($filePath, "r");
    $index = 0;
    foreach (explode("\n", $file, -1) as $line) {
        if ($index != 0) {
            $data[] = str_getcsv($line, ",");
        } else {
            $index = 1;
        }
    }
    return $data;
}

// Convert string to bool
function convertStrToBool($str) {
    if ($str == 'True' || $str == 'TRUE' || $str == 'true'):
        $ret = True;
    elseif ($str == 'False'|| $str == 'FALSE' || $str == 'false'):
        $ret = False;
    elseif ($str == 'NULL' || $str == 'Null' || $str == 'null' || $str == 'None'):
        $ret = NULL;
    else:
        $ret = $str;
    endif;
    return $ret;
}

// Convert string to interger or float number
function convertStrToNumber($str) {
    $pos = strpos($str, ".");
    // The !== operator can also be used.  Using != would not work as expected
    // because if the position of '.' is 0. The statement (0 != false) evaluates
    // to false.
    if ($pos !== false) {
        $number = (float) $str;
    } else {
        $number = (int) $str;
    }
    return $number;
}

// Check if a string is DateTime
function isDateTime($str) {
    if (DateTime::createFromFormat('Y-m-d H:i:s', $str) !== FALSE ||
            DateTime::createFromFormat('Y-m-d H:i:s.u', $str) !== FALSE ||
            DateTime::createFromFormat('Y-m-d\TH:i:s.u', $str) !== FALSE ||
            DateTime::createFromFormat('Y-m-d\TH:i:s.uZ', $str) !== FALSE) {
        return true;
    } else {
        return false;
    }
}

// Convert Datetime string to DateTime object
function convertStrToDateTime($str) {
    $UTCTime = new DateTimeZone("UTC");
    $dateTimeObj = new DateTime($str, $UTCTime);
    return $dateTimeObj;
}

// Convert string to blob data
function convertStrToBlob($str) {
    $blobData = "";
    if (!is_null($str)) {
        $strRep = str_replace(["[", "]"], ["", ""], $str);
        $byteDataArray = explode(";", $strRep);
        foreach ($byteDataArray as $value) {
            $mBlob = pack('C*', $value);
            $blobData.= $mBlob;
        }
        return $blobData;
}
}
// Convert data test from string type in CSV to other data type
function convertData($str) {
    if ($str == 'NULL'):
        $ret = NULL;
    elseif ($str == ""):
        $ret = "";
    elseif (is_numeric($str)):
        $ret = convertStrToNumber($str);
    elseif (isDateTime($str)):
        $ret = convertStrToDateTime($str);
    elseif ($str == "True" || $str == "False" || $str == "TRUE" ||
            $str == "FALSE" ||$str == "true" || $str == "false"):
        $ret = convertStrToBool($str);
    elseif (strpos($str, "[") !== false && strpos($str, "]") !== false):
        $ret = convertStrToBlob($str);
    elseif ($str == "GS_TIME_UNIT_YEAR"):
        $ret = \TimeUnit::YEAR;
    elseif ($str == "GS_TIME_UNIT_MONTH"):
        $ret = \TimeUnit::MONTH;
    elseif ($str == "GS_TIME_UNIT_DAY"):
        $ret = \TimeUnit::DAY;
    elseif ($str == "GS_TIME_UNIT_HOUR"):
        $ret = \TimeUnit::HOUR;
    elseif ($str == "GS_TIME_UNIT_MINUTE"):
        $ret = \TimeUnit::MINUTE;
    elseif ($str == "GS_TIME_UNIT_SECOND"):
        $ret = \TimeUnit::SECOND;
    elseif ($str == "GS_TIME_UNIT_MILLISECOND"):
        $ret = \TimeUnit::MILLISECOND;
    elseif ($str == "GS_CONTAINER_COLLECTION"):
        $ret = \ContainerType::COLLECTION;
    elseif ($str =="GS_CONTAINER_TIME_SERIES"):
        $ret = \ContainerType::TIME_SERIES;
    elseif ($str == "GS_INDEX_DEFAULT"):
        $ret = \IndexType::DEFAULT_TYPE;
    elseif ($str == "GS_INDEX_TREE"):
        $ret = \IndexType::TREE;
    elseif ($str == "GS_INDEX_FLAG_HASH"):
        $ret = \IndexType::HASH;
    else:
        $ret = $str;
    endif;
    return $ret;
}

function convertExtraData($str) {
    if ($str == '16KB_string'):
        $ret = file_get_contents('test/resource/longSizeString.txt');
    elseif ($str == '31KB_string'):
        $ret = file_get_contents('test/resource/31k.txt');
    elseif ($str == '128KB_string'):
         $ret = file_get_contents('test/resource/31k.txt');
    elseif ($str == 'exceed_16KB_string' || $str == 'exceed_31KB_string'):
        $ret = file_get_contents('test/resource/exceedSizeString.txt');
    elseif ($str == 'exceed_128KB_string'):
        $ret = file_get_contents('test/resource/exceed128KB.txt');
    else:
        $ret = $str;
    endif;
    return $ret;
}

function convertStrToRow($str) {
    $mArray = explode(":", $str);
    $rowName = $mArray[0];
    switch ($mArray[1]) {
        case "GS_TYPE_STRING":
            $rowType = \Type::STRING;
            break;
        case "GS_TYPE_BOOL":
            $rowType = \Type::BOOL;
            break;
        case "GS_TYPE_BYTE":
            $rowType = \Type::BYTE;
            break;
        case "GS_TYPE_SHORT":
            $rowType = \Type::SHORT;
            break;
        case "GS_TYPE_INTEGER":
            $rowType = \Type::INTEGER;
            break;
        case "GS_TYPE_LONG":
            $rowType = \Type::LONG;
            break;
        case "GS_TYPE_FLOAT":
            $rowType = \Type::FLOAT;
            break;
        case "GS_TYPE_DOUBLE":
            $rowType = \Type::DOUBLE;
            break;
        case "GS_TYPE_TIMESTAMP":
            $rowType = \Type::TIMESTAMP;
            break;
        case "GS_TYPE_BLOB":
            $rowType = \Type::BLOB;
            break;
    }
    return [$rowName, $rowType];
}

function preSetupContainer($gridstore, $containerName, $containerType) {
    $propList = [["A_0", \Type::STRING],
                 ["A9", \Type::BOOL],
                 ["Z0", \Type::BYTE],
                 ["A0Z", \Type::SHORT],
                 ["Z_9", \Type::INTEGER],
                 ["A0", \Type::LONG],
                 ["a_9", \Type::TIMESTAMP],
                 ["Z", \Type::FLOAT],
                 ["za", \Type::DOUBLE],
                 ["az", \Type::BLOB]];
    tearDown($gridstore, $containerName);
    if (is_array($containerName)) {
        $containerCount = sizeof($containerName) - 1;
        for ($i = 0; $i < $containerCount; $i++) {
            $rowKey = $propList;
            if ($containerType[$i] != "timestamp_timeseries") {
                $containerTypeTmp[$i] = "\$containerTypeConvert = \Type::";
                $containerTypeTmp[$i].= strtoupper($containerType[$i]);
                $containerTypeTmp[$i].= ";";
                eval($containerTypeTmp[$i]);
                array_unshift($rowKey, ["A00", $containerTypeConvert]);
                $containerInfo = new \ContainerInfo(["name" => $containerName[$i],
                                                    "columnInfoArray" => $rowKey,
                                                    "type" => \ContainerType::COLLECTION,
                                                    "rowKey" => true]);
            } else {
                array_unshift($rowKey, ["A00", \Type::TIMESTAMP]);
                $containerInfo = new \ContainerInfo(["name" => $containerName[$i],
                                                    "columnInfoArray" => $rowKey,
                                                    "type" => \ContainerType::TIME_SERIES,
                                                    "rowKey" => true]);
            }
            $gridstore->putContainer($containerInfo);
        }
    } else {
        $rowKey = $propList;
        if ($containerType != "timestamp_timeseries") {
            $containerTypeTmp = "\$containerTypeConvert = \Type::";
            $containerTypeTmp.= strtoupper($containerType);
            $containerTypeTmp.= ";";
            eval($containerTypeTmp);
            array_unshift($rowKey, ["A00", $containerTypeConvert]);
            $containerInfo = new \ContainerInfo(["name" => $containerName,
                                                "columnInfoArray" => $rowKey,
                                                "type" => \Type::COLLECTION,
                                                "rowKey" => true]);
        } else {
            array_unshif($rowKey, ["A00", \Type::TIMESTAMP]);
            $containerInfo = new \ContainerInfo(["name" => $containerName,
                                                "columnInfoArray" => $rowKey,
                                                "type" => \Type::TIMSE_SERIES,
                                                "rowKey" => true]);
        }
        $gridstore->putContainer($containerInfo);
    }
}

function tearDown($gridstore, $containerName) {
    if (is_array($containerName)) {
        for ($i = 0; $i < sizeof($containerName); $i ++) {
            $gridstore->dropContainer($containerName[$i]);
        }
    } else {
        $gridstore->dropContainer($containerName);
    }
}
?>
