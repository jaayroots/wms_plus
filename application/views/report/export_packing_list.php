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


//Add Page
$first_page = 0;
foreach ($list_pallet as $key => $list){
    // p($list['Product_Code']);exit;
    if($first_page != 0){
        $mpdf->AddPage();
        }
        $sum_all_qty = set_number_format($list['sum_all_qty']);
        $ex = explode("-",$list['location_code']);
   
        $header = '
        <table border="0" width="100%" style="vertical-align: top; font-size: 16px; color: #000000;">
            <tr>
                <td height="30" width="20%" align="center" style="font-size: 7.5em; font-family: tahoma; border: #000 1px solid; font-weight:bold;">' . $ex[0] . '</td>
                <td height="30" width="80%" align="center" style="font-size: 2.5em; font-family: tahoma; font-weight:bold; text-align: center;" >'.$list['product_name'].'<br/><span style="font-size: 2.2em;">'.$list['product_code'].'</span></td>
            </tr>
        </table>';

        $footer = "<table align='left' border='0' width='100%' style='border-bottom:1px solid gray; font-size: 1.5em;font-weight:bold;'>
                <tr>
                    <td width='35%'  align='center' style='font-family: tahoma; font-size: 3em; font-weight: bold; '>".$list['location_code']."</td>
        	    <td width='35%' align='center' style='font-family: tahoma; font-size: 3em;'>".$list['pallet_info']->Pallet_Code."</td>
        	    <td width='20%'  align='left' style='font-family: tahoma; font-size: 1.1em;'>DOC. Ref : ". $list['doc_refer_ext'] ."</td>
        	</tr>
        	<tr>
                    <td align='center' style='font-family: tahoma; font-size: 2.5em;'>" . $list['datas'][0]['product_class'] . "</td>
                    <td align='center' style='font-family: tahoma; font-size: 1em;'><barcode code='{$list['pallet_info']->Pallet_Code}' type='C128A' size='2' height='1' /></td>
                    <td align='left' style='font-family: tahoma; font-size: 1.1em;'>RCV. Date : " . $list['pallet_info']->Create_Date_Reprint . "</td>
                </tr>
        </table>";

        $footer .= '<table width="100%" style="vertical-align: bottom; font-family: tahoma; font-size: 9pt; color: #666666;">
            <tr>
                <td align="left">
                    Print By : ' . tis620_to_utf8($list['printBy']) . '  ,
                    Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '
                </td>
                <td align="right">Pallet Name : ' . $list['pallet_info']->Pallet_Name . '  |  WMS Revision :' . $list['revision']. ' , {PAGENO}/{nb}</td>
            </tr>
        </table>';

        // $mpdf->SetHTMLHeader($header);
        // $mpdf->SetHTMLHeader($header, 'E');
        $mpdf->SetHTMLFooter($footer);
        $mpdf->SetHTMLFooter($footer, 'E');
        
        $page = $header;
        
        $page .= "<table border='0' width='100%' cellspacing='0' cellpadding='0' style='margin-top: 20px;'>";
        

        $head_table = "
            <tr>
                <th width='75%' style='font-family: tahoma; font-size: 3em; border-left: #000 1px solid; border-top: #000 1px solid;'>" . _lang('lot') . "</th>
                <th width='25%' style='font-family: tahoma; font-size: 3em; border-left: #000 1px solid; border-top: #000 1px solid; border-right: #000 1px solid;'>" . _lang('qty') . "</th>
            </tr>
        ";
        
        $page .= $head_table;
        $body_table = '';
        foreach ($list['datas'] as $key_data => $data):

            $data['Product_NameEN'] = iconv("TIS-620", "UTF-8", $data['Product_NameEN']);
            $data['Product_Lot'] = iconv("TIS-620", "UTF-8", $data['Product_Lot']);
            $data['Product_Serial'] = iconv("TIS-620", "UTF-8", $data['Product_Serial']);
            $data['Invoice_No'] = iconv("TIS-620", "UTF-8", $data['Invoice_No']);
            $data['Unit_Value'] = iconv("TIS-620", "UTF-8", $data['Unit_Value']);
            $running_number = $key_data + 1;
            $body_table .= "
                <tr>
                    <td align='left' style='font-family: tahoma; font-size: 4em; padding: 5px 5px 5px 55px; font-weight: bold; border-left: #000 1px solid; border-top: #000 1px solid; border-bottom: #000 1px solid;'>
                        {$data['Product_Lot']}
                    </td>
                    <td align='right' style='font-family: tahoma; font-size: 3.5em; padding: 5px 55px 5px 5px; font-weight: bold; border: #000 1px solid;'>
                        {$data['Confirm_Qty']}
                    </td>
                </tr>";
                
        endforeach; 
     
            $body_table .= "
                <tr>
                    <td align='left' style='border-left: 0;'></td>
                    <td align='center' style='font-family: tahoma; font-size: 3.5em; font-weight: bold; padding: 5px 5px 5px 5px;'>".tis620_to_utf8($list['datas'][0]['Unit_Value'])."</td>
                </tr>";


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
