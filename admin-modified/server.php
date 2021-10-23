<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "add_server")) && (!hasPermissions("adv", "edit_server")))) { exit; }

if (isset($_POST["submit_server"])) {
    if (isset($_POST["edit"])) {
		if (!hasPermissions("adv", "edit_server")) { exit; }
        $rArray = getStreamingServersByID($_POST["edit"]);
        unset($rArray["id"]);
    } else {
		if (!hasPermissions("adv", "add_server")) { exit; }
        $rArray = Array("server_name" => "", "domain_name" => "", "server_ip" => "", "vpn_ip" => "", "diff_time_main" => 0, "http_broadcast_port" => 25461, "total_clients" => 1000, "system_os" => "", "network_interface" => "eth0", "status" => 2, "enable_geoip" => 0, "geoip_countries" => "[]", "geoip_type" => "low_priority", "can_delete" => 1, "rtmp_port" => 25462, "enable_isp" => 0, "boost_fpm" => 0, "network_guaranteed_speed" => 1000, "https_broadcast_port" => 25463, "whitelist_ips" => Array(), "timeshift_only" => 0, "isp_names" => Array());
    }
    if (strlen($_POST["server_ip"]) == 0) {
        $_STATUS = 1;
    }
    if (isset($rServers[$_POST["edit"]]["can_delete"])) {
        $rArray["can_delete"] = intval($rServers[$_POST["edit"]]["can_delete"]);
    }
    if (isset($_POST["enabled"])) {
        $rArray["enabled"] = intval($_POST["enabled"]);
        unset($_POST["enabled"]);
    }
    if (isset($_POST["total_clients"])) {
        $rArray["total_clients"] = intval($_POST["total_clients"]);
        unset($_POST["total_clients"]);
    }
	$rPorts = Array($rArray["http_broadcast_port"], $rArray["https_broadcast_port"], $rArray["rtmp_port"]);
    if (isset($_POST["http_broadcast_port"])) {
        $rArray["http_broadcast_port"] = intval($_POST["http_broadcast_port"]);
        unset($_POST["http_broadcast_port"]);
    }
    if (isset($_POST["https_broadcast_port"])) {
        $rArray["https_broadcast_port"] = intval($_POST["https_broadcast_port"]);
        unset($_POST["https_broadcast_port"]);
    }
    if (isset($_POST["rtmp_port"])) {
        $rArray["rtmp_port"] = intval($_POST["rtmp_port"]);
        unset($_POST["rtmp_port"]);
    }
    if (isset($_POST["diff_time_main"])) {
        $rArray["diff_time_main"] = intval($_POST["diff_time_main"]);
        unset($_POST["diff_time_main"]);
    }
    if (isset($_POST["network_guaranteed_speed"])) {
        $rArray["network_guaranteed_speed"] = intval($_POST["network_guaranteed_speed"]);
        unset($_POST["network_guaranteed_speed"]);
    }
    if (isset($_POST["timeshift_only"])) {
        $rArray["timeshift_only"] = true;
        unset($_POST["timeshift_only"]);
    } else {
        $rArray["timeshift_only"] = false;
    }
    if (isset($_POST["enable_geoip"])) {
        $rArray["enable_geoip"] = true;
        unset($_POST["enable_geoip"]);
    } else {
        $rArray["enable_geoip"] = false;
    }
    if (isset($_POST["geoip_countries"])) {
        $rArray["geoip_countries"] = Array();
        foreach ($_POST["geoip_countries"] as $rCountry) {
            $rArray["geoip_countries"][] = $rCountry;
        }
        unset($_POST["geoip_countries"]);
    } else {
        $rArray["geoip_countries"] = Array();
    }
    if (!isset($_STATUS)) {
        foreach($_POST as $rKey => $rValue) {
            if (isset($rArray[$rKey])) {
                $rArray[$rKey] = $rValue;
            }
        }
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
        if (isset($_POST["edit"])) {
            $rCols = "id,".$rCols;
            $rValues = ESC($_POST["edit"]).",".$rValues;
        }
        $rQuery = "REPLACE INTO `streaming_servers`(".$rCols.") VALUES(".$rValues.");";
        if ($db->query($rQuery)) {
            if (isset($_POST["edit"])) {
                $rInsertID = intval($_POST["edit"]);
				// Replace ports
				if ($rArray["http_broadcast_port"] <> $rPorts[0]) {
					changePort($rInsertID, 0, $rPorts[0], $rArray["http_broadcast_port"]);
				}
				if ($rArray["https_broadcast_port"] <> $rPorts[1]) {
					changePort($rInsertID, 1, $rPorts[1], $rArray["https_broadcast_port"]);
				}
				if ($rArray["rtmp_port"] <> $rPorts[2]) {
					changePort($rInsertID, 2, $rPorts[2], $rArray["rtmp_port"]);
				}
            } else {
                $rInsertID = $db->insert_id;
            }
			$rDifference = getTimeDifference($rInsertID);
			$db->query("UPDATE `streaming_servers` SET `diff_time_main` = ".intval($rDifference)." WHERE `id` = ".intval($rInsertID).";");
            $_STATUS = 0;
            $rServers = getStreamingServers();
            header("Location: ./server.php?id=".$rInsertID); exit;
        } else {
            $_STATUS = 2;
        }
    }
}

if (isset($_GET["id"])) {
    $rServerArr = $rServers[$_GET["id"]];
    if ((!$rServerArr) OR (!hasPermissions("adv", "edit_server"))) {
        exit;
    }
} else if (!hasPermissions("adv", "add_server")) { exit; }

$rCountries = Array(Array("id" => "ALL", "name" => "All Countries"), Array("id" => "A1", "name" => "Anonymous Proxy"), Array("id" => "A2", "name" => "Satellite Provider"), Array("id" => "O1", "name" => "Other Country"), Array("id" => "AF", "name" => "Afghanistan"), Array("id" => "AX", "name" => "Aland Islands"), Array("id" => "AL", "name" => "Albania"), Array("id" => "DZ", "name" => "Algeria"), Array("id" => "AS", "name" => "American Samoa"), Array("id" => "AD", "name" => "Andorra"), Array("id" => "AO", "name" => "Angola"), Array("id" => "AI", "name" => "Anguilla"), Array("id" => "AQ", "name" => "Antarctica"), Array("id" => "AG", "name" => "Antigua And Barbuda"), Array("id" => "AR", "name" => "Argentina"), Array("id" => "AM", "name" => "Armenia"), Array("id" => "AW", "name" => "Aruba"), Array("id" => "AU", "name" => "Australia"), Array("id" => "AT", "name" => "Austria"), Array("id" => "AZ", "name" => "Azerbaijan"), Array("id" => "BS", "name" => "Bahamas"), Array("id" => "BH", "name" => "Bahrain"), Array("id" => "BD", "name" => "Bangladesh"), Array("id" => "BB", "name" => "Barbados"), Array("id" => "BY", "name" => "Belarus"), Array("id" => "BE", "name" => "Belgium"), Array("id" => "BZ", "name" => "Belize"), Array("id" => "BJ", "name" => "Benin"), Array("id" => "BM", "name" => "Bermuda"), Array("id" => "BT", "name" => "Bhutan"), Array("id" => "BO", "name" => "Bolivia"), Array("id" => "BA", "name" => "Bosnia And Herzegovina"), Array("id" => "BW", "name" => "Botswana"), Array("id" => "BV", "name" => "Bouvet Island"), Array("id" => "BR", "name" => "Brazil"), Array("id" => "IO", "name" => "British Indian Ocean Territory"), Array("id" => "BN", "name" => "Brunei Darussalam"), Array("id" => "BG", "name" => "Bulgaria"), Array("id" => "BF", "name" => "Burkina Faso"), Array("id" => "BI", "name" => "Burundi"), Array("id" => "KH", "name" => "Cambodia"), Array("id" => "CM", "name" => "Cameroon"), Array("id" => "CA", "name" => "Canada"), Array("id" => "CV", "name" => "Cape Verde"), Array("id" => "KY", "name" => "Cayman Islands"), Array("id" => "CF", "name" => "Central African Republic"), Array("id" => "TD", "name" => "Chad"), Array("id" => "CL", "name" => "Chile"), Array("id" => "CN", "name" => "China"), Array("id" => "CX", "name" => "Christmas Island"), Array("id" => "CC", "name" => "Cocos (Keeling) Islands"), Array("id" => "CO", "name" => "Colombia"), Array("id" => "KM", "name" => "Comoros"), Array("id" => "CG", "name" => "Congo"), Array("id" => "CD", "name" => "Congo, Democratic Republic"), Array("id" => "CK", "name" => "Cook Islands"), Array("id" => "CR", "name" => "Costa Rica"), Array("id" => "CI", "name" => "Cote D'Ivoire"), Array("id" => "HR", "name" => "Croatia"), Array("id" => "CU", "name" => "Cuba"), Array("id" => "CY", "name" => "Cyprus"), Array("id" => "CZ", "name" => "Czech Republic"), Array("id" => "DK", "name" => "Denmark"), Array("id" => "DJ", "name" => "Djibouti"), Array("id" => "DM", "name" => "Dominica"), Array("id" => "DO", "name" => "Dominican Republic"), Array("id" => "EC", "name" => "Ecuador"), Array("id" => "EG", "name" => "Egypt"), Array("id" => "SV", "name" => "El Salvador"), Array("id" => "GQ", "name" => "Equatorial Guinea"), Array("id" => "ER", "name" => "Eritrea"), Array("id" => "EE", "name" => "Estonia"), Array("id" => "ET", "name" => "Ethiopia"), Array("id" => "FK", "name" => "Falkland Islands (Malvinas)"), Array("id" => "FO", "name" => "Faroe Islands"), Array("id" => "FJ", "name" => "Fiji"), Array("id" => "FI", "name" => "Finland"), Array("id" => "FR", "name" => "France"), Array("id" => "GF", "name" => "French Guiana"), Array("id" => "PF", "name" => "French Polynesia"), Array("id" => "TF", "name" => "French Southern Territories"), Array("id" => "MK", "name" => "Fyrom"), Array("id" => "GA", "name" => "Gabon"), Array("id" => "GM", "name" => "Gambia"), Array("id" => "GE", "name" => "Georgia"), Array("id" => "DE", "name" => "Germany"), Array("id" => "GH", "name" => "Ghana"), Array("id" => "GI", "name" => "Gibraltar"), Array("id" => "GR", "name" => "Greece"), Array("id" => "GL", "name" => "Greenland"), Array("id" => "GD", "name" => "Grenada"), Array("id" => "GP", "name" => "Guadeloupe"), Array("id" => "GU", "name" => "Guam"), Array("id" => "GT", "name" => "Guatemala"), Array("id" => "GG", "name" => "Guernsey"), Array("id" => "GN", "name" => "Guinea"), Array("id" => "GW", "name" => "Guinea-Bissau"), Array("id" => "GY", "name" => "Guyana"), Array("id" => "HT", "name" => "Haiti"), Array("id" => "HM", "name" => "Heard Island & Mcdonald Islands"), Array("id" => "VA", "name" => "Holy See (Vatican City State)"), Array("id" => "HN", "name" => "Honduras"), Array("id" => "HK", "name" => "Hong Kong"), Array("id" => "HU", "name" => "Hungary"), Array("id" => "IS", "name" => "Iceland"), Array("id" => "IN", "name" => "India"), Array("id" => "ID", "name" => "Indonesia"), Array("id" => "IR", "name" => "Iran, Islamic Republic Of"), Array("id" => "IQ", "name" => "Iraq"), Array("id" => "IE", "name" => "Ireland"), Array("id" => "IM", "name" => "Isle Of Man"), Array("id" => "IL", "name" => "Israel"), Array("id" => "IT", "name" => "Italy"), Array("id" => "JM", "name" => "Jamaica"), Array("id" => "JP", "name" => "Japan"), Array("id" => "JE", "name" => "Jersey"), Array("id" => "JO", "name" => "Jordan"), Array("id" => "KZ", "name" => "Kazakhstan"), Array("id" => "KE", "name" => "Kenya"), Array("id" => "KI", "name" => "Kiribati"), Array("id" => "KR", "name" => "Korea"), Array("id" => "KW", "name" => "Kuwait"), Array("id" => "KG", "name" => "Kyrgyzstan"), Array("id" => "LA", "name" => "Lao People's Democratic Republic"), Array("id" => "LV", "name" => "Latvia"), Array("id" => "LB", "name" => "Lebanon"), Array("id" => "LS", "name" => "Lesotho"), Array("id" => "LR", "name" => "Liberia"), Array("id" => "LY", "name" => "Libyan Arab Jamahiriya"), Array("id" => "LI", "name" => "Liechtenstein"), Array("id" => "LT", "name" => "Lithuania"), Array("id" => "LU", "name" => "Luxembourg"), Array("id" => "MO", "name" => "Macao"), Array("id" => "MG", "name" => "Madagascar"), Array("id" => "MW", "name" => "Malawi"), Array("id" => "MY", "name" => "Malaysia"), Array("id" => "MV", "name" => "Maldives"), Array("id" => "ML", "name" => "Mali"), Array("id" => "MT", "name" => "Malta"), Array("id" => "MH", "name" => "Marshall Islands"), Array("id" => "MQ", "name" => "Martinique"), Array("id" => "MR", "name" => "Mauritania"), Array("id" => "MU", "name" => "Mauritius"), Array("id" => "YT", "name" => "Mayotte"), Array("id" => "MX", "name" => "Mexico"), Array("id" => "FM", "name" => "Micronesia, Federated States Of"), Array("id" => "MD", "name" => "Moldova"), Array("id" => "MC", "name" => "Monaco"), Array("id" => "MN", "name" => "Mongolia"), Array("id" => "ME", "name" => "Montenegro"), Array("id" => "MS", "name" => "Montserrat"), Array("id" => "MA", "name" => "Morocco"), Array("id" => "MZ", "name" => "Mozambique"), Array("id" => "MM", "name" => "Myanmar"), Array("id" => "NA", "name" => "Namibia"), Array("id" => "NR", "name" => "Nauru"), Array("id" => "NP", "name" => "Nepal"), Array("id" => "NL", "name" => "Netherlands"), Array("id" => "AN", "name" => "Netherlands Antilles"), Array("id" => "NC", "name" => "New Caledonia"), Array("id" => "NZ", "name" => "New Zealand"), Array("id" => "NI", "name" => "Nicaragua"), Array("id" => "NE", "name" => "Niger"), Array("id" => "NG", "name" => "Nigeria"), Array("id" => "NU", "name" => "Niue"), Array("id" => "NF", "name" => "Norfolk Island"), Array("id" => "MP", "name" => "Northern Mariana Islands"), Array("id" => "NO", "name" => "Norway"), Array("id" => "OM", "name" => "Oman"), Array("id" => "PK", "name" => "Pakistan"), Array("id" => "PW", "name" => "Palau"), Array("id" => "PS", "name" => "Palestinian Territory, Occupied"), Array("id" => "PA", "name" => "Panama"), Array("id" => "PG", "name" => "Papua New Guinea"), Array("id" => "PY", "name" => "Paraguay"), Array("id" => "PE", "name" => "Peru"), Array("id" => "PH", "name" => "Philippines"), Array("id" => "PN", "name" => "Pitcairn"), Array("id" => "PL", "name" => "Poland"), Array("id" => "PT", "name" => "Portugal"), Array("id" => "PR", "name" => "Puerto Rico"), Array("id" => "QA", "name" => "Qatar"), Array("id" => "RE", "name" => "Reunion"), Array("id" => "RO", "name" => "Romania"), Array("id" => "RU", "name" => "Russian Federation"), Array("id" => "RW", "name" => "Rwanda"), Array("id" => "BL", "name" => "Saint Barthelemy"), Array("id" => "SH", "name" => "Saint Helena"), Array("id" => "KN", "name" => "Saint Kitts And Nevis"), Array("id" => "LC", "name" => "Saint Lucia"), Array("id" => "MF", "name" => "Saint Martin"), Array("id" => "PM", "name" => "Saint Pierre And Miquelon"), Array("id" => "VC", "name" => "Saint Vincent And Grenadines"), Array("id" => "WS", "name" => "Samoa"), Array("id" => "SM", "name" => "San Marino"), Array("id" => "ST", "name" => "Sao Tome And Principe"), Array("id" => "SA", "name" => "Saudi Arabia"), Array("id" => "SN", "name" => "Senegal"), Array("id" => "RS", "name" => "Serbia"), Array("id" => "SC", "name" => "Seychelles"), Array("id" => "SL", "name" => "Sierra Leone"), Array("id" => "SG", "name" => "Singapore"), Array("id" => "SK", "name" => "Slovakia"), Array("id" => "SI", "name" => "Slovenia"), Array("id" => "SB", "name" => "Solomon Islands"), Array("id" => "SO", "name" => "Somalia"), Array("id" => "ZA", "name" => "South Africa"), Array("id" => "GS", "name" => "South Georgia And Sandwich Isl."), Array("id" => "ES", "name" => "Spain"), Array("id" => "LK", "name" => "Sri Lanka"), Array("id" => "SD", "name" => "Sudan"), Array("id" => "SR", "name" => "Suriname"), Array("id" => "SJ", "name" => "Svalbard And Jan Mayen"), Array("id" => "SZ", "name" => "Swaziland"), Array("id" => "SE", "name" => "Sweden"), Array("id" => "CH", "name" => "Switzerland"), Array("id" => "SY", "name" => "Syrian Arab Republic"), Array("id" => "TW", "name" => "Taiwan"), Array("id" => "TJ", "name" => "Tajikistan"), Array("id" => "TZ", "name" => "Tanzania"), Array("id" => "TH", "name" => "Thailand"), Array("id" => "TL", "name" => "Timor-Leste"), Array("id" => "TG", "name" => "Togo"), Array("id" => "TK", "name" => "Tokelau"), Array("id" => "TO", "name" => "Tonga"), Array("id" => "TT", "name" => "Trinidad And Tobago"), Array("id" => "TN", "name" => "Tunisia"), Array("id" => "TR", "name" => "Turkey"), Array("id" => "TM", "name" => "Turkmenistan"), Array("id" => "TC", "name" => "Turks And Caicos Islands"), Array("id" => "TV", "name" => "Tuvalu"), Array("id" => "UG", "name" => "Uganda"), Array("id" => "UA", "name" => "Ukraine"), Array("id" => "AE", "name" => "United Arab Emirates"), Array("id" => "GB", "name" => "United Kingdom"), Array("id" => "US", "name" => "United States"), Array("id" => "UM", "name" => "United States Outlying Islands"), Array("id" => "UY", "name" => "Uruguay"), Array("id" => "UZ", "name" => "Uzbekistan"), Array("id" => "VU", "name" => "Vanuatu"), Array("id" => "VE", "name" => "Venezuela"), Array("id" => "VN", "name" => "Viet Nam"), Array("id" => "VG", "name" => "Virgin Islands, British"), Array("id" => "VI", "name" => "Virgin Islands, U.S."), Array("id" => "WF", "name" => "Wallis And Futuna"), Array("id" => "EH", "name" => "Western Sahara"), Array("id" => "YE", "name" => "Yemen"), Array("id" => "ZM", "name" => "Zambia"), Array("id" => "ZW", "name" => "Zimbabwe"));
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
                                    <a href="./servers.php"><li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> Back to Servers</li></a>
                                </ol>
                            </div>
                            <h4 class="page-title"><?php if (isset($rServerArr)) { echo "Edit"; } else { echo "Add"; } ?> Server</h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-xl-12">
                        <?php if ((isset($_STATUS)) && ($_STATUS == 0)) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            Server operation was completed successfully.
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            There was an error performing this operation! Please check the form entry and try again.
                        </div>
                        <?php } ?>
                        <div class="card">
                            <div class="card-body">
                                <form action="./server.php<?php if (isset($_GET["id"])) { echo "?id=".$_GET["id"]; } ?>" method="POST" id="server_form" data-parsley-validate="">
                                    <?php if (isset($rServerArr)) { ?>
                                    <input type="hidden" name="edit" value="<?=$rServerArr["id"]?>" />
                                    <input type="hidden" name="status" value="<?=$rServerArr["status"]?>" />
                                    <?php } ?>
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#server-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline">Details</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#advanced-options" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-folder-alert-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline">Advanced</span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="server-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="server_name">Server Name</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="server_name" name="server_name" value="<?php if (isset($rServerArr)) { echo htmlspecialchars($rServerArr["server_name"]); } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="domain_name">Domain Name</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="domain_name" name="domain_name" placeholder="www.example.com" value="<?php if (isset($rServerArr)) { echo htmlspecialchars($rServerArr["domain_name"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="server_ip">Server IP</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="server_ip" name="server_ip" value="<?php if (isset($rServerArr)) { echo htmlspecialchars($rServerArr["server_ip"]); } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="vpn_ip">VPN IP</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="vpn_ip" name="vpn_ip" value="<?php if (isset($rServerArr)) { echo htmlspecialchars($rServerArr["vpn_ip"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="total_clients">Max Clients</label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="total_clients" name="total_clients" value="<?php if (isset($rServerArr)) { echo htmlspecialchars($rServerArr["total_clients"]); } else { echo "1000"; } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="timeshift_only">Timeshift Only</label>
                                                            <div class="col-md-2">
                                                                <input name="timeshift_only" id="timeshift_only" type="checkbox" <?php if (isset($rServerArr)) { if ($rServerArr["timeshift_only"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
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
                                            <div class="tab-pane" id="advanced-options">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="http_broadcast_port">HTTP Port</label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="http_broadcast_port" name="http_broadcast_port" value="<?php if (isset($rServerArr)) { echo htmlspecialchars($rServerArr["http_broadcast_port"]); } else { echo "25461"; } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="https_broadcast_port">HTTPS Port</label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="https_broadcast_port" name="https_broadcast_port" value="<?php if (isset($rServerArr)) { echo htmlspecialchars($rServerArr["https_broadcast_port"]); } else { echo "25463"; } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="rtmp_port">RTMP Port</label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="rtmp_port" name="rtmp_port" value="<?php if (isset($rServerArr)) { echo htmlspecialchars($rServerArr["rtmp_port"]); } else { echo "25462"; } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="diff_time_main">Time Difference - Seconds</label>
                                                            <div class="col-md-2">
                                                                <input type="text" disabled class="form-control" id="diff_time_main" name="diff_time_main" value="<?php if (isset($rServerArr)) { echo htmlspecialchars($rServerArr["diff_time_main"]); } else { echo "0"; } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="network_interface">Network Interface</label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="network_interface" name="network_interface" value="<?php if (isset($rServerArr)) { echo htmlspecialchars($rServerArr["network_interface"]); } else { echo "eth0"; } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="network_guaranteed_speed">Network Speed - Mbps</label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="network_guaranteed_speed" name="network_guaranteed_speed" value="<?php if (isset($rServerArr)) { echo htmlspecialchars($rServerArr["network_guaranteed_speed"]); } else { echo "1000"; } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="system_os">Operating System</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="system_os" name="system_os" value="<?php if (isset($rServerArr)) { echo htmlspecialchars($rServerArr["system_os"]); } else { echo "Ubuntu 14.04.5 LTS"; } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="enable_geoip">GeoIP Load Balancing</label>
                                                            <div class="col-md-2">
                                                                <input name="enable_geoip" id="enable_geoip" type="checkbox" <?php if (isset($rServerArr)) { if ($rServerArr["enable_geoip"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <select name="geoip_type" id="geoip_type" class="form-control select2" data-toggle="select2">
                                                                    <?php foreach (Array("high_priority" => "High Priority", "low_priority" => "Low Priority", "strict" => "Strict") as $rType => $rText) { ?>
                                                                    <option <?php if (isset($rServerArr)) { if ($rServerArr["geoip_type"] == $rType) { echo "selected "; } } ?>value="<?=$rType?>"><?=$rText?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="geoip_countries">GeoIP Countries</label>
                                                            <div class="col-md-8">
                                                                <select name="geoip_countries[]" id="geoip_countries" class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Choose...">
                                                                    <?php $rSelected = json_decode($rServerArr["geoip_countries"], True);
                                                                    foreach ($rCountries as $rCountry) { ?>
                                                                    <option <?php if (isset($rServerArr)) { if (in_array($rCountry["id"], $rSelected)) { echo "selected "; } } ?>value="<?=$rCountry["id"]?>"><?=$rCountry["name"]?></option>
                                                                    <?php } ?>
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
                                                        <input name="submit_server" type="submit" class="btn btn-primary" value="<?php if (isset($rServerArr)) { echo "Edit"; } else { echo "Add"; } ?>" />
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
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/libs/parsleyjs/parsley.min.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
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
        
        $(document).ready(function() {
            $('select').select2({width: '100%'})
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            elems.forEach(function(html) {
              var switchery = new Switchery(html);
            });
            
            $('#exp_date').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                minDate: new Date(),
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });
            
            $("#no_expire").change(function() {
                if ($(this).prop("checked")) {
                    $("#exp_date").prop("disabled", true);
                } else {
                    $("#exp_date").removeAttr("disabled");
                }
            });
            
            $(window).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
            
            $("#total_clients").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#http_broadcast_port").inputFilter(function(value) { return /^\d*$/.test(value) && (value === "" || parseInt(value) <= 65535); });
            $("#https_broadcast_port").inputFilter(function(value) { return /^\d*$/.test(value) && (value === "" || parseInt(value) <= 65535); });
            $("#rtmp_port").inputFilter(function(value) { return /^\d*$/.test(value) && (value === "" || parseInt(value) <= 65535); });
            $("#diff_time_main").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#network_guaranteed_speed").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("form").attr('autocomplete', 'off');
        });
        </script>
    </body>
</html>