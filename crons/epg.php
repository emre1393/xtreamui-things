<?php
/**
 * FIXED EPG CRON
 * 
 * Description:
 *  English
 *   Fixed data overload on table `epg_data`
 *   tilt panel fixed due to epg
 *  Spanish
 *   Se corrigió la sobrecarga de datos en la tabla `epg_data`
 *   Se Evita la caída del panel a partir de lo anterior
 * 
 * Version:2.0
 * New Features:
 *   exception handling, continues to work with the following sources
 * 
 * Fixed by Midd09
 *  Telegram: t.me/Midd98
 *  XU Forum: Midd98
 * 
 * Thanks to:
 *  tweakunwanted -> OpenXC-Main (For decoded init.php and epg.php to use the database and epg model)
 */


// for infinite time of execution 
ini_set('max_execution_time', 0);
set_time_limit(0);

/** Handle warning and all errors (controll errors in download xml)*/
set_error_handler(
    function ($severity, $message, $file, $line) {
        throw new ErrorException($message, $severity, $severity, $file, $line);
    }
);

require_once(str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php'); /** Require Xtream-Codes functions (Database settings and controller) */
require IPTV_INCLUDES_PATH . 'epg.php'; /** Require epg model from Xtream-Codes (Download and parse data from xml) */

$EPG = array();
$f566700a43ee8e1f0412fe10fbdf03df->query('SELECT DISTINCT E.id, E.epg_name, E.epg_file, E.data, S.channel_id, S.epg_lang FROM  `epg` E left JOIN `streams` S on E.id = S.epg_id  WHERE E.id != 0');
if (0 < $f566700a43ee8e1f0412fe10fbdf03df->d1e5CE3b87bB868B9E6EfD39aA355a4f()) { /** if have epg source */
    $D465fc5085f41251c6fa7c77b8333b0f = $f566700a43ee8e1f0412fe10fbdf03df->C126fd559932F625CDf6098D86c63880(); /** mysql results */
    /** foreach all channels with epg source */
    foreach ($D465fc5085f41251c6fa7c77b8333b0f as $row) {
        $row['id'] = intval($row['id']);
        if( !array_key_exists($row['id'], $EPG) ){
            $EPG[$row['id']] = array(
                'id'        => $row['id'],
                'epg_file'  => $row['epg_file'],
                'epg_name'  => $row['epg_name'],
                'data'  => $row['data'],
                'canales'   => array(),
            );
        }
        if(!empty($row['channel_id'])) $EPG[$row['id']]['canales'][$row['channel_id']] = array( 'epg_lang' => $row['epg_lang'] );
    }
    if( count($EPG) == 0){
        exit(0);// epg sources not valid or foreach fail
    }

    $f566700a43ee8e1f0412fe10fbdf03df->Fc53e22ae7eE3bb881CD95Fb606914F0("TRUNCATE `epg_data`"); // delete all data from epg_data

    foreach($EPG as $epg){
        try {
            $epg_data = new E3223A8ad822526d8F69418863b6E8B5($epg['epg_file']); /** Load epg_file with xtream-codes epg model */
        }catch (Exception $e) {
            continue; // Download or uncompressing file failed. Skipping this source
        }

        if($epg_data->validEpg){ 
            /** get data from channels with epg ( if stream have epg source ) */
            if( !empty($epg['canales']) ){
                try{
                    $programacion_a_insertar = $epg_data->a0b90401c3241088846A84F33c2B50fF($epg['id'], $epg['canales']);
                    foreach($programacion_a_insertar as $insert){
                        try{
                            $f566700a43ee8e1f0412fe10fbdf03df->Fc53e22ae7eE3bb881CD95Fb606914F0("INSERT INTO `epg_data` (`epg_id`, `channel_id`, `start`, `end`, `lang`, `title`, `description`) VALUES {$insert}");
                        }catch (Exception $e) {
                            // error on insert data in epg_data
                        }
                    }
                }catch (Exception $e) {
                    // Error on parse xml
                }

            }

            /** set channels and last_update of the current epg source */
            $canales_epg = json_encode($epg_data->a53d17AB9BD15890715e7947C1766953()); /** channels to update epg table */
            if( $canales_epg != $epg['data'] ){
                $f566700a43ee8e1f0412fe10fbdf03df->Fc53e22ae7eE3bb881CD95Fb606914F0("UPDATE `epg` SET `last_updated` = ".time().", `data` = '". $f566700a43ee8e1f0412fe10fbdf03df->escape($canales_epg) ."'  WHERE id = {$epg['id']}");
            }else{
                $f566700a43ee8e1f0412fe10fbdf03df->Fc53e22ae7eE3bb881CD95Fb606914F0("UPDATE `epg` SET `last_updated` = ".time()."  WHERE id = {$epg['id']}");
            }
        }
    }
}
exit(0);