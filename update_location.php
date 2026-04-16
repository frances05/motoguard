<?php

if(isset($_GET['lat']) && isset($_GET['lng'])){

    $lat = $_GET['lat'];
    $lng = $_GET['lng'];

    // SAVE CURRENT LOCATION
    file_put_contents("api/location.txt", "$lat,$lng");

    // SAVE HISTORY
    $time = time();
    $line = "$time,$lat,$lng\n";

    file_put_contents("api/history.txt", $line, FILE_APPEND);

    echo "OK";
}
?>