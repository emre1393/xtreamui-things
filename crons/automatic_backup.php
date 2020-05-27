<?php

/*
To run this script, put it into /home/xtreamcodes/iptv_xtream_codes/crons folder. 
Restart panel, panel will add this php file as a cronjob for xtreamcodes user.

i forked this php from https://www.worldofiptv.com/threads/how-to-get-work-automatique-backup-in-xtream-ui-22f.7853/

what it does? it backups db, compresses, then copies this compressed file to your rclone remote path


https://rclone.org/commands/rclone_copy/

https://rclone.org/install/

First install rclone, then run "rclone config" to setup your remote storage.
See rclone config docs for more details.
*/

include "/home/xtreamcodes/iptv_xtream_codes/admin/functions.php";

$AutoDBBackup = false;

// if last backup time is lower than (current time - periodicity), script runs.
//(or day or week or month, why the hell do you backup once a month?) 

if (isset($rAdminSettings['automatic_backups']) && ! empty($rAdminSettings['automatic_backups'])) {

    if ($rAdminSettings['automatic_backups'] == 'hourly' && $rAdminSettings['automatic_backups_check'] < (time() - 3600)) {

        $AutoDBBackup = true;

    } else if ($rAdminSettings['automatic_backups'] == 'daily' && $rAdminSettings['automatic_backups_check'] < (time() - 86400)) {

        $AutoDBBackup = true;

    } else if ($rAdminSettings['automatic_backups'] == 'weekly' && $rAdminSettings['automatic_backups_check'] < (time() - 604800)) {

        $AutoDBBackup = true;

    } else if ($rAdminSettings['automatic_backups'] == 'monthly' && $rAdminSettings['automatic_backups_check'] < (time() - 2592000)) {

        $AutoDBBackup = true;

    }

}


if ($AutoDBBackup) {

    $rDateOfNow =  date("Y-m-d_H:i:s");  // define current time example: 2020-05-27_18:18:33

// edit rclone remote name in $rRemoteFolder line, i named mine as "google_drive", replace it with yours, you don't need to change other thigs.

    $rRemotePath = "google_drive";  // you can edit remote name here and in the rclone config when you want.

    $rRemoteFolder = "db_backups";  // this is the target folder in the rclone remote path. 

    $rSleepTimeForBackup = 5; //i had to use "sleep" to wait mysql process done before gzip command. increase it if your db is big or server is slow.

    $rFilename = MAIN_DIR . "adtools/backups/backup_" . $rDateOfNow . ".sql";

    $rGzipTheFile = MAIN_DIR . "adtools/backups/backup_" . $rDateOfNow . ".gz";

    $rCommand = "mysqldump -u ".$_INFO["db_user"]." -p".$_INFO["db_pass"]." -P ".$_INFO["db_port"]." ".$_INFO["db_name"]." --ignore-table=xtream_iptvpro.user_activity --ignore-table=xtream_iptvpro.stream_logs --ignore-table=xtream_iptvpro.panel_logs --ignore-table=xtream_iptvpro.client_logs --ignore-table=xtream_iptvpro.epg_data --ignore-table=xtream_iptvpro.mag_logs > \"".$rFilename."\" && sleep ".$rSleepTimeForBackup."; gzip < ".$rFilename." > ".$rGzipTheFile." &&  /usr/bin/rclone copy --no-traverse ".$rGzipTheFile." ".$rRemotePath.":".$rRemoteFolder." && rm ".$rGzipTheFile.";";

    $rRet = shell_exec($rCommand . "2>&1");

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
