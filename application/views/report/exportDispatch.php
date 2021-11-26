<?php
date_default_timezone_set('Asia/Bangkok') ;
$settings = native_session::retrieve();
$mpdf=new mPDF('th','A4-L','11','',5,5,20,25,5,5);
$mpdf->ignore_invalid_utf8 = true;

//START BY POR 2013-10-10 สร้าง header และ detail ในส่วนที่ต้องการแสดง PDF
$header = '
<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
    <tr>
        <td height="30" width="200">
            <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
        </td>
        <td style="text-align: center;font-weight: bold;">'.iconv("TIS-620","UTF-8",'Dispatch Report').' '.date("M Y").'</td>
   <td width="200"> </td>
</tr>
</table>
';

$footer = '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
<tr>
<td width="50%" align="left">
        Print By : ' . iconv("TIS-620", "UTF-8", $printBy) . '  ,
        Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
        <td align="right">{PAGENO}/{nb}</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header,'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer,'E');

$head_table='
<style>
    .tborder{
            /*border:1px solid #333333;*/
            border-top:1px solid #333333;
            border-right:1px solid #333333;
     
    }

    .tborder td,
    .tborder th{
            /*border-top:1px solid #333333;*/
            border-left:1px solid #333333;
            border-right:1px solid #333333;
            border-bottom:1px solid #333333;
            font-size:10px;
    }
</style>

<table width="100%" cellspacing="0" style="border:1px solid #666666;font-size:10px;font-family: arial;" class="tborder">
    <thead>
        <tr style="border:1px solid #333333;" bgcolor="#B0C4DE">
            <th width="7%" height="35">'._lang('document_no').'</th>
            <th width="9%">'._lang('document_ext').'</th>
            <th width="9%">'._lang('doc_refer_int').'</th>
            <th width="10%">'._lang('product_code').'</th>
            <th width="20%">'._lang('product_name').'</th>
            <th width="7%">'._lang('lot').'</th>
            <th width="7%">'._lang('Mfd_Date').'</th>
            <th width="9%">'._lang('serial').'</th>
            <th width="5%">'._lang('qty').'</th>
            <th width="5%">'._lang('unit').'</th>
            <th width="10%">'._lang('from').'</th>
            <th width="10%">'._lang('to').'</th>
            <th width="10%">'._lang('Description').'</th>';

            if($this->config->item('build_pallet')):
            $head_table.='<th width="10%">'._lang('pallet_code').'</th>';
            endif;

            $head_table.='<th width="10%">'._lang('remark').'</th>
        </tr>
    </thead>
    <tbody>';

    $page ='';
    //$page.=$header_page;
    $page.=$head_table;

$total = 0;
    if(!empty($datas)){
            $noPage=1;

if($this->config->item('build_pallet')):
   $colspan = 15;
   $colspan2 = 7;
else:
   $colspan = 12;
   $colspan2 = 4;
endif;

            foreach($datas as $keydate => $values){
                // p($keydate); exit;
                    $iTemp = 1;

                    foreach($values as $keyRow => $data){

                        foreach($data as $key=>$value){

                                $total += $value['Dispatch_Qty'];
                                if( $iTemp == 1){
                                        $page .= '
                                        <tr bgcolor="#F0F8FF ">
                                                <td colspan="'.$colspan.'" align="left">'.$keydate.'</td>
                                        </tr>';
                                }
                                $page.='
                                    <tr>
                                        <td valign="top" align="center">'.$value['Document_No'].'</td>
                                        <td valign="top" align="center">'.$value['Doc_Refer_Ext'].'</td>
                                        <td valign="top" align="center">'.$value['Doc_Refer_Int'].'</td>
                                        <td valign="top" align="center">'.$value['Product_Code'].'</td>
                                        <td valign="top" align="left">'.iconv("TIS-620","UTF-8",$value['Product_Name']).'</td>
                                        <td valign="top" align="center">'.$value['Product_Lot'].'</td>
                                        <td valign="top" align="center">'.date("d/m/Y", strtotime($value['Product_Mfd'])).'</td>
                                        <td valign="top" align="center">'.$value['Product_Serial'].'</td>
                                        <td valign="top" style="text-align:right;">'.set_number_format($value['Dispatch_Qty']).'</td>
                                        <td valign="top" align="center">'.$value['Unit_Value'].'</td>
                                        <td valign="top" align="left">'.$value['From_sup'].'</td>
                                        <td valign="top" align="left">'.$value['Destination_Code'].'</td>
                                        <td valign="top" align="center">'.iconv("TIS-620","UTF-8",$value['Destination_Name']).'</td>';

                                        if($this->config->item('build_pallet')):
                                            $page.='<td valign="top" align="left">'.$value['Pallet_Code'].'</td>';
                                        endif;

                                        $page.='<td valign="top" align="left">'.iconv("TIS-620","UTF-8",$value['Remark']).'</td>
                                    </tr>';
                                $noPage++;
                                $iTemp++;

                          }
                    }
                    // <td valign="top" style="text-align:right;">'.set_number_format($value['Dispatch_Qty']).'</td>
                $page.='
                    <tr>
                        <td colspan="8" valign="top" align="center">Total</td>
                        
                        <td valign="top" style="text-align:right;">'.set_number_format($total).'</td>
                        <td colspan="'.$colspan2.'" valign="top" align="center"></td>
                    </tr>';
            }
    }
    $page.='</tbody></table>';
    // p( $page);exit;

    $this->load->helper('file');
    $stylesheet =  read_file('../libraries/pdf/style.css');
    $mpdf->WriteHTML($stylesheet,1);
    $mpdf->WriteHTML($page);

    $filename = 'Dispatch-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
    $strSaveName = $settings['uploads']['upload_path'] . $filename;
    $tmp = $mpdf->Output($strSaveName, 'F');
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");
    readfile($strSaveName);