<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "mass_sedits_vod"))) { exit; }

$rCategories = getCategories("movie");

if (isset($_POST["submit_stream"])) {
    $rArray = Array();
    if (isset($_POST["c_movie_symlink"])) {
        if (isset($_POST["movie_symlink"])) {
            $rArray["movie_symlink"] = 1;
        } else {
            $rArray["movie_symlink"] = 0;
        }
    }
    if (isset($_POST["c_direct_source"])) {
        if (isset($_POST["direct_source"])) {
            $rArray["direct_source"] = 1;
        } else {
            $rArray["direct_source"] = 0;
        }
    }
    if (isset($_POST["c_read_native"])) {
        if (isset($_POST["read_native"])) {
            $rArray["read_native"] = 1;
        } else {
            $rArray["read_native"] = 0;
        }
    }
    if (isset($_POST["c_remove_subtitles"])) {
        if (isset($_POST["remove_subtitles"])) {
            $rArray["remove_subtitles"] = 1;
        } else {
            $rArray["remove_subtitles"] = 0;
        }
    }
    if (isset($_POST["c_category_id"])) {
        $rArray["category_id"] = intval($_POST["category_id"]);
    }
    if (isset($_POST["c_custom_sid"])) {
        $rArray["custom_sid"] = $_POST["custom_sid"];
    }
    if (isset($_POST["c_target_container"])) {
        $rArray["target_container"] = json_encode(Array($_POST["target_container"]));
    }
    if (isset($_POST["c_transcode_profile_id"])) {
        $rArray["transcode_profile_id"] = $_POST["transcode_profile_id"];
        if ($rArray["transcode_profile_id"] > 0) {
            $rArray["enable_transcode"] = 1;
        } else {
            $rArray["enable_transcode"] = 0;
        }
    }
    $rStreamIDs = json_decode($_POST["streams"], True);
    if (count($rStreamIDs) > 0) {
        foreach ($rStreamIDs as $rStreamID) {
            $rQueries = Array();
            foreach ($rArray as $rKey => $rValue) {
                $rQueries[] = "`".ESC($rKey)."` = '".ESC($rValue)."'";
            }
            if (count($rQueries) > 0) {
                $rQueryString = join(",", $rQueries);
                $rQuery = "UPDATE `streams` SET ".$rQueryString." WHERE `id` = ".intval($rStreamID).";";
                if (!$db->query($rQuery)) {
                    $_STATUS = 1;
                }
            }
            if (isset($_POST["c_server_tree"])) {
                $rStreamExists = Array();
                $result = $db->query("SELECT `server_stream_id`, `server_id` FROM `streams_sys` WHERE `stream_id` = ".intval($rStreamID).";");
                if (($result) && ($result->num_rows > 0)) {
                    while ($row = $result->fetch_assoc()) {
                        $rStreamExists[intval($row["server_id"])] = intval($row["server_stream_id"]);
                    }
                }
                $rStreamsAdded = Array();
                $rServerTree = json_decode($_POST["server_tree_data"], True);
                foreach ($rServerTree as $rServer) {
                    if ($rServer["parent"] <> "#") {
                        $rServerID = intval($rServer["id"]);
                        $rStreamsAdded[] = $rServerID;
                        if ($rServer["parent"] == "source") {
                            $rParent = "NULL";
                        } else {
                            $rParent = intval($rServer["parent"]);
                        }
                        if (isset($rStreamExists[$rServerID])) {
                            if (!$db->query("UPDATE `streams_sys` SET `parent_id` = ".$rParent." WHERE `server_stream_id` = ".$rStreamExists[$rServerID].";")) {
                                $_STATUS = 1;
                            }
                        } else {
                            if (!$db->query("INSERT INTO `streams_sys`(`stream_id`, `server_id`, `parent_id`) VALUES(".intval($rStreamID).", ".$rServerID.", ".$rParent.");")) {
                                $_STATUS = 1;
                            }
                        }
                    }
                }
                foreach ($rStreamExists as $rServerID => $rDBID) {
                    if (!in_array($rServerID, $rStreamsAdded)) {
                        $db->query("DELETE FROM `streams_sys` WHERE `server_stream_id` = ".$rDBID.";");
                    }
                }
            }
            if (isset($_POST["c_bouquets"])) {
                $rBouquets = $_POST["bouquets"];
                foreach ($rBouquets as $rBouquet) {
                    addToBouquet("stream", $rBouquet, $rStreamID);
                }
                foreach (getBouquets() as $rBouquet) {
                    if (!in_array($rBouquet["id"], $rBouquets)) {
                        removeFromBouquet("stream", $rBouquet["id"], $rStreamID);
                    }
                }
            }
        }
        if (isset($_POST["reencode_on_edit"])) {
            APIRequest(Array("action" => "vod", "sub" => "start", "stream_ids" => array_values($rStreamIDs)));
        }
        if (isset($_POST["reprocess_tmdb"])) {
            foreach ($rStreamIDs as $rStreamID) {
                if (intval($rStreamID) > 0) {
                    $db->query("INSERT INTO `tmdb_async`(`type`, `stream_id`, `status`) VALUES(1, ".intval($rStreamID).", 0);");
                }
            }
        }
		if (isset($_POST["c_bouquets"])) {
			scanBouquets();
		}
    }
    $_STATUS = 0;
}


$rTranscodeProfiles = getTranscodeProfiles();
$rServerTree = Array();
$rServerTree[] = Array("id" => "source", "parent" => "#", "text" => "<strong>".$_["stream_source"]."</strong>", "icon" => "mdi mdi-youtube-tv", "state" => Array("opened" => true));
foreach ($rServers as $rServer) {
    $rServerTree[] = Array("id" => $rServer["id"], "parent" => "#", "text" => $rServer["server_name"], "icon" => "mdi mdi-server-network", "state" => Array("opened" => true));
}

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
                                    <a href="./movies.php"><li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> <?=$_["back_to_movies"]?></li></a>
                                </ol>
                            </div>
                            <h4 class="page-title"><?=$_["mass_edit_movies"]?> <small id="selected_count"></small></h4>
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
							<?=$_["mass_edit_movies_success"]?>
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?=$_["error_occured"]?>
                        </div>
                        <?php } ?>
                        <div class="card">
                            <div class="card-body">
                                <form action="./movie_mass.php" method="POST" id="stream_form">
                                    <input type="hidden" name="server_tree_data" id="server_tree_data" value="" />
                                    <input type="hidden" name="streams" id="streams" value="" />
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#stream-selection" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-play mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["movies"]?></span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#stream-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["details"]?></span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#load-balancing" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-server-network mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["servers"]?></span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="stream-selection">
                                                <div class="row">
                                                    <div class="col-md-3 col-6">
                                                        <input type="text" class="form-control" id="stream_search" value="" placeholder="Search Movies...">
                                                    </div>
                                                    <div class="col-md-3 col-6">
                                                        <select id="category_search" class="form-control" data-toggle="select2">
                                                            <option value="" selected><?=$_["all_categories"]?></option>
                                                            <?php foreach ($rCategories as $rCategory) { ?>
                                                            <option value="<?=$rCategory["id"]?>"<?php if ((isset($_GET["category"])) && ($_GET["category"] == $rCategory["id"])) { echo " selected"; } ?>><?=$rCategory["category_name"]?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 col-6">
                                                        <select id="filter" class="form-control" data-toggle="select2">
                                                            <option value="" selected><?=$_["no_filter"]?></option>
                                                            <option value="1"><?=$_["encoded"]?></option>
                                                            <option value="2"><?=$_["encoding"]?></option>
                                                            <option value="3"><?=$_["down"]?></option>
                                                            <option value="4"><?=$_["ready"]?></option>
                                                            <option value="5"><?=$_["direct"]?></option>
                                                            <option value="6"><?=$_["no_tmdb_match"]?></option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 col-8">
                                                        <select id="show_entries" class="form-control" data-toggle="select2">
                                                            <?php foreach (Array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                            <option<?php if ($rAdminSettings["default_entries"] == $rShow) { echo " selected"; } ?> value="<?=$rShow?>"><?=$rShow?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-1 col-2">
                                                        <button type="button" class="btn btn-info waves-effect waves-light" onClick="toggleStreams()">
                                                            <i class="mdi mdi-selection"></i>
                                                        </button>
                                                    </div>
                                                    <table id="datatable-mass" class="table table-borderless mb-0">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th class="text-center"><?=$_["id"]?></th>
                                                                <th><?=$_["stream_name"]?></th>
                                                                <th><?=$_["category"]?></th>
                                                                <th class="text-center"><?=$_["status"]?></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="stream-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
															<?=$_["mass_edit_info"]?>
                                                        </p>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="category_id" name="c_category_id">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="category_id"><?=$_["category_name"]?></label>
                                                            <div class="col-md-8">
                                                                <select disabled name="category_id" id="category_id" class="form-control" data-toggle="select2">
                                                                    <?php foreach ($rCategories as $rCategory) { ?>
                                                                    <option value="<?=$rCategory["id"]?>"><?=$rCategory["category_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="bouquets" name="c_bouquets">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="bouquets"><?=$_["select_bouquets"]?></label>
                                                            <div class="col-md-8">
                                                                <select disabled name="bouquets[]" id="bouquets" class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Choose...">
                                                                    <?php foreach (getBouquets() as $rBouquet) { ?>
                                                                    <option value="<?=$rBouquet["id"]?>"><?=$rBouquet["bouquet_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="direct_source" data-type="switch" name="c_direct_source">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="direct_source"><?=$_["direct_source"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="direct_source" id="direct_source" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="read_native"><?=$_["native_frames"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="read_native" id="read_native" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="read_native" data-type="switch" name="c_read_native">
                                                                <label></label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="movie_symlink" data-type="switch" name="c_movie_symlink">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="movie_symlink"><?=$_["create_symlink"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="movie_symlink" id="movie_symlink" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="custom_sid"><?=$_["custom_channel_sid"]?></label>
                                                            <div class="col-md-2">
                                                                <input type="text" disabled class="form-control" id="custom_sid" name="custom_sid" value="">
                                                            </div>
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="custom_sid" name="c_custom_sid">
                                                                <label></label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="target_container" name="c_target_container">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="target_container"><?=$_["target_container"]?></label>
                                                            <div class="col-md-2">
                                                                <select disabled name="target_container" id="target_container" class="form-control" data-toggle="select2">
                                                                    <?php foreach (Array("mp4", "mkv", "avi", "mpg") as $rContainer) { ?>
                                                                    <option value="<?=$rContainer?>"><?=$rContainer?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="remove_subtitles"><?=$_["remove_subtitles"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="remove_subtitles" id="remove_subtitles" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="remove_subtitles" data-type="switch" name="c_remove_subtitles">
                                                                <label></label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" class="activate" data-name="transcode_profile_id" name="c_transcode_profile_id">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="transcode_profile_id"><?=$_["transcoding_profile"]?></label>
                                                            <div class="col-md-8">
                                                                <select name="transcode_profile_id" disabled id="transcode_profile_id" class="form-control" data-toggle="select2">
                                                                    <option selected value="0"><?=$_["transcoding_disabled"]?></option>
                                                                    <?php foreach ($rTranscodeProfiles as $rProfile) { ?>
                                                                    <option value="<?=$rProfile["profile_id"]?>"><?=$rProfile["profile_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["prev"]?></a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["next"]?></a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="load-balancing">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <div class="checkbox checkbox-single col-md-1 checkbox-offset checkbox-primary">
                                                                <input type="checkbox" data-name="on_demand" class="activate" name="c_server_tree" id="c_server_tree">
                                                                <label></label>
                                                            </div>
                                                            <label class="col-md-3 col-form-label" for="server_tree"><?=$_["server_tree"]?></label>
                                                            <div class="col-md-8">
                                                                <div id="server_tree"></div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <div class="col-md-1"></div>
                                                            <label class="col-md-3 col-form-label" for="reencode_on_edit"><?=$_["reencode_on_edit"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="reencode_on_edit" id="reencode_on_edit" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                            </div>
                                                            <div class="col-md-1"></div>
                                                            <label class="col-md-3 col-form-label" for="reprocess_tmdb"><?=$_["reprocess_tmdb_data"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="reprocess_tmdb" id="reprocess_tmdb" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd" />
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary"><?=$_["prev"]?></a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <input name="submit_stream" type="submit" class="btn btn-primary" value="<?=$_["edit_movies"]?>" />
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
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        var rSwitches = [];
        var rSelected = [];
        
        function getCategory() {
            return $("#category_search").val();
        }
        function getFilter() {
            return $("#filter").val();
        }
        function toggleStreams() {
            $("#datatable-mass tr").each(function() {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                    if ($(this).find("td:eq(0)").html()) {
                        window.rSelected.splice($.inArray($(this).find("td:eq(0)").html(), window.rSelected), 1);
                    }
                } else {            
                    $(this).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                    if ($(this).find("td:eq(0)").html()) {
                        window.rSelected.push($(this).find("td:eq(0)").html());
                    }
                }
            });
            $("#selected_count").html(" - " + window.rSelected.length + " selected")
        }
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
                window.rSwitches[$(html).attr("id")] = switchery;
                if (($(html).attr("id") != "reencode_on_edit") && ($(html).attr("id") != "reprocess_tmdb")) {
                    window.rSwitches[$(html).attr("id")].disable();
                }
            });
            $('#server_tree').jstree({ 'core' : {
                'check_callback': function (op, node, parent, position, more) {
                    switch (op) {
                        case 'move_node':
                            if (node.id == "source") { return false; }
                            return true;
                    }
                },
                'data' : <?=json_encode($rServerTree)?>
            }, "plugins" : [ "dnd" ]
            });
            $("#stream_form").submit(function(e){
                $("#server_tree_data").val(JSON.stringify($('#server_tree').jstree(true).get_json('#', {flat:true})));
                rPass = false;
                $.each($('#server_tree').jstree(true).get_json('#', {flat:true}), function(k,v) {
                    if (v.parent == "source") {
                        rPass = true;
                    }
                });
                if ((rPass == false) && ($("#c_server_tree").is(":checked"))) {
                    e.preventDefault();
                    $.toast("<?=$_["select_at_least_one_server"]?>");
                }
                $("#streams").val(JSON.stringify(window.rSelected));
                if (window.rSelected.length == 0) {
                    e.preventDefault();
                    $.toast("<?=$_["select_at_least_one_movie"]?>");
                }
            });
            $("input[type=checkbox].activate").change(function() {
                if ($(this).is(":checked")) {
                    if ($(this).data("type") == "switch") {
                        window.rSwitches[$(this).data("name")].enable();
                    } else {
                        $("#" + $(this).data("name")).prop("disabled", false);
                        if ($(this).data("name") == "days_to_restart") {
                            $("#time_to_restart").prop("disabled", false);
                        }
                    }
                } else {
                    if ($(this).data("type") == "switch") {
                        window.rSwitches[$(this).data("name")].disable();
                    } else {
                        $("#" + $(this).data("name")).prop("disabled", true);
                        if ($(this).data("name") == "days_to_restart") {
                            $("#time_to_restart").prop("disabled", true);
                        }
                    }
                }
            });
            $(".clockpicker").clockpicker();
            $(document).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
            $("#probesize_ondemand").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#delay_minutes").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#tv_archive_duration").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("form").attr('autocomplete', 'off');
            rTable = $("#datatable-mass").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: "./table_search.php",
                    "data": function(d) {
                        d.id = "movie_list",
                        d.category = getCategory(),
                        d.filter = getFilter()
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [0,3]}
                ],
                "rowCallback": function(row, data) {
                    if ($.inArray(data[0], window.rSelected) !== -1) {
                        $(row).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                    }
                },
                pageLength: <?=$rAdminSettings["default_entries"] ?: 10?>
            });
            $('#stream_search').keyup(function(){
                rTable.search($(this).val()).draw();
            })
            $('#show_entries').change(function(){
                rTable.page.len($(this).val()).draw();
            })
            $('#category_search').change(function(){
                rTable.ajax.reload(null, false);
            })
            $('#filter').change(function(){
                rTable.ajax.reload( null, false );
            })
            $("#datatable-mass").selectable({
                filter: 'tr',
                selected: function (event, ui) {
                    if ($(ui.selected).hasClass('selectedfilter')) {
                        $(ui.selected).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                        window.rSelected.splice($.inArray($(ui.selected).find("td:eq(0)").html(), window.rSelected), 1);
                    } else {            
                        $(ui.selected).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                        window.rSelected.push($(ui.selected).find("td:eq(0)").html());
                    }
                    $("#selected_count").html(" - " + window.rSelected.length + " <?=$_["selected"]?>")
                }
            });
        });
        </script>
    </body>
</html>