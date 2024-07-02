<?php

use Google\Http\Batch;

// Create connection
if ($_SERVER['HTTP_HOST'] == 'aplatin.altervista.org')
{
    $servername = "localhost";
    $username = "aplatin";
    $password = "!";
    $dbname = "my_aplatin";

}

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error)
{
    die("Port: " . ini_get("mysqli.default_port") . "\n\r\n\r | Connection to server failed <B>(1)</B>: " . $conn->connect_error);
}
else
{
    $conn->set_charset("utf8mb4");
}

function latinQuery($varsArray, $sqlCode, $flatten = false, $uniquevalue = false, $singleresult = false)
{
    global $conn;

    if ($varsArray != null)
    {
        $varsArray = array_slice($varsArray, 0, substr_count($sqlCode, "?"));
    }

    $result = mysqli_execute_query($conn, $sqlCode, $varsArray);

    if ($result === false)
    {
        return mysqli_error($conn);
    }
    else if ($result === true)
    {
        $temporary_id = mysqli_insert_id($conn);
        if (isset($temporary_id))
        {
            return $temporary_id;
        }
        else
        {
            return $result;
        }
    }

    $result = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $temparray = [];

    if ($uniquevalue !== false && $uniquevalue !== null)
    {
        foreach ($result as $row)
        {
            if (isset($row[$uniquevalue]))
            {
                $temparray[$row[$uniquevalue]] = $row;
            }
        }

        $result = $temparray;
    }

    if ($flatten != false)
    {
        if ($flatten == "all")
        {
            $result = flattenArray($result, false);
        }
        else
        {
            $result = flattenArray($result, true);
        }
    }

    if ($singleresult === true)
    {
        $result = array_values($result);
        $result = end($result);
        $result = $result . "";
    }

    return $result;

}

function prepQueryBatch($sql_queries_array, $combineUpdateQueries = false)
{
    $sql_queries_array = array_unique($sql_queries_array);
    $batch = implode(";", array_values(array_unique($sql_queries_array)));
    $batch = preg_replace('/\s+/', ' ', $batch);
    $batch = explode(";", $batch);
    $batch = array_filter($batch);
    $batch = combineLikeInsertQueries($batch);

    if ($combineUpdateQueries)
    {
        $batch = combineLikeUpdateQueries($batch);
    }

    return $batch;
}

function sqlBatchRun($sql_queries_array, $combineUpdateQueries = false)
{
    // Create connection
    global $conn;

    // Check connection
    if ($conn->connect_error)
    {
        die("Connection to server failed (3): " . $conn->connect_error);
    }

    $batch = prepQueryBatch($sql_queries_array, $combineUpdateQueries);

    foreach ($batch as $q)
    {

        $result = $conn->query($q);

        if ($result == false)
        {
            return '{"success":false, "error":"' . mysqli_error($conn) . '"}';
        }
    }
    return '{"success":true,"num_queries":' . count($batch) . '}';
}

function sqlBatchRunAsync($sql_queries_array)
{
    // Create connection
    global $conn;

    // Check connection
    if ($conn->connect_error)
    {
        die("Connection to server failed (3): " . $conn->connect_error);
    }

    $batch = prepQueryBatch($sql_queries_array);
    $batch = implode(";", $batch);

    $result = $conn->multi_query($batch);
    if ($result == false)
    {
        return mysqli_error($conn);
    }
    else
    {
        return $result;
    }
}


function flattenArray($array, $unique = true)
{
    $return = array();
    array_walk_recursive($array, function ($a) use (&$return)
    {
        $return[] = $a;
    });

    if ($unique)
    {
        $return = array_unique($return);
    }

    $return = array_values($return);

    return $return;

}

function combineLikeInsertQueries($queryArray)
{
    //cleanup
    if (is_string($queryArray))
    {
        $queryArray = explode(";", $queryArray);
    }

    $queryArray = array_values(array_filter($queryArray));
    $queryArray = array_map(function ($x)
    {
        return trim(preg_replace('/\s+/', ' ', $x));

    }, $queryArray);
    array_unique($queryArray);
    sort($queryArray);

    $insertQueries = array_values(array_filter($queryArray, function ($x)
    {
        return (str_starts_with(strtolower($x), strtolower("INSERT IGNORE INTO")) || str_starts_with(strtolower($x), strtolower("INSERT INTO"))) && !str_contains(strtolower($x), strtolower("DUPLICATE KEY"));
    }));

    $nonInsertQueries = array_filter(array_values(array_diff($queryArray, $insertQueries)));

    $insertQueries = array_map(function ($x)
    {
        return trim(preg_replace('~(?<!\\\\)(?:\\\\{2})*(?:"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\')(*SKIP)(?!)|\s+~s', '', $x));

    }, $insertQueries);

    // var_dump($insertQueries);

    $insertQueries = array_map(function ($x)
    {

        $phrases = ["ON DUPLICATE KEY UPDATE", "INSERT INTO", "INSERT IGNORE INTO"];
        $nospacephrases = array_map(function ($y)
        {

            return trim(str_replace(' ', '', $y));

        }, $phrases);

        return " " . trim(str_replace($nospacephrases, $phrases, $x)) . " ";

    }, $insertQueries);

    $categorizedInsertQueries = [];

    foreach ($insertQueries as $iq)
    {
        $splitQuery = explode("VALUES", $iq);
        $introString = $splitQuery[0];

        if (!isset($categorizedInsertQueries[$introString]))
        {
            $categorizedInsertQueries[$introString] = [];
        }
        array_push($categorizedInsertQueries[$introString], $splitQuery[1]);
    }

    $insertQueries = [];
    foreach ($categorizedInsertQueries as $start => $valueList)
    {
        array_push($insertQueries, $start . " VALUES " . implode(", ", $valueList) . "; ");
    }

    return array_values(array_unique(array_filter([ ...$insertQueries, ...$nonInsertQueries])));

}

function combineLikeUpdateQueries($queryArray)
{
    //only tested with one static column, one varying column

    //cleanup
    if (is_string($queryArray))
    {
        $queryArray = explode(";", $queryArray);
    }

    $queryArray = array_values(array_filter($queryArray));
    $queryArray = array_map(function ($x)
    {
        return trim(preg_replace('/\s+/', ' ', $x));

    }, $queryArray);
    array_unique($queryArray);
    sort($queryArray);

    $updateQueries = array_values(array_filter($queryArray, function ($x)
    {
        return (str_starts_with(strtolower($x), strtolower("UPDATE ")));
    }));

    $nonUpdateQueries = array_filter(array_values(array_diff($queryArray, $updateQueries)));

    $updateQueries = array_map(function ($x)
    {
        return trim(preg_replace('~(?<!\\\\)(?:\\\\{2})*(?:"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\')(*SKIP)(?!)|\s+~s', '', $x));

    }, $updateQueries);

    $updateQueries = array_map(function ($x)
    {

        $phrases = ["UPDATE"];
        $nospacephrases = array_map(function ($y)
        {

            return trim(str_replace(' ', '', $y));

        }, $phrases);

        return " " . trim(str_replace($nospacephrases, $phrases, $x)) . " ";

    }, $updateQueries);

    $categorizedUpdateQueries = [];

    foreach ($updateQueries as $uq)
    {
        $splitQuery = explode("SET", $uq);
        $introString = $splitQuery[0];
        $rest = $splitQuery[1];
        $when = explode(";", explode("WHERE", $rest)[1])[0];
        $then = explode("WHERE", $rest)[0];

        if (!isset($categorizedUpdateQueries[$introString]))
        {
            $categorizedUpdateQueries[$introString] = [];
        }
        array_push($categorizedUpdateQueries[$introString], ["then" => $then, "when" => $when]);
    }

    $updateQueries = [];
    foreach ($categorizedUpdateQueries as $start => $thenwhenlist)
    {
        // var_explain($thenwhenlist);
        $first_then_col = explode("=", array_values($thenwhenlist)[0]['then'])[0];

        $query = $start . " SET " . $first_then_col . "= CASE ";

        foreach ($thenwhenlist as $thenwhen)
        {
            $then_val = explode("=", $thenwhen['then'])[1];

            $query .= " WHEN " . $thenwhen['when'] . " THEN " . $then_val;

        }
        $query .= " END ";

        array_push($updateQueries, $query);
    }

    return array_values(array_unique(array_filter([ ...$updateQueries, ...$nonUpdateQueries])));
}



// =----LEGACY-----=


function SQLRun($sql_code)
{
    return latinQuery(null, $sql_code);
}

function SQLQ($sql_code)
{
    return latinQuery(null, $sql_code, true, false, true);
}

function SQLQuarry($sql_code, $flatten = false, $uniquevalue = null)
{
    return latinQuery(null, $sql_code, $flatten, $uniquevalue);
}

function LHQuery(...$args)
{
    return latinQuery(...$args);
}