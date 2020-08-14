<?php
/*Rev:26.09.18r0*/
/* source is https://streaming-servers.com/downloads/servers_checker.php and https://github.com/Rubensigner/xtream-codes-decoded/blob/master/crons/servers_checker.php
i just added geolite2 download command with xtream-ui.com link. also i am not sure about it will work with current xc 2.9.3  or  not.  */

if (!@$argc) {
    die(0);
}
require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
cli_set_process_title('XtreamCodes[Server Checker]');
$Ed756578679cd59095dfa81f228e8b38 = TMP_DIR . md5(AFFb052ccA396818D81004ff99db49aA() . __FILE__);
BBD9e78Ac32626E138E758E840305A7c($Ed756578679cd59095dfa81f228e8b38);
$C6d911124d37d84aae02dbbd27390026 = intval(trim(shell_exec('ps aux | grep signal_receiver | grep -v grep | wc -l')));
if ($C6d911124d37d84aae02dbbd27390026 == 0) {
    shell_exec(PHP_BIN . ' ' . IPTV_PANEL_DIR . 'tools/signal_receiver.php > /dev/null 2>/dev/null &');
}
$ad4bdb9efb0d9470d5540d122b892978 = intval(trim(shell_exec('ps aux | grep pipe_reader | grep -v grep | wc -l')));
if ($ad4bdb9efb0d9470d5540d122b892978 == 0) {
    shell_exec(PHP_BIN . ' ' . IPTV_PANEL_DIR . 'tools/pipe_reader.php > /dev/null 2>/dev/null &');
}
$E8f9dea3c6b73e7af883fbd526c728b7 = intval(trim(shell_exec('ps aux | grep panel_monitor | grep -v grep | wc -l')));
if ($E8f9dea3c6b73e7af883fbd526c728b7 == 0) {
    shell_exec(PHP_BIN . ' ' . IPTV_PANEL_DIR . 'tools/panel_monitor.php > /dev/null 2>/dev/null &');
}
$C6788cc6a7dc5e43413102489972862e = intval(trim(shell_exec('ps aux | grep watchdog_data | grep -v grep | wc -l')));
if ($C6788cc6a7dc5e43413102489972862e == 0) {
    shell_exec(PHP_BIN . ' ' . IPTV_PANEL_DIR . 'tools/watchdog_data.php > /dev/null 2>/dev/null &');
}
if (!file_exists(MOVIES_IMAGES)) {
    mkdir(MOVIES_IMAGES);
}
if (!file_exists(ENIGMA2_PLUGIN_DIR)) {
    mkdir(ENIGMA2_PLUGIN_DIR);
}
$b05334022f117f99e07e10e7120b3707 = (int) trim(shell_exec('free | grep -c available'));
if ($b05334022f117f99e07e10e7120b3707 == 0) {
    $E747328102236137f1cbe650c295e4a8 = intval(shell_exec('/usr/bin/free -tk | grep -i Mem: | awk \'{print $2}\''));
    $ce5ef88878670c9526bfdfab8267f916 = $E747328102236137f1cbe650c295e4a8 - intval(shell_exec('/usr/bin/free -tk | grep -i Mem: | awk \'{print $4+$6+$7}\''));
} else {
    $E747328102236137f1cbe650c295e4a8 = intval(shell_exec('/usr/bin/free -tk | grep -i Mem: | awk \'{print $2}\''));
    $ce5ef88878670c9526bfdfab8267f916 = $E747328102236137f1cbe650c295e4a8 - intval(shell_exec('/usr/bin/free -tk | grep -i Mem: | awk \'{print $7}\''));
}
$Beead58eb65f6a16b84a5d7f85a2dbd0 = intval(shell_exec('lscpu | awk -F " : " \'/Core/ { c=$2; }; /Socket/ { print c*$2 }\' '));
$e8e405eb735fdd81a223c0a28cff7f7e = intval(shell_exec('grep --count ^processor /proc/cpuinfo'));
$c447e2c8da4eb35e33ad00d1171c001d = trim(shell_exec('cat /proc/cpuinfo | grep \'model name\' | uniq | awk -F: \'{print $2}\''));
$f7d72ebd2d5da7acdd31cff803526ba4 = intval(shell_exec('ps aux|awk \'NR > 0 { s +=$3 }; END {print s}\''));
$d0d324f3dbb8bbc5fff56e8a848beb7a = a78BF8D35765BE2408C50712cE7a43Ad::$StreamingServers[SERVER_ID]['network_interface'];
$c01d5077f34dc0ef046a6efa9e8e24f4 = $B5490c2f61c894c091e04441954a0f09 = $b35a2cc5b2ffb4acd1cf6b8b08ee4f43 = NULL;
if (!empty($d0d324f3dbb8bbc5fff56e8a848beb7a)) {
    $b35a2cc5b2ffb4acd1cf6b8b08ee4f43 = file_get_contents('/sys/class/net/' . $d0d324f3dbb8bbc5fff56e8a848beb7a . '/speed');
    $b10021b298f7d4ce2f8e80315325fa1a = trim(file_get_contents('/sys/class/net/' . $d0d324f3dbb8bbc5fff56e8a848beb7a . '/statistics/tx_bytes'));
    $C5b51b10f98c22fb985e90c23eade263 = trim(file_get_contents('/sys/class/net/' . $d0d324f3dbb8bbc5fff56e8a848beb7a . '/statistics/rx_bytes'));
    sleep(1);
    $e54a6ff3afc52767cdd38f62ab4c38d1 = trim(file_get_contents('/sys/class/net/' . $d0d324f3dbb8bbc5fff56e8a848beb7a . '/statistics/tx_bytes'));
    $d1a978924624c41845605404ded7e846 = trim(file_get_contents('/sys/class/net/' . $d0d324f3dbb8bbc5fff56e8a848beb7a . '/statistics/rx_bytes'));
    $c01d5077f34dc0ef046a6efa9e8e24f4 = round(($e54a6ff3afc52767cdd38f62ab4c38d1 - $b10021b298f7d4ce2f8e80315325fa1a) / 1024 * 0.0078125, 2);
    $B5490c2f61c894c091e04441954a0f09 = round(($d1a978924624c41845605404ded7e846 - $C5b51b10f98c22fb985e90c23eade263) / 1024 * 0.0078125, 2);
}
$b1e6fdf64c397fe6d855488ade08962a = shell_exec('ps ax | grep -v grep | grep ffmpeg | grep -c ' . FFMPEG_PATH);
$b491382721126ed130f32155d616b806 = array('total_ram' => $E747328102236137f1cbe650c295e4a8, 'total_used' => $ce5ef88878670c9526bfdfab8267f916, 'cores' => $Beead58eb65f6a16b84a5d7f85a2dbd0, 'threads' => $e8e405eb735fdd81a223c0a28cff7f7e, 'kernel' => trim(shell_exec('uname -r')), 'total_running_streams' => $b1e6fdf64c397fe6d855488ade08962a, 'cpu_name' => $c447e2c8da4eb35e33ad00d1171c001d, 'cpu_usage' => (int) $f7d72ebd2d5da7acdd31cff803526ba4 / $e8e405eb735fdd81a223c0a28cff7f7e, 'network_speed' => $b35a2cc5b2ffb4acd1cf6b8b08ee4f43, 'bytes_sent' => $c01d5077f34dc0ef046a6efa9e8e24f4, 'bytes_received' => $B5490c2f61c894c091e04441954a0f09);
$A752e3fbe7ed63f10be97634c544dc8f = array_values(array_unique(array_map('trim', explode('', shell_exec('ip -4 addr | grep -oP \'(?<=inet\\s)\\d+(\\.\\d+){3}\'')))));
$f566700a43ee8e1f0412fe10fbdf03df->query('UPDATE `streaming_servers` SET `server_hardware` = \'%s\',`whitelist_ips` = \'%s\' WHERE `id` = \'%d\'', json_encode($b491382721126ed130f32155d616b806), json_encode($A752e3fbe7ed63f10be97634c544dc8f), SERVER_ID);
define('GEOIP2_FILENAME', IPTV_PANEL_DIR . 'GeoLite2.mmdb');
if (!file_exists(GEOIP2_FILENAME) || 86400 <= time() - filemtime(GEOIP2_FILENAME)) {
   passthru('wget --no-check-certificate --user-agent "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.125 Safari/537.36" --timeout=40 "https://xtream-ui.com/GeoLite2/GeoLite2.mmdb" -O "' . GEOIP2_FILENAME . '" -q 2>/dev/null');
   touch(GEOIP2_FILENAME);
}
?>
