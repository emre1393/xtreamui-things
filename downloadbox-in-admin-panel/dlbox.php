<?php
include "session.php"; include "functions.php";
if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "movies", "add_movie",  "import_movies", "series", "add_series", "episodes"))) { exit; }

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
include "dlbox_links.php";
            if ($rSettings["sidebar"]) { ?>
            <div class="content-page"><div class="content"><div class="container-fluid">
            <?php } else { ?>
            <div class="wrapper"><div class="container-fluid">
            <?php } ?>


            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <ul class="nav nav-tabs nav-bordered dashboard-tabs">
                            <li class="nav-item">
                                <a class="nav-link">
                                <button class="btn btn-primary" onclick="openPage('Radarr', this, 'orange')" id="defaultOpen">Radarr for Movies</button>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link">
                                <button class="btn btn-primary" onclick="openPage('Sonarr', this, 'orange')">Sonarr for Series</button>
                                </a>
                            </li>
                            <li class="nav-item">
                            <a class="nav-link">
                                <button class="btn btn-primary" onclick="openPage('Deluge', this, 'orange')">Deluge for P2P Downloads</button>
                                </a>
                            </li>
                            <li class="nav-item float-right">
                                <a class="nav-link" href="javascript:location.reload();">
                                    <button type="button" class="btn btn-red waves-effect waves-light btn btn-primary btn-sm"><i class="mdi mdi-refresh"></i>Refresh</button>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>     
            <!-- end page title --> 

            <!-- there are 3 tabcontents here, for radarr, sonarr and deluge --> 
            <!-- edit dlbox_links.php and put related link -->
                <div id="Radarr" class="tabcontent">
                    <div class="row">
                        <div class="col-12">
                            <html lang="en">
                                <!-- edit src="link" for your radarr web interface link -->    
                                <center><iframe src="<?=$_["radarr_url"]?>" style=" background: white; border: none; width: 100%; height: 750px; align: center"></iframe></center>
                            </html>
                        </div><!-- end col -->
                    </div><!-- end row -->
                </div><!-- tabcontent -->

                <div id="Sonarr" class="tabcontent">
                    <div class="row">
                        <div class="col-12">
                            <html lang="en">
                                <!-- edit src="link" for your sonarr web interface link -->
                                <center><iframe src="<?=$_["sonarr_url"]?>" style=" background: white; border: none; width: 100%; height: 750px; align: center"></iframe></center>
                            </html>
                        </div><!-- end col -->
                    </div><!-- end row -->
                </div><!-- tabcontent -->

                <div id="Deluge" class="tabcontent">
                    <div class="row">
                        <div class="col-12">
                            <html lang="en">
                                <!-- edit src="link" for your deluge web interface link -->
                                <center><iframe src="<?=$_["deluge_url"]?>" style=" background: white; border: none; width: 100%; height: 750px; align: center"></iframe></center>
                            </html>
                        </div><!-- end col -->
                    </div><!-- end row -->
                </div><!-- tabcontent -->

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
        <script src="assets/libs/jquery-knob/jquery.knob.min.js"></script>
        <script src="assets/libs/peity/jquery.peity.min.js"></script>
		<script src="assets/libs/apexcharts/apexcharts.min.js"></script>
        <script src="assets/libs/jquery-number/jquery.number.js"></script>
        <script src="assets/js/app.min.js"></script>
        <!-- https://www.w3schools.com/howto/howto_js_full_page_tabs.asp -->
        <script>
        function openPage(pageName,elmnt,color) {
        var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
            tablinks = document.getElementsByClassName("btn btn-primary");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].style.backgroundColor = "";
        }
        document.getElementById(pageName).style.display = "block";
        elmnt.style.backgroundColor = color;
        }

        // Get the element with id="defaultOpen" and click on it
        document.getElementById("defaultOpen").click();
        </script>
        
    </body>
</html>