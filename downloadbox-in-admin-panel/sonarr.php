<?php
include "session.php"; include "functions.php";
if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "series", "add_series", "episodes"))) { exit; }

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
                                </li>
                            </ol>
                        </div>
                        <h4 class="page-title">Sonarr for Series</h4>
                    </div>
                </div>
            </div>     
            <!-- end page title --> 

                <div class="row">
                    <div class="col-12">
                        <html lang="en">
                        <!-- edit src="link" for your sonarr web interface link -->
                        <center><iframe src="http://192.168.1.240/sonarr" style=" background: white; border: none; width: 100%; height: 750px; align: center"></iframe></center>
                        </html>

                        </div><!-- end col-->
                </div><!-- end row-->
            </div> <!-- end container -->
        </div> <!-- end wrapper -->

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
        <script src="assets/js/app.min.js"></script>
    </body>
</html>