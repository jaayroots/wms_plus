<?php
$dirname = basename(__DIR__);
$pathinfo = pathinfo(realpath(__FILE__));
$status = @shell_exec('svnversion ' . str_replace("application/views/{$dirname}/{$pathinfo['basename']}", '', realpath(__FILE__)));
if (preg_match('/\d+/', $status, $match)) {
    $revision = $match[0];
}
date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$have_update = FALSE;
$margin_top = ($have_update ? 5 : 5);
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, $margin_top, 10, 5, 5);

$first_page = 0;
foreach ($list_pallet as $key => $list){
    if($first_page != 0){
        $mpdf->AddPage();
        }
        $sum_all_qty = set_number_format($list['sum_all_qty']);
        $header = '<br>
        <table border="0" width="100%" style="vertical-align: top; font-size: 16px; ">
        <tr>
            <td height="30" width="100%" align="center" style="font-size: 35px;font-weight:bold;"> PALLET TAG</td>
        </tr>
    </table> <br>';
       
     $footer =" <table width='100%' align='right'>
            <tr> 
              <th></th> 
              <th  align='right' style='font-family: arial; font-size: 55px; '>".$list['pallet_info']->Pallet_Code."&nbsp;&nbsp; &nbsp;</th>
            </tr>
             <tr> 
               <th></th> 
                <th align='right'>
                 <barcode code='{$list['pallet_info']->Pallet_Code}' type='C128A' size='2' height='0.8' /></th>
            </tr>
            </table>";

        $footer.= '<table width="100%" style="vertical-align: bottom; font-family: tahoma; font-size: 9pt; color: #666666;">
            <tr>
                <td align="left">
                    Print By : ' . tis620_to_utf8($list['printBy']) . '  ,
                    Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '
                </td>
                   <td align="right">Pallet Name : ' . $list['pallet_info']->Pallet_Name . '  |  WMS Revision :' . $list['revision']. ' , {PAGENO}/{nb}</td>
            </tr>
        </table>';

        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLHeader($header, 'E');
        $mpdf->SetHTMLFooter($footer);
        $mpdf->SetHTMLFooter($footer, 'E');
        
        $page = $header;
        $page .= "<table  width='100%'  border='0' cellspacing='1' cellpadding='1' >";
        $body_table = '';
        foreach ($list['datas'] as $key_data => $data):
            $data['Product_NameEN'] = iconv("TIS-620", "UTF-8", $data['Product_NameEN']);
            $data['Product_Lot'] = iconv("TIS-620", "UTF-8", $data['Product_Lot']);
            $data['Unit_Value'] = iconv("TIS-620", "UTF-8", $data['Unit_Value']);
    
            if( $data['Product_Lot']  == " " ||  $data['Product_Lot'] == "" ){
                $data['Product_Lot'] ="EMPTY";
                $style ="color:LightGrey;";
            }else{
                $data['Product_Lot'] = $data['Product_Lot'];
                $style ="color:back;";
            }
            // p( $data['Product_Lot']); exit;
            $lot =strlen($data['Product_Lot']) ;
            if( $lot > 15 ){
               $fount = "65px;";
            }else{
                $fount = "85px;";
            }
            $name = strlen($data['Product_NameEN']);
            if( $name > 37 ){
                $fountsize = "30px;";
             }else{
                 $fountsize = "41px;";
             }


             $doc =strlen($list['doc_refer_ext']) ;
            //  p($doc); exit;
            if( $doc > 15 ){
               $fountdoc = "30px;";
            }else{
                $fountdoc = "41px;";
            }
            // $test ='DR#FG31/2020_ADJ05.02.21';
            $running_number = $key_data + 1;
            $body_table= "
            <tr>
                <th  height='41px' align='left' style=' font-size: 37px; font-family: arial;  font-weight: bold;'> DATE </th></th>
                <th  colspan='2' height='41px' align='left'  style=' font-size: 40px; font-family: arial;'>" . $list['pallet_info']->Create_Date_Reprint . "   &nbsp;&nbsp; DOC :&nbsp;<span style='font-size: ".$fountdoc."' > ". $list['doc_refer_ext'] ."</span></th>
            </tr>
            <tr>
                <th  height='85px' align='left'  style=' font-family: arial; font-size: 37px; font-weight: bold; '> LOCATION </th></th>
                <td colspan='2' height='85px'  bgcolor='LightGrey' style=' font-family: arial; font-size: 85px; font-weight: bold; border:#FFF 1px solid;'>".$list['location_code']."</td>
            </tr> 
            <tr>
                <th  height='85px' align='left' style=' font-family: arial; font-size: 35px; font-weight: bold;'> ITEM CODE </th></th>
                <td colspan='2' height='85px' bgcolor='LightGrey'  style='font-family: arial; font-size: 85px; font-weight: bold; border:#FFF 1px solid;'>".$data['Product_Code']."</td>
            </tr>
            <tr >
                <th height='85px' align='left'  style=' font-family: arial; font-size: 37px; font-weight: bold;'> NAME </th></th>
                <td colspan='2' height='85px' style=' font-family: arial; font-size: ". $fountsize." border:#FFF 1px solid;'>". $data['Product_NameEN']."</td>
            </tr>
            <tr>
                <th  height='85px' align='left'  style=' font-family: arial; font-size: 37px; font-weight: bold;'> LOT NO. </th></th>
                <td colspan='2' height='120px'  bgcolor='LightGrey'  color='LightGrey' style='".$style."font-size: ".$fount." font-weight: bold; border:#FFF 1px solid;'>".$data['Product_Lot']."</td>
            </tr>
            <tr>
                <td   width='20px'  align='left' style=' font-family: arial; font-size: 37px; font-weight: bold;'> QTY </td>
                <td   align='left'  width='20%'   style=' font-weight: bold; font-family: arial; font-size: 70px; '>" .$data['Confirm_Qty']. " </td>
                <td   width='620' align='left' style=' font-family: arial; font-size: 53px; font-weight: bold;'>&nbsp;".tis620_to_utf8($list['datas'][0]['Unit_Value'])." </td>
                </tr>
                ";
                
        endforeach; 
     
           


        $page .= $body_table;

        $page .= "</table>";
        $this->load->helper('file');
        $stylesheet = read_file('../libraries/pdf/style.css');
        $mpdf->WriteHTML($stylesheet, 1);
        
      
        $mpdf->WriteHTML($page);
        $first_page++;
    }

      



if ($file_name == "") :
    $filename = "PalletTag({$list['pallet_info']->Pallet_Code})-" . date('Ymd') . '-' . date('His') . '.pdf';
else :
    $filename = $file_name;
endif;

$strSaveName = getcwd() . str_replace("./", "/", $settings['uploads']['upload_path']) . $filename;
$srrSaveName = str_replace("\\", "/", $strSaveName);

$tmp = $mpdf->Output($strSaveName, 'I');
exit;

// BEGIN CUPS Auto Print
if ($server_print) {
    $printer_name = $this->config->item("printer_outbound");
    $printer = (!empty($printer_name) ? " -P " . $printer_name : "");
    shell_exec('lpr "' . $strSaveName . '"' . $printer);
} else {
    if (is_null($print_me)) :
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $filename . "\"");
        readfile($strSaveName);
    endif;
}
// END CUPS
