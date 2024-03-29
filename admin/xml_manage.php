<?php
require_once('../includes/config.php');
require_once('../includes/functions/func.admin.php');
require_once('../includes/functions/func.sqlquery.php');

$mysqli = db_connect($config);
session_start();
checkloggedadmin();
if(isset($_POST['update']))
{
    if(!check_allow()){
        ?>
        <script src="plugins/bower_components/jquery/dist/jquery.min.js"></script>
        <script>
            $(document).ready(function(){
                $('#sa-title').trigger('click');
            });
        </script>
    <?php

    }
    else {

        update_option($config,"xml_latest",$_POST['xml_latest']);
        update_option($config,"xml_featured",$_POST['xml_featured']);

        $message = '<span style="color:green;">( XML Options Updated )</span>';

        transfer($config,'xml_manage.php','XML Options Updated','Manage XML Feeds');
        exit;
    }
}

include("header.php");
?>


    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row bg-title">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                    <h4 class="page-title">Manage XML Feeds</h4>
                </div>
                <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                    <ol class="breadcrumb">
                        <li><a href="index.php">Dashboard</a></li>
                        <li class="active">Manage XML</li>
                    </ol>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /row -->
            <div class="row">
                <div class="col-sm-6">
                    <div class="white-box">
                        <div>
                            <div class="text-left"><h3 class="box-title">XML Links</h3></div>
                        </div>
                        <div class="table-responsive">
                            <table cellspacing="1" cellpadding="1" class="table">
                                <form action="" method="post" name="f1" id="f1">
                                    <thead>
                                    <tr>
                                        <th>Feed Name</th>
                                        <th>Stauts</th>
                                        <th>Link</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Latest Ads</td>
                                            <td><?php if(get_option( $config, "xml_latest")== 1){ echo "Enabled"; } ELSE { echo "Disabled"; } ?></td>
                                            <td><a target="_new" href="<?php echo $config['site_url']; ?>xml.php?t=latestads"><?php echo $config['site_url']; ?>xml.php?t=latestads</a></td>
                                        </tr>
                                        <tr>
                                            <td>Premium Ads</td>
                                            <td><?php if(get_option( $config, "xml_featured") == 1){ echo "Enabled"; } ELSE { echo "Disabled"; } ?></td>
                                            <td><a target="_new" href="<?php echo $config['site_url']; ?>xml.php?t=premiumads"><?php echo $config['site_url']; ?>xml.php?t=premiumads</a></td>
                                        </tr>
                                    </tbody>
                                    <table width="99%"  border="0" align="center" cellpadding="2" cellspacing="0">
                                        <tr>
                                            <td width="200" valign="middle">&nbsp;</td>
                                            <td valign="middle"><div align="center"><span class="style5 style6">Showing 1-2 of 2 result(s)</span></div></td>
                                            <td width="200" valign="middle">&nbsp;</td>
                                        </tr>
                                    </table>
                                </form>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="white-box">
                        <form class="form form-horizontal" action="xml_manage.php" method="post">
                            <div>
                                <div class="text-left"><h3 class="box-title">XML Setting <?php echo $message; ?></h3></div>
                            </div>


                            <div class="form-group bt-switch">
                                <label class="col-sm-4 control-label">Latest Ads:</label>
                                <div class="col-sm-6">
                                    <input name="xml_latest" type="checkbox" <?php if(get_option( $config, "xml_latest") == '1'){ echo "checked"; } ?> data-on-color="success" data-off-color="warning">
                                </div>
                            </div>
                            <div class="form-group bt-switch">
                                <label class="col-sm-4 control-label">Premium Ads:</label>
                                <div class="col-sm-6">
                                    <input name="xml_featured" type="checkbox" <?php if(get_option( $config, "xml_featured") == '1'){ echo "checked"; } ?> data-on-color="success" data-off-color="warning">
                                </div>
                            </div>





                            <!--Default Horizontal Form-->
                            <div class="form-group">
                                <label class="col-sm-4 control-label"></label>
                                <div class="col-sm-6">
                                    <input name="update" type="submit" class="btn btn-primary btn-radius" value="Update">
                                </div>
                            </div>
                            <!--Default Horizontal Form-->

                        </form>
                    </div>
                </div>

            </div>
            <!-- /.row -->




<?php include("footer.php"); ?>