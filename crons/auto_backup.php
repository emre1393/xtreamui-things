<?php

/*
To run this script, put it into /home/xtreamcodes/iptv_xtream_codes/crons folder. 
Restart panel, panel will add this php file as a cronjob for xtreamcodes user.
Otherwise add manually,*/

// add this: */1 * * * * /home/xtreamcodes/iptv_xtream_codes/php/bin/php /home/xtreamcodes/iptv_xtream_codes/crons/auto_backup.php # Xtream-Codes IPTV Panel 

/*
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

// choose mail or rclone below and edit related parts.

if ($AutoDBBackup) {

    $rDateOfNow =  date("Y-m-d_H:i:s");  // define current time example: 2020-05-27_18:18:33
    
    $rMailorRclone = "M";   # use "M" or "R", if statement will use one of these.

//edit mail addresses if you want to use mutt,

    $rSenderMail = "sender@example.com"; # mail address of sender

    $rRecepeintMail = "receipient@example.com";  # mail address of recepeint.


// edit these 2 line to send backup with rclone
//edit rclone remote name in $rRemoteFolder line, i named mine as "google_drive", replace it with yours, you don't need to change other thigs.

    $rRemotePath = "google_drive";  // you can edit rclone remote name in $rRemoteFolder line here and in the rclone config when you want.

    $rRemoteFolder = "db_backups";  // this is the target folder in the rclone remote path. 

    $rSleepTimeForBackup = 5; //i had to use "sleep" to wait mysql process done before gzip command. increase it if your db is big or server is slow.

    //You DON'T need to edit rest of them.

    $rFilename = MAIN_DIR . "adtools/backups/backup_" . $rDateOfNow . ".sql";

    $rTheGzipFile = MAIN_DIR . "adtools/backups/backup_" . $rDateOfNow . ".gz";


    $rCommand0 = "mysqldump -u ".$_INFO["db_user"]." -p".$_INFO["db_pass"]." -P ".$_INFO["db_port"]." ".$_INFO["db_name"]." --ignore-table=xtream_iptvpro.user_activity --ignore-table=xtream_iptvpro.stream_logs --ignore-table=xtream_iptvpro.panel_logs --ignore-table=xtream_iptvpro.client_logs --ignore-table=xtream_iptvpro.epg_data --ignore-table=xtream_iptvpro.mag_logs > \"".$rFilename."\" && sleep ".$rSleepTimeForBackup."; gzip < ".$rFilename." > ".$rTheGzipFile.";";

    $rCommand1 = "export EMAIL=".$rSenderMail." && echo \"Database Backup ".$rDateOfNow."\" | mutt -e 'my_hdr From: Admin <".$rSenderMail.">' -s \"Database Backup ".$rDateOfNow."\" -a ".$rTheGzipFile." -- \"".$rRecepeintMail."\"";

    $rCommand2 = "/usr/bin/rclone copy --no-traverse ".$rTheGzipFile." ".$rRemotePath.":".$rRemoteFolder.";";

    //backup db with first command
    $rRet = shell_exec($rCommand0);

    //echo($rCommand1);  //for debug

    //echo($rCommand2);  //for debug

    // checks .gz exits and sends backup.gz file
    if (file_exists($rTheGzipFile)) {

        if ($rMailorRclone == 'M') {
            if (file_exists("/usr/bin/mutt") && file_exists("/usr/sbin/sendmail")) {

            $rRet = shell_exec($rCommand1); // . "2>&1"

            } else {

                $rRet = shell_exec("echo \"mutt and/or sendmail couldn't found, please install them first\nsudo apt-get install mutt sendmail\" >> /home/xtreamcodes/auto_backup.log;");
                echo "check error logs in /home/xtreamcodes/auto_backup.log";
            }
        } else if ($rMailorRclone == 'R') {
                    //check rclone remote config file exists in xtreamcodes user
                if (file_exists("/home/xtreamcodes/.config/rclone/rclone.conf")) {

                    $rRet = shell_exec($rCommand2); // . "2>&1"

                } else {
                        // tell user to setup rclone first.
                    $rRet = shell_exec("echo \"rclone config couldn't found, make sure you did setup your rclone remote\n if you already did setup rclone remote drive, copy rclone config file from root user to xtreamcodes user's home folder with this command.\nmkdir -p /home/xtreamcodes/.config/rclone/ && cp /root/.config/rclone/rclone.conf /home/xtreamcodes/.config/rclone/rclone.conf; \" >> /home/xtreamcodes/auto_backup.log;");
                    echo "check error logs in /home/xtreamcodes/auto_backup.log";
                }
            } 

        } else {

            echo "Invalid send choice, wont' send anything.";
        }
    }

    // delete .gz file
    $rRet = shell_exec("rm ".$rTheGzipFile." 2>&1;");

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
