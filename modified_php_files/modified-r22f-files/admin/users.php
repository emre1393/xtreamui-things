<?php
include "session.php"; include "functions.php";

if ($rPermissions["is_admin"]) {
	if (!hasPermissions("adv", "users")) { exit; }
    $rRegisteredUsers = getRegisteredUsers();
} else {
    $rRegisteredUsers = getRegisteredUsers($rUserInfo["id"]);
}

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
                                        <a href="#" onClick="clearFilters();">
                                            <button type="button" class="btn btn-warning waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-filter-remove"></i>
                                            </button>
                                        </a>
                                        <a href="#" onClick="changeZoom();">
                                            <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-magnify"></i>
                                            </button>
                                        </a>
                                        <?php if (!$detect->isMobile()) { ?>
                                        <a href="#" onClick="toggleAuto();">
                                            <button type="button" class="btn btn-dark waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-refresh"></i> <span class="auto-text">Auto-Refresh</span>
                                            </button>
                                        </a>
                                        <?php } else { ?>
                                        <a href="javascript:location.reload();" onClick="toggleAuto();">
                                            <button type="button" class="btn btn-dark waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-refresh"></i> Refresh
                                            </button>
                                        </a>
                                        <?php }
										if ((hasPermissions("adv", "add_user")) OR ($rPermissions["is_reseller"])) { ?>
                                        <a href="user<?php if ($rPermissions["is_reseller"]) { echo "_reseller"; } ?>.php">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-plus"></i> Add User
                                            </button>
                                        </a>
										<?php } ?>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title">Users</h4>
                        </div>
                    </div>
                </div>
                <!-- end page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body" style="overflow-x:auto;">
                                <form id="users_search">
                                    <div class="form-group row mb-4">
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" id="user_search" value="" placeholder="Search Users...">
                                        </div>
                                        <label class="col-md-2 col-form-label text-center" for="user_reseller">Filter Results</label>
                                        <div class="col-md-3">
                                            <select id="user_reseller" class="form-control" data-toggle="select2">
                                                <option value="" selected>All Resellers</option>
                                                <?php foreach ($rRegisteredUsers as $rRegisteredUser) { ?>
                                                <option value="<?=$rRegisteredUser["id"]?>"><?=$rRegisteredUser["username"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select id="user_filter" class="form-control" data-toggle="select2">
                                                <option value="" selected>No Filter</option>
                                                <option value="1">Active</option>
                                                <option value="2">Disabled</option>
                                                <option value="3">Banned</option>
                                                <option value="4">Expired</option>
                                                <option value="5">Trial</option>
                                            </select>
                                        </div>
                                        <label class="col-md-1 col-form-label text-center" for="user_show_entries">Show</label>
                                        <div class="col-md-1">
                                            <select id="user_show_entries" class="form-control" data-toggle="select2">
                                                <?php foreach (Array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                <option<?php if ($rAdminSettings["default_entries"] == $rShow) { echo " selected"; } ?> value="<?=$rShow?>"><?=$rShow?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                                <table id="datatable-users" class="table dt-responsive nowrap font-normal">
                                    <thead>
                                        <tr>
                                            <th class="text-center">ID</th>
                                            <th>Username</th>
                                            <th>Password</th>
                                            <th>Reseller</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Online</th>
                                            <th class="text-center">Trial</th>
                                            <th class="text-center">Expiration</th>
                                            <th class="text-center">Active</th>
                                            <th class="text-center">Conns.</th>
                                            <th class="text-center">Last Connection</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div> <!-- end card body-->
                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div>
                <!-- end row-->
            </div> <!-- end container -->
            <?php if ((($rPermissions["is_reseller"]) && ($rPermissions["allow_download"])) OR ($rPermissions["is_admin"])) { ?>
            <div class="modal fade downloadModal" role="dialog" aria-labelledby="downloadLabel" aria-hidden="true" style="display: none;" data-username="" data-password="">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="downloadModal">Download Playlist</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>
                        <div class="modal-body">
                            <div class="col-12">
                                <select id="download_type" class="form-control" data-toggle="select2">
                                    <option value="">Select an ouput format: </option>
                                    <?php
                                    $result = $db->query("SELECT * FROM `devices` ORDER BY `device_id` ASC;");
                                    if (($result) && ($result->num_rows > 0)) {
                                        while ($row = $result->fetch_assoc()) {
                                            if ($row["copy_text"]) {
                                                echo '<optgroup label="'.$row["device_name"].'"><option data-text="'.str_replace('"', '\"', $row["copy_text"]).'" value="type='.$row["device_key"].'&amp;output=hls">'.$row["device_name"].' - HLS </option><option data-text="'.str_replace('"', '\"', $row["copy_text"]).'" value="type='.$row["device_key"].'&amp;output=mpegts">'.$row["device_name"].' - MPEGTS</option></optgroup>';
                                            } else {
                                                echo '<optgroup label="'.$row["device_name"].'"><option value="type='.$row["device_key"].'&amp;output=hls">'.$row["device_name"].' - HLS </option><option value="type='.$row["device_key"].'&amp;output=mpegts">'.$row["device_name"].' - MPEGTS</option></optgroup>';
                                            }
                                        }
                                    } ?>
                                </select>
                            </div>
                            <div class="col-12" style="margin-top:10px;">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="download_url" value="">
                                    <div class="input-group-append">
                                        <button class="btn btn-warning waves-effect waves-light" type="button" onClick="copyDownload();"><i class="mdi mdi-content-copy"></i></button>
                                        <button class="btn btn-info waves-effect waves-light" type="button" onClick="doDownload();" id="download_button" disabled><i class="mdi mdi-download"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
            <?php } ?>
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
        <script src="assets/libs/datatables/jquery.dataTables.min.js"></script>
        <script src="assets/libs/datatables/dataTables.bootstrap4.js"></script>
        <script src="assets/libs/select2/select2.min.js"></script>
        <script src="assets/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="assets/libs/datatables/responsive.bootstrap4.min.js"></script>
        <script src="assets/libs/datatables/dataTables.buttons.min.js"></script>
        <script src="assets/libs/datatables/buttons.bootstrap4.min.js"></script>
        <script src="assets/libs/datatables/buttons.html5.min.js"></script>
        <script src="assets/libs/datatables/buttons.flash.min.js"></script>
        <script src="assets/libs/datatables/buttons.print.min.js"></script>
        <script src="assets/libs/datatables/dataTables.keyTable.min.js"></script>
        <script src="assets/libs/datatables/dataTables.select.min.js"></script>
        <script src="assets/js/pages/form-remember.js"></script>
        <script src="assets/js/app.min.js"></script>

        <!-- Datatables init -->
        <script>
        var autoRefresh = true;
        var rClearing = false;

        function api(rID, rType) {
            if (rType == "delete") {
                if (confirm('Are you sure you want to delete this user?') == false) {
                    return;
                }
            } else if (rType == "kill") {
                if (confirm('Are you sure you want to kill all connections for this user?') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=user&sub=" + rType + "&user_id=" + rID, function(data) {
                if (data.result === true) {
                    if (rType == "delete") {
                        $.toast("User has been deleted.");
                    } else if (rType == "enable") {
                        $.toast("User has been enabled.");
                    } else if (rType == "disable") {
                        $.toast("User has been disabled.");
                    } else if (rType == "unban") {
                        $.toast("User has been unbanned.");
                    } else if (rType == "ban") {
                        $.toast("User has been banned.");
                    } else if (rType == "kill") {
                        $.toast("All connections for this user have been killed.");
                    }
                    $.each($('.tooltip'), function (index, element) {
                        $(this).remove();
                    });
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-users").DataTable().ajax.reload(null, false);
                } else {
                    $.toast("An error occured while processing your request.");
                }
            });
        }
        function download(username, password) {
            $("#download_type").val("");
            $("#download_button").attr("disabled", true);
            $('.downloadModal').data('username', username);
            $('.downloadModal').data('password', password);
            $('.downloadModal').modal('show');
        }
       
        $("#download_type").change(function() {
            if ($("#download_type").val().length > 0) {
                <?php
                if (strlen($rUserInfo["reseller_dns"]) > 0) {
                    $rDNS = $rUserInfo["reseller_dns"];
                } else {
                    $rDNS = $rServers[$_INFO["server_id"]]["domain_name"] ? $rServers[$_INFO["server_id"]]["domain_name"] : $rServers[$_INFO["server_id"]]["server_ip"];
                }
                ?>
                rText = "http://<?=$rDNS?>:<?=$rServers[$_INFO["server_id"]]["http_broadcast_port"]?>/get.php?username=" + $('.downloadModal').data('username') + "&password=" + $('.downloadModal').data('password') + "&" + decodeURIComponent($('.downloadModal select').val());
                if ($("#download_type").find(':selected').data('text')) {
                    rText = $("#download_type").find(':selected').data('text').replace("{DEVICE_LINK}", '"' + rText + '"');
                    $("#download_button").attr("disabled", true);
                } else {
                    $("#download_button").attr("disabled", false);
                }
                $("#download_url").val(rText);
            } else {
                $("#download_url").val("");
            }
        });
        function doDownload() {
            if ($("#download_url").val().length > 0) {
                window.open($("#download_url").val());
            }
        }
        function copyDownload() {
            $("#download_url").select();
            document.execCommand("copy");
        }
        function toggleAuto() {
            if (autoRefresh == true) {
                autoRefresh = false;
                $(".auto-text").html("Manual Mode");
            } else {
                autoRefresh = true;
                $(".auto-text").html("Auto-Refresh");
            }
        }
        function getFilter() {
            return $("#user_filter").val();
        }
        function getReseller() {
            return $("#user_reseller").val();
        }
        function reloadUsers() {
            if (autoRefresh == true) {
                $('[data-toggle="tooltip"]').tooltip("hide");
                $("#datatable-users").DataTable().ajax.reload(null, false);
            }
            setTimeout(reloadUsers, 10000);
        }
        function changeZoom() {
            if ($("#datatable-users").hasClass("font-large")) {
                $("#datatable-users").removeClass("font-large");
                $("#datatable-users").addClass("font-normal");
            } else if ($("#datatable-users").hasClass("font-normal")) {
                $("#datatable-users").removeClass("font-normal");
                $("#datatable-users").addClass("font-small");
            } else {
                $("#datatable-users").removeClass("font-small");
                $("#datatable-users").addClass("font-large");
            }
            $("#datatable-users").DataTable().draw();
        }
        function clearFilters() {
            window.rClearing = true;
            $("#user_search").val("").trigger('change');
            $('#user_filter').val("").trigger('change');
            $('#user_reseller').val("").trigger('change');
            $('#user_show_entries').val("<?=$rAdminSettings["default_entries"] ?: 10?>").trigger('change');
            window.rClearing = false;
            $('#datatable-users').DataTable().search($("#user_search").val());
            $('#datatable-users').DataTable().page.len($('#user_show_entries').val());
            $("#datatable-users").DataTable().page(0).draw('page');
            $('[data-toggle="tooltip"]').tooltip("hide");
            $("#datatable-users").DataTable().ajax.reload( null, false );
        }
        $(document).ready(function() {
			$(window).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
            formCache.init();
            formCache.fetch();
            
            $.fn.dataTable.ext.errMode = 'none';
            $('select').select2({width: '100%'});
            $("#datatable-users").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>",
                    },
                    infoFiltered: ""
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    $('[data-toggle="tooltip"]').tooltip();
                },
                createdRow: function(row, data, index) {
                    $(row).addClass('user-' + data[0]);
                },
                responsive: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "./table_search.php",
                    "data": function(d) {
                        d.id = "users",
                        d.filter = getFilter(),
                        d.reseller = getReseller()
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [0,4,5,6,7,8,9,10,11]},
                    {"visible": false, "targets": [10]},
                    {"orderable": false, "targets": [11]}
                ],
                order: [[ 0, "desc" ]],
                pageLength: <?=$rAdminSettings["default_entries"] ?: 10?>,
                stateSave: true
            })
            $("#datatable-users").css("width", "100%");
            $('#user_search').keyup(function(){
                if (!window.rClearing) {
                    $('#datatable-users').DataTable().search($(this).val()).draw();
                }
            });
            $('#user_show_entries').change(function(){
                if (!window.rClearing) {
                    $('#datatable-users').DataTable().page.len($(this).val()).draw();
                }
            });
            $('#user_filter').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-users").DataTable().ajax.reload( null, false );
                }
            });
            $('#user_reseller').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-users").DataTable().ajax.reload( null, false );
                }
            });
            <?php if (!$detect->isMobile()) { ?>
            setTimeout(reloadUsers, 10000);
            <?php }
            if (!$rAdminSettings["auto_refresh"]) { ?>
            toggleAuto();
            <?php } ?>
            if ($('#user_search').val().length > 0) {
                $('#datatable-users').DataTable().search($('#user_search').val()).draw();
            }
        });
        
        $(window).bind('beforeunload', function() {
            formCache.save();
        });
        </script>
    </body>
</html>