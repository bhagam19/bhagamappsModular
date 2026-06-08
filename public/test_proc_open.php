<?php
$process = proc_open('ls', [
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w']
], $pipes);

if (is_resource($process)) {
    echo stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    proc_close($process);
} else {
    echo "No se pudo abrir el proceso.";
}
