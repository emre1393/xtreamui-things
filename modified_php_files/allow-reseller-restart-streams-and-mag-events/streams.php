<?php
include "session.php"; include "functions.php";
if (($rPermissions["is_reseller"]) && (!$rPermissions["reset_stb_data"])) { exit; }
if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "streams"))) { exit; }

if (isset($_GET["category"])) {
    if (!isset($rCategories[$_GET["category"]])) {
        exit;
    } else {
        $rCategory = $rCategories[$_GET["category"]];
    }
} else {
    $rCategory = null;
}

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
        if ($rSettings["sidebar"]) { ?>
        <div class="content-page<?php if ($rPermissions["is_reseller"]) { echo " boxed-layout-ext"; } ?>"><div class="content"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper<?php if ($rPermissions["is_reseller"]) { echo " boxed-layout-ext"; } ?>"><div class="container-fluid">
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
                                                <span class="auto-text">Auto-Refresh</span>
                                            </button>
                                        </a>
                                        <?php } else { ?>
                                        <a href="javascript:location.reload();" onClick="toggleAuto();">
                                            <button type="button" class="btn btn-dark waves-effect waves-light btn-sm">
                                                Refresh
                                            </button>
                                        </a>
                                        <?php }
                                        if ($rPermissions["is_admin"]) {
										if (hasPermissions("adv", "add_stream")) { ?>
                                        <a href="stream.php">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                Add Stream
                                            </button>
                                        </a>
										<?php }
										if (hasPermissions("adv", "create_channel")) { ?>
                                        <a href="created_channel.php">
                                            <button type="button" class="btn btn-purple waves-effect waves-light btn-sm">
                                                Create
                                            </button>
                                        </a>
                                        <?php }
										} ?>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title">Streams<?php if ($rCategory) { echo " - ".$rCategory["category_name"]; } ?></h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body" style="overflow-x:auto;">
                                <form id="stream_form">
                                    <div class="form-group row mb-4">
                                        <?php if ($rPermissions["is_reseller"]) { ?>
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" id="stream_search" value="" placeholder="Search Streams...">
                                        </div>
                                        <div class="col-md-3">
                                            <select id="stream_category_id" class="form-control" data-toggle="select2">
                                                <option value="" selected>All Categories</option>
                                                <?php foreach ($rCategories as $rCategory) { ?>
                                                <option value="<?=$rCategory["id"]?>"<?php if ((isset($_GET["category"])) && ($_GET["category"] == $rCategory["id"])) { echo " selected"; } ?>><?=$rCategory["category_name"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select id="stream_server_id" class="form-control" data-toggle="select2">
                                                <option value="" selected>All Servers</option>
                                                <?php foreach (getStreamingServers() as $rServer) { ?>
                                                <option value="<?=$rServer["id"]?>"<?php if ((isset($_GET["server"])) && ($_GET["server"] == $rServer["id"])) { echo " selected"; } ?>><?=$rServer["server_name"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <label class="col-md-1 col-form-label text-center" for="stream_show_entries">Show</label>
                                        <div class="col-md-2">
                                            <select id="stream_show_entries" class="form-control" data-toggle="select2">
                                                <?php foreach (Array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                <option<?php if ($rAdminSettings["default_entries"] == $rShow) { echo " selected"; } ?> value="<?=$rShow?>"><?=$rShow?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <?php } else { ?>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" id="stream_search" value="" placeholder="Search Streams...">
                                        </div>
                                        <div class="col-md-3">
                                            <select id="stream_server_id" class="form-control" data-toggle="select2">
                                                <option value="" selected>All Servers</option>
                                                <?php foreach (getStreamingServers() as $rServer) { ?>
                                                <option value="<?=$rServer["id"]?>"<?php if ((isset($_GET["server"])) && ($_GET["server"] == $rServer["id"])) { echo " selected"; } ?>><?=$rServer["server_name"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select id="stream_category_id" class="form-control" data-toggle="select2">
                                                <option value="" selected>All Categories</option>
                                                <?php foreach ($rCategories as $rCategory) { ?>
                                                <option value="<?=$rCategory["id"]?>"<?php if ((isset($_GET["category"])) && ($_GET["category"] == $rCategory["id"])) { echo " selected"; } ?>><?=$rCategory["category_name"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select id="stream_filter" class="form-control" data-toggle="select2">
                                                <option value=""<?php if (!isset($_GET["filter"])) { echo " selected"; } ?>>No Filter</option>
                                                <option value="1"<?php if ((isset($_GET["filter"])) && ($_GET["filter"] == 1)) { echo " selected"; } ?>>Online</option>
                                                <option value="2"<?php if ((isset($_GET["filter"])) && ($_GET["filter"] == 2)) { echo " selected"; } ?>>Down</option>
                                                <option value="3"<?php if ((isset($_GET["filter"])) && ($_GET["filter"] == 3)) { echo " selected"; } ?>>Stopped</option>
                                                <option value="4"<?php if ((isset($_GET["filter"])) && ($_GET["filter"] == 4)) { echo " selected"; } ?>>Starting</option>
                                                <option value="5"<?php if ((isset($_GET["filter"])) && ($_GET["filter"] == 5)) { echo " selected"; } ?>>On Demand</option>
                                                <option value="6"<?php if ((isset($_GET["filter"])) && ($_GET["filter"] == 6)) { echo " selected"; } ?>>Direct</option>
												<option value="7"<?php if ((isset($_GET["filter"])) && ($_GET["filter"] == 7)) { echo " selected"; } ?>>Timeshift</option>
                                                <option value="8"<?php if ((isset($_GET["filter"])) && ($_GET["filter"] == 8)) { echo " selected"; } ?>>Created Channel</option>
                                            </select>
                                        </div>
                                        <label class="col-md-1 col-form-label text-center" for="stream_show_entries">Show</label>
                                        <div class="col-md-1">
                                            <select id="stream_show_entries" class="form-control" data-toggle="select2">
                                                <?php foreach (Array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                <option<?php if ($rAdminSettings["default_entries"] == $rShow) { echo " selected"; } ?> value="<?=$rShow?>"><?=$rShow?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </form>
                                <table id="datatable-streampage" class="table dt-responsive nowrap font-normal">
                                    <thead>
                                        <tr>
                                            <th class="text-center">ID</th>
                                            <th class="text-center">Icon</th>
                                            <th>Name</th>
                                            <th>Source</th>
                                            <!-- uptime and actions buttons for allowed reseller groups -->
                                            <?php if ($rPermissions["is_admin"]) { ?>
                                            <th class="text-center">Clients</th>
                                            <?php } ?>
                                            <?php if (($rPermissions["is_admin"]) OR (($rPermissions["is_reseller"]) && ($rPermissions["reseller_controls_streams"]))){ ?>
                                            <th class="text-center">Uptime</th>
                                            <th class="text-center">Actions</th>
                                            <?php } ?>
                                            <?php if ($rPermissions["is_admin"]) { ?>
                                            <th class="text-center">Player</th>
                                            <th class="text-center">EPG</th>
                                            <?php } ?>
                                            <th class="text-center">Stream Info</th>
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
        <script src="assets/libs/select2/select2.min.js"></script>
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
        <script src="assets/libs/magnific-popup/jquery.magnific-popup.min.js"></script>
        <script src="assets/js/pages/form-remember.js"></script>
        <script src="assets/js/app.min.js"></script>

        <script>
        var autoRefresh = true;
        var rClearing = false;
        
        function toggleAuto() {
            if (autoRefresh == true) {
                autoRefresh = false;
                $(".auto-text").html("Manual Mode");
            } else {
                autoRefresh = true;
                $(".auto-text").html("Auto-Refresh");
            }
        }
        
        function api(rID, rServerID, rType) {
            if (rType == "delete") {
                if (confirm('Are you sure you want to delete this stream?') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=stream&sub=" + rType + "&stream_id=" + rID + "&server_id=" + rServerID, function(data) {
                if (data.result == true) {
                    if (rType == "start") {
                        $.toast("Stream successfully started. It will take a minute or so before the stream becomes available.");
                    } else if (rType == "stop") {
                        $.toast("Stream successfully stopped.");
                    } else if (rType == "restart") {
                        $.toast("Stream successfully restarted. It will take a minute or so before the stream becomes available.");
                    } else if (rType == "delete") {
                        $.toast("Stream successfully deleted.");
                    }
                    $.each($('.tooltip'), function (index, element) {
                        $(this).remove();
                    });
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-streampage").DataTable().ajax.reload( null, false );
                } else {
                    $.toast("An error occured while processing your request.");
                }
            }).fail(function() {
                $.toast("An error occured while processing your request.");
            });
        }
        function player(rID) {
            $.magnificPopup.open({
                items: {
                    src: "./player.php?type=live&id=" + rID,
                    type: 'iframe'
                }
            });
        }
        function reloadStreams() {
            if (autoRefresh == true) {
                $('[data-toggle="tooltip"]').tooltip("hide");
                $("#datatable-streampage").DataTable().ajax.reload( null, false );
            }
            setTimeout(reloadStreams, 5000);
        }

        function getCategory() {
            return $("#stream_category_id").val();
        }
        function getFilter() {
            return $("#stream_filter").val();
        }
        function getServer() {
            return $("#stream_server_id").val();
        }
        function changeZoom() {
            if ($("#datatable-streampage").hasClass("font-large")) {
                $("#datatable-streampage").removeClass("font-large");
                $("#datatable-streampage").addClass("font-normal");
            } else if ($("#datatable-streampage").hasClass("font-normal")) {
                $("#datatable-streampage").removeClass("font-normal");
                $("#datatable-streampage").addClass("font-small");
            } else {
                $("#datatable-streampage").removeClass("font-small");
                $("#datatable-streampage").addClass("font-large");
            }
            $("#datatable-streampage").draw();
        }
        function clearFilters() {
            window.rClearing = true;
            $("#stream_search").val("").trigger('change');
            $('#stream_filter').val("").trigger('change');
            $('#stream_server_id').val("").trigger('change');
            $('#stream_category_id').val("").trigger('change');
            $('#stream_show_entries').val("<?=$rAdminSettings["default_entries"] ?: 10?>").trigger('change');
            window.rClearing = false;
            $('#datatable-streampage').DataTable().search($("#stream_search").val());
            $('#datatable-streampage').DataTable().page.len($('#stream_show_entries').val());
            $("#datatable-streampage").DataTable().page(0).draw('page');
            $('[data-toggle="tooltip"]').tooltip("hide");
            $("#datatable-streampage").DataTable().ajax.reload( null, false );
        }
        $(document).ready(function() {
			$(window).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
            formCache.init();
			<?php if (!isset($_GET["filter"])) { ?>
            formCache.fetch();
			<?php } ?>
            
            $('select').select2({width: '100%'});
            $("#datatable-streampage").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    $('[data-toggle="tooltip"]').tooltip();
                },
                createdRow: function(row, data, index) {
                    $(row).addClass('stream-' + data[0]);
                },
                responsive: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "./table_search.php",
                    "data": function(d) {
                        d.id = "streams",
                        d.category = getCategory();
                        <?php if ($rPermissions["is_admin"]) { ?>
                        d.filter = getFilter();
                        // next 2 lines lists all streams to allowed reseller groups. panel doesn't have live + down filter. i didn't want to mess with it.
                        <?php } else if (($rPermissions["is_reseller"]) && ($rPermissions["reseller_controls_streams"])) { ?>
                        d.filter = 0;
                        <?php } else { ?>
                        d.filter = 1;
                        <?php } ?>
                        d.server = getServer();
                    }
                },
                columnDefs: [
                    <?php if ($rPermissions["is_admin"]) { ?>
                    {"className": "dt-center", "targets": [0,1,4,5,6,7,8,9]},
                    {"orderable": false, "targets": [6,7]}
                    <?php } else { ?>
                    {"className": "dt-center", "targets": [0,1,4]}
                    <?php } ?>
                ],
                order: [[ 0, "desc" ]],
                pageLength: <?=$rAdminSettings["default_entries"] ?: 10?>,
                lengthMenu: [10, 25, 50, 250, 500, 1000],
                stateSave: true
            });
            $("#datatable-streampage").css("width", "100%");
            $('#stream_search').keyup(function(){
                if (!window.rClearing) {
                    $('#datatable-streampage').DataTable().search($(this).val()).draw();
                }
            });
            $('#stream_show_entries').change(function(){
                if (!window.rClearing) {
                    $('#datatable-streampage').DataTable().page.len($(this).val()).draw();
                }
            });
            $('#stream_category_id').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-streampage").DataTable().ajax.reload( null, false );
                }
            });
            $('#stream_server_id').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-streampage").DataTable().ajax.reload( null, false );
                }
            });
            $('#stream_filter').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-streampage").DataTable().ajax.reload( null, false );
                }
            });
            <?php if (!$detect->isMobile()) { ?>
            setTimeout(reloadStreams, 5000);
            <?php }
            if (!$rAdminSettings["auto_refresh"]) { ?>
            toggleAuto();
            <?php } ?>
            if ($('#stream_search').val().length > 0) {
                $('#datatable-streampage').DataTable().search($('#stream_search').val()).draw();
            }
        });
        
        $(window).bind('beforeunload', function() {
            formCache.save();
        });
        </script>
    </body>
</html>