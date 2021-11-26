<?php

# Create by i'Ton! 20130422
if (!function_exists('genOptionDropdown')) {

    function genOptionDropdown($aoData, $type = "SYS", $force = TRUE, $optionDefault = TRUE, $strOption = NULL) {// Edit by Ton! 20131107
        $option = array();
        $text_option = '';
//        if (count($aoData) > 0) :
        if (empty($strOption)):
            if ($force == TRUE):
                $text_option = 'Please select.';
            else:
                $text_option = 'Select All.';
            endif;
        else:
            $text_option = $strOption;
        endif;

        switch ($type) :
            case "SYS" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Dom_Code] = $data->Dom_EN_Desc;
                    endforeach;
                }break;
            case "SYS_SELECT_ALL" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Dom_Code] = $data->Dom_EN_Desc;
                    endforeach;
                }break;
            case "CONTACT" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Contact_Id] = $data->First_NameTH . " " . $data->Last_NameTH;
                    endforeach;
                }break;

// Add By Akkarapol, 17/09/2013, สร้างฟังก์ชั่นใหม่ เพราะต้องการให้ค่า Index ที่ได้ออกมาเป็น UserLogin_Id แทนที่จะเป็น Contact_Id โดย Join เอาเฉพาะ Contact ที่มี UserLogin เท่านั้น เพราะเห็นว่า อย่างไรก็เอา UserLogin_Id ไปเก็บเป็น Detail อยู่แล้ว จะได้ไม่ผิดที่เอา UserLogin_Id มาใช้บ้าง Contact_Id มาใช้บ้าง เพื่อให้เป็น มาตรฐานเดียวกัน
            case "CONTACTWITHUSERLOGIN" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->UserLogin_Id] = $data->First_NameTH . " " . $data->Last_NameTH;
                    endforeach;
                }break;
// END Add By Akkarapol, 17/09/2013, สร้างฟังก์ชั่นใหม่ เพราะต้องการให้ค่า Index ที่ได้ออกมาเป็น UserLogin_Id แทนที่จะเป็น Contact_Id โดย Join เอาเฉพาะ Contact ที่มี UserLogin เท่านั้น เพราะเห็นว่า อย่างไรก็เอา UserLogin_Id ไปเก็บเป็น Detail อยู่แล้ว จะได้ไม่ผิดที่เอา UserLogin_Id มาใช้บ้าง Contact_Id มาใช้บ้าง เพื่อให้เป็น มาตรฐานเดียวกัน

            case "COMPANY" : {
//                    $option['']='Please select'; comment by kik เพราะว่า ไม่ต้องการให้ขึ้น empty เนื่องจากการ query ข้อมูลจะผิดพลาด และไม่จำเป็น(26/08/2013)
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    if (count($aoData) > 0) :
                        foreach ($aoData as $data) :
                            $option[$data->Company_Id] = $data->Company_NameEN;
                        endforeach;
                    endif;
                }break;
            case "LOC" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Location_Id] = $data->Location_Code;
                    endforeach;
                }break;
            case "CITY" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->City_Id] = $data->City_NameEN;
                    endforeach;
                }break;
            case "WH" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    if (count($aoData) > 0) :
                        foreach ($aoData as $data) :
                            $option[$data->Warehouse_Id] = $data->Warehouse_NameEN;
                        endforeach;
                    endif;
                }break;
            case "StorType" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->StorageType_Id] = $data->StorageType_NameEn;
                    endforeach;
                }break;
            case "ZONE" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Zone_Id] = $data->Zone_NameEn;
                    endforeach;
                }break;
            case "STOR" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Storage_Id] = $data->Storage_NameEn;
                    endforeach;
                }break;
            case "STORDetail" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Storage_Detail_Id] = $data->Storage_Code;
                    endforeach;
                }break;
            case "BT" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->BusinessType_Id] = $data->BusinessType_NameEN;
                    endforeach;
                }break;
            case "TITLENAME" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->TitleName_Id] = $data->TitleName_Code;
                    endforeach;
                }break;
            case "DEPARTMENT" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Department_Id] = $data->Department_NameEN;
                    endforeach;
                }break;
            case "POSITION" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Position_Id] = $data->Position_NameEN;
                    endforeach;
                }break;

// Add BY Akkarapol, 25/11/2013, เพิ่มการ Generate Dropdown List ให้กับการใช้งาน UOM
            case "UOM" : {
                    if ($optionDefault === TRUE):
                        $option['0'] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Id] = $data->name;
                    endforeach;
                }break;
// END Add BY Akkarapol, 25/11/2013, เพิ่มการ Generate Dropdown List ให้กับการใช้งาน UOM
// Add BY Akkarapol, 25/11/2013, เพิ่มการ Generate Dropdown List ให้กับการใช้งาน UOM Template
            case "UOM_TEMPLATE" : {
                    if ($optionDefault === TRUE):
                        $option['0'] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Id] = $data->public_name;
                    endforeach;
                }break;
//END Add BY Akkarapol, 25/11/2013, เพิ่มการ Generate Dropdown List ให้กับการใช้งาน UOM Template

            case "IMAGE" : {// Add by Ton! 20131119
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
//                            $option[$data->ImageItem_Id] = $data->ImageName
                        $option[$data->ImageItem_Id] = $data->ImageName . $data->ImageExt; // Edit by Ton! 20140114
                    endforeach;
                }break;
            case "PROVINCE" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Province_Id] = $data->Province_NameEN;
                    endforeach;
                }break;
            case "COUNTRY" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Country_Id] = $data->Country_NameEN;
                    endforeach;
                }break;
            case "PRODGROUP" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->ProductGroup_Id] = $data->ProductGroup_NameEN;
                    endforeach;
                }break;
            case "PRODBRAND" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->ProductBrand_Id] = $data->ProductBrand_NameEN;
                    endforeach;
                }break;
            case "MODULE" : {// Add by Ton! 20140124
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Module] = $data->Module;
                    endforeach;
                }break;
            case "SYS_ID" : {// Add by Ton! 20140129
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Dom_ID] = $data->Dom_EN_Desc;
                    endforeach;
                }break;
            case "CON_SIZE" : {
                    if ($optionDefault === TRUE):
                        $option[''] = $text_option;
                    endif;
                    foreach ($aoData as $data) :
                        $option[$data->Cont_Size_Id] = $data->Cont_Size_No . " " . $data->Cont_Size_Unit_Code;
                    endforeach;
                }break;
        endswitch;
//        endif;
        return $option;
    }

}

if (!function_exists('thai_json_encode')) {

    function thai_json_encode($data) {   // fix all thai elements
        if (is_array($data)) :
            foreach ($data as $a => $b) :
                if (is_array($data[$a])) :
                    $data[$a] = thai_json_encode($data[$a]); // Change to use recursive function
                else :
                    $data[$a] = @iconv("tis-620", "utf-8//TRANSLIT", $b);
                endif;
            endforeach;
        else :
            $data = @iconv("tis-620", "utf-8//TRANSLIT", $data);
        endif;
        return $data;
    }

}


/**
 * function for encode from 'tis620' to 'utf8'
 */
if (!function_exists('tis620_to_utf8')):

    function tis620_to_utf8($text) {
        $utf8 = "";
        for ($i = 0; $i < strlen($text); $i++) :
            $a = substr($text, $i, 1);
            $val = ord($a);

            if ($val < 0x80) :
                $utf8 .= $a;
            elseif ((0xA1 <= $val && $val < 0xDA) || (0xDF <= $val && $val <= 0xFB)):
                $unicode = 0x0E00 + $val - 0xA0;
                $utf8 .= chr(0xE0 | ($unicode >> 12));
                $utf8 .= chr(0x80 | (($unicode >> 6) & 0x3F));
                $utf8 .= chr(0x80 | ($unicode & 0x3F));
            endif;
        endfor;

        return $utf8;
    }

endif;

if (!function_exists('utf8_to_tis620')) {

    function utf8_to_tis620($data) {   // fix all thai elements
        if (is_array($data)) :
            foreach ($data as $a => $b) :
                if (is_array($data[$a])) :
//                    $data[$a] = js_thai_encode($data[$a]);
                    $data[$a] = utf8_to_tis620($data[$a]);
                else :
                    $data[$a] = @iconv("utf-8", "tis-620//TRANSLIT", $b);
                endif;
            endforeach;
        else :
            $data = @iconv("utf-8", "tis-620//TRANSLIT", $data);
        endif;
        return $data;
    }

}

function convertDate($s_date, $s_from, $s_to, $s_return_delimiter) {
    $s_return_date = '';
    $s_from = strtolower($s_from);
    $s_to = strtolower($s_to);
    $s_date = str_replace(array('\'', '-', '.', ',', ' '), '/', $s_date);
    $a_date = explode('/', $s_date);
    if (count($a_date) != 3) {
        return NULL;
    } else {
        switch ($s_from) {
            case 'eng': # dd/mm/yyyy
                $day = $a_date[0];
                $month = $a_date[1];
                $year = $a_date[2];
                break;
            case 'usa': # mm/dd/yyyy
                $month = $a_date[0];
                $day = $a_date[1];
                $year = $a_date[2];
                break;
            case 'iso': # yyyy/mm/dd
                $year = $a_date[0];
                $month = $a_date[1];
                $day = $a_date[2];
                break;
            default: # error message
                return NULL;
        }

# substitution fixes of valid alternative human input e.g. 1/12/08
        if (strlen($day) == 1) {
            $day = '0' . $day;
        }
        if (strlen($month) == 1) {
            $month = '0' . $month;
        }
        if (strlen($year) == 3) {
            $year = substr(date('Y'), 0, strlen(date('Y')) - 3) . $year;
        }
        if (strlen($year) == 2) {
            $year = substr(date('Y'), 0, strlen(date('Y')) - 2) . $year;
        }
        if (strlen($year) == 1) {
            $year = substr(date('Y'), 0, strlen(date('Y')) - 1) . $year;
        }

        switch ($s_to) {
            case 'eng': # dd/mm/yyyy
                $s_return_date = $day . $s_return_delimiter . $month . $s_return_delimiter . $year;
                break;
            case 'usa': # mm/dd/yyyy
                $s_return_date = $month . $s_return_delimiter . $day . $s_return_delimiter . $year;
                break;
            case "iso": # yyyy/mm/dd
                $s_return_date = $year . $s_return_delimiter . $month . $s_return_delimiter . $day;
                break;
            default: # error message
                return NULL;
        }

# if it's an invalid calendar date e.g. 40/02/2009 or rt/we/garbage
        if (!is_numeric($month) || !is_numeric($day) || !is_numeric($year)) {
            return NULL;
        } elseif (!checkdate($month, $day, $year)) {
            return NULL;
        }
        return $s_return_date;
    }
}

if (!function_exists('createGRNNo')) {// Add by Ton! 20130503

    function createGRNNo() {
        $GRNNo = ''; // Format-GRN No. = GRNYYYYMMDD-00001 (Running 5 digit ต่อวัน)

        $CI = &get_instance();
//        $CI->load->database();
        $CI->db->query("INSERT INTO STK_T_Running_Number VALUES ('1')");
        $No = str_pad($CI->db->insert_id(), 5, "0", STR_PAD_LEFT);

        $GRNNo = 'GRN' . date('Y') . date('m') . date('d') . '-' . $No;
        return $GRNNo;
    }

}

if (!function_exists('createDDRNo')) {// Add by Ton! 20130503

    function createDDRNo() {
        $GRNNo = ''; // Format-GRN No. = GRNYYYYMMDD-00001 (Running 5 digit ต่อวัน)

        $CI = &get_instance();
//        $CI->load->database();
        $CI->db->query("INSERT INTO STK_T_Running_Number VALUES ('1')");
        $No = str_pad($CI->db->insert_id(), 5, "0", STR_PAD_LEFT);

        $GRNNo = 'DDR' . date('Y') . date('m') . date('d') . '-' . $No;
        return $GRNNo;
    }

}

function createReLNo() {
    $ReLNo = 'REL' . date('Y') . date('m') . date('d') . '-' . date("His");
    $CI = &get_instance();
//    $CI->load->database();
    $CI->db->query("INSERT INTO STK_T_Running_Number VALUES ('1')");
    $No = str_pad($CI->db->insert_id(), 5, "0", STR_PAD_LEFT);

    $ReLNo = 'REL' . date('Y') . date('m') . date('d') . '-' . $No;
    return $ReLNo;
}

function createAdjNo() {
    $AdjNo = ''; // Format-GRN No. = GRNYYYYMMDD-00001 (Running 5 digit ต่อวัน)

    $CI = &get_instance();
//    $CI->load->database();
    $CI->db->query("INSERT INTO STK_T_Running_Number VALUES ('1')");
    $No = str_pad($CI->db->insert_id(), 5, "0", STR_PAD_LEFT);

    $AdjNo = 'ADJ' . date('Y') . date('m') . date('d') . '-' . $No;
    return $AdjNo;
}

function createDocumentNo($prefix = "") {
    if ("" == $prefix) :
        $prefix = "UNK";
    endif;

    $CI = &get_instance();
//    $CI->load->database();
    $CI->db->query("INSERT INTO STK_T_Running_Number VALUES ('1')");
    $running_no = str_pad($CI->db->insert_id(), 5, "0", STR_PAD_LEFT);
    $document_no = $prefix . date('Y') . date('m') . date('d') . '-' . $running_no;
    return $document_no;
}

// Add by Akkarapol, 28/08/2013, เพิ่มฟังก์ชั่นสำหรับ แรนด้อมตัวเลขและตัวอักษร
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) :
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    endfor;

    return $randomString;
}

// END Add by Akkarapol, 28/08/2013, เพิ่มฟังก์ชั่นสำหรับ แรนด้อมตัวเลขและตัวอักษร
#Add by KIK , 05-09-2013
#คำนวน new Balance ใหม่ เพื่อนำไปเก็บลง table STK_T_Inbound
#return ค่า new balance ที่คำนวนใหม่แล้ว
#parameter 1.inbound:receive 2.inbound:dispatch 3.inbound:adjust 4.new confrim quantity
function calculateNewBalanceInbound($Rev = 0, $DP = 0, $AD = 0, $CfQty) {
    $newBalance = 0;
    $newBalance = $Rev - $DP - $AD - $CfQty;
    return $newBalance;
}

#คำนวน ค่า allowcate || estimate คือ จำนวนที่น่าจะใช้ได้
#return ค่า allowcate ไปให้ยังฟังก์ชั่น
#parameter 1.inbound:receive 2.inbound:dispatch 3.inbound:adjust 4.inbound : pre dispate quantity

function getCalculateAllowcate($Rev = 0, $DP = 0, $AD = 0, $PD_DP) {

    $allowcate = 0;
    $allowcate = $Rev - $DP - $AD - $PD_DP;
    if ($allowcate == 0 || $allowcate < 0):
        $allowcate = 0;
    endif;
    return $allowcate;
}

#end Add by KIK , 05-09-2013
#add by kik (23-09-2013)
#Get data of languages
#receive key of languages
#return value of key

function _lang($key) {
    $CI = &get_instance();
    $data = $CI->lang->line($key);
    return $data;
}

/**
 * function for convert date
 */
if (!function_exists('convert_date_format')) {

    function convert_date_format($date) {   // fix all thai elements
        $ex = explode("/", $date);
        return $ex['2'] . "-" . $ex['1'] . "-" . $ex['0'] . " 00:00:00";
    }

}

if (!function_exists('get_client_ip')):

    function get_client_ip() {// get IP // Add by Ton! 20131101
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

endif;

if (!function_exists('check_browser')):

    function check_browser() {// check_browser // Add by Ton! 20131101
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('|MSIE ([0-9].[0-9]{1,2})|', $useragent, $matched)) :
            $browser_version = $matched[1];
            $browser = 'IE';
        elseif (preg_match('|Chrome/([0-9\.]+)|', $useragent, $matched)) :
            $browser_version = $matched[1];
            $browser = 'Chrome';
        elseif (preg_match('|Opera ([0-9].[0-9]{1,2})|', $useragent, $matched)) :
            $browser_version = $matched[1];
            $browser = 'Opera';
        elseif (preg_match('|Firefox/([0-9\.]+)|', $useragent, $matched)) :
            $browser_version = $matched[1];
            $browser = 'Firefox';
        elseif (preg_match('|Safari/([0-9\.]+)|', $useragent, $matched)) :
            $browser_version = $matched[1];
            $browser = 'Safari';
        else :
            $browser_version = 0;
            $browser = 'other';
        endif;

        return $browser;
    }

endif;


if (!function_exists('check_browser_version')):

    function check_browser_version() {// check_browser_version // Add by Ton! 20131101
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('|MSIE ([0-9].[0-9]{1,2})|', $useragent, $matched)) :
            $browser_version = $matched[1];
            $browser = 'IE';
        elseif (preg_match('|Chrome/([0-9\.]+)|', $useragent, $matched)) :
            $browser_version = $matched[1];
            $browser = 'Chrome';
        elseif (preg_match('|Opera ([0-9].[0-9]{1,2})|', $useragent, $matched)) :
            $browser_version = $matched[1];
            $browser = 'Opera';
        elseif (preg_match('|Firefox/([0-9\.]+)|', $useragent, $matched)) :
            $browser_version = $matched[1];
            $browser = 'Firefox';
        elseif (preg_match('|Safari/([0-9\.]+)|', $useragent, $matched)) :
            $browser_version = $matched[1];
            $browser = 'Safari';
        else :
            $browser_version = 0;
            $browser = 'other';
        endif;

        return $browser_version;
    }

endif;

//+++++ADD BY POR 2013-11-14 สร้าง function สำหรับแปลงวันที่ให้เป็นตัวอักษร
if (!function_exists('convertDateStirng')):

    function convertDateString($getdate, $lang = 'ENG', $type = NULL) {
        /* =====function สำหรับ return วันที่แบบเต็ม โดยรูปแบบที่จะส่งเข้ามาเป็นดังนี้
          convertDateStirng('2013-11-15','ENG')  -> กรณีต้องการให้แสดงวันที่ภาษาอังกฤษแบบเต็ม
          convertDateStirng('2013-11-15','TH')  -> กรณีต้องการให้แสดงวันที่ภาษาไทยแบบเต็ม

          +++++กรณีแบบย่อ จะส่ง $type เข้ามาด้วย คือ $type=I ->I ย่อมาจาก initials
          convertDateStirng('2013-11-15','ENG','I')  -> กรณีต้องการให้แสดงวันที่ภาษาอังกฤษแบบย่อ
          convertDateStirng('2013-11-15','TH','I')  -> กรณีต้องการให้แสดงวันที่ภาษาไทยแบบย่อ

          ซึ่งวันที่ที่ส่งเข้ามาอาจถูกคั่นด้วย / - . , หรือ ช่องว่าง

          ถ้าจะสร้าง function แปลงเป็นภาษาอื่นๆ ให้ตั้งชื่อเป็น  nameMonth+ตัวแปร $lang ที่ส่งเข้ามา (กรณีแสดงเดือนเต็ม)
          ถ้าจะสร้าง function แปลงเป็นภาษาอื่นๆ ให้ตั้งชื่อเป็น  shortMonth+ตัวแปร $lang ที่ส่งเข้ามา (กรณีแสดงเดือนย่อ)

         */

//แปลงวันที่ให้อยู่ในรูปแบบเดียวกันก่อน
        $getdate = strtolower($getdate);
        $getdate = str_replace(array('\'', '-', '.', ',', ' '), '/', $getdate);
        $chgdate = explode('/', $getdate);

        if (count($chgdate) != 3) {
            return NULL;
        } else {
//ถ้าตัวแรกมีทั้งหมด 2 ตัวแสดงว่าวันที่ส่งเข้ามาเป็น วัน เดือน ปี เข้ามา ให้แปลงอยู่ในรูป ปี เดือน วัน
            if (strlen($chgdate[0]) == 2):
                $y = $chgdate[2];
                $m = $chgdate[1];
                $d = $chgdate[0];
            else:
                $d = $chgdate[2];
                $m = $chgdate[1];
                $y = $chgdate[0];
            endif;
        }
        if ($lang == 'TH'):
            $y = $y + 543;
        endif;

        if ($type == 'I'):
            $function = 'shortMonth' . $lang;
        else:
            $function = 'nameMonth' . $lang;
        endif;

        $m = $function($m);

        return $d . " " . $m . " " . $y;
    }

endif;

if (!function_exists('nameMonthTH')):

    function nameMonthTH($getData) { // หาชื่อเต็มเดือน ภาษาไทย ตามเลขเดือนที่รับค่ามา
        if ($getData == "01" || $getData == "1") :
            $returnMonth = "มกราคม";
        elseif ($getData == "02" || $getData == "2") :
            $returnMonth = "กุมภาพันธ์";
        elseif ($getData == "03" || $getData == "3") :
            $returnMonth = "มีนาคม";
        elseif ($getData == "04" || $getData == "4") :
            $returnMonth = "เมษายน";
        elseif ($getData == "05" || $getData == "5") :
            $returnMonth = "พฤษภาคม";
        elseif ($getData == "06" || $getData == "6"):
            $returnMonth = "มิถุนายน";
        elseif ($getData == "07" || $getData == "7") :
            $returnMonth = "กรกฎาคม";
        elseif ($getData == "08" || $getData == "8"):
            $returnMonth = "สิงหาคม";
        elseif ($getData == "09" || $getData == "9") :
            $returnMonth = "กันยายน";
        elseif ($getData == "10") :
            $returnMonth = "ตุลาคม";
        elseif ($getData == "11") :
            $returnMonth = "พฤศจิกายน";
        elseif ($getData == "12") :
            $returnMonth = "ธันวาคม";
        endif;

        return $returnMonth;
    }

endif;

if (!function_exists('nameMonthENG')):

    function nameMonthENG($getData) { // หาชื่อเต็มเดือน ภาษาอังกฤษ ตามเลขเดือนที่รับค่ามา
        if ($getData == "01" || $getData == "1") {
            $returnMonth = "January";
        } elseif ($getData == "02" || $getData == "2") {
            $returnMonth = "February";
        } elseif ($getData == "03" || $getData == "3") {
            $returnMonth = "March";
        } elseif ($getData == "04" || $getData == "4") {
            $returnMonth = "April";
        } elseif ($getData == "05" || $getData == "5") {
            $returnMonth = "May";
        } elseif ($getData == "06" || $getData == "6") {
            $returnMonth = "June";
        } elseif ($getData == "07" || $getData == "7") {
            $returnMonth = "July";
        } elseif ($getData == "08" || $getData == "8") {
            $returnMonth = "August";
        } elseif ($getData == "09" || $getData == "9") {
            $returnMonth = "September";
        } elseif ($getData == "10") {
            $returnMonth = "October";
        } elseif ($getData == "11") {
            $returnMonth = "November";
        } elseif ($getData == "12") {
            $returnMonth = "December";
        }

        return $returnMonth;
    }

endif;

if (!function_exists('shortMonthTH')):

    function shortMonthTH($getData) { // หาชื่อย่อเดือน ภาษาไทย ตามเลขเดือนที่รับค่ามา
        if ($getData == "01" || $getData == "1") {
            $returnMonth = "ม.ค.";
        } elseif ($getData == "02" || $getData == "2") {
            $returnMonth = "ก.พ.";
        } elseif ($getData == "03" || $getData == "3") {
            $returnMonth = "มี.ค.";
        } elseif ($getData == "04" || $getData == "4") {
            $returnMonth = "เม.ย.";
        } elseif ($getData == "05" || $getData == "5") {
            $returnMonth = "พ.ค.";
        } elseif ($getData == "06" || $getData == "6") {
            $returnMonth = "มิ.ย.";
        } elseif ($getData == "07" || $getData == "7") {
            $returnMonth = "ก.ค.";
        } elseif ($getData == "08" || $getData == "8") {
            $returnMonth = "ส.ค.";
        } elseif ($getData == "09" || $getData == "9") {
            $returnMonth = "ก.ย.";
        } elseif ($getData == "10") {
            $returnMonth = "ต.ค.";
        } elseif ($getData == "11") {
            $returnMonth = "พ.ย.";
        } elseif ($getData == "12") {
            $returnMonth = "ธ.ค.";
        }

        return $returnMonth;
    }

endif;

if (!function_exists('shortMonthENG')):

    function shortMonthENG($getData) { // หาชื่อย่อเดือน ภาษาอังกฤษ ตามเลขเดือนที่รับค่ามา
        if ($getData == "01" || $getData == "1") {
            $returnMonth = "Jan.";
        } elseif ($getData == "02" || $getData == "2") {
            $returnMonth = "Feb.";
        } elseif ($getData == "03" || $getData == "3") {
            $returnMonth = "Mar.";
        } elseif ($getData == "04" || $getData == "4") {
            $returnMonth = "Apr.";
        } elseif ($getData == "05" || $getData == "5") {
            $returnMonth = "May";
        } elseif ($getData == "06" || $getData == "6") {
            $returnMonth = "Jun.";
        } elseif ($getData == "07" || $getData == "7") {
            $returnMonth = "Jul.";
        } elseif ($getData == "08" || $getData == "8") {
            $returnMonth = "Aug.";
        } elseif ($getData == "09" || $getData == "9") {
            $returnMonth = "Sep.";
        } elseif ($getData == "10") {
            $returnMonth = "Oct.";
        } elseif ($getData == "11") {
            $returnMonth = "Nov.";
        } elseif ($getData == "12") {
            $returnMonth = "Dec.";
        }

        return $returnMonth;
    }

endif;
//+++++END ADD
//+++++ADD BY POR 2013-11-27 function format ของตัวเลข
if (!function_exists('set_number_format')):

//function สำหรับจัด format ให้กับตัวเลข
    function set_number_format($data = 0) {
        $CI = & get_instance();
        $data = str_replace(",", "", floatval($data));
        $data = number_format($data, $CI->config->item('format_number'));

//        if($data == 0):
//            $data = number_format(0, $CI->config->item('format_number'));
//        endif;

        return $data;
    }

endif;
//+++++END ADD

if (!function_exists('re_number_format')):

    //# Add by Ken 20150625, fucntion ลบ "," สำหรับใช้ลง DB :
    function re_number_format($data) {
        if (!empty($data)):
            $data = str_replace(',', '', $data);
        else:
            $data = 0;
        endif;
        return $data;
    }

endif;
//
//
// Add By Akkarapol, 16/02/2014, เพิ่มฟังก์ชั่น object_to_array สำหรับแปลง Object ให้เป็น Array
if (!function_exists('object_to_array')):

    function object_to_array($data) {
        if (is_object($data)) :
            $data = get_object_vars($data);
        endif;

        if (is_array($data)) :
            return array_map(__FUNCTION__, $data);
        else:
            return $data;
        endif;
    }

endif;

/**
 * @created kik : 20140313
 * @param array $array
 * @param string $sortby
 * @param string $direction
 * @return array
 */
function sort_arr_by_index($array, $sortby, $direction = 'asc') {

    $sortedArr = array();
    $tmp_Array = array();

    foreach ($array as $k => $v) {
        $tmp_Array[] = strtolower($v[$sortby]);
    }

    if ($direction == 'asc') {
        asort($tmp_Array);
    } else {
        arsort($tmp_Array);
    }

    foreach ($tmp_Array as $k => $tmp) {
        $sortedArr[] = $array[$k];
    }

    return $sortedArr;
}

/*
 * -------- function arguments --------
 *   $array ........ array of objects
 *   $sortby ....... the object-key to sort by
 *   $direction ... 'asc' = ascending
 * --------
 */

function sort_arr_of_obj($array, $sortby, $direction = 'asc') {

    $sortedArr = array();
    $tmp_Array = array();

    foreach ($array as $k => $v) { //p($v->$sortby);//exit();
        #add sort date by kik
        $date_exp = explode("-", $v->$sortby); //p($date_exp);
        $date_exp_1 = explode(" ", $date_exp[2]); //p($date_exp_1);
        $date_exp_2 = explode(":", $date_exp_1[1]); //p($date_exp_2);exit();
        $date1 = mktime($date_exp_2[0], $date_exp_2[1], $date_exp_2[2], $date_exp[1], $date_exp_1[0] + 1, $date_exp[0]);
        $tmp_Array[] = strtolower($date1);

        #this not sort by date
//        $tmp_Array[] = strtolower($v->$sortby);
    }

    if ($direction == 'asc') {
        asort($tmp_Array);
    } else {
        arsort($tmp_Array);
    }

    foreach ($tmp_Array as $k => $tmp) {
        $sortedArr[] = $array[$k];
    }

    return $sortedArr;
}

/**
 * @author Akkarapol
 * @param type $array
 * @param type $position
 * @param type $insert_array
 * function for insert array to array between $position
 */
if (!function_exists('array_insert')):

    function array_insert(&$array, $position, $insert_array) {
        $first_array = array_splice($array, 0, $position);
        $array = array_merge($first_array, $insert_array, $array);
    }

endif;

if (!function_exists('create_DocumentNo')):

    function create_document_no_by_type($doc_type = "UNK") {// Add by Ton! 20140428
        $doc_no = "";

        $sql = "SELECT TOP 1 STK_T_Workflow.* FROM STK_T_Workflow WHERE STK_T_Workflow.Document_No LIKE '"
                . $doc_type . "%' ORDER BY STK_T_Workflow.Flow_Id DESC";
        $CI = &get_instance();
        $result = $CI->db->query($sql)->row();

        $CI->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());
        $current_year = date("y", strtotime($human));
        $current_month = date("m", strtotime($human));
        $current_day = date("d", strtotime($human));

        if (!empty($result)):
            $last_doc_no_year = substr($result->Document_No, strlen($doc_type), 2);
            $last_doc_no_month = substr($result->Document_No, strlen($doc_type) + 2, 2);
            $last_doc_no_day = substr($result->Document_No, strlen($doc_type) + 4, 2);
            $last_doc_no = substr($result->Document_No, strlen($doc_type) + 7, 5);

            $No = str_pad(1, 3, "0", STR_PAD_LEFT);
            if ((int) $current_year >= (int) $last_doc_no_year):
                if ((int) $current_month >= (int) $last_doc_no_month):
                    if ((int) $current_day <= (int) $last_doc_no_day):// New Day.
                        $No = str_pad((int) $last_doc_no + 1, 3, "0", STR_PAD_LEFT);
                    endif;
                endif;
            endif;

            $doc_no = $doc_type . $current_year . $current_month . $current_day . '-' . $No;
        else:
            $No = str_pad(1, 3, "0", STR_PAD_LEFT);
            $doc_no = $doc_type . $current_year . $current_month . $current_day . '-' . $No;
        endif;

        return $doc_no;
    }

endif;


/**
 * @author Akkarapol
 * function for swap data in array
 */
if (!function_exists('array_swap_assoc')):

    function array_swap_assoc($key1, $key2, $array) {
        $newArray = array();
        foreach ($array as $key => $value) {
            if ($key == $key1) {
                $newArray[$key2] = $array[$key2];
            } elseif ($key == $key2) {
                $newArray[$key1] = $array[$key1];
            } else {
                $newArray[$key] = $value;
            }
        }
        return $newArray;
    }

endif;

if (!function_exists('read_file_old_password')):
# New Password ever used. Not available.

    function read_file_old_password($UserLogin_Id, $new_password, $path, $reset_pw = FALSE) {// Add by Ton! 20140512
        $password_ever_used = FALSE;

        # get log password by user_id.
        $old_password = read_file($path . (string) $UserLogin_Id . "_password.txt");
        if (!empty($old_password)):
            $explode_old_password = explode(",", $old_password);
            $array_old_password = $explode_old_password;

            if ($reset_pw === FALSE):// == sha1(password)
                if (in_array(sha1($new_password), $array_old_password)):
                    $password_ever_used = TRUE;
                endif;
            endif;
        endif;

        return $password_ever_used;
    }

endif;

if (!function_exists('write_file_old_password')):

    function write_file_old_password($UserLogin_Id, $new_password, $path) {// Add by Ton! 20140512
        $write_file = TRUE;

        $old_password = read_file($path . (string) $UserLogin_Id . "_password.txt");
        if (!empty($old_password)):
            $old_password = $old_password . "," . sha1($new_password);
            $write_file = write_file($path . (string) $UserLogin_Id . "_password.txt", $old_password);
        else:
            $write_file = write_file($path . (string) $UserLogin_Id . "_password.txt", sha1($new_password));
        endif;

        return $write_file;
    }

endif;

if (!function_exists('random_password')):

    function random_password($numchar) {// Add by Ton! 20140512
        $characters = "a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,1,2,3,4,5,6,7,8,9,0,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
        $array = explode(",", $characters);
        shuffle($array);
        $newstring = implode($array, "");
        return substr($newstring, 3, $numchar);
    }

endif;

if (!function_exists('send_mail_reset_password')):

    function send_mail_reset_password($to, $subject, $message, $headers) {// Add by Ton! 20140512
        mail($to, $subject, $message, $headers);
    }

endif;


/**
 * @author Akkarapol
 * function for encode base64 use in URL
 */
if (!function_exists('base64_encode_for_url')):

    function base64_encode_for_url($str) {
        return str_replace(array('+', '/'), array(',', '-'), base64_encode($str));
    }

endif;


/**
 * @author Akkarapol
 * function for decode base64 use from URL
 */
if (!function_exists('base64_decode_for_url')):

    function base64_decode_for_url($str) {
        return base64_decode(str_replace(array(',', '-'), array('+', '/'), $str));
    }

endif;


/**
 * @author Akkarapol
 * function for change xml to array
 */
if (!function_exists('xml_to_array')):

    function xml_to_array($xml) {
        $array = array();
        foreach ($xml as $key => $datas):
            $get_name = $datas->getName();
            $data = get_object_vars($datas);
            if (!empty($data)) :
                $array[$get_name][] = $datas instanceof SimpleXMLElement ? xml_to_array($datas) : $data;
            else :
                if (trim($datas) === "TRUE") :
                    $response = TRUE;
                elseif (trim($datas) === "FALSE") :
                    $response = FALSE;
                else :
                    $response = trim($datas);
                endif;
                $array[$get_name] = $response;
            endif;
        endforeach;
        return $array;
    }

endif;

//function for return colspan case have show hide column : ADD BY POR 2014-07-24
function colspan_report($obj_xml, $conf, $status = 0) {
    #Load config
    $conf_build_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
    $conf_statusprice = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
    $conf_invoice = empty($conf['invoice']) ? false : @$conf['invoice'];
    $conf_container = empty($conf['container']) ? false : @$conf['container'];

    //ADD BY POR 2014-06-25 read column from xml input parameter
    $show_hide = array();
    $view['all_column'] = 0;
    $all_column = 0;
    $colspan_all = array();

    //ตรวจสอบว่ามีการ config receiving _report หรือไม่ ถ้ามีให้ตรวจสอบการ config ค่าต่างๆในตาราง
    if (!empty($obj_xml)):
        if ($status == 0): //กรณีจัด colspan แบบ fix code
            $column = $obj_xml;  //แทนค่า column ทั้งหมดที่มี
            $all_column = count($column);

            //====ตัวแปรสำหรับสร้าง colspan
            $i = 0;  //สำหรับบอกว่ามี colspan ทั้งหมดกี่กลุ่ม
            $cols = 0; //สำหรับบอกว่าให้บวก coslspan เพิ่มขึ้นหรือไม่ ถ้า 0 คือไม่ต้องบวก
            $colspan = 0;  //colspan ในแต่ละกลุ่ม
            //====จบการสร้างตัวแปรสำหรับ colspan
            //วนลูปหาคีย์และค่าที่มีทั้งหมดใน receiving_report xml
            foreach ($obj_xml as $key => $value):
                $show_hide[$key] = $value;

                if (!$conf_statusprice):  //กรณี price per unit เป็น false ค่าที่อยู่ใน xml จะเป็น false โดยปริยาย
                    if ($key == 'price_per_unit' || $key == 'unit_price' || $key == 'all_price'):
                        $value = FALSE;
                        $show_hide[$key] = $value;
                    endif;
                endif;

                if (!$conf_build_pallet): //กรณี build_pallet เป็น false ค่าที่อยู่ใน xml จะเป็น false โดยปริยาย
                    if ($key == 'pallet_code'):
                        $value = FALSE;
                        $show_hide[$key] = $value;
                    endif;
                endif;

                if (!$conf_invoice): //กรณี build_pallet เป็น false ค่าที่อยู่ใน xml จะเป็น false โดยปริยาย
                    if ($key == 'invoice'):
                        $value = FALSE;
                        $show_hide[$key] = $value;
                    endif;
                endif;

                if (!$conf_container): //กรณี build_pallet เป็น false ค่าที่อยู่ใน xml จะเป็น false โดยปริยาย
                    if ($key == 'container'):
                        $value = FALSE;
                        $show_hide[$key] = $value;
                    endif;
                endif;

                //=================ตรวจสอบว่ามีกำหนดให้ colspan = FALSE หรือไม่ ทั้งนี้การ colspan จะแบ่งเป็นกี่ส่วนนั้นต้องสัมพันธ์กับหน้า view ด้วย
                if (is_array($value)): //กรณีพบว่า ค่า value มีการกำหนดค่าเป็น array
                    $column_key = ""; //key ใน array
                    $column_val = ""; //value ใน array
                    foreach ($value as $keys => $val): //วนหาค่าที่อยู่ใน array เพิ่มเติม
                        if ($keys == 'value'):  //value = ค่าของ column ที่ต้องการให้ show hide
                            $show_hide[$key] = $val;   //กำหนดให้ column นั้น มีค่าตามที่กำหนด
                            $column_key = $keys; //แทนค่า  $column_key = value
                            $column_val = $val; //แทนค่า $column_val  = ค่าที่กำหนด
                        elseif ($keys == 'colspan'): //ถ้าพบว่ามี colspan ให้ตรวจสอบให้ colspan รวมกันหรือไม่
                            if ($column_key == 'value' && $column_val): //กรณีที่กำหนดให้แสดง column
                                if (!$val): //กรณีกำหนดให้ไม่ต้อง colspan
                                    $i+=1; //ให้สร้าง colspan ตัวใหม่
                                    $colspan_all[$i] = 1;
                                    $colspan = 0; //คืนค่าให้กับ $colspan
                                    $cols = 0; //บอกให้รู้ว่าบวก colspan จบแล้ว
                                else:
                                    if ($cols == 0): //ถ้าก่อนหน้านี้สร้าง coslpan จบแล้ว จะต้องตั้งชื่อใหม่
                                        $i+=1; //ให้สร้าง colspan ตัวใหม่
                                    else:
                                        $colspan = $colspan_all['colspan' . $i];  //ดึง colspan ล่าสุดออกมาคำนวณเพื่อบวกค่าเข้าไปอีก 1
                                    endif;
                                    $colspan_all[$i] = $colspan + 1;
                                    $cols = 1; //บอกให้รู้ว่ามีการบวก colspan ยังไม่จบ
                                endif;
                            else: //กรณีกำหนดให้ hide column
                                $all_column = $all_column - 1; //ให้ลบ column ทั้งหมดออก 1
                                break; //หากมีการกำหนดให้ hide column
                            endif;

                        endif;
                    endforeach;
                else: //============กรณีไม่ใช่ array แสดงว่ากำหนดให้มี colspan โดยปริยาย
                    if ($value):
                        if ($cols == 0): //ถ้าก่อนหน้านี้สร้าง coslpan จบแล้ว จะต้องตั้งชื่อใหม่
                            $i+=1;
                        else:
                            $colspan = $colspan_all[$i];
                        endif;

                        $colspan_all[$i] = $colspan + 1;
                        $cols = 1; //บอกให้รู้ว่ามีการบวก colspan ยังไม่จบ
                    else:
                        $all_column-=1; //ถ้าค่าเป็น false ให้ column ทั้งหมดลบ 1 เพื่อจะได้รู้ว่ามี column ที่แสดงทั้งหมดเท่าไหร่
                    endif;

                endif;
                //===================จบการหา colspan

            endforeach;
        else:  //กรณีจัด colspan แบบใหม่คือ loop แสดง total
            //KEN 2015-06-09
            $colspan = 0;
            foreach ($obj_xml as $k => $val):
                if (!$conf_statusprice):  //กรณี price per unit เป็น false ค่าที่อยู่ใน xml จะเป็น false โดยปริยาย
                    if ($k == 'price_per_unit' || $k == 'unit_price' || $k == 'all_price'):
                        $val = FALSE;
                    endif;
                endif;

                if (!$conf_build_pallet): //กรณี build_pallet เป็น false ค่าที่อยู่ใน xml จะเป็น false โดยปริยาย
                    if ($k == 'pallet_code'):
                        $val = FALSE;
                    endif;
                endif;

                if (!$conf_invoice): //กรณี build_pallet เป็น false ค่าที่อยู่ใน xml จะเป็น false โดยปริยาย
                    if ($k == 'invoice'):
                        $val = FALSE;
                    endif;
                endif;

                if (!$conf_container): //กรณี build_pallet เป็น false ค่าที่อยู่ใน xml จะเป็น false โดยปริยาย
                    if ($k == 'container'):
                        $val = FALSE;
                    endif;
                endif;

                if (is_array($val)): //จะเข้าเคสนี้ก็ต่อเมื่อ
                    if ($val['value']):  //@ show column
                        $colspan ++;
                        if ($val['colspan'] == false):
                            $all_column += $colspan;
                            $colspan_all[$k]['colspan'] = $colspan;
                            $colspan_all[$k]['show'] = "";
                            $colspan = 0;  //Reset start count span
                        endif;
                    endif;
                    $show_hide[$k] = $val['value'];
                else:
                    if ($val): //@ show column
                        $colspan ++;
                    endif;
                    $show_hide[$k] = $val;
                endif;
            endforeach;
        endif;
    else:
        //===================กรณีที่ไม่มี config เกี่ยวกับ receiving report
        if (!$conf_statusprice):  //กรณี price per unit เป็น false จะกำหนด column ที่เกี่ยวข้อง เป็น FALSE
            $show_hide['price_per_unit'] = FALSE;
            $show_hide['unit_price'] = FALSE;
            $show_hide['all_price'] = FALSE;
        endif;

        if (!$conf_build_pallet): //กรณี build_pallet เป็น false จะกำหนด column pallet_code เป็น FALSE
            $show_hide['pallet_code'] = FALSE;
        endif;

        if (!$conf_invoice): //กรณี invoice เป็น false จะกำหนด column invoice เป็น FALSE
            $show_hide['invoice'] = FALSE;
        endif;

        if (!$conf_container): //กรณี container เป็น false จะกำหนด column container เป็น FALSE
            $show_hide['container'] = FALSE;
        endif;
    endif;

    /**
     * Set show/hide column for use in css div
     */
    $set_css_for_show_column = "";

    foreach ($show_hide as $k => $SH):

        if (!$SH):

            if ($set_css_for_show_column == ''):
                $set_css_for_show_column .= " .{$k}";
            else:
                $set_css_for_show_column .= ", .{$k}";
            endif;
        endif;
    endforeach;

    if ($set_css_for_show_column != ''):

        $set_css_for_show_column .= "{display:none;}";

    endif;

    return array('all_column' => $all_column, 'colspan_all' => $colspan_all, 'show_hide' => json_encode($show_hide), 'set_css_for_show_column' => $set_css_for_show_column);
}

/**
 * Implode Array Quote
 * Extract Array for search in query where in
 *
 * @param Array $data Array of flow id for search
 * @return
 */
function implodeArrayQuote($data, $quote = "'") {
    return $quote . implode("','", $data) . $quote;
}

function formatDataForGroupDispatch($order_detail_data, &$pdf_data, $column, $order_map_by_id) {
    $group = json_decode($column['show_hide']);

    foreach ($order_detail_data as $idx => $val) :

        // Mapping Hash
        $idx_tmp = "";
        $idx_tmp .= isset($group->lot) && $group->lot == TRUE ? $val->Product_Lot : "";
        $idx_tmp .= isset($group->serial) && $group->serial == TRUE ? $val->Product_Serial : "";
        $idx_tmp .= isset($group->mfd) && $group->mfd == TRUE ? $val->Product_Mfd : "";
        $idx_tmp .= isset($group->exp) && $group->exp == TRUE ? $val->Product_Exp : "";

        $idx_hash = md5($val->Product_Id . $val->Unit_Value . $idx_tmp);

        if (array_key_exists($idx_hash, $pdf_data)) :
            $pdf_data[$idx_hash]['total'] += $val->Confirm_Qty;
            if (!array_key_exists($order_map_by_id[$val->Order_Id]['Document_No'], $pdf_data[$idx_hash]['remark'])) :
                $pdf_data[$idx_hash]['remark'][] = $order_map_by_id[$val->Order_Id]['Document_No'];
            endif;
        else :
            $pdf_data[$idx_hash]['total'] = $val->Confirm_Qty;
            $pdf_data[$idx_hash]['product_name'] = $val->Product_Name;
            $pdf_data[$idx_hash]['product_code'] = $val->Product_Code;
            $pdf_data[$idx_hash]['lot'] = $val->Product_Lot;
            $pdf_data[$idx_hash]['serial'] = $val->Product_Serial;
            $pdf_data[$idx_hash]['mfd'] = $val->Product_Mfd;
            $pdf_data[$idx_hash]['exp'] = $val->Product_Exp;
            $pdf_data[$idx_hash]['unit'] = $val->Unit_Value;
            $pdf_data[$idx_hash]['remark'][] = $order_map_by_id[$val->Order_Id]['Document_No'];
        endif;

        //$pdf_data[$idx]['Document_No'] = $order_id_map[$val->Order_Id]['Document_No'];
        //$pdf_data[$idx]['ASN'] = $order_id_map[$val->Order_Id]['ASN'];

    endforeach;

    return $pdf_data;
}

/**
 * Format Data For Group Picking
 *
 * Grouping data before send to renter in PDF format
 *
 * @param object|array $order_detail_data Query result for loop and extract data.
 * @param object $pdf_data
 */
function formatDataForGroupPicking($order_detail_data, &$pdf_data, $column, $order_map_by_id) {
    $group = json_decode($column['show_hide']);

    foreach ($order_detail_data as $idx => $val) :

        //echo "<pre>"; print_r( $val ); exit;
        // Mapping Hash
        $idx_tmp = "";
        $idx_tmp .= isset($group->lot) && $group->lot == TRUE ? $val->Product_Lot : "";
        $idx_tmp .= isset($group->serial) && $group->serial == TRUE ? $val->Product_Serial : "";
        $idx_tmp .= isset($group->mfd) && $group->mfd == TRUE ? $val->Product_Mfd : "";
        $idx_tmp .= isset($group->exp) && $group->exp == TRUE ? $val->Product_Exp : "";
        $idx_tmp .= isset($group->status) && $group->status == TRUE ? $val->Product_Status : "";
        $idx_tmp .= isset($group->sub_status) && $group->sub_status == TRUE ? $val->Product_Sub_Status : "";
	$idx_tmp .= $val->Suggest_Location;

        $idx_hash = md5($val->Product_Id . $val->Unit_Value . $idx_tmp);

        if (array_key_exists($idx_hash, $pdf_data)) :
            $pdf_data[$idx_hash]['total'] += $val->Confirm_Qty;
            $pdf_data[$idx_hash]['total_reserv'] += $val->Reserv_Qty;
            if (!array_key_exists($order_map_by_id[$val->Order_Id]['Document_No'], $pdf_data[$idx_hash]['remark'])) :
                $pdf_data[$idx_hash]['remark'][] = $order_map_by_id[$val->Order_Id]['Document_No'];
            endif;
        else :
            $pdf_data[$idx_hash]['total'] = $val->Confirm_Qty;
            $pdf_data[$idx_hash]['total_reserv'] = $val->Reserv_Qty;
            $pdf_data[$idx_hash]['product_name'] = $val->Product_Name;
            $pdf_data[$idx_hash]['product_code'] = $val->Product_Code;
            $pdf_data[$idx_hash]['status'] = $val->Product_Status;
            $pdf_data[$idx_hash]['sub_status'] = $val->Sub_Status_Value;
            $pdf_data[$idx_hash]['lot'] = $val->Product_Lot;
            $pdf_data[$idx_hash]['serial'] = $val->Product_Serial;
            $pdf_data[$idx_hash]['mfd'] = $val->Product_Mfd;
            $pdf_data[$idx_hash]['exp'] = $val->Product_Exp;
            $pdf_data[$idx_hash]['unit'] = $val->Unit_Value;
            $pdf_data[$idx_hash]['suggest'] = $val->Suggest_Location;
            $pdf_data[$idx_hash]['actual'] = $val->Actual_Location;
            $pdf_data[$idx_hash]['remark'][] = $order_map_by_id[$val->Order_Id]['Document_No'];
        endif;

        //$pdf_data[$idx]['Document_No'] = $order_id_map[$val->Order_Id]['Document_No'];
        //$pdf_data[$idx]['ASN'] = $order_id_map[$val->Order_Id]['ASN'];

    endforeach;

    return $pdf_data;
}

/**
 * function for replace array with recursive to all child
 */
if (!function_exists('my_array_replace_recursive')):

    function my_array_replace_recursive($array1, $array2) {
        foreach ($array2 as $key => $val) :
            if (is_array($val) && !empty($array1[$key])):
                $array1[$key] = my_array_replace_recursive($array1[$key], $val);
            else:
                $array1[$key] = $val;
            endif;
        endforeach;
        return $array1;
    }

endif;

function generateImageId($id, $type) {
    if (strtolower($type) == "i") {
        $prefix = "i";
    } else if (strtolower($type) == "c") {
        $prefix = "c";
    } else if (strtolower($type) == "p") {
        $prefix = "p";
    }
    $image_id = str_pad($id, 6, 0, STR_PAD_LEFT);
    return array("FULL" => $prefix . $image_id, "PATH" => array(substr($image_id, 0, 1), substr($image_id, 3, 1)));
}

function getImagePath($image_id) {
    $path = array();
    $path[] = substr($image_id, 1, 1);
    $path[] = substr($image_id, 4, 1);
    $filename = $image_id . ".png";
    return array("FULL" => $filename, "PATH" => $path);
}

if (!function_exists('change_letter_number')):

    function change_letter_number($letter) {
        $letter = strtolower($letter);
        $characters = "a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z";
        $array = explode(",", $characters);
        return array_search($letter, $array);
    }

endif;


/**
 * Checks date if matches given format and validity of the date.
 * Examples:
 * <code>
 * is_date('22.22.2222', 'mm.dd.yyyy'); // returns false
 * is_date('11/30/2008', 'mm/dd/yyyy'); // returns true
 * is_date('30-01-2008', 'dd-mm-yyyy'); // returns true
 * is_date('2008 01 30', 'yyyy mm dd'); // returns true
 * </code>
 * @param string $value the variable being evaluated.
 * @param string $format Format of the date. Any combination of <i>mm<i>, <i>dd<i>, <i>yyyy<i>
 * with single character separator between.
 */
function is_valid_date($value, $format = 'mm/dd/yyyy') {
    if (strlen($value) >= 6 && ((strlen($format) == 8) || strlen($format) == 10)) {
        // find separator. Remove all other characters from $format
        $separator_only = date_separator($format);
        $separator = $separator_only[0]; // separator is first character

        if ($separator && strlen($separator_only) == 2) {
            // make regex
            $regexp = str_replace('mm', '(0?[1-9]|1[0-2])', $format);
            $regexp = str_replace('dd', '(0?[1-9]|[1-2][0-9]|3[0-1])', $regexp);
            if ((strlen($format) == 8)):
                $regexp = str_replace('yy', '[0-9][0-9]', $regexp);
            else:
                $regexp = str_replace('yyyy', '(19|20)?[0-9][0-9]', $regexp);
            endif;

            if ($separator == '/'):
                $regexp = str_replace($separator, "\\" . $separator, $regexp);
            endif;

            if ($regexp != $value && preg_match('/' . $regexp . '\z/', $value)) {
                // check date
                $arr = explode($separator, $value);
                $arr_format = array_flip(explode($separator, $format));

                $day = $arr[$arr_format['dd']];
                $month = $arr[$arr_format['mm']];
                if ((strlen($format) == 8)):
                    $year = $arr[$arr_format['yy']];
                else:
                    $year = $arr[$arr_format['yyyy']];
                endif;

                if (strlen($year) == 2):
                    $year = ($year > date("y") + 20 ? ("25" . $year) - 543 : ("20" . $year));
                endif;

                if (@checkdate($month, $day, $year)):
                    return true;
                endif;
            }
        }
    }

    return false;
}

/**
 * Function for change date style
 * @param type $value
 * @param type $from_format
 * @param type $to_format
 * @return boolean
 */
if (!function_exists('change_date_style')):
    function change_date_style($value, $from_format = NULL, $to_format = "mm/dd/yyyy") {
        if(!empty($value) && !empty($from_format) && is_valid_date($value, $from_format)):
            $from_separator = date_separator($from_format);
            $from_separator = $from_separator[0];
            $from_explode_date = explode($from_separator, $value);
            if(count($from_explode_date) != 3)
                return false;
            $from_format = str_replace('dd', 'd', str_replace('mm', 'm', str_replace('yy', 'y', str_replace('yyyy', 'y', $from_format))));
            $from_explode_format = explode($from_separator, $from_format);
            $index_format = array_flip($from_explode_format);
            $day = $from_explode_date[$index_format['d']];
            $month = $from_explode_date[$index_format['m']];
            $year = $from_explode_date[$index_format['y']];
            if ((strlen($year) == 2)):
                $year = ($year > date("y") + 20 ? ("25" . $year) - 543 : ("20" . $year));
            endif;
            $tmp = mktime(0, 0, 0, $month,   $day,   $year);
            $to_format = str_replace('dd', 'd', str_replace('mm', 'm', str_replace('yy', 'y', str_replace('yyyy', 'Y', $to_format))));
            $return_date = date($to_format,$tmp);
            return $return_date;
        endif;
        return false;
    }
endif;

if (!function_exists('convert_date_format')):
    function convert_date_format($value, $from_format = NULL, $to_format = NULL) {

        if(!empty($value)):
            return $value;
        else:
            return false;
        endif;

    }

endif;

/**
 * Function for find separator. Remove all other characters from $format
 * @param type $string
 * @return type
 */
function date_separator($string){
    return str_replace(array('m', 'd', 'y'), '', $string);
}
