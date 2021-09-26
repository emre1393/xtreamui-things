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

$rOrdered = Array();
$result = $db->query("SELECT `id`, `type`, `stream_display_name`, `category_id` FROM `streams` ORDER BY `order` ASC, `stream_display_name` ASC;");
if (($result) && ($result->num_rows > 0)) {
    while ($row = $result->fetch_assoc()) {
        $rOrdered[$row["category_id"]][] = Array("id" => $row["id"], "value" => $row["stream_display_name"]);
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
                                        <a href="channel_order.php">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                <?=$_["simple"]?>
                                            </button>
                                        </a>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title"><?=$_["advanced_channel_order"]?></h4>
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
                                <form action="./channel_order_alt.php" method="POST" id="channel_order_form">
                                    <input type="hidden" id="stream_order_array" name="stream_order_array" value="" />
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#order-stream" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-play mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["manual_ordering"]?></span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="order-stream">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            <?=$_["bouquet_order_alt_sort_text"]?>
                                                        </p>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="select_category"><?=$_["select_category"]?></label>
                                                            <div class="col-md-8">
                                                                <select name="select_category" id="select_category" class="form-control select2" data-toggle="select2">>
                                                                    <?php foreach (getCategories() as $rCategory) { ?>
                                                                    <option value="<?=$rCategory["id"]?>"><?=$rCategory["category_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <select multiple id="sort_stream" class="form-control" style="min-height:400px;"></select>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0 add-margin-top-20">
                                                    <li class="list-inline-item">
                                                        <a href="javascript: void(0);" onClick="MoveUp()" class="btn btn-purple"><i class="mdi mdi-chevron-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveDown()" class="btn btn-purple"><i class="mdi mdi-chevron-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveTop()" class="btn btn-pink"><i class="mdi mdi-chevron-triple-up"></i></a>
                                                        <a href="javascript: void(0);" onClick="MoveBottom()" class="btn btn-pink"><i class="mdi mdi-chevron-triple-down"></i></a>
                                                        <a href="javascript: void(0);" onClick="AtoZ()" class="btn btn-info"><?=$_["a_to_z"]?></a>
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
        var rOrder = <?=json_encode($rOrdered);?>;
        
        function AtoZ() {
            $("#sort_stream").append($("#sort_stream option").remove().sort(function(a, b) {
                var at = $(a).text().toUpperCase(), bt = $(b).text().toUpperCase();
                return (at > bt) ? 1 : ((at < bt) ? -1 : 0);
            }));
            saveOrder();
        }
        function MoveUp() {
            var rSelected = $('#sort_stream option:selected');
            if (rSelected.length) {
                var rPrevious = rSelected.first().prev()[0];
                if ($(rPrevious).html() != '') {
                    rSelected.first().prev().before(rSelected);
                }
            }
            saveOrder();
        }
        function MoveDown() {
            var rSelected = $('#sort_stream option:selected');
            if (rSelected.length) {
                rSelected.last().next().after(rSelected);
            }
            saveOrder();
        }
        function MoveTop() {
            var rSelected = $('#sort_stream option:selected');
            if (rSelected.length) {
                rSelected.prependTo($('#sort_stream'));
            }
            saveOrder();
        }
        function MoveBottom() {
            var rSelected = $('#sort_stream option:selected');
            if (rSelected.length) {
                rSelected.appendTo($('#sort_stream'));
            }
            saveOrder();
        }
        function saveOrder() {
            window.rOrder[$("#select_category").val()] = [];
            $('#sort_stream option').each(function() {
                window.rOrder[$("#select_category").val()].push({"id": $(this).val(), "value": $(this).text()});
            });
        }
        
        $(document).ready(function() {
            $('.select2').select2({width: '100%'});
            $("#select_category").change(function() {
                $("#sort_stream").empty();
                $(window.rOrder[$(this).val()]).each(function() {
                    $("#sort_stream").append(new Option($(this)[0].value, $(this)[0].id));
                });
                $("#sort_stream").trigger('change');
            });
            $("#channel_order_form").submit(function(e){
                rFinalOrder = [];
                $("#select_category > option").each(function() {
                    $(window.rOrder[$(this).val()]).each(function() {
                        rFinalOrder.push($(this)[0].id);
                    });
                });
                $("#stream_order_array").val(JSON.stringify(rFinalOrder));
            });
            
            $("#select_category").trigger('change');
        });
        </script>
    </body>
</html>