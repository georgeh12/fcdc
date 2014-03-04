<?php
header('Content-type:text/plain; charset=UTF-8');

$file = $_POST['file'];
$ip = $_POST['ip'];
$start = $_POST['start'];
$end = $_POST['end'];
$data = $_POST['data'];
$error = $_POST['error'];

$handle = fopen('files/' . $file . ' ' . $start . '-' . $end . '.txt', 'w+');
fwrite( $handle, $data );
fclose( $handle );

$handle = fopen('errors/' . $file . ' ' . $start . '-' . $end . '.txt', 'w+');
fwrite( $handle, $error );
fclose( $handle );

$handle = fopen("settings/current.txt", "w+");
fwrite( $handle, $end );
fclose( $handle );

$handle = fopen("logs/progress.log", "a");
fwrite( $handle, date('Y/m/d H:i:s') . ' > ' . $file . ' > ' . gethostname() . ' > ' . $ip . ' > ' . $start . '-' . $end . "\n" );
fclose( $handle );
?>