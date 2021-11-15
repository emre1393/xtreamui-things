<?php
include "session.php"; include "functions.php";
if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "live_connections"))) { exit; }
if (($rPermissions["is_reseller"]) && (!$rPermissions["reseller_client_connection_logs"])) { exit; }

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
                                        <?php if (!$detect->isMobile()) { ?>
                                        <a href="#" onClick="toggleAuto();" style="margin-right:10px;">
                                            <button type="button" class="btn btn-dark waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-refresh"></i> <span class="auto-text"><?=$_["auto_refresh"]?></span>
                                            </button>
                                        </a>
                                        <?php } else { ?>
                                        <a href="javascript:location.reload();" onClick="toggleAuto();" style="margin-right:10px;">
                                            <button type="button" class="btn btn-dark waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-refresh"></i> <?=$_["refresh"]?>
                                            </button>
                                        </a>
                                        <?php } ?>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title"><?=$_["live_connections"]?></h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body" style="overflow-x:auto;">
                                <form id="user_activity_search">
                                    <div class="form-group row mb-4">
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" id="live_search" value="" placeholder="<?=$_["search_logs"]?>...">
                                        </div>
                                        <label class="col-md-1 col-form-label text-center" for="live_filter"><?=$_["filter"]?></label>
                                        <div class="col-md-3">
                                            <select id="live_filter" class="form-control" data-toggle="select2">
                                                <option value="" selected><?=$_["all_servers"]?></option>
                                                <?php foreach (getStreamingServers() as $rServer) { ?>
                                                <option value="<?=$rServer["id"]?>"><?=$rServer["server_name"]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <label class="col-md-1 col-form-label text-center" for="live_show_entries"><?=$_["show"]?></label>
                                        <div class="col-md-1">
                                            <select id="live_show_entries" class="form-control" data-toggle="select2">
                                                <?php foreach (Array(10, 25, 50, 250, 500, 1000) as $rShow) { ?>
                                                <option<?php if ($rAdminSettings["default_entries"] == $rShow) { echo " selected"; } ?> value="<?=$rShow?>"><?=$rShow?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                                <table id="datatable-activity" class="table dt-responsive nowrap">
                                    <thead>
                                        <tr>
                                            <th class="text-center"><?=$_["id"]?></th>
                                            <th class="text-center"><?=$_["status"]?></th>
                                            <th><?=$_["username"]?></th>
                                            <th><?=$_["stream"]?></th>
                                            <th><?=$_["server"]?></th>
                                            <th class="text-center">Container</th>
                                            <th class="text-center"><?=$_["useragent"]?></th>
                                            <th class="text-center"><?=$_["time"]?></th>
                                            <th class="text-center"><?=$_["ip"]?></th>
                                            <th class="text-center">Country</th>
                                            <th class="text-center">ISP</th>
                                            <th class="text-center"><?=$_["actions"]?></th>
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

        <!-- Datatables init -->
        <script>
        var autoRefresh = true;
        var rClearing = false;
        
        function toggleAuto() {
            if (autoRefresh == true) {
                autoRefresh = false;
                $(".auto-text").html("<?=$_["manual_mode"]?>");
            } else {
                autoRefresh = true;
                $(".auto-text").html("<?=$_["auto_refresh"]?>");
            }
        }
        function api(rID, rType) {
            $.getJSON("./api.php?action=user_activity&sub=" + rType + "&pid=" + rID, function(data) {
                if (data.result === true) {
                    if (rType == "kill") {
                        $.toast("<?=$_["connection_has_been_killed"]?>");
                    }
                    $.each($('.tooltip'), function (index, element) {
                        $(this).remove();
                    });
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-activity").DataTable().ajax.reload(null, false);
                } else {
                    $.toast("<?=$_["error_occured"]?>");
                }
            });
        }
        function reloadUsers() {
            if (autoRefresh == true) {
                $('[data-toggle="tooltip"]').tooltip("hide");
                $("#datatable-activity").DataTable().ajax.reload(null, false);
            }
            setTimeout(reloadUsers, 2000);
        }
        function getServer() {
            return $("#live_filter").val();
        }
        function clearFilters() {
            window.rClearing = true;
            $("#live_search").val("").trigger('change');
            $('#live_filter').val("").trigger('change');
            $('#live_show_entries').val("<?=$rAdminSettings["default_entries"] ?: 10?>").trigger('change');
            window.rClearing = false;
            $('#datatable-activity').DataTable().search($("#live_search").val());
            $('#datatable-activity').DataTable().page.len($('#live_show_entries').val());
            $("#datatable-activity").DataTable().page(0).draw('page');
            $('[data-toggle="tooltip"]').tooltip("hide");
            $("#datatable-activity").DataTable().ajax.reload( null, false );
        }
        $(document).ready(function() {
			$(window).keypress(function(event){
				if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
			});
            formCache.init();
            formCache.fetch();
            
            <?php if (isset($_GET["server_id"])) { ?>
            $("#live_filter").val(<?=$_GET["server_id"]?>);
            <?php } ?>
            
            $('select').select2({width: '100%'});
            $("#datatable-activity").DataTable({
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    },
                    infoFiltered: ""
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    $('[data-toggle="tooltip"]').tooltip();
                },
                responsive: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "./table_search.php",
                    "data": function(d) {
                        d.id = "live_connections";
                        d.server_id = getServer();
                        <?php if (isset($_GET["stream_id"])) { ?>
                        d.stream_id = <?=intval($_GET["stream_id"])?>;
                        <?php } else if (isset($_GET["user_id"])) { ?>
                        d.user_id = <?=intval($_GET["user_id"])?>;
                        <?php } ?>
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [0,1,5,7,8,9,10,11]},
                    {"className": "ellipsis", "targets": [6]},
                    {"orderable": false, "targets": [11]}
                ],
                order: [[ 0, "desc" ]],
                pageLength: <?=$rAdminSettings["default_entries"] ?: 10?>,
                lengthMenu: [10, 25, 50, 250, 500, 1000],
                stateSave: true
            });
            $("#datatable-activity").css("width", "100%");
            $('#live_search').keyup(function(){
                if (!window.rClearing) {
                    $('#datatable-activity').DataTable().search($(this).val()).draw();
                }
            })
            $('#live_show_entries').change(function(){
                if (!window.rClearing) {
                    $('#datatable-activity').DataTable().page.len($(this).val()).draw();
                }
            })
            $('#live_filter').change(function(){
                if (!window.rClearing) {
                    $('[data-toggle="tooltip"]').tooltip("hide");
                    $("#datatable-activity").DataTable().ajax.reload( null, false );
                }
            })
            <?php if (!$detect->isMobile()) { ?>
            setTimeout(reloadUsers, 5000);
            <?php } ?>
            $('#datatable-activity').DataTable().search($(this).val()).draw();
            <?php if (!$rAdminSettings["auto_refresh"]) { ?>
            toggleAuto();
            <?php } ?>
        });
        
        $(window).bind('beforeunload', function() {
            formCache.save();
        });
        </script>

        <!-- App js-->
        <script src="assets/js/app.min.js"></script>
    </body>
</html>