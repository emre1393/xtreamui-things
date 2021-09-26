<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "channel_order"))) { exit; }

if (isset($_POST["stream_order_array"])) {
    set_time_limit(0);
    ini_set('mysql.connect_timeout', 0);
    ini_set('max_execution_time', 0);
    ini_set('default_socket_timeout', 0);
    $rOrder = json_decode($_POST["stream_order_array"], True);
    $rSort = 0;
    foreach ($rOrder as $rStream) {
        $db->query("UPDATE `streams` SET `order` = ".intval($rSort)." WHERE `id` = ".intval($rStream).";");
        $rSort ++;
    }
}

$rOrdered = Array("stream" => Array(), "movie" => Array(), "series" => Array(), "radio" => Array());
$result = $db->query("SELECT `id`, `type`, `stream_display_name`, `category_id` FROM `streams` ORDER BY `order` ASC, `stream_display_name` ASC;");
if (($result) && ($result->num_rows > 0)) {
    while ($row = $result->fetch_assoc()) {
        if (($row["type"] == 1) OR ($row["type"] == 3)) {
            $rOrdered["stream"][] = $row;
        } else if ($row["type"] == 2) {
            $rOrdered["movie"][] = $row;
        } else if ($row["type"] == 4) {
            $rOrdered["radio"][] = $row;
        } else if ($row["type"] == 5) {
            $rOrdered["series"][] = $row;
        }
    }
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
                                    <li>
                                        <a href="channel_order_alt.php">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                <?=$_["advanced"]?>
                                            </button>
                                        </a>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title"><?=$_["channel_order"]?></h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-xl-12">
                        <?php if ($rSettings["channel_number_type"] <> "manual") { ?>
                        <div class="alert alert-danger show" role="alert">
                            <?=$_["channel_order_info"]?>
                        </div>
                        <?php } ?>
                        <div class="card">
                            <div class="card-body">
                                <form action="./channel_order.php" method="POST" id="channel_order_form">
                                    <input type="hidden" id="stream_order_array" name="stream_order_array" value="" />
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#order-stream" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-play mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["streams"]?></span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#order-movie" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-movie mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["movies"]?></span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#order-series" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-youtube-tv mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["series"]?></span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#order-radio" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-radio mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["stations"]?></span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="order-stream">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            <?=$_["bouquet_order_sort_text"]?>
                                                        </p>
                                                        <select multiple id="sort_stream" class="form-control" style="min-height:400px;">
                                                        <?php foreach ($rOrdered["stream"] as $rStream) { ?>
                                                            <option value="<?=$rStream["id"]?>"><?=$rStream["stream_display_name"]?></option>
                                                        <?php } ?>
                                                        </select>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0 add-margin-top-20">
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" onClick="MoveUp('stream')" class="btn btn-purple"><i class="mdi mdi-chevron-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveDown('stream')" class="btn btn-purple"><i class="mdi mdi-chevron-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveTop('stream')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveBottom('stream')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="AtoZ('stream')" class="btn btn-info"><?=$_["a_to_z"]?></a>
                                                    </li>
                                                    <li class="list-inline-item float-right">
                                                        <button type="submit" class="btn btn-primary waves-effect waves-light"><?=$_["save_changes"]?></button>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="order-movie">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            <?=$_["bouquet_order_sort_text"]?>
                                                        </p>
                                                        <select multiple id="sort_movie" class="form-control" style="min-height:400px;">
                                                        <?php foreach ($rOrdered["movie"] as $rStream) { ?>
                                                            <option value="<?=$rStream["id"]?>"><?=$rStream["stream_display_name"]?></option>
                                                        <?php } ?>
                                                        </select>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0 add-margin-top-20">
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" onClick="MoveUp('movie')" class="btn btn-purple"><i class="mdi mdi-chevron-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveDown('movie')" class="btn btn-purple"><i class="mdi mdi-chevron-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveTop('movie')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveBottom('movie')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="AtoZ('movie')" class="btn btn-info"><?=$_["a_to_z"]?></a>
                                                    </li>
                                                    <li class="list-inline-item float-right">
                                                        <button type="submit" class="btn btn-primary waves-effect waves-light"><?=$_["save_changes"]?></button>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="order-series">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            <?=$_["bouquet_order_sort_text"]?>
                                                        </p>
                                                        <select multiple id="sort_series" class="form-control" style="min-height:400px;">
                                                        <?php foreach ($rOrdered["series"] as $rStream) { ?>
                                                            <option value="<?=$rStream["id"]?>"><?=$rStream["stream_display_name"]?></option>
                                                        <?php } ?>
                                                        </select>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0 add-margin-top-20">
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" onClick="MoveUp('series')" class="btn btn-purple"><i class="mdi mdi-chevron-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveDown('series')" class="btn btn-purple"><i class="mdi mdi-chevron-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveTop('series')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveBottom('series')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="AtoZ('series')" class="btn btn-info"><?=$_["a_to_z"]?></a>
                                                    </li>
                                                    <li class="list-inline-item float-right">
                                                        <button type="submit" class="btn btn-primary waves-effect waves-light"><?=$_["save_changes"]?></button>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="order-radio">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            <?=$_["bouquet_order_sort_text"]?>
                                                        </p>
                                                        <select multiple id="sort_radio" class="form-control" style="min-height:400px;">
                                                        <?php foreach ($rOrdered["radio"] as $rStream) { ?>
                                                            <option value="<?=$rStream["id"]?>"><?=$rStream["stream_display_name"]?></option>
                                                        <?php } ?>
                                                        </select>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0 add-margin-top-20">
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" onClick="MoveUp('radio')" class="btn btn-purple"><i class="mdi mdi-chevron-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveDown('radio')" class="btn btn-purple"><i class="mdi mdi-chevron-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveTop('radio')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveBottom('radio')" class="btn btn-pink"><i class="mdi mdi-chevron-triple-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="AtoZ('radio')" class="btn btn-info"><?=$_["a_to_z"]?></a>
                                                    </li>
                                                    <li class="list-inline-item float-right">
                                                        <button type="submit" class="btn btn-primary waves-effect waves-light"><?=$_["save_changes"]?></button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
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
        <script src="assets/libs/nestable2/jquery.nestable.min.js"></script>
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
        <script src="assets/libs/datatables/dataTables.rowReorder.js"></script>
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        function AtoZ(rType) {
            $("#sort_" + rType).append($("#sort_" + rType + " option").remove().sort(function(a, b) {
                var at = $(a).text().toUpperCase(), bt = $(b).text().toUpperCase();
                return (at > bt) ? 1 : ((at < bt) ? -1 : 0);
            }));
        }
        function MoveUp(rType) {
            var rSelected = $('#sort_' + rType + ' option:selected');
            if (rSelected.length) {
                var rPrevious = rSelected.first().prev()[0];
                if ($(rPrevious).html() != '') {
                    rSelected.first().prev().before(rSelected);
                }
            }
        }
        function MoveDown(rType) {
            var rSelected = $('#sort_' + rType + ' option:selected');
            if (rSelected.length) {
                rSelected.last().next().after(rSelected);
            }
        }
        function MoveTop(rType) {
            var rSelected = $('#sort_' + rType + ' option:selected');
            if (rSelected.length) {
                rSelected.prependTo($('#sort_' + rType));
            }
        }
        function MoveBottom(rType) {
            var rSelected = $('#sort_' + rType + ' option:selected');
            if (rSelected.length) {
                rSelected.appendTo($('#sort_' + rType));
            }
        }
        $(document).ready(function() {
            $("#channel_order_form").submit(function(e){
                rOrder = [];
                $('#sort_stream option').each(function() {
                    rOrder.push($(this).val());
                });
                $('#sort_movie option').each(function() {
                    rOrder.push($(this).val());
                });
                $('#sort_series option').each(function() {
                    rOrder.push($(this).val());
                });
                $('#sort_radios option').each(function() {
                    rOrder.push($(this).val());
                });
                $("#stream_order_array").val(JSON.stringify(rOrder));
            });
        });
        </script>
    </body>
</html>