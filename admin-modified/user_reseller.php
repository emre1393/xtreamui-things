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
	
	
	
	
    //$canGenerateTrials = checkTrials();
//} else {
    //$canGenerateTrials = True;
//}


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
				//$rArray["bouquet"] = $rPackage["bouquets"];
				
			
                
				
                $rArray["max_connections"] = $rPackage["max_connections"];
                $rArray["is_restreamer"] = $rPackage["is_restreamer"];
                $rArray["is_isplock"] = $rPackage["is_isplock"];
                //$rArray["forced_country"] = $rPackage["forced_country"];   

                if ( (isset($rUser)) && ($rUser["forced_country"] <> $rPackage["forced_country"] )) {
                    $rArray["forced_country"] = "O1";
                } else {
                    $rArray["forced_country"] = $rPackage["forced_country"];
                }
                
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
	$rArray["bouquet"] = array_values(json_decode($_POST["bouquets_selected"], True));
    unset($_POST["bouquets_selected"]);
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
                    if (isset($rUser)) {
                        if ($isMag) {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rArray["username"])."', '".ESC($rArray["password"])."', ".intval(time()).", '[<b>UserPanel</b> -> <u>Extend MAG</u>] ".ESC($_POST["mac_address_mag"])." with Package [".ESC($rPackage["package_name"])."], Credits: <font color=\"green\">".ESC($rUserInfo["credits"])."</font> -> <font color=\"red\">".$rNewCredits."</font>');");
                        } else if ($isE2) {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rArray["username"])."', '".ESC($rArray["password"])."', ".intval(time()).", '[<b>UserPanel</b> -> <u>Extend Enigma</u>] ".ESC($_POST["mac_address_e2"])." with Package [".ESC($rPackage["package_name"])."], Credits: <font color=\"green\">".ESC($rUserInfo["credits"])."</font> -> <font color=\"red\">".$rNewCredits."</font>');");
                        } else {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rArray["username"])."', '".ESC($rArray["password"])."', ".intval(time()).", '[<b>UserPanel</b> -> <u>Extend Line</u>] ".ESC($rArray["username"])." with Package [".ESC($rPackage["package_name"])."], Credits: <font color=\"green\">".ESC($rUserInfo["credits"])."</font> -> <font color=\"red\">".$rNewCredits."</font>');");
                        }
                    } else {
                        if ($isMag) {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rArray["username"])."', '".ESC($rArray["password"])."', ".intval(time()).", '[<b>UserPanel</b> -> <u>New MAG</u>] ".ESC($_POST["mac_address_mag"])." with Package ".ESC($rPackage["package_name"])."], Credits: <font color=\"green\">".ESC($rUserInfo["credits"])."</font> -> <font color=\"red\">".$rNewCredits."</font>');");
                        } else if ($isE2) {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rArray["username"])."', '".ESC($rArray["password"])."', ".intval(time()).", '[<b>UserPanel</b> -> <u>New Enigma</u>] ".ESC($_POST["mac_address_e2"])." with Package [".ESC($rPackage["package_name"])."], Credits: <font color=\"green\">".ESC($rUserInfo["credits"])."</font> -> <font color=\"red\">".$rNewCredits."</font>');");
                        } else {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rArray["username"])."', '".ESC($rArray["password"])."', ".intval(time()).", '[<b>UserPanel</b> -> <u>New Line</u>] ".ESC($rArray["username"])." with Package [".ESC($rPackage["package_name"])."], Credits: <font color=\"green\">".ESC($rUserInfo["credits"])."</font> -> <font color=\"red\">".$rNewCredits."</font>');");
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
                $_STATUS = 0;
            } else {
                $_STATUS = 2;
            }
            if (!isset($_GET["id"])) {
                $_GET["id"] = $rInsertID;
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

$rCountries = Array("A1" => "Anonymous Proxy", "A2" => "Satellite Provider", "O1" => "Other Country, Contact with Admin", "AF" => "Afghanistan", "AX" => "Aland Islands", "AL" => "Albania", "DZ" => "Algeria", "AS" => "American Samoa", "AD" => "Andorra", "AO" => "Angola", "AI" => "Anguilla", "AQ" => "Antarctica", "AG" => "Antigua And Barbuda", "AR" => "Argentina", "AM" => "Armenia", "AW" => "Aruba", "AU" => "Australia", "AT" => "Austria", "AZ" => "Azerbaijan", "BS" => "Bahamas", "BH" => "Bahrain", "BD" => "Bangladesh", "BB" => "Barbados", "BY" => "Belarus", "BE" => "Belgium", "BZ" => "Belize", "BJ" => "Benin", "BM" => "Bermuda", "BT" => "Bhutan", "BO" => "Bolivia", "BA" => "Bosnia And Herzegovina", "BW" => "Botswana", "BV" => "Bouvet Island", "BR" => "Brazil", "IO" => "British Indian Ocean Territory", "BN" => "Brunei Darussalam", "BG" => "Bulgaria", "BF" => "Burkina Faso", "BI" => "Burundi", "KH" => "Cambodia", "CM" => "Cameroon", "CA" => "Canada", "CV" => "Cape Verde", "KY" => "Cayman Islands", "CF" => "Central African Republic", "TD" => "Chad", "CL" => "Chile", "CN" => "China", "CX" => "Christmas Island", "CC" => "Cocos (Keeling) Islands", "CO" => "Colombia", "KM" => "Comoros", "CG" => "Congo", "CD" => "Congo, Democratic Republic", "CK" => "Cook Islands", "CR" => "Costa Rica", "CI" => "Cote D'Ivoire", "HR" => "Croatia", "CU" => "Cuba", "CY" => "Cyprus", "CZ" => "Czech Republic", "DK" => "Denmark", "DJ" => "Djibouti", "DM" => "Dominica", "DO" => "Dominican Republic", "EC" => "Ecuador", "EG" => "Egypt", "SV" => "El Salvador", "GQ" => "Equatorial Guinea", "ER" => "Eritrea", "EE" => "Estonia", "ET" => "Ethiopia", "FK" => "Falkland Islands (Malvinas)", "FO" => "Faroe Islands", "FJ" => "Fiji", "FI" => "Finland", "FR" => "France", "GF" => "French Guiana", "PF" => "French Polynesia", "TF" => "French Southern Territories", "MK" => "Fyrom", "GA" => "Gabon", "GM" => "Gambia", "GE" => "Georgia", "DE" => "Germany", "GH" => "Ghana", "GI" => "Gibraltar", "GR" => "Greece", "GL" => "Greenland", "GD" => "Grenada", "GP" => "Guadeloupe", "GU" => "Guam", "GT" => "Guatemala", "GG" => "Guernsey", "GN" => "Guinea", "GW" => "Guinea-Bissau", "GY" => "Guyana", "HT" => "Haiti", "HM" => "Heard Island & Mcdonald Islands", "VA" => "Holy See (Vatican City State)", "HN" => "Honduras", "HK" => "Hong Kong", "HU" => "Hungary", "IS" => "Iceland", "IN" => "India", "ID" => "Indonesia", "IR" => "Iran, Islamic Republic Of", "IQ" => "Iraq", "IE" => "Ireland", "IM" => "Isle Of Man", "IL" => "Israel", "IT" => "Italy", "JM" => "Jamaica", "JP" => "Japan", "JE" => "Jersey", "JO" => "Jordan", "KZ" => "Kazakhstan", "KE" => "Kenya", "KI" => "Kiribati", "KR" => "Korea", "KW" => "Kuwait", "KG" => "Kyrgyzstan", "LA" => "Lao People's Democratic Republic", "LV" => "Latvia", "LB" => "Lebanon", "LS" => "Lesotho", "LR" => "Liberia", "LY" => "Libyan Arab Jamahiriya", "LI" => "Liechtenstein", "LT" => "Lithuania", "LU" => "Luxembourg", "MO" => "Macao", "MG" => "Madagascar", "MW" => "Malawi", "MY" => "Malaysia", "MV" => "Maldives", "ML" => "Mali", "MT" => "Malta", "MH" => "Marshall Islands", "MQ" => "Martinique", "MR" => "Mauritania", "MU" => "Mauritius", "YT" => "Mayotte", "MX" => "Mexico", "FM" => "Micronesia, Federated States Of", "MD" => "Moldova", "MC" => "Monaco", "MN" => "Mongolia", "ME" => "Montenegro", "MS" => "Montserrat", "MA" => "Morocco", "MZ" => "Mozambique", "MM" => "Myanmar", "NA" => "Namibia", "NR" => "Nauru", "NP" => "Nepal", "NL" => "Netherlands", "AN" => "Netherlands Antilles", "NC" => "New Caledonia", "NZ" => "New Zealand", "NI" => "Nicaragua", "NE" => "Niger", "NG" => "Nigeria", "NU" => "Niue", "NF" => "Norfolk Island", "MP" => "Northern Mariana Islands", "NO" => "Norway", "OM" => "Oman", "PK" => "Pakistan", "PW" => "Palau", "PS" => "Palestinian Territory, Occupied", "PA" => "Panama", "PG" => "Papua New Guinea", "PY" => "Paraguay", "PE" => "Peru", "PH" => "Philippines", "PN" => "Pitcairn", "PL" => "Poland", "PT" => "Portugal", "PR" => "Puerto Rico", "QA" => "Qatar", "RE" => "Reunion", "RO" => "Romania", "RU" => "Russian Federation", "RW" => "Rwanda", "BL" => "Saint Barthelemy", "SH" => "Saint Helena", "KN" => "Saint Kitts And Nevis", "LC" => "Saint Lucia", "MF" => "Saint Martin", "PM" => "Saint Pierre And Miquelon", "VC" => "Saint Vincent And Grenadines", "WS" => "Samoa", "SM" => "San Marino", "ST" => "Sao Tome And Principe", "SA" => "Saudi Arabia", "SN" => "Senegal", "RS" => "Serbia", "SC" => "Seychelles", "SL" => "Sierra Leone", "SG" => "Singapore", "SK" => "Slovakia", "SI" => "Slovenia", "SB" => "Solomon Islands", "SO" => "Somalia", "ZA" => "South Africa", "GS" => "South Georgia And Sandwich Isl.", "ES" => "Spain", "LK" => "Sri Lanka", "SD" => "Sudan", "SR" => "Suriname", "SJ" => "Svalbard And Jan Mayen", "SZ" => "Swaziland", "SE" => "Sweden", "CH" => "Switzerland", "SY" => "Syrian Arab Republic", "TW" => "Taiwan", "TJ" => "Tajikistan", "TZ" => "Tanzania", "TH" => "Thailand", "TL" => "Timor-Leste", "TG" => "Togo", "TK" => "Tokelau", "TO" => "Tonga", "TT" => "Trinidad And Tobago", "TN" => "Tunisia", "TR" => "Turkey", "TM" => "Turkmenistan", "TC" => "Turks And Caicos Islands", "TV" => "Tuvalu", "UG" => "Uganda", "UA" => "Ukraine", "AE" => "United Arab Emirates", "GB" => "United Kingdom", "US" => "United States", "UM" => "United States Outlying Islands", "UY" => "Uruguay", "UZ" => "Uzbekistan", "VU" => "Vanuatu", "VE" => "Venezuela", "VN" => "Viet Nam", "VG" => "Virgin Islands, British", "VI" => "Virgin Islands, U.S.", "WF" => "Wallis And Futuna", "EH" => "Western Sahara", "YE" => "Yemen", "ZM" => "Zambia", "ZW" => "Zimbabwe");

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
                                    <input type="hidden" name="bouquets_selected" id="bouquets_selected" value="" />
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
                                                    <span class="d-none d-sm-inline">Review Purchase/Bouquet Editor</span>
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
                                                            <label class="col-md-4 col-form-label" for="member_id">Reseller</label>
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
                                                        <?php if (isset($rUser)) { ?>
                                                        <div class="form-group row mb-4" >
                                                            <label class="col-md-4 col-form-label" >ISP Lock Status </label>
                                                            <div class="col-md-2">
                                                                <input disabled type="text" class="form-control text-center" value="<?php if (isset($rUser) && $rUser["is_isplock"] == 1)  { echo "Enabled"; } else {echo "Disabled";} ?>">
                                                            </div>
                                                        </div>

                                                        <div class="form-group row mb-4" >
                                                            <label class="col-md-4 col-form-label" >ISP Description </label>
                                                            <div class="col-md-8">
                                                                <input disabled type="text" class="form-control" value="<?php if (isset($rUser) && strlen($rUser["isp_desc"]) > 0)  { echo $rUser["isp_desc"]; } else {echo "Not available";} ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4" >
                                                            <label class="col-md-4 col-form-label" >Forced Country </label>
                                                            <div class="col-md-8">
                                                                <input disabled type="text" class="form-control" value="<?php if (isset($rUser) && strlen($rUser["forced_country"]) > 0)  { echo $rCountries[$rUser["forced_country"]]; } else {echo "Off";} ?>">
                                                            </div>
                                                        </div>

                                                        <?php } ?>
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
                                            <div class="tab-pane dt-responsive nowrap" id="bouquets" style="margin-top:30px;">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <?php foreach (getBouquets() as $rBouquet) { ?>
                                                            <div class="col-md-6">
                                                                <div class="custom-control custom-checkbox mt-1">
                                                                    <input type="checkbox" class="custom-control-input bouquet-checkbox" id="bouquet-<?=$rBouquet["id"]?>" name="bouquet[]" value="<?=$rBouquet["id"]?>"<?php if(isset($rUser)) { if(in_array($rBouquet["id"], json_decode($rUser["bouquet"], True))) { echo " checked"; } } ?>>
                                                                    <label class="custom-control-label" for="bouquet-<?=$rBouquet["id"]?>"><?=$rBouquet["bouquet_name"]?></label>
                                                                </div>
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" onClick="selectAll()" class="btn btn-secondary">Select All</a>
                                                        <a href="javascript: void(0);" onClick="selectNone()" class="btn btn-secondary">Deselect ALL</a>
                                                        <input name="submit_user" type="submit" class="btn btn-primary purchase" value="<?php if (isset($rUser)) { echo "Edit"; } else { echo "Purchase"; } ?>" />
                                                    </li>
                                                </ul>
                                            </div>
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
		<script src="assets/libs/jquery-ui/jquery-ui.min.js"></script>
        <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
        <script src="assets/libs/switchery/switchery.min.js"></script>
        <script src="assets/libs/select2/select2.min.js"></script>
        <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
        <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
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
		<script src="assets/libs/moment/moment.min.js"></script>
		<script src="assets/libs/daterangepicker/daterangepicker.js"></script>
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
		<script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/js/pages/jquery.number.min.js"></script>
		<script src="assets/libs/parsleyjs/parsley.min.js"></script>
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
        
		function selectAll() {
            $(".bouquet-checkbox").each(function() {
                $(this).prop('checked', true);
            });
        }

        function selectNone() {
            $(".bouquet-checkbox").each(function() {
                $(this).prop('checked', false);
            });
        }
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
		function isValidMac_address(address) {
            var regex = /^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/;
            if(regex.test(address)){
                return true;
            }
            else {
                return false;
            }
        }
        function isValidLink(link){
            regexp =  /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/;
            if (regexp.test(link)){
                return true;
            }
            else {
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

                    }
                });
            } else {
                $("#max_connections").val(<?=$rUser["max_connections"]?>);
                $("#cost_credits").html(0);
                $("#remaining_credits").html($.number(<?=$rUserInfo["credits"]?>, 2));
                $("#exp_date").val('<?=date("Y-m-d", $rUser["exp_date"])?>');
                <?php if (!$canGenerateTrials) { ?>
                $(".purchase").prop('disabled', true);
				<?php }?>

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
			
			$("#user_form").submit(function(e){
				var rBouquets = [];
                $("#bouquets").find(".custom-control-input:checked").each(function(){
                    rBouquets.push(parseInt($(this).val()));
                });
				if(rBouquets.length < 1){
                    $("#bouquets").find(".custom-control-input").each(function(){
                        rBouquets.push(parseInt($(this).val()));
                    });
                }
				
                $("#bouquets_selected").val(JSON.stringify(rBouquets));
                $("#allowed_ua option").prop('selected', true);
                $("#allowed_ips option").prop('selected', true);
            });
            
			
            
            $(document).keypress(function(event){
                if (event.which == '13') {
                    event.preventDefault();
                }
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