<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of im_ex
 *
 * @author P'Zex
 */
class im_ex extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('excel');
        
        $this->load->model('im_ex_model', 'imex');
    }

    function index() {
        $this->load->view('imex');
    }

    function upload() {
        //foreach ($_FILES["xfile"]["error"] as $key => $error) {
        //    if ($error == UPLOAD_ERR_OK) {
        $name = $_FILES["xfile"]["name"];
        move_uploaded_file($_FILES["xfile"]["tmp_name"], $_SERVER["DOCUMENT_ROOT"] . "/dynamic/wms/v2/WMSPlus/uploads/files/" . $_FILES['xfile']['name']);
        //   }
        // }
        echo "<h2>Successfully Uploaded </h2>";
        $this->import($name);
    }

    function view() {
        $view['lstH'] = $this->imex->getHeaderPicking();
        $this->load->view("showimport", $view);
    }

    function import($name) {
        //$inputFile="C:/AppServ/www/wms/uploads/files/interorgexcel.xls";
        $inputFile = $_SERVER["DOCUMENT_ROOT"] . "/dynamic/wms/v2/WMSPlus/uploads/files/$name";
        //header('Content-Type: text/html; charset=utf-8');
        $objPHPExcel = PHPExcel_IOFactory::load($inputFile);
        //$objReader = PHPExcel_IOFactory::createReader('Excel2007');
        //$objReader->setReadDataOnly(true);
        //echo "Import";
        //exit();
        //$objPHPExcel = $objReader->load($inputFile);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $num_page = 0;
        $r = 0; // ตัวแปรนับจำนวน Row
        $r_detail = 0; // ตัวแปรเริ่มต้นของ Detail
        $have_footer = false; // ตัวแปรสำหรับเช็ค ว่าเจอ Footer หรือยัง
        $have_header = false; // ตัวแปรสำหรับเช็ค ว่าเจอ Header หรือยัง
        $dHeader = array(); // ตัวแปรสำหรับเก็บ ส่วนของ Header 
        $dH = array();
        $rRec = array(); // ตัวแปรสำหรับเก็บ ส่วนของ Detail
        $rDetail = array();
        $row_detail = 0; // ตัวแปรสำหรับ เก็บช่องของ Array ข้อมูล Detail
        $c = 0; // ตัวแปร สำหรับ ช่อง Array ของ Detail
        //$jobno="";
        $jobno = $this->imex->LastId();
        foreach ($objWorksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // This loops all cells,
            // even if it is not set.
            // By default, only cells
            // that are set will be
            // iterated.
            //---- Find Header ----
            $value = $objWorksheet->getCellByColumnAndRow(0, $r)->getValue();
            $pos = strpos($value, "แผ่นที่");
            if ($pos !== false) {
                $num_page++;
                $index = $row->getRowIndex();
                $r_printdate = $index + 1;
                $r_renterid = $index + 2;
                $r_fwh_docno = $index + 9;
                $r_twh_docdate = $index + 10;
                $r_remark = $index + 11;
                $r_detail = $index + 16;

                $vprint_date = $objWorksheet->getCellByColumnAndRow(0, $r_printdate)->getValue();
                $vprint_date = explode(":", $vprint_date);
                $vrenterid = $objWorksheet->getCellByColumnAndRow(0, $r_renterid)->getValue();
                $vfwh = $objWorksheet->getCellByColumnAndRow(0, $r_fwh_docno)->getValue();
                $vfwh = explode(':', $vfwh);
                $vdocno = $objWorksheet->getCellByColumnAndRow(2, $r_fwh_docno)->getValue();
                $vdocno = explode(':', $vdocno);
                $vtwh = $objWorksheet->getCellByColumnAndRow(0, $r_twh_docdate)->getValue();
                $vtwh = explode(':', $vtwh);
                $vdocdate = $objWorksheet->getCellByColumnAndRow(2, $r_twh_docdate)->getValue();
                $vdocdate = explode(':', $vdocdate);
                $vremark = $objWorksheet->getCellByColumnAndRow(0, $r_remark)->getValue();
                $vremark = explode(':', $vremark);


                $dHeader["IMP_JobNo"] = $jobno;
                $dHeader["IMP_PrintDate"] = $vprint_date[1];
                $dHeader["IMP_RenterID"] = $vrenterid;
                $dHeader["IMP_FromWH"] = $vfwh[1];
                $dHeader["IMP_ToWH"] = $vtwh[1];
                $dHeader["IMP_HRemark"] = $vremark[1];
                $dHeader["IMP_DocNo"] = $vdocno[1];
                $dHeader["IMP_DocDate"] = $vdocdate[1];


                $have_header = true;
                //echo $vprint_date." - ".$vrenterid." - ".$vfwh." - ".$vdocno." - ".$vtwh.
                //        " - ".$vdocdate." - ".$vremark."<br/>-----------------------<br/>";
            }
            //--- End Find Header ----
            // --- Find Footer -----
            if ($have_header == true) {
                $value = $objWorksheet->getCellByColumnAndRow(3, $r)->getValue();
                $pos2 = strpos($value, "รวม");
                if ($pos2 !== false) {
                    $have_footer = true;
                    //echo "Foooter<br/>";
                    $index = $row->getRowIndex();
                    $r_remark = $index;
                    $r_ucreate = $index + 2;
                    $r_udatecreate = $index + 4;
                    $r_expos_user = $index + 7;
                    $r_expos_date = $index + 9;

                    $f_remark = $objWorksheet->getCellByColumnAndRow(0, $r_remark)->getValue();
                    $f_fremark = explode(':', $f_remark);
                    $f_ucreate = $objWorksheet->getCellByColumnAndRow(0, $r_ucreate)->getValue();
                    $f_create_user = explode(' ', $f_ucreate);
                    $f_utransfer = $objWorksheet->getCellByColumnAndRow(1, $r_ucreate)->getValue();
                    $f_ship_root = explode(' ', $f_utransfer);
                    $f_ucreate_date = $objWorksheet->getCellByColumnAndRow(0, $r_udatecreate)->getValue();
                    $f_create_date = explode(' ', $f_ucreate_date);
                    $f_utrans_date = $objWorksheet->getCellByColumnAndRow(0, $r_udatecreate)->getValue();
                    $f_ship_date = explode(' ', $f_utrans_date);
                    $f_expos_user = $objWorksheet->getCellByColumnAndRow(0, $r_expos_user)->getValue();
                    $f_pick_dept = explode(' ', $f_expos_user);
                    $f_receive_user = $objWorksheet->getCellByColumnAndRow(1, $r_expos_user)->getValue();
                    $f_rec_root = explode(' ', $f_receive_user);
                    $f_expos_date = $objWorksheet->getCellByColumnAndRow(0, $r_expos_date)->getValue();
                    $f_pick_date = explode('วันที่', $f_expos_date);
                    $f_receive_date = $objWorksheet->getCellByColumnAndRow(1, $r_expos_date)->getValue();
                    $f_rec_date = explode('วันที่', $f_receive_date);

                    $dHeader["IMP_CreateUser"] = $f_create_user[1];
                    $d = explode('/', $f_create_date[1]);
                    $dHeader["IMP_CreateDate"] = $d[2] . "-" . $d[1] . "-" . $d[0];
                    $dHeader["IMP_ShipRoot"] = $f_ship_root[1];
                    $d = explode('/', $f_ship_date[1]);
                    $dHeader["IMP_ShipDate"] = $d[2] . "-" . $d[1] . "-" . $d[0];
                    $dHeader["IMP_PickDept"] = $f_pick_dept[1];
                    $d = explode('/', $f_pick_date[1]);
                    if (strpos($d[0], ".") === false)
                        $dHeader["IMP_PickDate"] = $d[2] . "-" . $d[1] . "-" . $d[0];
                    else
                        $dHeader["IMP_PickDate"] = "";
                    $dHeader["IMP_FRemark"] = $f_fremark[1];
                    $dHeader["IMP_RecRoot"] = $f_rec_root[1];
                    $d = explode('/', $f_rec_date[1]);
                    if (strpos($d[0], ".") === false)
                        $dHeader["IMP_RecDate"] = $d[2] . "-" . $d[1] . "-" . $d[0];
                    else
                        $dHeader["IMP_RecDate"] = "";

//                echo $f_remark."<br/>".$f_ucreate."[".$f_ucreate_date."]"."-----------".
//                        $f_utransfer."[".$f_utrans_date."]"."<br/>".
//                     $f_expos_user."[$f_expos_date]"."-----".$f_receive_user."[$f_receive_date]<br/>";

                    $dH[$row_detail] = $dHeader;
                    $rDetail[$row_detail] = $rRec;

                    unset($rRec);
                    $row_detail++;
                    $jobno++;
                    $c = 0;
                    $have_header = false;
                    //echo $jobno."<br/>";
                    //$this->imex->im($jobno);
                    //$this->imex->import($dH,$rDetail);
                }
            }
            //---- End Find Footer ----
            //------ Find Detail -----
            if ($have_header == true) {
                if ($r >= $r_detail) {
                    $r_detail = $r;
                    $_data = array();
                    $no = $objWorksheet->getCellByColumnAndRow(0, $r_detail)->getValue();
                    if ($no != "") {
                        $item = $objWorksheet->getCellByColumnAndRow(1, $r_detail)->getValue();
                        $item = explode(' ', $item);
                        $fsubinv = $objWorksheet->getCellByColumnAndRow(2, $r_detail)->getValue();
                        $tsubinv = $objWorksheet->getCellByColumnAndRow(3, $r_detail)->getValue();
                        $prdqty = $objWorksheet->getCellByColumnAndRow(4, $r_detail)->getValue();
                        $unit = $objWorksheet->getCellByColumnAndRow(5, $r_detail)->getValue();
                        $_data['IMP_JobNo'] = $jobno;
                        $_data["IMP_DSeq"] = $no;
                        $_data["IMP_PRDBarcode"] = $item[0];
                        $_data["IMP_PRDDesc"] = $item[1];
                        $_data["IMP_FromSubInv"] = $fsubinv;
                        $_data["IMP_ToSubInv"] = $tsubinv;
                        $_data["IMP_PRDQTY"] = $prdqty;
                        $_data["IMP_PRDUnit"] = $unit;
                        $rRec[$c] = $_data;
                        $c++;
                        //print_r($rRec);
                        //echo $no."---".$itemcode."<br/>";
                    }
                }
                //$have_footer=false;
            }
            //--- End Find Detail ---
            $r++;
        }
        $this->imex->import($dH, $rDetail);
        $view['lstH'] = $this->imex->getHeaderPicking();
        $this->load->view("showimport", $view);
    }

}

?>
