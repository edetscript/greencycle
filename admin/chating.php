<?php
require_once('../includes/config.php');
require_once('../includes/functions/func.admin.php');
require_once('../includes/functions/func.sqlquery.php');

$mysqli = db_connect($config);
session_start();
checkloggedadmin();

require_once('../includes/functions/func.users.php');
require_once('../includes/functions/func.sqlquery.php');

include("header.php");

?>

<!-- Page Content -->
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Messages</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="../index.php">Dashboard</a></li>
                    <li class="active">Messages</li>
                </ol>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    <div id="quickad-tbs" class="wrap">
                        <div id="quickad-alert" class="quickad-alert"></div>
                    </div>
                    <form method="post" name="f1" id="f1">
                        <div>
                            <div class="pull-left"><h3 class="box-title">All Messages List</h3></div>
                            <div class="pull-right">
                                <p class="text-muted">
                                    <button data-ajax-response="deletemarked" data-ajax-action="deleteMessage" class="btn btn-danger waves-effect waves-light m-r-10"><i class="fa fa-trash-o"></i> Delete Marked</button>
                                </p>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <hr>

                        <div class="table-responsive" id="js-table-list">
                            <table id="myTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="sortingNone">
                                            <div class="checkbox checkbox-success">
                                                <input type="checkbox" name="selall" value="checkbox" id="selall" onClick="checkBox(this)">
                                                <label for="selall"></label>
                                            </div>
                                        </th>
                                        <th class="sortingNone">#ID</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Message</th>
                                        <th>Time</th>
                                        <th>Received</th>
                                        <th class="sortingNone">Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th class="sortingNone">None</th>
                                        <th class="sortingNone">#ID</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Message</th>
                                        <th>date</th>
                                        <th class="sortingNone">Received</th>
                                        <th class="sortingNone">Action</th>
                                    </tr>
                                </tfoot>
                                <tbody>

                                <?php
                            if(isset($_GET['from']) && isset($_GET['to']) ){
                                $query = "SELECT * FROM `".$config['db']['pre']."messages` where ((to_uname = '".mysqli_real_escape_string($mysqli, $_GET['from'])."' AND from_uname = '".mysqli_real_escape_string($mysqli,$_GET['to'])."' ) OR (to_uname = '".mysqli_real_escape_string($mysqli,$_GET['to'])."' AND from_uname = '".mysqli_real_escape_string($mysqli,$_GET['from'])."' )) ORDER BY message_id DESC";
                            }
                            else{
                                $query = "SELECT * FROM `".$config['db']['pre']."messages` where message_type = 'text' ORDER BY message_id DESC";
                            }

                            $result = $mysqli->query($query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                $id = $row['message_id'];
                                $fromuname = $row['from_uname'];
                                $touname = $row['to_uname'];

                                $msgdate = $row['message_date'];
                                $msgcontent = $row['message_content'];
                                $recd = $row['recd'];
                                $msgtype = $row['message_type'];

                                $picname = "";
                                $picname2 = "";

                                $query1 = "SELECT image FROM `".$config['db']['pre']."user` WHERE username='".$row['from_uname']."' LIMIT 1";
                                $query_result = mysqli_query ($mysqli, $query1);
                                while ($info = mysqli_fetch_array($query_result))
                                {
                                    $picname = "small_".$info['image'];
                                }

                                $query4 = "SELECT image FROM `".$config['db']['pre']."user` WHERE username='".$row['to_uname']."' LIMIT 1";
                                $query_result4 = mysqli_query ($mysqli, $query4);
                                while ($info4 = mysqli_fetch_array($query_result4))
                                {
                                    $picname2 = "small_".$info4['image'];
                                }


                                if ($recd == "0"){
                                    $recd = '<span class="label label-info">Unread</span>';
                                }
                                elseif($recd == "1")
                                {
                                    $recd = '<span class="label label-success">Read</span>';
                                }

                                ?>

                                <tr class="mail-contnet ajax-item-listing" data-item-id="<?php echo $id ?>">
                                    <td>
                                        <input type="hidden" name="titles[]" id="titles[]" value="<?php echo $id;?>">

                                        <div class="checkbox checkbox-success">
                                            <input type="checkbox" name="list[]" id="checkbox<?php echo $id;?>" class="service-checker" value="<?php echo $id;?>" style="display: block">
                                            <label for="checkbox<?php echo $id;?>"></label>
                                        </div>
                                    </td>

                                    <td><?php echo $id ?></td>
                                    <!--<td><img src="../storage/user_image/<?php /*echo $picname; */?>" alt="<?php /*echo $username */?>" class="img-circle bg-theme" width="40"></td>-->


                                    <td><img src="../storage/profile/<?php echo $picname; ?>" alt="<?php echo $row['from_uname'] ?>" class="img-circle bg-theme" width="30"> <?php echo $row['from_uname'] ?></td>
                                    <td><img src="../storage/profile/<?php echo $picname2; ?>" alt="<?php echo $row['to_uname'] ?>" class="img-circle bg-theme" width="30"> <?php echo $row['to_uname'] ?></td>
                                    <td width="20%" style="max-width: 100px;word-break: break-all;"><?php echo $msgcontent ?></td>
                                    <td><?php echo date('M dS g:iA', strtotime($msgdate)); ?></td>
                                    <td><?php echo $recd ?></td>
                                    <td class="text-nowrap">
                                        <a href="chating.php?from=<?php echo $row['from_uname'] ?>&to=<?php echo $row['to_uname'] ?>" data-toggle="tooltip" data-original-title="Filter <?php echo $row['from_uname'] ?> and <?php echo $row['to_uname'] ?> Conversation"> <i class="ti-eye m-r-10 font-bold"></i></a>
                                        <a href="message_delete.php?id=<?php echo $id;?>" data-toggle="tooltip" data-original-title="Delete Message" class="action item-js-delete" data-ajax-action="deleteMessage"> <i class="ti-close text-danger"></i> </a>
                                    </td>
                                </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        <!-- /.row -->

        <script>
            var ajaxurl = '<?php echo $config['site_url'].'admin-ajax.php'; ?>';
        </script>
        <?php include("footer.php"); ?>
        <script src="js/admin-ajax.js"></script>
        <script src="js/alert.js"></script>
