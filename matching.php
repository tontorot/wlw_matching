<?php
$matching_log = file_get_contents('./matching.dat');
$matching_data = json_decode($matching_log,true);

$_temp['matching_data'] = $matching_data;
require('header.tpl');
require('matching.tpl');
require('footer.tpl');

echo $_output;
?>