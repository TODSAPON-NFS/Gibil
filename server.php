<?php
    include 'sqlFunctions.php';
    $panels = getPanels();
    echo json_encode($panels);
?>

