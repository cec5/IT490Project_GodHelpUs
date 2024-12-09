<?php

$configIni = parse_ini_file("../config/source.ini");
$deployUsername = $configIni["username"];
$deployPassword = $configIni["password"];
$deployDefaultHostname = $configIni["hostname"];
$deployDefaultPort = $configIni["port"];

function generateRandomName() {
    // Lists of words
    $adjectives = [
        'fast', 'silly', 'happy', 'lazy', 'brave', 'gentle', 'kind', 'smart', 'witty', 'calm'
    ];
    $colors = [
        'red', 'blue', 'green', 'yellow', 'orange', 'purple', 'black', 'white', 'pink', 'brown'
    ];
    $animals = [
        'cat', 'dog', 'fox', 'bear', 'tiger', 'lion', 'wolf', 'rabbit', 'eagle', 'panda'
    ];

    // Pick one word from each list randomly
    $adjective = $adjectives[array_rand($adjectives)];
    $color = $colors[array_rand($colors)];
    $animal = $animals[array_rand($animals)];

    // Combine the words with a separator (e.g., '-')
    return "{$adjective}-{$color}-{$animal}";
}

function getFormattedDateTime() {
    $currentDateTime = new DateTime();
    $formattedDateTime = $currentDateTime->format('Ymd-His');
    return $formattedDateTime;
}

// Gets SSH Connection
function createSSHConnection($hostname = 'localhost', $port = 22) {
    $configIni = parse_ini_file("../config/source.ini");
    $deployUsername = $configIni["username"];
    $deployPassword = $configIni["password"];
	$connection = ssh2_connect($hostname, $port);
    if (ssh2_auth_password($connection, $deployUsername, $deployPassword)) {
        echo "Auth success\n";
    } else {
        echo "Auth failure\n";
        die('AUTHENTICATION FAILURE');
    }
    return $connection;
}

function sendFile($conn, $localFileLocation, $remoteFileLocation) {
    ssh2_scp_send($conn, $localFileLocation, $remoteFileLocation);
}

function getFile($conn, $remoteFileLocation, $localFileLocation) {
    ssh2_scp_recv($conn, $remoteFileLocation, $localFileLocation);
}

function getVersionPath($job, $version = 'latest') {
    return $version . '/dev/' . $job . '/';
}

function getDeploymentFromRequest($requestObj) {
    $host = $requestObj['DeploymentIp'];
    $filePath = $requestObj['FilePath'];
    $fileName = basename($filePath);
    $connection = createSSHConnection($host);
    echo var_dump($connection);
    if ($connection != NULL) {
        $localFilePath = '../bundles/' . $fileName;
        $xferSuccess = getFile($connection, $filePath, $localFilePath);
        ssh2_exec($connection, 'exit');
        // TODO: run deploy.sh on the newly deployed code_package
        // $filename stores the name of the file, but $localFilePath stores its relative path to this file
        // dew it
        return true;
    } else {
        return NULL;
    }
}

?>