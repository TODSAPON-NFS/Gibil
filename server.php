<?php
    include 'sqlFunctions.php';
    $accounts = getAccounts();
    echo json_encode($accounts);
?>

