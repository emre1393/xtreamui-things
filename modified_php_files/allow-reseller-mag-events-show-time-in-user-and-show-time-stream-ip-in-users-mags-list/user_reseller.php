<?php
include "session.php"; include "functions.php";
if ($rPermissions["is_admin"]) { exit; }

$rRegisteredUsers = getRegisteredUsers($rUserInfo["id"]);

if ((isset($_GET["trial"])) OR (isset($_POST["trial"]))) {
    if ($rAdminSettings["disable_trial"]) {
        $canGenerateTrials = False;
    } else if (floatval($rUserInfo["credits"]) < floatval($rPermissions["minimum_trial_credits"])) {
        $canGenerateTrials = False;
    } else {
        $canGenerateTrials = checkTrials();
    }
} else {
    $canGenerateTrials = True;
}

if (isset($_POST["submit_user"])) {
    $_POST["mac_address_mag"] = strtoupper($_POST["mac_address_mag"]);
    $_POST["mac_address_e2"] = strtoupper($_POST["mac_address_e2"]);
    if (isset($_POST["edit"])) {
        if (!hasPermissions("user", $_POST["edit"])) { exit; }
        $rUser = getUser($_POST["edit"]);
        if (!$rUser) {
            exit;
        }
    }
    if (isset($rUser)) {
        $rArray = $rUser;
        unset($rArray["id"]);
    } else {
        $rArray = Array("member_id" => 0, "username" => "", "password" => "", "exp_date" => null, "admin_enabled" => 1, "enabled" => 1, "admin_notes" => "", "reseller_notes" => "", "bouquet" => Array(), "max_connections" => 1, "is_restreamer" => 0, "allowed_ips" => Array(), "allowed_ua" => Array(), "created_at" => time(), "created_by" => -1, "is_mag" => 0, "is_e2" => 0, "force_server_id" => 0, "is_isplock" => 0, "isp_desc" => "", "forced_country" => "", "is_stalker" => 0, "bypass_ua" => 0, "play_token" => "");
    }
    if (!empty($_POST["package"])) {
        $rPackage = getPackage($_POST["package"]);
        // Check package is within permissions.
        if (($rPackage) && (in_array($rUserInfo["member_group_id"], json_decode($rPackage["groups"], True)))) {
            // Ignore post and get information from package instead.
            if ($_POST["trial"]) {
                $rCost = floatval($rPackage["trial_credits"]);
            } else {
                $rOverride = json_decode($rUserInfo["override_packages"], True);
                if ((isset($rOverride[$rPackage["id"]]["official_credits"])) && (strlen($rOverride[$rPackage["id"]]["official_credits"]) > 0)) {
                    $rCost = floatval($rOverride[$rPackage["id"]]["official_credits"]);
                } else {
                    $rCost = floatval($rPackage["official_credits"]);
                }
            }
            if ((floatval($rUserInfo["credits"]) >= $rCost) && ($canGenerateTrials)) {
                if ($_POST["trial"]) {
                    $rArray["exp_date"] = strtotime('+'.intval($rPackage["trial_duration"]).' '.$rPackage["trial_duration_in"]);
                    $rArray["is_trial"] = 1;
                } else {
                    if (isset($rUser)) {
                        if ($rUser["exp_date"] >= time()) {
                            $rArray["exp_date"] = strtotime('+'.intval($rPackage["official_duration"]).' '.$rPackage["official_duration_in"], intval($rUser["exp_date"]));
                        } else {
                            $rArray["exp_date"] = strtotime('+'.intval($rPackage["official_duration"]).' '.$rPackage["official_duration_in"]);
                        }
                    } else {
                        $rArray["exp_date"] = strtotime('+'.intval($rPackage["official_duration"]).' '.$rPackage["official_duration_in"]);
                    }
                    $rArray["is_trial"] = 0;
                }
                $rArray["bouquet"] = $rPackage["bouquets"];
                $rArray["max_connections"] = $rPackage["max_connections"];
                $rArray["is_restreamer"] = $rPackage["is_restreamer"];
                $rOwner = $_POST["member_id"];
                if (in_array($rOwner, array_keys($rRegisteredUsers))) {
                    $rArray["member_id"] = $rOwner;
                } else {
                    $rArray["member_id"] = $rUserInfo["id"]; // Invalid owner, reset.
                }
                $rArray["reseller_notes"] = $_POST["reseller_notes"];
                if (isset($_POST["is_mag"])) {
                    $rArray["is_mag"] = 1;
                }
                if (isset($_POST["is_e2"])) {
                    $rArray["is_e2"] = 1;
                }
            } else {
                $_STATUS = 4; // Not enough credits.
            }
        } else {
            $_STATUS = 3; // Invalid package.
        }
    } else if (isset($rUser)) {
        // No package, just editing fields.
        $rArray["reseller_notes"] = $_POST["reseller_notes"];
        $rOwner = $_POST["member_id"];
        if (in_array($rOwner, array_keys($rRegisteredUsers))) {
            $rArray["member_id"] = $rOwner;
        } else {
            $rArray["member_id"] = $rUserInfo["id"]; // Invalid owner, reset.
        }
    } else {
        $_STATUS = 3; // Invalid package.
    }
    if (!$rPermissions["allow_change_pass"]) {
        if (isset($rUser)) {
            $_POST["password"] = $rUser["password"];
        } else {
            $_POST["password"] = "";
        }
    }
    if ((!$rPermissions["allow_change_pass"]) && (!$rAdminSettings["change_usernames"])) {
        if (isset($rUser)) {
			$_POST["username"] = $rUser["username"];
        } else {
			$_POST["username"] = "";
        }
    }
    if ((strlen($_POST["username"]) == 0) OR (($rArray["is_mag"]) && (!isset($rUser))) OR (($rArray["is_e2"]) && (!isset($rUser)))) {
        $_POST["username"] = generateString(10);
    } else if ((($rArray["is_mag"]) && (isset($rUser))) OR (($rArray["is_e2"]) && (isset($rUser)))) {
        $_POST["username"] = $rUser["username"];
    }
    if ((strlen($_POST["password"]) == 0) OR (($rArray["is_mag"]) && (!isset($rUser))) OR (($rArray["is_e2"]) && (!isset($rUser)))) {
        $_POST["password"] = generateString(10);
    } else if ((($rArray["is_mag"]) && (isset($rUser))) OR (($rArray["is_e2"]) && (isset($rUser)))) {
        $_POST["password"] = $rUser["password"];
    }
    $rArray["username"] = $_POST["username"];
    $rArray["password"] = $_POST["password"];
    if (!isset($rUser)) {
        $result = $db->query("SELECT `id` FROM `users` WHERE `username` = '".ESC($rArray["username"])."';");
        if (($result) && ($result->num_rows > 0)) {
            $_STATUS = 6; // Username in use.
        }
    }
    if ((($_POST["is_mag"]) && (!filter_var($_POST["mac_address_mag"], FILTER_VALIDATE_MAC))) OR ((strlen($_POST["mac_address_e2"]) > 0) && (!filter_var($_POST["mac_address_e2"], FILTER_VALIDATE_MAC)))) {
        $_STATUS = 7;
    } else if ($_POST["is_mag"]) {
        $result = $db->query("SELECT `user_id` FROM `mag_devices` WHERE mac = '".ESC(base64_encode($_POST["mac_address_mag"]))."' LIMIT 1;");
        if (($result) && ($result->num_rows > 0)) {
            if (isset($_POST["edit"])) {
                if (intval($result->fetch_assoc()["user_id"]) <> intval($_POST["edit"])) {
                    $_STATUS = 8; // MAC in use.
                }
            } else {
                $_STATUS = 8; // MAC in use.
            }
        }
    } else if ($_POST["is_e2"]) {
        $result = $db->query("SELECT `user_id` FROM `enigma2_devices` WHERE mac = '".ESC($_POST["mac_address_e2"])."' LIMIT 1;");
        if (($result) && ($result->num_rows > 0)) {
            if (isset($_POST["edit"])) {
                if (intval($result->fetch_assoc()["user_id"]) <> intval($_POST["edit"])) {
                    $_STATUS = 8; // MAC in use.
                }
            } else {
                $_STATUS = 8; // MAC in use.
            }
        }
    }
    if ($rAdminSettings["reseller_restrictions"]) {
        if (isset($_POST["allowed_ips"])) {
            if (!is_array($_POST["allowed_ips"])) {
                $_POST["allowed_ips"] = Array($_POST["allowed_ips"]);
            }
            $rArray["allowed_ips"] = json_encode($_POST["allowed_ips"]);
        } else {
            $rArray["allowed_ips"] = "[]";
        }
        if (isset($_POST["allowed_ua"])) {
            if (!is_array($_POST["allowed_ua"])) {
                $_POST["allowed_ua"] = Array($_POST["allowed_ua"]);
            }
            $rArray["allowed_ua"] = json_encode($_POST["allowed_ua"]);
        } else {
            $rArray["allowed_ua"] = "[]";
        }
    }
    if (!isset($_STATUS)) {
        $rArray["created_by"] = $rUserInfo["id"];
        $rCols = "`".ESC(implode('`,`', array_keys($rArray)))."`";
        foreach (array_values($rArray) as $rValue) {
            isset($rValues) ? $rValues .= ',' : $rValues = '';
            if (is_array($rValue)) {    
                $rValue = json_encode($rValue);
            }
            if (is_null($rValue)) {
                $rValues .= 'NULL';
            } else {
                $rValues .= '\''.ESC($rValue).'\'';
            }
        }
        if (isset($rUser)) {
            $rCols = "`id`,".$rCols;
            $rValues = ESC($rUser["id"]).",".$rValues;
        }
        $isMag = False; $isE2 = False;
        // Confirm Reseller can generate MAG.
        if ($rArray["is_mag"]) {
            if (($rPackage["can_gen_mag"]) OR (isset($rUser))) {
                $isMag = True;
            }
        }
        if ($rArray["is_e2"]) {
            if (($rPackage["can_gen_e2"]) OR (isset($rUser))) {
                $isE2 = True;
            }
        }
        if ((!$isMag) && (!$isE2) && (($rPackage["only_mag"]) OR ($rPackage["only_e2"])) AND (!isset($rUser))) {
            $_STATUS = 5; // Not allowed to generate normal users!
        } else {
            // Checks completed, run,
            $rQuery = "REPLACE INTO `users`(".$rCols.") VALUES(".$rValues.");";
            if ($db->query($rQuery)) {
                if (isset($rUser)) {
                    $rInsertID = intval($rUser["id"]);
                } else {
                    $rInsertID = $db->insert_id;
                }
                if (isset($rCost)) {
                    $rNewCredits = floatval($rUserInfo["credits"]) - floatval($rCost);
                    $db->query("UPDATE `reg_users` SET `credits` = '".floatval($rNewCredits)."' WHERE `id` = ".intval($rUserInfo["id"]).";");
                    // added ".ESC($_POST["mac_address_mag"])." into line 222 and 230, reseller logs will have mag mac address
                    if (isset($rUser)) {
                        if ($isMag) {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rArray["username"])."', '".ESC($rArray["password"])."', ".intval(time()).", '[<b>UserPanel</b> -> <u>Extend MAG</u>] ".ESC($_POST["mac_address_mag"])." with Package [".ESC($rPackage["package_name"])."], Credits: <font color=\"green\">".ESC($rUserInfo["credits"])."</font> -> <font color=\"red\">".$rNewCredits."</font>');");
                        } else if ($isE2) {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rArray["username"])."', '".ESC($rArray["password"])."', ".intval(time()).", '[<b>UserPanel</b> -> <u>Extend Enigma</u>] with Package [".ESC($rPackage["package_name"])."], Credits: <font color=\"green\">".ESC($rUserInfo["credits"])."</font> -> <font color=\"red\">".$rNewCredits."</font>');");
                        } else {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rArray["username"])."', '".ESC($rArray["password"])."', ".intval(time()).", '[<b>UserPanel</b> -> <u>Extend Line</u>] with Package [".ESC($rPackage["package_name"])."], Credits: <font color=\"green\">".ESC($rUserInfo["credits"])."</font> -> <font color=\"red\">".$rNewCredits."</font>');");
                        }
                    } else {
                        if ($isMag) {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rArray["username"])."', '".ESC($rArray["password"])."', ".intval(time()).", '[<b>UserPanel</b> -> <u>New MAG</u>] ".ESC($_POST["mac_address_mag"])." with Package ".ESC($rPackage["package_name"])."], Credits: <font color=\"green\">".ESC($rUserInfo["credits"])."</font> -> <font color=\"red\">".$rNewCredits."</font>');");
                        } else if ($isE2) {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rArray["username"])."', '".ESC($rArray["password"])."', ".intval(time()).", '[<b>UserPanel</b> -> <u>New Enigma</u>] with Package [".ESC($rPackage["package_name"])."], Credits: <font color=\"green\">".ESC($rUserInfo["credits"])."</font> -> <font color=\"red\">".$rNewCredits."</font>');");
                        } else {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rArray["username"])."', '".ESC($rArray["password"])."', ".intval(time()).", '[<b>UserPanel</b> -> <u>New Line</u>] with Package [".ESC($rPackage["package_name"])."], Credits: <font color=\"green\">".ESC($rUserInfo["credits"])."</font> -> <font color=\"red\">".$rNewCredits."</font>');");
                        }
                        $rAccessOutput = json_decode($rPackage["output_formats"], True);
                        $rLockDevice = $rPackage["lock_device"];
                    }
                    $rUserInfo["credits"] = $rNewCredits;
                }
                if ((!isset($rUser)) && ((isset($rInsertID)) && (isset($rAccessOutput)))) {
                    $db->query("DELETE FROM `user_output` WHERE `user_id` = ".intval($rInsertID).";");
                    foreach ($rAccessOutput as $rOutputID) {
                        $db->query("INSERT INTO `user_output`(`user_id`, `access_output_id`) VALUES(".intval($rInsertID).", ".intval($rOutputID).");");
                    }
                }
                if ($isMag) {
                    $result = $db->query("SELECT `mag_id` FROM `mag_devices` WHERE `user_id` = ".intval($rInsertID)." LIMIT 1;");
                    if ((isset($result)) && ($result->num_rows == 1)) {
                        $db->query("UPDATE `mag_devices` SET `mac` = '".base64_encode(ESC(strtoupper($_POST["mac_address_mag"])))."' WHERE `user_id` = ".intval($rInsertID).";");
                    } else if (!isset($rUser)) {
                        $db->query("INSERT INTO `mag_devices`(`user_id`, `mac`, `lock_device`) VALUES(".intval($rInsertID).", '".ESC(base64_encode(strtoupper($_POST["mac_address_mag"])))."', ".intval($rLockDevice).");");
                    }
                } else if ($isE2) {
                    $result = $db->query("SELECT `device_id` FROM `enigma2_devices` WHERE `user_id` = ".intval($rInsertID)." LIMIT 1;");
                    if ((isset($result)) && ($result->num_rows == 1)) {
                        $db->query("UPDATE `enigma2_devices` SET `mac` = '".ESC(strtoupper($_POST["mac_address_e2"]))."' WHERE `user_id` = ".intval($rInsertID).";");
                    } else if (!isset($rUser)) {
                        $db->query("INSERT INTO `enigma2_devices`(`user_id`, `mac`, `lock_device`) VALUES(".intval($rInsertID).", '".ESC(strtoupper($_POST["mac_address_e2"]))."', ".intval($rLockDevice).");");
                    }
                }
                header("Location: ./user_reseller.php?id=".$rInsertID); exit;
            } else {
                $_STATUS = 2;
            }
        }
    }
}

if (isset($_GET["id"])) {
    if (!hasPermissions("user", $_GET["id"])) { exit; }
    $rUser = getUser($_GET["id"]);
    if (!$rUser) {
        exit;
    }
    $rUser["mac_address_mag"] = base64_decode(getMAGUser($_GET["id"])["mac"]);
    $rUser["mac_address_e2"] = getE2User($_GET["id"])["mac"];
    $rUser["outputs"] = getOutputs($rUser["id"]);
}

$rCountries = Array(Array("id" => "", "name" => "Off"), Array("id" => "A1", "name" => "Anonymous Proxy"), Array("id" => "A2", "name" => "Satellite Provider"), Array("id" => "O1", "name" => "Other Country"), Array("id" => "AF", "name" => "Afghanistan"), Array("id" => "AX", "name" => "Aland Islands"), Array("id" => "AL", "name" => "Albania"), Array("id" => "DZ", "name" => "Algeria"), Array("id" => "AS", "name" => "American Samoa"), Array("id" => "AD", "name" => "Andorra"), Array("id" => "AO", "name" => "Angola"), Array("id" => "AI", "name" => "Anguilla"), Array("id" => "AQ", "name" => "Antarctica"), Array("id" => "AG", "name" => "Antigua And Barbuda"), Array("id" => "AR", "name" => "Argentina"), Array("id" => "AM", "name" => "Armenia"), Array("id" => "AW", "name" => "Aruba"), Array("id" => "AU", "name" => "Australia"), Array("id" => "AT", "name" => "Austria"), Array("id" => "AZ", "name" => "Azerbaijan"), Array("id" => "BS", "name" => "Bahamas"), Array("id" => "BH", "name" => "Bahrain"), Array("id" => "BD", "name" => "Bangladesh"), Array("id" => "BB", "name" => "Barbados"), Array("id" => "BY", "name" => "Belarus"), Array("id" => "BE", "name" => "Belgium"), Array("id" => "BZ", "name" => "Belize"), Array("id" => "BJ", "name" => "Benin"), Array("id" => "BM", "name" => "Bermuda"), Array("id" => "BT", "name" => "Bhutan"), Array("id" => "BO", "name" => "Bolivia"), Array("id" => "BA", "name" => "Bosnia And Herzegovina"), Array("id" => "BW", "name" => "Botswana"), Array("id" => "BV", "name" => "Bouvet Island"), Array("id" => "BR", "name" => "Brazil"), Array("id" => "IO", "name" => "British Indian Ocean Territory"), Array("id" => "BN", "name" => "Brunei Darussalam"), Array("id" => "BG", "name" => "Bulgaria"), Array("id" => "BF", "name" => "Burkina Faso"), Array("id" => "BI", "name" => "Burundi"), Array("id" => "KH", "name" => "Cambodia"), Array("id" => "CM", "name" => "Cameroon"), Array("id" => "CA", "name" => "Canada"), Array("id" => "CV", "name" => "Cape Verde"), Array("id" => "KY", "name" => "Cayman Islands"), Array("id" => "CF", "name" => "Central African Republic"), Array("id" => "TD", "name" => "Chad"), Array("id" => "CL", "name" => "Chile"), Array("id" => "CN", "name" => "China"), Array("id" => "CX", "name" => "Christmas Island"), Array("id" => "CC", "name" => "Cocos (Keeling) Islands"), Array("id" => "CO", "name" => "Colombia"), Array("id" => "KM", "name" => "Comoros"), Array("id" => "CG", "name" => "Congo"), Array("id" => "CD", "name" => "Congo, Democratic Republic"), Array("id" => "CK", "name" => "Cook Islands"), Array("id" => "CR", "name" => "Costa Rica"), Array("id" => "CI", "name" => "Cote D'Ivoire"), Array("id" => "HR", "name" => "Croatia"), Array("id" => "CU", "name" => "Cuba"), Array("id" => "CY", "name" => "Cyprus"), Array("id" => "CZ", "name" => "Czech Republic"), Array("id" => "DK", "name" => "Denmark"), Array("id" => "DJ", "name" => "Djibouti"), Array("id" => "DM", "name" => "Dominica"), Array("id" => "DO", "name" => "Dominican Republic"), Array("id" => "EC", "name" => "Ecuador"), Array("id" => "EG", "name" => "Egypt"), Array("id" => "SV", "name" => "El Salvador"), Array("id" => "GQ", "name" => "Equatorial Guinea"), Array("id" => "ER", "name" => "Eritrea"), Array("id" => "EE", "name" => "Estonia"), Array("id" => "ET", "name" => "Ethiopia"), Array("id" => "FK", "name" => "Falkland Islands (Malvinas)"), Array("id" => "FO", "name" => "Faroe Islands"), Array("id" => "FJ", "name" => "Fiji"), Array("id" => "FI", "name" => "Finland"), Array("id" => "FR", "name" => "France"), Array("id" => "GF", "name" => "French Guiana"), Array("id" => "PF", "name" => "French Polynesia"), Array("id" => "TF", "name" => "French Southern Territories"), Array("id" => "MK", "name" => "Fyrom"), Array("id" => "GA", "name" => "Gabon"), Array("id" => "GM", "name" => "Gambia"), Array("id" => "GE", "name" => "Georgia"), Array("id" => "DE", "name" => "Germany"), Array("id" => "GH", "name" => "Ghana"), Array("id" => "GI", "name" => "Gibraltar"), Array("id" => "GR", "name" => "Greece"), Array("id" => "GL", "name" => "Greenland"), Array("id" => "GD", "name" => "Grenada"), Array("id" => "GP", "name" => "Guadeloupe"), Array("id" => "GU", "name" => "Guam"), Array("id" => "GT", "name" => "Guatemala"), Array("id" => "GG", "name" => "Guernsey"), Array("id" => "GN", "name" => "Guinea"), Array("id" => "GW", "name" => "Guinea-Bissau"), Array("id" => "GY", "name" => "Guyana"), Array("id" => "HT", "name" => "Haiti"), Array("id" => "HM", "name" => "Heard Island & Mcdonald Islands"), Array("id" => "VA", "name" => "Holy See (Vatican City State)"), Array("id" => "HN", "name" => "Honduras"), Array("id" => "HK", "name" => "Hong Kong"), Array("id" => "HU", "name" => "Hungary"), Array("id" => "IS", "name" => "Iceland"), Array("id" => "IN", "name" => "India"), Array("id" => "ID", "name" => "Indonesia"), Array("id" => "IR", "name" => "Iran, Islamic Republic Of"), Array("id" => "IQ", "name" => "Iraq"), Array("id" => "IE", "name" => "Ireland"), Array("id" => "IM", "name" => "Isle Of Man"), Array("id" => "IL", "name" => "Israel"), Array("id" => "IT", "name" => "Italy"), Array("id" => "JM", "name" => "Jamaica"), Array("id" => "JP", "name" => "Japan"), Array("id" => "JE", "name" => "Jersey"), Array("id" => "JO", "name" => "Jordan"), Array("id" => "KZ", "name" => "Kazakhstan"), Array("id" => "KE", "name" => "Kenya"), Array("id" => "KI", "name" => "Kiribati"), Array("id" => "KR", "name" => "Korea"), Array("id" => "KW", "name" => "Kuwait"), Array("id" => "KG", "name" => "Kyrgyzstan"), Array("id" => "LA", "name" => "Lao People's Democratic Republic"), Array("id" => "LV", "name" => "Latvia"), Array("id" => "LB", "name" => "Lebanon"), Array("id" => "LS", "name" => "Lesotho"), Array("id" => "LR", "name" => "Liberia"), Array("id" => "LY", "name" => "Libyan Arab Jamahiriya"), Array("id" => "LI", "name" => "Liechtenstein"), Array("id" => "LT", "name" => "Lithuania"), Array("id" => "LU", "name" => "Luxembourg"), Array("id" => "MO", "name" => "Macao"), Array("id" => "MG", "name" => "Madagascar"), Array("id" => "MW", "name" => "Malawi"), Array("id" => "MY", "name" => "Malaysia"), Array("id" => "MV", "name" => "Maldives"), Array("id" => "ML", "name" => "Mali"), Array("id" => "MT", "name" => "Malta"), Array("id" => "MH", "name" => "Marshall Islands"), Array("id" => "MQ", "name" => "Martinique"), Array("id" => "MR", "name" => "Mauritania"), Array("id" => "MU", "name" => "Mauritius"), Array("id" => "YT", "name" => "Mayotte"), Array("id" => "MX", "name" => "Mexico"), Array("id" => "FM", "name" => "Micronesia, Federated States Of"), Array("id" => "MD", "name" => "Moldova"), Array("id" => "MC", "name" => "Monaco"), Array("id" => "MN", "name" => "Mongolia"), Array("id" => "ME", "name" => "Montenegro"), Array("id" => "MS", "name" => "Montserrat"), Array("id" => "MA", "name" => "Morocco"), Array("id" => "MZ", "name" => "Mozambique"), Array("id" => "MM", "name" => "Myanmar"), Array("id" => "NA", "name" => "Namibia"), Array("id" => "NR", "name" => "Nauru"), Array("id" => "NP", "name" => "Nepal"), Array("id" => "NL", "name" => "Netherlands"), Array("id" => "AN", "name" => "Netherlands Antilles"), Array("id" => "NC", "name" => "New Caledonia"), Array("id" => "NZ", "name" => "New Zealand"), Array("id" => "NI", "name" => "Nicaragua"), Array("id" => "NE", "name" => "Niger"), Array("id" => "NG", "name" => "Nigeria"), Array("id" => "NU", "name" => "Niue"), Array("id" => "NF", "name" => "Norfolk Island"), Array("id" => "MP", "name" => "Northern Mariana Islands"), Array("id" => "NO", "name" => "Norway"), Array("id" => "OM", "name" => "Oman"), Array("id" => "PK", "name" => "Pakistan"), Array("id" => "PW", "name" => "Palau"), Array("id" => "PS", "name" => "Palestinian Territory, Occupied"), Array("id" => "PA", "name" => "Panama"), Array("id" => "PG", "name" => "Papua New Guinea"), Array("id" => "PY", "name" => "Paraguay"), Array("id" => "PE", "name" => "Peru"), Array("id" => "PH", "name" => "Philippines"), Array("id" => "PN", "name" => "Pitcairn"), Array("id" => "PL", "name" => "Poland"), Array("id" => "PT", "name" => "Portugal"), Array("id" => "PR", "name" => "Puerto Rico"), Array("id" => "QA", "name" => "Qatar"), Array("id" => "RE", "name" => "Reunion"), Array("id" => "RO", "name" => "Romania"), Array("id" => "RU", "name" => "Russian Federation"), Array("id" => "RW", "name" => "Rwanda"), Array("id" => "BL", "name" => "Saint Barthelemy"), Array("id" => "SH", "name" => "Saint Helena"), Array("id" => "KN", "name" => "Saint Kitts And Nevis"), Array("id" => "LC", "name" => "Saint Lucia"), Array("id" => "MF", "name" => "Saint Martin"), Array("id" => "PM", "name" => "Saint Pierre And Miquelon"), Array("id" => "VC", "name" => "Saint Vincent And Grenadines"), Array("id" => "WS", "name" => "Samoa"), Array("id" => "SM", "name" => "San Marino"), Array("id" => "ST", "name" => "Sao Tome And Principe"), Array("id" => "SA", "name" => "Saudi Arabia"), Array("id" => "SN", "name" => "Senegal"), Array("id" => "RS", "name" => "Serbia"), Array("id" => "SC", "name" => "Seychelles"), Array("id" => "SL", "name" => "Sierra Leone"), Array("id" => "SG", "name" => "Singapore"), Array("id" => "SK", "name" => "Slovakia"), Array("id" => "SI", "name" => "Slovenia"), Array("id" => "SB", "name" => "Solomon Islands"), Array("id" => "SO", "name" => "Somalia"), Array("id" => "ZA", "name" => "South Africa"), Array("id" => "GS", "name" => "South Georgia And Sandwich Isl."), Array("id" => "ES", "name" => "Spain"), Array("id" => "LK", "name" => "Sri Lanka"), Array("id" => "SD", "name" => "Sudan"), Array("id" => "SR", "name" => "Suriname"), Array("id" => "SJ", "name" => "Svalbard And Jan Mayen"), Array("id" => "SZ", "name" => "Swaziland"), Array("id" => "SE", "name" => "Sweden"), Array("id" => "CH", "name" => "Switzerland"), Array("id" => "SY", "name" => "Syrian Arab Republic"), Array("id" => "TW", "name" => "Taiwan"), Array("id" => "TJ", "name" => "Tajikistan"), Array("id" => "TZ", "name" => "Tanzania"), Array("id" => "TH", "name" => "Thailand"), Array("id" => "TL", "name" => "Timor-Leste"), Array("id" => "TG", "name" => "Togo"), Array("id" => "TK", "name" => "Tokelau"), Array("id" => "TO", "name" => "Tonga"), Array("id" => "TT", "name" => "Trinidad And Tobago"), Array("id" => "TN", "name" => "Tunisia"), Array("id" => "TR", "name" => "Turkey"), Array("id" => "TM", "name" => "Turkmenistan"), Array("id" => "TC", "name" => "Turks And Caicos Islands"), Array("id" => "TV", "name" => "Tuvalu"), Array("id" => "UG", "name" => "Uganda"), Array("id" => "UA", "name" => "Ukraine"), Array("id" => "AE", "name" => "United Arab Emirates"), Array("id" => "GB", "name" => "United Kingdom"), Array("id" => "US", "name" => "United States"), Array("id" => "UM", "name" => "United States Outlying Islands"), Array("id" => "UY", "name" => "Uruguay"), Array("id" => "UZ", "name" => "Uzbekistan"), Array("id" => "VU", "name" => "Vanuatu"), Array("id" => "VE", "name" => "Venezuela"), Array("id" => "VN", "name" => "Viet Nam"), Array("id" => "VG", "name" => "Virgin Islands, British"), Array("id" => "VI", "name" => "Virgin Islands, U.S."), Array("id" => "WF", "name" => "Wallis And Futuna"), Array("id" => "EH", "name" => "Western Sahara"), Array("id" => "YE", "name" => "Yemen"), Array("id" => "ZM", "name" => "Zambia"), Array("id" => "ZW", "name" => "Zimbabwe"));

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
        if ($rSettings["sidebar"]) { ?>
        <div class="content-page"><div class="content boxed-layout"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper boxed-layout"><div class="container-fluid">
        <?php } ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <a href="./users.php"><li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> Back to Users</li></a>
                                </ol>
                            </div>
                            <h4 class="page-title"><?php if (isset($rUser)) { echo "Edit"; } else { echo "Add"; } ?> <?php if (isset($_GET["trial"])) { echo "Trial "; } ?>User</h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-xl-12">
                        <?php if (!$canGenerateTrials) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            You have used your allowance of trials for this period. Please try again later.
                        </div>
                        <?php }
                        if (isset($_STATUS)) {
                        if ($_STATUS == 0) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            User operation was completed successfully.
                        </div>
                        <?php } else if ($_STATUS == 1) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            An invalid expiration date was entered. Please try again.
                        </div>
                        <?php } else if ($_STATUS == 2) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            There was an error performing this operation! Please check the form entry and try again.
                        </div>
                        <?php } else if ($_STATUS == 3) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            An invalid package was selected. Please try again.
                        </div>
                        <?php } else if ($_STATUS == 4) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            You don't have enough credits to complete this purchase!
                        </div>
                        <?php } else if ($_STATUS == 5) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            You are not permitted to generate normal users!
                        </div>
                        <?php } else if ($_STATUS == 6) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            This username already exists. Please try another.
                        </div>
                        <?php } else if ($_STATUS == 7) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            An invalid MAC address was entered, please try again.
                        </div>
                        <?php } else if ($_STATUS == 8) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            This MAC address is already in use. Please use another.
                        </div>
                        <?php }
                        }
                        if ((isset($rUser)) AND ($rUser["is_trial"])) { ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            This is a trial user. Extending the package will convert it to an official package.
                        </div>
                        <?php  } ?>
                        <div class="card">
                            <div class="card-body">
                                <form action="./user_reseller.php<?php if (isset($_GET["id"])) { echo "?id=".$_GET["id"]; } ?>" method="POST" id="user_form">
                                    <?php if (isset($rUser)) { ?>
                                    <input type="hidden" name="edit" value="<?=$rUser["id"]?>" />
                                    <?php }
                                    if (isset($_GET["trial"])) { ?>
                                    <input type="hidden" name="trial" value="1" />
                                    <?php } ?>
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#user-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline">Details</span>
                                                </a>
                                            </li>
                                            <?php if ($rAdminSettings["reseller_restrictions"]) { ?>
											<li class="nav-item">
                                                <a href="#restrictions" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-hazard-lights mr-1"></i>
                                                    <span class="d-none d-sm-inline">Restrictions</span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <li class="nav-item">
                                                <a href="#review-purchase" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-book-open-variant mr-1"></i>
                                                    <span class="d-none d-sm-inline">Review Purchase</span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="user-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4" id="uname">
                                                            <label class="col-md-4 col-form-label" for="username">Username</label>
                                                            <div class="col-md-8">
                                                                <input<?php if ((!$rPermissions["allow_change_pass"]) && (!$rAdminSettings["change_usernames"])) { echo " disabled"; } ?> type="text" class="form-control" id="username" name="username" placeholder="auto-generate if blank" value="<?php if (isset($rUser)) { echo htmlspecialchars($rUser["username"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4" id="pass">
                                                            <label class="col-md-4 col-form-label" for="password">Password</label>
                                                            <div class="col-md-8">
                                                                <input<?php if (!$rPermissions["allow_change_pass"]) { echo " disabled"; } ?> type="text" class="form-control" id="password" name="password" placeholder="auto-generate if blank" value="<?php if (isset($rUser)) { echo htmlspecialchars($rUser["password"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="member_id">Owner</label>
                                                            <div class="col-md-8">
                                                                <select name="member_id" id="member_id" class="form-control select2" data-toggle="select2">
                                                                    <?php foreach ($rRegisteredUsers as $rRegisteredUser) { ?>
                                                                    <option <?php if (isset($rUser)) { if (intval($rUser["member_id"]) == intval($rRegisteredUser["id"])) { echo "selected "; } } else if ($rUserInfo["id"] == $rRegisteredUser["id"]) { echo "selected "; } ?>value="<?=$rRegisteredUser["id"]?>"><?=$rRegisteredUser["username"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="package"><?php if (isset($rUser)) { echo "Extend "; } ?>Package</label>
                                                            <div class="col-md-8">
                                                                <select name="package" id="package" class="form-control select2" data-toggle="select2">
                                                                    <?php if (isset($rUser)) { ?>
                                                                    <option value="">No Changes</option>
                                                                    <?php }
                                                                    foreach (getPackages() as $rPackage) {
                                                                    if (in_array($rUserInfo["member_group_id"], json_decode($rPackage["groups"], True))) {
                                                                        if ((($rPackage["is_trial"]) && ((isset($_GET["trial"])) OR (isset($_POST["trial"])))) OR (($rPackage["is_official"]) && ((!isset($_GET["trial"])) AND (!isset($_POST["trial"]))))) { ?>
                                                                        <option value="<?=$rPackage["id"]?>"><?=$rPackage["package_name"]?></option>
                                                                        <?php }
                                                                        }
                                                                    } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="max_connections">Max Connections</label>
                                                            <div class="col-md-2">
                                                                <input disabled type="text" class="form-control" id="max_connections" name="max_connections" value="<?php if (isset($rUser)) { echo htmlspecialchars($rUser["max_connections"]); } else { echo "1"; } ?>">
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="exp_date">Expiry <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Leave blank for unlimited." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2" style="padding-right: 0px; padding-left: 0px;">
                                                                <input type="text" style="padding-right: 1px; padding-left: 1px;" disabled class="form-control text-center date" id="exp_date" name="exp_date" value="<?php if (isset($rUser)) { if (!is_null($rUser["exp_date"])) { echo date("Y-m-d HH:mm", $rUser["exp_date"]); } else { echo "\" disabled=\"disabled"; } } ?>" data-toggle="date-picker" data-single-date-picker="true">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="is_mag">MAG Device <i data-toggle="tooltip" data-placement="top" title="" data-original-title="This option will be selected if this device is a MAG set top box. This will be a sub account and should not be modified directly." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input<?php if (isset($rUser)) { echo " disabled"; } ?> name="is_mag" id="is_mag" type="checkbox" <?php if (isset($rUser)) { if ($rUser["is_mag"] == 1) { echo "checked "; } } else if (isset($_GET["mag"])) { echo "checked "; } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="is_e2">Enigma Device <i data-toggle="tooltip" data-placement="top" title="" data-original-title="This option will be selected if this device is a Enigma set top box. This will be a sub account and should not be modified directly." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input<?php if (isset($rUser)) { echo " disabled"; } ?> name="is_e2" id="is_e2" type="checkbox" <?php if (isset($rUser)) { if ($rUser["is_e2"] == 1) { echo "checked "; } } else if (isset($_GET["e2"])) { echo "checked "; } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4" style="display:none" id="mac_entry_mag">
                                                            <label class="col-md-4 col-form-label" for="mac_address_mag">MAC Address</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="mac_address_mag" name="mac_address_mag" value="<?php if (isset($rUser)) { echo htmlspecialchars($rUser["mac_address_mag"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4" style="display:none" id="mac_entry_e2">
                                                            <label class="col-md-4 col-form-label" for="mac_address_e2">MAC Address</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="mac_address_e2" name="mac_address_e2" value="<?php if (isset($rUser)) { echo htmlspecialchars($rUser["mac_address_e2"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="reseller_notes">Reseller Notes</label>
                                                            <div class="col-md-8">
                                                                <textarea id="reseller_notes" name="reseller_notes" class="form-control" rows="3" placeholder=""><?php if (isset($rUser)) { echo htmlspecialchars($rUser["reseller_notes"]); } ?></textarea>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <?php if ($rAdminSettings["reseller_restrictions"]) { ?>
											<div class="tab-pane" id="restrictions">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="ip_field">Allowed IP Addresses</label>
                                                            <div class="col-md-8 input-group">
                                                                <input type="text" id="ip_field" class="form-control" value="">
                                                                <div class="input-group-append">
                                                                    <a href="javascript:void(0)" id="add_ip" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-plus"></i></a>
                                                                    <a href="javascript:void(0)" id="remove_ip" class="btn btn-danger waves-effect waves-light"><i class="mdi mdi-close"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="allowed_ips">&nbsp;</label>
                                                            <div class="col-md-8">
                                                                <select class="form-control" id="allowed_ips" name="allowed_ips[]" size=6 class="form-control" multiple="multiple">
                                                                <?php if (isset($rUser)) { foreach(json_decode($rUser["allowed_ips"], True) as $rIP) { ?>
                                                                <option value="<?=$rIP?>"><?=$rIP?></option>
                                                                <?php } } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="ua_field">Allowed User-Agents</label>
                                                            <div class="col-md-8 input-group">
                                                                <input type="text" id="ua_field" class="form-control" value="">
                                                                <div class="input-group-append">
                                                                    <a href="javascript:void(0)" id="add_ua" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-plus"></i></a>
                                                                    <a href="javascript:void(0)" id="remove_ua" class="btn btn-danger waves-effect waves-light"><i class="mdi mdi-close"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="allowed_ua">&nbsp;</label>
                                                            <div class="col-md-8">
                                                                <select class="form-control" id="allowed_ua" name="allowed_ua[]" size=6 class="form-control" multiple="multiple">
                                                                <?php if (isset($rUser)) { foreach(json_decode($rUser["allowed_ua"], True) as $rUA) { ?>
                                                                <option value="<?=$rUA?>"><?=$rUA?></option>
                                                                <?php } } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <?php } ?>
                                            <div class="tab-pane" id="review-purchase">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="alert alert-danger" role="alert" style="display:none;" id="no-credits">
                                                            <i class="mdi mdi-block-helper mr-2"></i> You do not have enough credits to complete this transaction!
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <table class="table" id="credits-cost">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center">Total Credits</th>
                                                                        <th class="text-center">Purchase Cost</th>
                                                                        <th class="text-center">Remaining Credits</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td class="text-center"><?=number_format($rUserInfo["credits"], 2)?></td>
                                                                        <td class="text-center" id="cost_credits"></td>
                                                                        <td class="text-center" id="remaining_credits"></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                            <table id="datatable-review" class="table dt-responsive nowrap" style="margin-top:30px;">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center">ID</th>
                                                                        <th>Bouquet Name</th>
                                                                        <th class="text-center">Channels</th>
                                                                        <th class="text-center">Series</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody></tbody>
                                                            </table>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <input name="submit_user" type="submit" class="btn btn-primary purchase" value="Purchase" />
                                                    </li>
                                                </ul>
                                            </div>
                                        </div> <!-- tab-content -->
                                    </div> <!-- end #basicwizard-->
                                </form>

                            </div> <!-- end card-body -->
                        </div> <!-- end card-->
                    </div> <!-- end col -->
                </div>
            </div> <!-- end container -->
        </div>
        <!-- end wrapper -->
        <?php if ($rSettings["sidebar"]) { echo "</div>"; } ?>
        <!-- Footer Start -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12 copyright text-center"><?=getFooter()?></div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->

        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/libs/jquery-toast/jquery.toast.min.js"></script>
        <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
        <script src="assets/libs/switchery/switchery.min.js"></script>
        <script src="assets/libs/select2/select2.min.js"></script>
        <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
        <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
        <script src="assets/libs/moment/moment.min.js"></script>
        <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
        <script src="assets/libs/datatables/jquery.dataTables.min.js"></script>
        <script src="assets/libs/datatables/dataTables.bootstrap4.js"></script>
        <script src="assets/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="assets/libs/datatables/responsive.bootstrap4.min.js"></script>
        <script src="assets/libs/datatables/dataTables.buttons.min.js"></script>
        <script src="assets/libs/datatables/buttons.bootstrap4.min.js"></script>
        <script src="assets/libs/datatables/buttons.html5.min.js"></script>
        <script src="assets/libs/datatables/buttons.flash.min.js"></script>
        <script src="assets/libs/datatables/buttons.print.min.js"></script>
        <script src="assets/libs/datatables/dataTables.keyTable.min.js"></script>
        <script src="assets/libs/datatables/dataTables.select.min.js"></script>
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/js/pages/jquery.number.min.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <style>
            .daterangepicker select.ampmselect,.daterangepicker select.hourselect,.daterangepicker select.minuteselect,.daterangepicker select.secondselect{
                background:#fff;
                border:1px solid #fff;
                color:rgb(0, 0, 0)
            }
        </style>
        <script>
        var swObjs = {};
        
        (function($) {
          $.fn.inputFilter = function(inputFilter) {
            return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
              if (inputFilter(this.value)) {
                this.oldValue = this.value;
                this.oldSelectionStart = this.selectionStart;
                this.oldSelectionEnd = this.selectionEnd;
              } else if (this.hasOwnProperty("oldValue")) {
                this.value = this.oldValue;
                this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
              }
            });
          };
        }(jQuery));
        
        function isValidDate(dateString) {
              var regEx = /^\d{4}-\d{2}-\d{2}$/;
              if(!dateString.match(regEx)) return false;  // Invalid format
              var d = new Date(dateString);
              var dNum = d.getTime();
              if(!dNum && dNum !== 0) return false; // NaN value, Invalid date
              return d.toISOString().slice(0,10) === dateString;
        }
		function isValidIP(rIP) {
            if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(rIP)) {
                return true;
            } else {
                return false;
            }
        }
        function evaluateForm() {
            if (($("#is_mag").is(":checked")) || ($("#is_e2").is(":checked"))) {
                if ($("#is_mag").is(":checked")) {
                    $("#mac_entry_mag").show();
                    $("#uname").hide()
                    $("#pass").hide()
                    window.swObjs["is_e2"].disable();
                } else {
                    $("#mac_entry_e2").show();
                    $("#uname").hide()
                    $("#pass").hide()
                    window.swObjs["is_mag"].disable();
                }
            } else {
                $("#mac_entry_mag").hide();
                $("#mac_entry_e2").hide();
                $("#uname").show()
                $("#pass").show()
                <?php if (!isset($rUser)) { ?>
                window.swObjs["is_e2"].enable();
                window.swObjs["is_mag"].enable();
                <?php } else { ?>
                window.swObjs["is_e2"].disable();
                window.swObjs["is_mag"].disable();
                <?php } ?>
            }
        }
        
        $("#package").change(function() {
            getPackage();
        });
        
        function getPackage() {
            var rTable = $('#datatable-review').DataTable();
            rTable.clear();
            rTable.draw();
            if ($("#package").val().length > 0) {
                $.getJSON("./api.php?action=get_package<?php if (isset($_GET["trial"])) { echo "_trial"; } ?>&package_id=" + $("#package").val()<?php if (isset($rUser)) { echo " + \"&user_id=".$rUser["id"]."\""; } ?>, function(rData) {
                    if (rData.result === true) {
                        $("#max_connections").val(rData.data.max_connections);
                        $("#cost_credits").html($.number(rData.data.cost_credits, 2));
                        $("#remaining_credits").html($.number(<?=$rUserInfo["credits"]?> - rData.data.cost_credits, 2));
                        $("#exp_date").val(rData.data.exp_date);
                        if (<?=$rUserInfo["credits"]?> - rData.data.cost_credits < 0) {
                            $("#credits-cost").hide();
                            $("#no-credits").show()
                            $(".purchase").prop('disabled', true);
                        } else {
                            $("#credits-cost").show();
                            $("#no-credits").hide()
                            $(".purchase").prop('disabled', false);
                        }
                        <?php if (!$canGenerateTrials) { ?>
                        // No trials left!
                        $(".purchase").prop('disabled', true);
                        <?php }
                        if (!isset($rUser)) { ?>
                        if (rData.data.can_gen_mag == 0) {
                            window.swObjs["is_mag"].disable();
                            $("#mac_entry_mag").hide();
                        }
                        if (rData.data.can_gen_e2 == 0) {
                            window.swObjs["is_e2"].disable();
                            $("#mac_entry_e2").hide();
                        }
                        <?php } ?>
                        $(rData.bouquets).each(function(rIndex) {
							rTable.row.add([rData.bouquets[rIndex].id, rData.bouquets[rIndex].bouquet_name, rData.bouquets[rIndex].bouquet_channels.length, rData.bouquets[rIndex].bouquet_series.length]);
                        });
                    }
                    rTable.draw();
                });
            } else {
                $("#max_connections").val(<?=$rUser["max_connections"]?>);
                $("#cost_credits").html(0);
                $("#remaining_credits").html($.number(<?=$rUserInfo["credits"]?>, 2));
                $("#exp_date").val('<?=date("Y-m-d H:m", $rUser["exp_date"])?>');
                <?php if (!$canGenerateTrials) { ?>
                $(".purchase").prop('disabled', true);
                <?php }
                foreach (json_decode($rUser["bouquet"], True) as $rBouquetID) {
                    $rBouquetData = getBouquet($rBouquetID);
					if (strlen($rBouquetID) > 0) { ?>
					rTable.row.add([<?=$rBouquetID?>, '<?=$rBouquetData["bouquet_name"]?>', <?=count(json_decode($rBouquetData["bouquet_channels"], True))?>, <?=count(json_decode($rBouquetData["bouquet_series"], True))?>]);
					<?php }
                } ?>
                rTable.draw();
            }
        }
        
        $(document).ready(function() {
            $('select.select2').select2({width: '100%'})
            $(".js-switch").each(function (index, element) {
                var init = new Switchery(element);
                window.swObjs[element.id] = init;
            });
            $('#exp_date').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                timePicker: true,
                timePicker24Hour: true,
                minDate: new Date(),
                locale: {
                    format: 'YYYY-MM-DD HH:mm'
                }
            });
            
            $("#no_expire").change(function() {
                if ($(this).prop("checked")) {
                    $("#exp_date").prop("disabled", true);
                } else {
                    $("#exp_date").removeAttr("disabled");
                }
            });
            
            $(".js-switch").on("change" , function() {
                evaluateForm();
            });
            
            $("#datatable-review").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [0,2,3]}
                ],
                responsive: false,
                bInfo: false,
                searching: false,
                paging: false
            });
			$("#user_form").submit(function(e){
                $("#allowed_ua option").prop('selected', true);
                $("#allowed_ips option").prop('selected', true);
            });
            
            $(window).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
            $("#add_ip").click(function() {
                if (($("#ip_field").val().length > 0) && (isValidIP($("#ip_field").val()))) {
                    var o = new Option($("#ip_field").val(), $("#ip_field").val());
                    $("#allowed_ips").append(o);
                    $("#ip_field").val("");
                } else {
                    $.toast("Please enter a valid IP address.");
                }
            });
            $("#remove_ip").click(function() {
                $('#allowed_ips option:selected').remove();
            });
            $("#add_ua").click(function() {
                if ($("#ua_field").val().length > 0) {
                    var o = new Option($("#ua_field").val(), $("#ua_field").val());
                    $("#allowed_ua").append(o);
                    $("#ua_field").val("");
                } else {
                    $.toast("Please enter a user-agent.");
                }
            });
            $("#remove_ua").click(function() {
                $('#allowed_ua option:selected').remove();
            });
            $("#max_connections").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("form").attr('autocomplete', 'off');
            
            evaluateForm();
            getPackage();
        });
        </script>
    </body>
</html>