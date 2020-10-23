<?php
// a simple cron for db backup

include "/home/xtreamcodes/iptv_xtream_codes/admin/functions.php";

$AutoDBBackup = false; // DON'T touch to this.

if (isset($rAdminSettings['automatic_backups']) && ! empty($rAdminSettings['automatic_backups'])) {

    if ($rAdminSettings['automatic_backups'] == 'hourly' && $rAdminSettings['automatic_backups_check'] < (time() - 3600)) {
        $AutoDBBackup = true;
    } else if ($rAdminSettings['automatic_backups'] == 'daily' && $rAdminSettings['automatic_backups_check'] < (time() - 86400)) {
        $AutoDBBackup = true;
    } else if ($rAdminSettings['automatic_backups'] == 'weekly' && $rAdminSettings['automatic_backups_check'] < (time() - 604800)) {
        $AutoDBBackup = true;
    } else if ($rAdminSettings['automatic_backups'] == 'monthly' && $rAdminSettings['automatic_backups_check'] < (time() - 2592000)) {
        $AutoDBBackup = true;
    } else if ($rAdminSettings['automatic_backups'] == 'off') {
        $AutoDBBackup = false;
    }
}

$rDateOfNow =  date("Y-m-d_H:i:s");  // define current time, example: 2020-05-27_18:18:33

$rFilename = MAIN_DIR . "adtools/backups/backup_" . $rDateOfNow . ".sql";



$rCommand = "mysqldump -u ".$_INFO["db_user"]." -p".$_INFO["db_pass"]." -P ".$_INFO["db_port"]." ".$_INFO["db_name"]." --ignore-table=xtream_iptvpro.user_activity --ignore-table=xtream_iptvpro.stream_logs --ignore-table=xtream_iptvpro.panel_logs --ignore-table=xtream_iptvpro.client_logs --ignore-table=xtream_iptvpro.epg_data --ignore-table=xtream_iptvpro.mag_logs > \"".$rFilename."\" ;";


if ($AutoDBBackup) {
    //backup db with  command
    shell_exec($rCommand);

    if (file_exists($rFilename)) {

        $rAdminSettings['automatic_backups_check'] = time();

        writeAdminSettings();  // writes last time when script worked

        // rest of script deletes older files according to backups to keep data.
        $rBackups = getBackups();

        if ((count($rBackups) > intval($rAdminSettings["backups_to_keep"])) && (intval($rAdminSettings["backups_to_keep"]) > 0)) {
            $rDelete = array_slice($rBackups, 0, count($rBackups) - intval($rAdminSettings["backups_to_keep"]));
            foreach ($rDelete as $rItem) {
                if (file_exists(MAIN_DIR."adtools/backups/".$rItem["filename"])) {
                    unlink(MAIN_DIR."adtools/backups/".$rItem["filename"]);
                }
            }
        }
    }
}
?>