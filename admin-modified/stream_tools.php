<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "stream_tools"))) { exit; }

if (isset($_POST["replace_dns"])) {
	$rOldDNS = ESC(str_replace("/", "\/", $_POST["old_dns"]));
	$rNewDNS = ESC(str_replace("/", "\/", $_POST["new_dns"]));
	$db->query("UPDATE `streams` SET `stream_source` = REPLACE(`stream_source`, '".$rOldDNS."', '".$rNewDNS."');");
	$_STATUS = 1;
} else if (isset($_POST["move_streams"])) {
	$rSource = $_POST["source_server"];
	$rReplacement = $_POST["replacement_server"];
	$rExisting = Array();
	$result = $db->query("SELECT `id` FROM `streams_sys` WHERE `server_id` = ".intval($rReplacement).";");
	if (($result) && ($result->num_rows > 0)) {
		while ($row = $result->fetch_assoc()) {
			$rExisting[] = intval($row["id"]);
		}
	}
	$result = $db->query("SELECT `id` FROM `streams_sys` WHERE `server_id` = ".intval($rSource).";");
	if (($result) && ($result->num_rows > 0)) {
		while ($row = $result->fetch_assoc()) {
			if (in_array(intval($row["id"]), $rExisting)) {
				$db->query("DELETE FROM `streams_sys` WHERE `id` = ".intval($row["id"]).";");
			}
		}
	}
	$db->query("UPDATE `streams_sys` SET `server_id` = ".intval($rReplacement)." WHERE `server_id` = ".intval($rSource).";");
	$_STATUS = 2;
} else if (isset($_POST["cleanup_streams"])) {
    $rStreams = getStreamList();
    $rStreamArray = Array();
    foreach ($rStreams as $rStream) {
        $rStreamArray[] = intval($rStream["id"]);
    }
    $rDelete = Array();
    $result = $db->query("SELECT `server_stream_id`, `stream_id` FROM `streams_sys`;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            if (!in_array(intval($row["stream_id"]), $rStreamArray)) {
                $rDelete[] = $row["server_stream_id"];
            }
        }
    }
    if (count($rDelete) > 0) {
        $db->query("DELETE FROM `streams_sys` WHERE `server_stream_id` IN (".join(",", $rDelete).");");
    }
    $rDelete = Array();
    $result = $db->query("SELECT `id`, `stream_id` FROM `client_logs`;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            if (!in_array(intval($row["stream_id"]), $rStreamArray)) {
                $rDelete[] = $row["id"];
            }
        }
    }
    if (count($rDelete) > 0) {
        $db->query("DELETE FROM `client_logs` WHERE `id` IN (".join(",", $rDelete).");");
    }
    $rDelete = Array();
    $result = $db->query("SELECT `id`, `stream_id` FROM `stream_logs`;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            if (!in_array(intval($row["stream_id"]), $rStreamArray)) {
                $rDelete[] = $row["id"];
            }
        }
    }
    if (count($rDelete) > 0) {
        $db->query("DELETE FROM `stream_logs` WHERE `id` IN (".join(",", $rDelete).");");
    }
    $rDelete = Array();
    $result = $db->query("SELECT `activity_id`, `stream_id` FROM `user_activity`;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            if (!in_array(intval($row["stream_id"]), $rStreamArray)) {
                $rDelete[] = $row["activity_id"];
            }
        }
    }
    if (count($rDelete) > 0) {
        $db->query("DELETE FROM `user_activity` WHERE `activity_id` IN (".join(",", $rDelete).");");
    }
    $_STATUS = 3;
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
                                    <a href="./streams.php"><li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> Back to Streams</li></a>
                                </ol>
                            </div>
                            <h4 class="page-title">Stream Tools</h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-xl-12">
                        <?php if ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            Stream DNS replacement was successful. 
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 2)) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            Streams have been moved from the source server to the replacement server.
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 3)) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            Stream cleanup was successful!
                        </div>
                        <?php } ?>
                        <div class="card">
                            <div class="card-body">
								<div id="basicwizard">
									<ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
										<li class="nav-item">
											<a href="#dns-replacement" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
												<i class="mdi mdi-dns mr-1"></i>
												<span class="d-none d-sm-inline">DNS Replacement</span>
											</a>
										</li>
										<li class="nav-item">
											<a href="#move-streams" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
												<i class="mdi mdi-folder-move mr-1"></i>
												<span class="d-none d-sm-inline">Move Streams</span>
											</a>
										</li>
                                        <li class="nav-item">
											<a href="#cleanup" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
												<i class="mdi mdi-wrench mr-1"></i>
												<span class="d-none d-sm-inline">Cleanup</span>
											</a>
										</li>
									</ul>
									<div class="tab-content b-0 mb-0 pt-0">
										<div class="tab-pane" id="dns-replacement">
											<form action="./stream_tools.php" method="POST" id="tools_form" data-parsley-validate="">
                                                <div class="row">
                                                    <div class="col-12">
														<p class="sub-header">
                                                            The DNS replacement tool can be used to replace the domain name of a stream with another. It can replace any text within a stream, such as username and password.
                                                        </p>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="old_dns">Old DNS</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="old_dns" name="old_dns" value="" placeholder="http://example.com" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
														<div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="new_dns">New DNS</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="new_dns" name="new_dns" value="" placeholder="http://newdns.com" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
													<li class="list-inline-item">
														<div class="custom-control custom-checkbox">
															<input type="checkbox" class="custom-control-input" id="confirmReplace">
															<label class="custom-control-label" for="confirmReplace">I confirm that I want to replace the old DNS with the new DNS above.</label>
														</div>
													</li>
                                                    <li class="list-inline-item float-right">
                                                        <input disabled name="replace_dns" id="replace_dns" type="submit" class="btn btn-primary" value="Replace DNS" />
                                                    </li>
                                                </ul>
											</form>
										</div>
										<div class="tab-pane" id="move-streams">
											<form action="./stream_tools.php" method="POST" id="tools_form" data-parsley-validate="">
												<div class="row">
													<div class="col-12">
														<p class="sub-header">
															This tool will allow you to move all streams from one server to another.
														</p>
														<div class="form-group row mb-4">
															<label class="col-md-4 col-form-label" for="source_server">Source Server</label>
															<div class="col-md-8">
																<select name="source_server" id="source_server" class="form-control select2" data-toggle="select2">
																	<?php foreach ($rServers as $rServer) { ?>
																	<option value="<?=$rServer["id"]?>"><?=$rServer["server_name"]?></option>
																	<?php } ?>
																</select>
															</div>
														</div>
														<div class="form-group row mb-4">
															<label class="col-md-4 col-form-label" for="replacement_server">Replacement Server</label>
															<div class="col-md-8">
																<select name="replacement_server" id="replacement_server" class="form-control select2" data-toggle="select2">
																	<?php foreach ($rServers as $rServer) { ?>
																	<option value="<?=$rServer["id"]?>"><?=$rServer["server_name"]?></option>
																	<?php } ?>
																</select>
															</div>
														</div>
													</div> <!-- end col -->
												</div> <!-- end row -->
												<ul class="list-inline wizard mb-0">
													<li class="list-inline-item">
														<div class="custom-control custom-checkbox">
															<input type="checkbox" class="custom-control-input" id="confirmReplace2">
															<label class="custom-control-label" for="confirmReplace2">I confirm that I want to move all streams from the source server to the replacement.</label>
														</div>
													</li>
													<li class="list-inline-item float-right">
														<input disabled name="move_streams" id="move_streams" type="submit" class="btn btn-primary" value="Move Streams" />
													</li>
												</ul>
											</form>
										</div>
                                        <div class="tab-pane" id="cleanup">
											<form action="./stream_tools.php" method="POST" id="tools_form" data-parsley-validate="">
												<div class="row">
													<div class="col-12">
														<p class="sub-header">
															This tool will clean up your streams database, removing invalid entries from the streams sys table and all logs. Xtream Codes monitors your streams sys table and will use resources doing so, it's best to clean this up periodically.
														</p>
													</div> <!-- end col -->
												</div> <!-- end row -->
												<ul class="list-inline wizard mb-0">
													<li class="list-inline-item">
														<div class="custom-control custom-checkbox">
															<input type="checkbox" class="custom-control-input" id="confirmReplace3">
															<label class="custom-control-label" for="confirmReplace3">I confirm that I want to clean up my streams database.</label>
														</div>
													</li>
													<li class="list-inline-item float-right">
														<input disabled name="cleanup_streams" id="cleanup_streams" type="submit" class="btn btn-primary" value="Cleanup" />
													</li>
												</ul>
											</form>
										</div>
									</div> <!-- tab-content -->
								</div> <!-- end #basicwizard-->
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
        <script src="assets/libs/parsleyjs/parsley.min.js"></script>
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        $(document).ready(function() {
			$('select.select2').select2({width: '100%'});
            $(window).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
			$("#confirmReplace").change(function() {
				if ($(this).is(":checked")) {
					$("#replace_dns").attr("disabled", false);
				} else {
					$("#replace_dns").attr("disabled", true);
				}
			});
			$("#confirmReplace2").change(function() {
				if ($(this).is(":checked")) {
					$("#move_streams").attr("disabled", false);
				} else {
					$("#move_streams").attr("disabled", true);
				}
			});
            $("#confirmReplace3").change(function() {
				if ($(this).is(":checked")) {
					$("#cleanup_streams").attr("disabled", false);
				} else {
					$("#cleanup_streams").attr("disabled", true);
				}
			});
            $("form").attr('autocomplete', 'off');
        });
        </script>
    </body>
</html>