<?php
require_once('../includes/config.php');
require_once('../includes/functions/func.admin.php');
require_once('../includes/functions/func.sqlquery.php');

$mysqli = db_connect($config);
session_start();
checkloggedadmin();

$error = "";
$adminId = isset($_GET['id'])? $_GET['id'] : $_SESSION['admin']['id'];
if(isset($_POST['Submit']))
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
    else{
        if($_FILES['file']['name'] != "")
        {
            $uploaddir = '../storage/profile/';
            $original_filename = $_FILES['file']['name'];
            $random1 = rand(9999,100000);
            $random2 = rand(9999,200000);
            $random3 = $random1.$random2;
            $extensions = explode(".", $original_filename);
            $extension = $extensions[count($extensions) - 1];
            $uniqueName =  $random3 . "." . $extension;
            $uploadfile = $uploaddir . $uniqueName;

            $file_type = "file";

            if ($extension == "jpg" || $extension == "jpeg" || $extension == "gif" || $extension == "png") {
                $file_type = "image";

                $size = filesize($_FILES['file']['tmp_name']);

                $image = $_FILES["file"]["name"];
                $uploadedfile = $_FILES['file']['tmp_name'];

                if ($image) {
                    if ($extension == "jpg" || $extension == "jpeg") {
                        $uploadedfile = $_FILES['file']['tmp_name'];
                        $src = imagecreatefromjpeg($uploadedfile);
                    } else if ($extension == "png") {
                        $uploadedfile = $_FILES['file']['tmp_name'];
                        $src = imagecreatefrompng($uploadedfile);
                    } else {
                        $src = imagecreatefromgif($uploadedfile);
                    }

                    list($width, $height) = getimagesize($uploadedfile);

                    $newwidth = 225;
                    $newheight = 225;
                    //$newheight = ($height / $width) * $newwidth;
                    $tmp = imagecreatetruecolor($newwidth, $newheight);

                    imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                    $filename = $uploaddir . "small" . $uniqueName;

                    imagejpeg($tmp, $filename, 100);

                    imagedestroy($src);
                    imagedestroy($tmp);
                }


            }
            //else if it's not bigger then 0, then it's available '
            //and we send 1 to the ajax request
            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
                //$time = date('Y-m-d H:i:s', time());
                $query = "Update `".$config['db']['pre']."admins` set
                name='" . addslashes($_POST['name']) . "',
                image='$uniqueName'
                WHERE id = {$_GET['id']} ";
                $query_result = $mysqli->query($query);

                $success = "Profile Updated Successfully";
            }
        }
        else{
            //$time = date('Y-m-d H:i:s', time());
            $query = "Update `".$config['db']['pre']."admins` set
            name='" . addslashes($_POST['name']) . "',
            WHERE id = {$_GET['id']}";
            $query_result = $mysqli->query($query);

            $success = "Profile Updated Successfully";
        }
    }


}

if($_POST['change'])
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
    else{
        $select=mysqli_query($mysqli,"select * from `".$config['db']['pre']."admins`  where id = '".$adminId."'");
        $count1=mysqli_num_rows($select);

        if($count1>0)
        {
            mysqli_query($mysqli,"update `".$config['db']['pre']."admins` set username='".$_POST["username"]."', email='".$_POST["email"]."' , password='".md5($_POST["newPassword"])."' where id = '".$adminId."'");
            ?>
            <script type="text/javascript">

                alert("Changing Successfully");

            </script>
        <?php
        }
        else
        {
            $error="Email and Password not match.";
        }
    }
}

include("header.php");
?>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row bg-title">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                    <h4 class="page-title">Admin</h4>
                </div>
                <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                    <ol class="breadcrumb">
                        <li><a href="index.php">Dashboard</a></li>
                        <li class="active">Edit Admin Profile</li>
                    </ol>
                </div>
                <!-- /.col-lg-12 -->
            </div>
        <span style="color:#df6c6e;">
                    <?php
                    if(!empty($error)){
                        echo '<div class="byMsg byMsgError">! '.$error.'</div>';
                    }
                    ?>
                </span>
            <span style="color:#31df0c;">
                    <?php
                    if(!empty($success)){
                        echo '<div class="byMsg byMsgSuccess">! '.$success.'</div>';
                    }
                    ?>
                </span>
            <!-- /row -->
            <div class="row">
                <div class="col-md-12">
                    <?php

                    $sql = "SELECT * FROM `".$config['db']['pre']."admins` where id = '".$adminId."'";
                    $res = $mysqli->query($sql);
                    $fetch = mysqli_fetch_assoc($res);
                    ?>
                    <div class="panel panel-info">
                        <div class="panel-wrapper collapse in" aria-expanded="true">
                            <div class="panel-body">
                                <form name="form1" method="post" action="#" id="send" enctype="multipart/form-data">
                                    <div class="form-body">
                                        <h3 class="box-title">Person Info</h3>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="col-md-2">
                                                        <img src="../storage/profile/small<?php echo $fetch['image'];?>" alt="<?php echo $fetch['name'];?>" style="width: 80px; border-radius: 50%">
                                                    </div>
                                                    <div class="col-md-10">
                                                        <label class="control-label">Profile Picture</label>
                                                        <div class="col-sm-12">
                                                            <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                                                <div class="form-control" data-trigger="fileinput"> <i class="glyphicon glyphicon-file fileinput-exists"></i> <span class="fileinput-filename"></span></div>
                    <span class="input-group-addon btn btn-default btn-file"> <span class="fileinput-new">Select file</span> <span class="fileinput-exists">Change</span>
                    <input type="file" name="file">
                    </span> <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a> </div>
                                                        </div>
                                                        <span class="help-block"> Change Your Photo</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="exampleInputfullname">Full Name</label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><i class="ti-user"></i></div>
                                                        <input type="text" class="form-control" id="exampleInputfullname" placeholder="Full Name" name="name" value="<?php echo $fetch['name'];?>">
                                                        <span class="help-block"></span>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <br>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" name="Submit" class="btn btn-success"> <i class="fa fa-check"></i> Save</button>
                                                    <a href="index.php" class="btn btn-default">Cancel</a>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="white-box">
                        <h3 class="box-title m-b-0">Account Setting</h3>
                        <form name="form2" method="post" action="#" id="send2">
                            <div class="form-body">
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputuname">User Name</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="ti-user"></i></div>
                                                <input type="text" class="form-control" id="exampleInputuname" placeholder="Username" name="username" value="<?php echo $fetch['username'];?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Email address</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="ti-email"></i></div>
                                                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email" name="email" value="<?php echo $fetch['email'];?>">
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputpwd1">Password</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="ti-lock"></i></div>
                                                <input type="password" class="form-control" id="exampleInputpwd1" placeholder="Enter Old Password" name="oldPassword">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputpwd2">New Password</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="ti-lock"></i></div>
                                                <input type="password" class="form-control" id="exampleInputpwd2" placeholder="Enter New Password" name="newPassword">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="form-actions">
                                <input type="submit" name="change" class="btn btn-success" value="Change"  />
                                <a href="index.php" class="btn btn-default">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /.row -->

<?php include("footer.php"); ?>