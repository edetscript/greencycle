<?php
require_once('includes/config.php');
session_start();
require_once('includes/classes/class.template_engine.php');
require_once('includes/functions/func.global.php');
require_once('includes/functions/func.users.php');
require_once('includes/functions/func.sqlquery.php');
require_once('includes/lang/lang_'.$config['lang'].'.php');
if($config['mod_rewrite'] == 0)
    require_once('includes/simple-url.php');
else
    require_once('includes/seo-url.php');

$mysqli = db_connect($config);


if(checkloggedin()) {

    $status = check_item_status($config,$_GET['id']);

    $header_text = "";
    $header_note = "";
    $resubmit = "";
    if($status == "pending"){
        $header_text = $lang['EDIT-AD'];
        $resubmit = 0;
    }
    elseif($status == "active" or $status == "softreject" or $status == "hide")
    {
        if(check_valid_resubmission($config,$_GET['id'])){
            $header_text = $lang['RE-SUBISSION'];
            $header_note = $lang['RE-SUBISSION-TEXT'];
            $resubmit = 1;
        }else{
            message($lang['ALREADY-EXIST'],$lang['RESUMIT-EXIST-TEXT'],$config,$lang,$link,'',false);
            exit;
        }

    }else {
        error($lang['PAGENOTEXIST'], __LINE__, __FILE__, 1, $lang, $config, $link);
        exit;
    }

    $query = "SELECT * FROM `".$config['db']['pre']."payments` WHERE payment_install='1' ORDER BY  payment_id";
    $query_result = @mysqli_query ($mysqli,$query) OR error(mysqli_error($mysqli));
    while ($info = @mysqli_fetch_array($query_result))
    {
        $payment_types[$info['payment_id']]['id'] = $info['payment_id'];
        $payment_types[$info['payment_id']]['title'] = $info['payment_title'];
    }

    $featured_project_fee = $config['featured_fee'];
    $urgent_project_fee = $config['urgent_fee'];
    $highlight_project_fee = $config['highlight_fee'];

    if(check_valid_author($config,$_GET['id'])){

        $total[0] = mysqli_num_rows(mysqli_query(db_connect($config), "SELECT 1 FROM " . $config['db']['pre'] . "product where id = '" . $_GET['id'] . "' limit 1"));
        $sql = "SELECT * FROM " . $config['db']['pre'] . "product where  id = '" . $_GET['id'] . "' limit 1";
        $result = mysqli_query(db_connect($config), $sql);

        $item_custom = array();
        $item_checkbox = array();

        if (mysqli_num_rows($result) > 0) {
            // output data of each row

            $info = mysqli_fetch_assoc($result);

            $item_id = $info['id'];
            $item_title = $info['product_name'];
            $item_description = $info['description'];
            $item_catid = $info['category'];
            $item_subcatid = $info['sub_category'];
            $item_featured = $info['featured'];
            $item_urgent = $info['urgent'];
            $item_highlight = $info['highlight'];
            $item_price = $info['price'];
            $item_negotiable = $info['negotiable'];
            $item_phone = $info['phone'];
            $item_hide_phone = $info['hide_phone'];
            $item_tag = $info['tag'];
            $item_location = $info['location'];
            $item_city = $info['city'];
            $item_state = $info['state'];
            $item_country = $info['country'];
            $item_status = $info['status'];
            $item_view = $info['view'];
            $item_screen = $info['screen_shot'];
            $item_created_at = timeAgo($info['created_at']);
            $item_updated_at = date('d M Y', $info['updated_at']);
            $item_custom_field = $info['custom_fields'];
            $custom_fields = explode(',', $info['custom_fields']);
            $custom_types = explode(',', $info['custom_types']);
            $custom_data = explode(',', $info['custom_values']);

            $item_contact_phone = $info['contact_phone'];
            $item_contact_email = $info['contact_email'];
            $item_contact_chat = $info['contact_chat'];


            $item_catid = isset($_POST['catid']) ? $_POST['catid'] : $item_catid;
            $item_subcatid = isset($_POST['subcatid']) ? $_POST['subcatid'] : $item_subcatid;

            $get_main = get_maincat_by_id($config, $item_catid);
            $get_sub = get_subcat_by_id($config, $item_subcatid);
            $item_category = $get_main['cat_name'];
            $item_caticon = $get_main['icon'];
            $item_sub_category = $get_sub['sub_cat_name'];


            $custom_fields = get_customFields_by_catid($config, $mysqli, $item_catid, $item_subcatid, $custom_fields, $custom_data);

            foreach ($custom_fields as $key => $value) {
                if ($value['userent']) {
                    $custom_db_fields[$value['id']] = $value['title'];
                    $custom_db_data[$value['id']] = str_replace(',', '&#44;', $value['default']);
                }
            }

            $showCustomField = (count($custom_fields) > 0) ? 1 : 0;

            $latlong = $info['latlong'];
            $map = explode(',', $latlong);
            $lat = $map[0];
            $long = $map[1];


            $imagesCount = 0;
            $maxImgLength = 5;
            $screen = "";
            if($info['screen_shot'] != ""){
                $screen = explode(',', $info['screen_shot']);
                foreach ($screen as $value) {
                    //REMOVE SPACE FROM $VALUE ----
                    $value = trim($value);
                    $screen2[] = '<div class="MultiFile-label"><a class="MultiFile-remove" href="#" id="removeAdImg" data-item-id="' . $item_id . '" data-img-name="' . $value . '">x</a> <span><span class="MultiFile-label" title="File selected: ' . $value . '"><span class="MultiFile-title">' . $value . '</span><img class="MultiFile-preview" style="max-height:100px; max-width:100px;" src="' . $config['site_url'] . 'storage/products/screenshot/small_' . $value . '"></span></span></div>
                    ';
                    $imagesCount++;
                }
                $maxImgLength = 5 - $imagesCount;
                $screen = implode('  ', $screen2);
            }
        }
        else {
            error($lang['PAGENOTEXIST'], __LINE__, __FILE__, 1, $lang, $config, $link);
            exit;
        }

        if(!isset($_POST["submit"])) {

            if (isset($_POST['editcat'])) {
                $cat = get_maincategory($config);
                $subcat = get_categories($config, $mysqli);
                $page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/choose-category.html');
                $page->SetLoop('CATEGORY', $cat);
                $page->SetLoop('SUBCAT', $subcat);
                $page->SetParameter('CATID', $item_catid);
                $page->SetParameter('SUBCATID', $item_subcatid);
            } else {
                $page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/ad-edit.html');
            }

            $contact_phone = ($item_contact_phone == 1) ? "checked" : "";
            $contact_email = ($item_contact_email == 1) ? "checked" : "";
            $contact_chat = ($item_contact_chat == 1) ? "checked" : "";

            $page->SetParameter('OVERALL_HEADER', create_header($config, $lang, $item_title . " - ".$lang['EDIT'], $link));
            $page->SetLoop('ERRORS', "");
            $page->SetLoop('PAYMENT_TYPES', $payment_types);
            $page->SetLoop('CUSTOMFIELDS', $custom_fields);
            $page->SetParameter('SHOWCUSTOMFIELD', $showCustomField);
            $page->SetParameter('ITEM_ID', $item_id);
            $page->SetParameter('TITLE', $item_title);
            $page->SetParameter('FEATURED', $item_featured);
            $page->SetParameter('URGENT', $item_urgent);
            $page->SetParameter('HIGHLIGHT', $item_highlight);
            $page->SetParameter('CATID', $item_catid);
            $page->SetParameter('SUBCATID', $item_subcatid);
            $page->SetParameter('CATEGORY', $item_category);
            $page->SetParameter('CATICON', $item_caticon);
            $page->SetParameter('SUBCATEGORY', $item_sub_category);
            $page->SetParameter('LOCATION', $item_location);
            $page->SetParameter('CITY', $item_city);
            $page->SetParameter('STATE', $item_state);
            $page->SetParameter('COUNTRY', $item_country);
            $page->SetParameter ('CITYFIELD', get_cityName_by_id($config,$item_city));
            $page->SetParameter('LATITUDE', $lat);
            $page->SetParameter('LONGITUDE', $long);
            $page->SetParameter('DESCRIPTION', $item_description);
            $page->SetParameter('PRICE', $item_price);
            $page->SetParameter('NEGOTIATE', $item_negotiable);
            $page->SetParameter('PHONE', $item_phone);
            $page->SetParameter('HIDE_PHONE', $item_hide_phone);
            $page->SetParameter('ITEM_SCREENS', $screen);
            $page->SetParameter('IMGCOUNT', $imagesCount);
            $page->SetParameter('MAXIMGLNT', $maxImgLength);
            $page->SetParameter('ITEM_STATUS', $item_status);
            $page->SetParameter('TAGS', $item_tag);
            $page->SetParameter ('CONTACT_PHONE', $contact_phone);
            $page->SetParameter ('CONTACT_EMAIL', $contact_email);
            $page->SetParameter ('CONTACT_CHAT', $contact_chat);
            $page->SetParameter('FEATURED_FEE', $featured_project_fee);
            $page->SetParameter('HIGHLIGHT_FEE', $highlight_project_fee);
            $page->SetParameter('URGENT_FEE', $urgent_project_fee);
            $page->SetParameter ('FEATURED', '');
            $page->SetParameter ('HIGHLIGHT', '');
            $page->SetParameter ('URGENT', '');
            $page->SetParameter('HEADER_TEXT', $header_text);
            $page->SetParameter('HEADER_NOTE', $header_note);
            $page->SetParameter('RESUBMIT', $resubmit);
            $page->SetParameter ('DEFAULT_COUNTRY', check_user_country($config));
            $page->SetParameter('OVERALL_FOOTER', create_footer($config, $lang, $link));
            $page->CreatePageEcho($lang, $config, $link);
        }
        else{

            $urgent = isset($_POST['urgent']) ? 1 : 0;
            $featured = isset($_POST['featured']) ? 1 : 0;
            $highlight = isset($_POST['highlight']) ? 1 : 0;

            $errors = array();
            $inputstring = $_POST['title'];
            $first_title = strtok($inputstring, " ");

            $payment_req = "";
            if(isset($_POST['urgent'])){
                if(!isset($_POST['payment_id'])){
                    $payment_req = $lang['PAYMENT_METHOD_REQ'];
                }
            }
            if(isset($_POST['featured'])){
                if(!isset($_POST['payment_id'])){
                    $payment_req = $lang['PAYMENT_METHOD_REQ'];
                }
            }
            if(isset($_POST['highlight'])){
                if(!isset($_POST['payment_id'])){
                    $payment_req = $lang['PAYMENT_METHOD_REQ'];
                }
            }

            if(!empty($payment_req))
                $errors[]['message'] = $payment_req;

            if(empty($_POST['title'])) {
                $errors[]['message'] = $lang['ADTITLE_REQ'];
            }
            if(empty($_POST['subcatid']) or empty($_POST['catid'])) {
                $errors[]['message'] = $lang['CAT_REQ'];
            }
            if(empty($_POST['content'])) {
                $errors[]['message'] = $lang['DESC_REQ'];
            }
            if(empty($_POST['tags'])) {
                $errors[]['message'] = $lang['TAG_REQ'];
            }
            if(empty($_POST['country'])) {
                $errors[]['message'] = "Please select your country.";
            }
            if(empty($_POST['city'])) {
                $errors[]['message'] = $lang['CITY_REQ'] ;
            }
            if(empty($_POST['state'])) {
                $errors[]['message'] = $lang['STATE_REQ'] ;
            }
            if(!empty($_POST['price'])) {
                if (!is_numeric($_POST['price'])) {
                    $errors[]['message'] = $lang['PRICE_MUST_NO'];
                }
            }

            if(isset($_POST['subcatid'])){
                $custom_fields = get_customFields_by_catid($config,$mysqli,$_POST['catid'],$_POST['subcatid']);

                foreach($custom_fields as $key=>$value)
                {
                    if($value['userent'])
                    {
                        $custom_db_fields[$value['id']] = $value['title'];
                        $custom_db_data[$value['id']] = str_replace(',','&#44;',$value['default']);
                    }
                }

                $showCustomField = (count($custom_fields) > 0) ? 1 : 0;
            }

            $location = $_POST['location'];
            if(!empty($location)){

                $mapLat     =   $_POST['latitude'];
                $mapLong    =   $_POST['longitude'];
                $latlong = $mapLat.",".$mapLong;
            }
            else{
                $errors[]['message'] = $lang['LOC_REQ'];
            }

            if(empty($_POST['agree'])) {
                $errors[]['message'] = $lang['AGREE_COPYRIGHT'];
            }

            if(isset($_FILES['item_screen']) && count($_FILES['item_screen']['error']) == 1 && $_FILES['item_screen']['error'][0] > 0){
                if($imagesCount == 0){
                    $errors[]['message'] = $lang['PIC_REQ'];
                }
            }

            if($resubmit == 1){
                if(empty($_POST['comments'])) {
                    //$errors[]['message'] = $lang['COMMENT_REQ'];
                }
            }

            if(count($errors) > 0)
            {
                $page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/ad-edit.html');
                $page->SetParameter ('OVERALL_HEADER', create_header($config,$lang,$lang['EDIT-AD'],""));
                $page->SetLoop('ERRORS', $errors);
                $page->SetLoop ('PAYMENT_TYPES', $payment_types);

                $page->SetParameter ('FEATURED', $featured);
                $page->SetParameter ('URGENT', $urgent);
                $page->SetParameter ('HIGHLIGHT', $highlight);

                $maincat = get_maincat_by_id($config,$_POST['catid']);
                $maincatName = $maincat['cat_name'];
                $maincatIcon = $maincat['icon'];
                $subcat = get_subcat_by_id($config,$_POST['subcatid']);
                $subcatName = $subcat['sub_cat_name'];

                $contact_phone = isset($_POST['contact_phone']) ? "checked" : "";
                $contact_email = isset($_POST['contact_email']) ? "checked" : "";
                $contact_chat = isset($_POST['contact_chat']) ? "checked" : "";

                $page->SetParameter ('CATID', $_POST['catid']);
                $page->SetParameter ('SUBCATID', $_POST['subcatid']);
                $page->SetParameter ('CATEGORY', $maincatName);
                $page->SetParameter ('CATICON', $maincatIcon);
                $page->SetParameter ('SUBCATEGORY', $subcatName);
                $page->SetParameter ('USERNAME', $_SESSION['user']['username']);
                $page->SetParameter ('TITLE',$_POST['title']);
                $page->SetParameter ('PRICE', $_POST['price']);
                $page->SetParameter ('PHONE', $_POST['phone']);
                $page->SetParameter ('LOCATION', $_POST['location']);
                $page->SetParameter ('CITY', $_POST['city']);
                $page->SetParameter ('STATE', $_POST['state']);
                $page->SetParameter ('COUNTRY', $_POST['country']);
                $page->SetParameter ('LATITUDE', $_POST['latitude']);
                $page->SetParameter ('LONGITUDE', $_POST['longitude']);
                $page->SetParameter ('DESCRIPTION', $_POST['content']);
                $page->SetParameter ('TAGS', $_POST['tags']);
                $page->SetParameter ('FEATURED_FEE', $featured_project_fee);
                $page->SetParameter ('HIGHLIGHT_FEE', $highlight_project_fee);
                $page->SetParameter ('URGENT_FEE', $urgent_project_fee);
                $page->SetParameter ('FEATURED', $featured);
                $page->SetParameter ('HIGHLIGHT', $highlight);
                $page->SetParameter ('URGENT', $urgent);
                $page->SetLoop ('CUSTOMFIELDS',$custom_fields);
                $page->SetParameter ('SHOWCUSTOMFIELD', $showCustomField);
                $page->SetParameter ('CONTACT_PHONE', $contact_phone);
                $page->SetParameter ('CONTACT_EMAIL', $contact_email);
                $page->SetParameter ('CONTACT_CHAT', $contact_chat);
                $page->SetParameter('ITEM_SCREENS', $screen);
                $page->SetParameter('IMGCOUNT', $imagesCount);
                $page->SetParameter('MAXIMGLNT', $maxImgLength);
                $page->SetParameter('HEADER_TEXT', $header_text);
                $page->SetParameter('HEADER_NOTE', $header_note);
                $page->SetParameter('RESUBMIT', $resubmit);
                $page->SetParameter ('CITYFIELD', $_POST['cityfield']);
                $page->SetParameter ('DEFAULT_COUNTRY', check_user_country($config));
                $page->SetParameter ('OVERALL_FOOTER', create_footer($config,$lang,$link));
                $page->CreatePageEcho($lang,$config,$link);
                exit();
            }
            else{

                $uploaddir = "storage/products/screenshot/"; //Screenshot upload directory

                $valid_formats = array("jpg"); // Valid image formats

                $countScreen = 0;
                $image_name2 = "";
                foreach ($_FILES['item_screen']['name'] as $name => $value) {
                    $filename = stripslashes($_FILES['item_screen']['name'][$name]);

                    $size = filesize($_FILES['item_screen']['tmp_name'][$name]);
                    //Convert extension into a lower case format
                    $ext = getExtension($filename);
                    $ext = strtolower($ext);
                    //File extension check
                    if (in_array($ext, $valid_formats)) {

                        if($ext=="jpg" || $ext=="jpeg" )
                        {
                            $uploadedfile = $_FILES['item_screen']['tmp_name'][$name];
                            $src = @imagecreatefromjpeg($uploadedfile);
                        }
                        else if($ext=="png")
                        {
                            $uploadedfile = $_FILES['item_screen']['tmp_name'];
                            $src = @imagecreatefrompng($uploadedfile);
                        }
                        else
                        {
                            $src = @imagecreatefromgif($uploadedfile);
                        }


                        list($width,$height)=getimagesize($uploadedfile);

                        $newwidth=800;
                        $newheight=($height/$width)*$newwidth;
                        $tmp=imagecreatetruecolor($newwidth,$newheight);

                        $newwidth1=222;
                        $newheight1=($height/$width)*$newwidth1;
                        $tmp1=imagecreatetruecolor($newwidth1,$newheight1);

                        @imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight,$width,$height);
                        @imagecopyresampled($tmp1,$src,0,0,0,0,$newwidth1,$newheight1,$width,$height);

                        $random1 = rand(9999, 100000);
                        $random2 = rand(9999, 200000);
                        $image_name =  $first_title . '_' . $random1 . $random2 . '.' . $ext;
                        $image_name1 = 'small_' .$first_title . '_' . $random1 . $random2 . '.' . $ext;

                        //$newname = $uploaddir . $image_name;

                        $filename = $uploaddir . $image_name;
                        $filename1 = $uploaddir . $image_name1;
                        $pic_name = $image_name1;

                        @imagejpeg($tmp,$filename,100);
                        @imagejpeg($tmp1,$filename1,100);

                        @imagedestroy($src);
                        @imagedestroy($tmp);
                        @imagedestroy($tmp1);

                        //Moving file to uploads folder
                        if (!move_uploaded_file($_FILES['item_screen']['tmp_name'][$name], $filename)) {
                            $errors[]['message'] = $lang['ERROR_UPLOAD_IMG'];
                        }
                        if ($countScreen == 0)
                            $image_name2 = $image_name;
                        elseif ($countScreen >= 1)
                            $image_name2 = $image_name2.",".$image_name;

                    } else {
                        $errors[]['message'] = $lang['ONLY_JPG_ALLOW'];
                    }
                    $countScreen++;
                }

                $custom_db_fields = array();
                $custom_db_fields2 = '';
                $custom_db_types = array();
                $custom_db_types2 = '';
                $custom_db_data = array();
                $custom_db_data2 = '';

                foreach($custom_fields as $key=>$value)
                {
                    if($value['userent'])
                    {
                        $custom_db_fields[$value['id']] = $value['title'];
                        $custom_db_types[$value['id']] = $value['type'];
                        $custom_db_data[$value['id']] = str_replace(',','&#44;',$value['default']);
                    }
                }

                $custom_db_fields2 = implode(',',$custom_db_fields);
                $custom_db_types2 = implode(',',$custom_db_types);
                $custom_db_data2 = implode(',',$custom_db_data);

                $description = sanitize($_POST['content']);

                $timenow = date('Y-m-d H:i:s');

                $price  = isset($_POST['price'])? $_POST['price'] : 0;
                $negotiable  = isset($_POST['negotiable'])? $_POST['negotiable'] : 0;
                $phone  = isset($_POST['phone'])? $_POST['phone'] : "";
                $hide_phone  = isset($_POST['hide_phone'])? $_POST['hide_phone'] : 0;

                $contact_phone = isset($_POST['contact_phone']) ? 1 : 0;
                $contact_email = isset($_POST['contact_email']) ? 1 : 0;
                $contact_chat = isset($_POST['contact_chat']) ? 1 : 0;

                if($item_status == "pending"){
                    $imgs = "";
                    if($_POST['deletePrevImg'] != ""){

                        $uploaddir =  $config['site_url']."storage/products/screenshot/";
                        $deletePrevImg = explode(',',$_POST['deletePrevImg']);
                        $item_screen = explode(',',$item_screen);
                        $arr = array_diff($item_screen,$deletePrevImg);
                        foreach ($deletePrevImg as $value)
                        {
                            $value = trim($value);
                            //Delete Image From Storage ----
                            $filename1 = $uploaddir.$value;
                            if(file_exists($filename1)){
                                $filename1 = $uploaddir.$value;
                                $filename2 = $uploaddir."small_".$value;
                                unlink($filename1);
                                unlink($filename2);
                            }
                        }

                        $photo = "";
                        $count = 0;
                        foreach ($arr as $value)
                        {
                            $value = trim($value);
                            if($count == 0){
                                $photo .= $value;
                            }else{
                                $photo .= ",".$value;
                            }
                            $count++;
                        }

                        if($image_name2 != ""){
                            if($photo != "")
                                $imgs = $photo.','.$image_name2;
                            else
                                $imgs = $image_name2;
                        }else{
                            $imgs = $photo;
                        }

                    }
                    else{
                        if($image_name2 != ""){
                            if($item_screen != ""){
                                $imgs = $item_screen.','.$image_name2;
                            }else{
                                $imgs = $image_name2;
                            }
                        }else{
                            $imgs = $item_screen;
                        }
                    }

                    $sql = "UPDATE ".$config['db']['pre']."product set
                    user_id = '".$_SESSION['user']['id']."',
                    product_name = '".$_POST['title']."',
                    category = '".$_POST['catid']."',
                    sub_category = '".$_POST['subcatid']."',
                    description = '".$description."',
                    price = '".$price."',
                    negotiable = '".$negotiable."',
                    phone = '".$phone."',
                    hide_phone = '".$hide_phone."',
                    location = '".$_POST['location']."',
                    city = '".$_POST['city']."',
                    state = '".$_POST['state']."',
                    country = '".$_POST['country']."',
                    latlong = '$latlong',
                    screen_shot = '".addslashes($imgs)."',
                    tag = '".$_POST['tags']."',
                    custom_fields = '$custom_db_fields2',
                    custom_types = '$custom_db_types2',
                    custom_values = '".$custom_db_data2."',
                    created_at = '$timenow',
                    contact_phone = '$contact_phone',
                    contact_email = '$contact_email',
                    contact_chat = '$contact_chat'
                    WHERE id = '".$_GET['id']."'
                    ";
                }
                elseif($item_status == "active" or $item_status == "softreject" or $item_status == "hide")
                {
                    $imgs = "";
                    if($_POST['deletePrevImg'] != ""){

                        $uploaddir =  $config['site_url']."storage/products/screenshot/";
                        $deletePrevImg = explode(',',$_POST['deletePrevImg']);
                        $item_screen = explode(',',$item_screen);
                        $arr = array_diff($item_screen,$deletePrevImg);
                        foreach ($deletePrevImg as $value)
                        {
                            $value = trim($value);
                            //Delete Image From Storage ----
                            /*$filename1 = $uploaddir.$value;
                            if(file_exists($filename1)){
                                $filename1 = $uploaddir.$value;
                                $filename2 = $uploaddir."small_".$value;
                                unlink($filename1);
                                unlink($filename2);
                            }*/
                        }

                        $photo = "";
                        $count = 0;
                        foreach ($arr as $value)
                        {
                            $value = trim($value);
                            if($count == 0){
                                $photo .= $value;
                            }else{
                                $photo .= ",".$value;
                            }
                            $count++;
                        }

                        if($image_name2 != ""){
                            if($photo != "")
                                $imgs = $photo.','.$image_name2;
                            else
                                $imgs = $image_name2;
                        }else{
                            $imgs = $photo;
                        }

                    }
                    else{
                        if($image_name2 != ""){
                            if($item_screen != ""){
                                $imgs = $item_screen.','.$image_name2;
                            }else{
                                $imgs = $image_name2;
                            }
                        }else{
                            $imgs = $item_screen;
                        }
                    }

                    $sql = "INSERT into ".$config['db']['pre']."product_resubmit set
                    product_id = '".$item_id."',
                    user_id = '".$_SESSION['user']['id']."',
                    product_name = '".$_POST['title']."',
                    category = '".$_POST['catid']."',
                    sub_category = '".$_POST['subcatid']."',
                    description = '".$description."',
                    price = '".$price."',
                    negotiable = '".$negotiable."',
                    phone = '".$phone."',
                    hide_phone = '".$hide_phone."',
                    location = '".$_POST['location']."',
                    city = '".$_POST['city']."',
                    state = '".$_POST['state']."',
                    country = '".$_POST['country']."',
                    latlong = '$latlong',
                    screen_shot = '".$imgs."',
                    tag = '".$_POST['tags']."',
                    custom_fields = '$custom_db_fields2',
                    custom_types = '$custom_db_types2',
                    custom_values = '".$custom_db_data2."',
                    created_at = '$timenow',
                    comments = '".$_POST['comments']."',
                    contact_phone = '$contact_phone',
                    contact_email = '$contact_email',
                    contact_chat = '$contact_chat'
                    ";


                }

                $mysqli->query($sql);
                $product_id = $_GET['id'];

                $amount = 0;
                $trans_desc = "Make Ad ";
                if($featured == 1)
                {
                    $amount = $featured_project_fee;
                    $trans_desc = $trans_desc." Featured ";
                }
                if($urgent == 1)
                {
                    $amount = $amount+$urgent_project_fee;
                    $trans_desc = $trans_desc." Urgent ";
                }
                if($highlight == 1)
                {
                    $amount = $amount+$highlight_project_fee;
                    $trans_desc = $trans_desc." Highlight ";
                }

                if($amount>0){
                    if(isset($_POST['payment_id'])){
                        $query1 = "SELECT payment_title,payment_folder FROM `".$config['db']['pre']."payments` WHERE payment_id='" . $_POST['payment_id'] . "' AND payment_install='1' LIMIT 1";
                        $query_result1 = @mysqli_query ($mysqli,$query1) OR error(mysqli_error($mysqli));
                        while ($info1 = @mysqli_fetch_array($query_result1))
                        {
                            $title = $info1['payment_title'];
                            $folder = $info1['payment_folder'];
                        }

                        require_once('includes/payments/'.$folder.'/deposit.php');
                    }
                }
                else{
                    //transfer($config,$link['PENDINGADS'],$lang['AD_UPLOADED_SUCCESS'],$lang['AD_UPLOADED_SUCCESS']);
                    message($lang['SUCCESS'],$lang['ADSUCCESS'],$config,$lang,$link,'',false);
                    exit;
                }
            }
        }
    }
    else{
        error($lang['PAGENOTEXIST'], __LINE__, __FILE__, 1, $lang, $config, $link);
        exit;
    }
}
else{
    header("Location: login.php?ref=dashboard.php");
    exit();
}
?>