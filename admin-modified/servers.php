<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "servers"))) { exit; }

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
        if ($rSettings["sidebar"]) { ?>
        <div class="content-page"><div class="content"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper"><div class="container-fluid">
        <?php } ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li>
                                        <a href="javascript:location.reload();">
                                            <button type="button" class="btn btn-dark waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-refresh"></i> Refresh
                                            </button>
                                        </a>
										<?php if (hasPermissions("adv", "add_server")) { ?>
                                        <a href="server.php">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-plus"></i> Add Server
                                            </button>
                                        </a>
                                        <a href="install_server.php">
                                            <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-creation"></i> Install LB
                                            </button>
                                        </a>
										<?php } ?>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title">Servers</h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body" style="overflow-x:auto;">
                                <table id="datatable" class="table dt-responsive nowrap">
                                    <thead>
                                        <tr>
                                            <th class="text-center">ID</th>
                                            <th>Server Name</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Latency</th>
                                            <th>Domain Name</th>
                                            <th>Server IP</th>
                                            <th class="text-center">Client Slots</th>
                                            <th class="text-center">CPU %</th>
                                            <th class="text-center">MEM %</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rServers as $rServer) {
                                        if (((time() - $rServer["last_check_ago"]) > 360) AND ($rServer["can_delete"] == 1) AND ($rServer["status"] <> 3)) { $rServer["status"] = 2; } // Server Timeout
                                        if (in_array($rServer["status"], Array(0,1))) {
                                            $rServerText = Array(0 => "Disabled", 1 => "Online")[$rServer["status"]];
                                        } else if ($rServer["status"] == 2) {
                                            if ($rServer["last_check_ago"] > 0) {
                                                $rServerText = "Offline for ".intval((time() - $rServer["last_check_ago"])/60)." minutes";
                                            } else {
                                                $rServerText = "Offline";
                                            }
                                        } else if ($rServer["status"] == 3) {
                                            $rServerText = "Installing...";
                                        }
                                        $rWatchDog = json_decode($rServer["watchdog_data"], True);
                                        if (!is_array($rWatchDog)) {
                                            $rWatchDog = Array("total_mem_used_percent" => "N/A ", "cpu_avg" => "N/A ");
                                        }
										$rLatency = $rServer["latency"] * 1000;
										if ($rLatency > 0) {
											$rLatency = $rLatency." ms";
										} else {
											$rLatency = "--";
										}
                                        ?>
                                        <tr id="server-<?=$rServer["id"]?>">
                                            <td class="text-center"><?=$rServer["id"]?></td>
                                            <td><?=$rServer["server_name"]?></td>
                                            <td class="text-center" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?=$rServerText?>" ><i class="<?php if ($rServer["status"] == 1) { echo "btn-outline-success"; } else if ($rServer["status"] == "3") { echo "btn-outline-info"; } else { echo "btn-outline-danger"; } ?> mdi mdi-<?=Array(0 => "alarm-light-outline", 1 => "check-network", 2 => "alarm-light-outline", 3 => "creation")[$rServer["status"]]?>"></i></td>
                                            <td class="text-center"><?=$rLatency?></td>
                                            <td><?=$rServer["domain_name"]?></td>
                                            <td><?=$rServer["server_ip"]?></td>
											<?php if (hasPermissions("adv", "live_connections")) { ?>
                                            <td class="text-center"><a href="./live_connections.php?server_id=<?=$rServer["id"]?>"><?=count(getConnections($rServer["id"]))?> / <?=$rServer["total_clients"]?></a></td>
											<?php } else { ?>
											<td class="text-center"><?=count(getConnections($rServer["id"]))?> / <?=$rServer["total_clients"]?></td>
											<?php } ?>
                                            <td class="text-center"><?=intval($rWatchDog["cpu_avg"])?>%</td>
                                            <td class="text-center"><?=intval($rWatchDog["total_mem_used_percent"])?>%</td>
                                            <td class="text-center">
												<?php if (hasPermissions("adv", "edit_server")) { ?>
                                                <div class="btn-group">
                                                    <button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="Restart Services" class="btn btn-light waves-effect waves-light btn-xs btn-reboot-server" data-id="<?=$rServer["id"]?>"><i class="mdi mdi-restart"></i></button>
                                                    <button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="Start All Streams" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(<?=$rServer["id"]?>, 'start');"><i class="mdi mdi-play"></i></button>
                                                    <button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="Stop All Streams" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(<?=$rServer["id"]?>, 'stop');"><i class="mdi mdi-stop"></i></button>
                                                    <button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="Kill All Connections" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(<?=$rServer["id"]?>, 'kill');"><i class="fas fa-hammer"></i></button>
                                                    <a href="./server.php?id=<?=$rServer["id"]?>"><button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit Server" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>
                                                    <?php if ($rServer["can_delete"] == 1) { ?>
                                                    <button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete Server" class="btn btn-light waves-effect waves-light btn-xs" onClick="api(<?=$rServer["id"]?>, 'delete');"><i class="mdi mdi-close"></i></button>
                                                    <?php } else { ?>
                                                    <button disabled type="button" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-close"></i></button>
                                                    <?php } ?>
                                                </div>
												<?php } else { echo "--"; } ?>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div> <!-- end card body-->
                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div>
                <!-- end row-->
            </div> <!-- end container -->
        </div>
        <div class="modal fade bs-server-modal-center" tabindex="-1" role="dialog" aria-labelledby="restartServicesLabel" aria-hidden="true" style="display: none;" data-id="">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="restartServicesLabel">Restart Services</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row mb-4">
                            <label class="col-md-3 col-form-label" for="root_password">Password</label>
                            <div class="col-md-5">
                                <input type="text" class="form-control" id="root_password" value="">
                            </div>
                            <label class="col-md-2 col-form-label" for="ssh_port">SSH Port</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="ssh_port" value="22">
                            </div>
                        </div>
                        <div class="form-group row mb-4">
							<div class="col-md-6">
								<input id="restart_services_ssh" type="submit" class="btn btn-primary" value="Restart Services" style="width:100%" />
							</div>
							<div class="col-md-6">
								<input id="reboot_server_ssh" type="submit" class="btn btn-primary" value="Reboot Server" style="width:100%" />
							</div>
                        </div>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
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
        <script src="assets/js/app.min.js"></script>

        <script>
        function api(rID, rType) {
            if (rType == "delete") {
                if (confirm('Are you sure you want to delete this server and it\'s accompanying streams? This cannot be undone!') == false) {
                    return;
                }
            } else if (rType == "kill") {
                if (confirm('Are you sure you want to kill all connections to this server?') == false) {
                    return;
                }
            } else if (rType == "start") {
                if (confirm('Are you sure you want to start all streams on this server? This will restart already running streams.') == false) {
                    return;
                }
            } else if (rType == "stop") {
                if (confirm('Are you sure you want to stop all streams on this sterver?') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=server&sub=" + rType + "&server_id=" + rID, function(data) {
                if (data.result === true) {
                    if (rType == "delete") {
                        $("#server-" + rID).remove();
                        $.each($('.tooltip'), function (index, element) {
                            $(this).remove();
                        });
                        $('[data-toggle="tooltip"]').tooltip();
                        $.toast("Server successfully deleted.");
                    } else if (rType == "kill") {
                        $.toast("All server connections have been killed.");
                    } else if (rType == "start") {
                        $.toast("All streams on this server have been started.");
                    } else if (rType == "stop") {
                        $.toast("All streams on this server have been stopped.");
                    }
                } else {
                    $.toast("An error occured while processing your request.");
                }
            });
        }
        $("#restart_services_ssh").click(function() {
            $(".bs-server-modal-center").modal("hide");
            $.getJSON("./api.php?action=restart_services&ssh_port=" + $("#ssh_port").val() + "&server_id=" + $(".bs-server-modal-center").data("id") + "&password=" + $("#root_password").val(), function(data) {
                if (data.result === true) {
                    $.toast("Server will be restarted shortly.");
                } else {
                    $.toast("An error occured while processing your request.");
                }
                $("#root_password").val("");
                $("#ssh_port").val("22");
                $(".bs-server-modal-center").data("id", "");
            });
        });
		$("#reboot_server_ssh").click(function() {
            $(".bs-server-modal-center").modal("hide");
            $.getJSON("./api.php?action=reboot_server&ssh_port=" + $("#ssh_port").val() + "&server_id=" + $(".bs-server-modal-center").data("id") + "&password=" + $("#root_password").val(), function(data) {
                if (data.result === true) {
                    $.toast("Server will be restarted shortly.");
                } else {
                    $.toast("An error occured while processing your request.");
                }
                $("#root_password").val("");
                $("#ssh_port").val("22");
                $(".bs-server-modal-center").data("id", "");
            });
        });
        $(".btn-reboot-server").click(function() {
            $(".bs-server-modal-center").data("id", $(this).data("id"));
            $(".bs-server-modal-center").modal("show");
        });
        $(document).ready(function() {
            $("#datatable").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                responsive: false
            });
            $("#datatable").css("width", "100%");
        });
        </script>
    </body>
</html>