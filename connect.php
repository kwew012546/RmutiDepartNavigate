<?php
    $servername = "db1-cluster.rmuti.ac.th";
    $username = "deptnavigator-fet-st";
    $password = "hVZUQP54uCVh";
    $dbname = "deptnavigator_fet_st_db";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: {$conn->connect_error}");
    }
