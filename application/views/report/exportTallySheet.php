<?php 

header('Content-Type: text/html; charset=utf-8');
set_time_limit(0);
date_default_timezone_set('Asia/Bangkok') ;
$settings = native_session::retrieve();
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 60, 10, 5, 5);
$symbol_data = "";
$header = '
<table width="100%" style="vertical-align: bottom;  font-size: 16px; color: #000000;">
    <tr>
        <td height="30" width="200">
            <img style="width:auto;height:0.8cm;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
        </td>
        <td style="text-align: center;font-weight: bold;font-size: 24px">'. _lang('recive_product') .'/' . $data->Doc_Refer_Ext . '</td>
        <td height="30" width="300" align="right">' . tis620_to_utf8($data->Renter_Name) . '</td>
    </tr>
</table>
<table width="100%" align="center" border="0" style="font-size:14px; ">
    <tr>
        <td align="left" width=600>
            <table border="0" style="font-size:10px; " width=100%>
            <tr>
                <td width="150" align=left>'._lang('document_no_th').'</td>
                <td width="150">' . $data->Document_No . '</td>
                <td width="150">'._lang('document_internal_th').'</td>
                <td width="100">' . $data->Doc_Refer_Int . '</td>
                
            </tr> 
            <tr>
                <td width="100">'._lang('doc_refer_inv_th').'</td>
                <td width="70">' . tis620_to_utf8($data->Doc_Refer_Inv) . '</td>
                <td>'._lang('doc_refer_bl_th').'</td>
                <td>' . tis620_to_utf8($data->Doc_Refer_BL) . '</td>
             </tr>
             <tr>
                <td>'._lang('shipper_th').'</td>
                <td>' . tis620_to_utf8($data->Source_Name) . '</td>
                <td>'._lang('doc_refer_ce_th').'</td>         
                <td>' . tis620_to_utf8($data->Doc_Refer_CE) . '</td>
            </tr>   
            <tr>
                <td>'._lang('consignee_th').'</td>
                <td>' . tis620_to_utf8($data->Consignee_Name) . '</td>
                <td>'._lang('receive_type_th').'</td>
                <td>' . tis620_to_utf8($data->Dispatch_Type) . '</td>
            </tr>
            <tr>
                <td>'._lang('asn_date_th').'</td>
                <td>' . $data->Est_Action_Date . '</td>
                <td>'._lang('receive_date_th').'</td>
                <td>' . $data->Action_Date . '</td>
            </tr>  
            <tr>
                <td>'._lang('vendor_th').'</td>
                <td>' . tis620_to_utf8($data->Vendor_Name) . '</td>
                <td>'._lang('car_no_th').'</td>
                <td>' . tis620_to_utf8($data->Vendor_Car_No) . '</td>
            </tr>
            <tr>
                <td>'._lang('driver_name_th').'</td>
                <td>' .  tis620_to_utf8($data->Vendor_Driver_Name). '</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan=2>
                        <input type="checkbox" ' . ($data->Is_Pending=='Y'?'checked="checked"':'') . '>'._lang('pending_th').'
                                &nbsp;&nbsp;
                        <input type="checkbox" ' . ($data->Is_Repackage=='Y'?'checked="checked"':'') . '>'._lang('re_package_th').'
                </td>
                <td>'._lang('remark_th').'</td>
                <td>' . tis620_to_utf8($data->Remark) . '</td>
            </tr>   
            </table>
        </td>
        <td valign="top">
            <table width="100%" align="left" border="0" style="font-size:10px; ">
            <tr><td>'._lang('save_check').'</td></tr>
            <tr>
                <td width="300">
                    <table class="tb_border" style="font-size:10px; ">
                    <tr bgcolor="#FFF" align=center>
                        <td class="list" width="120" align="center">'._lang('status_container').'</td>
                        <td class="list" width=45 align="center">'._lang('status_container_normal').'</td>
                        <td class="list" width=45 align="center">'._lang('status_container_unusual').'</td>
                        <td class="list"  width=165 align="center">'._lang('status_container_remark').'</td>
                    </tr>   
                    <tr bgcolor="#FFF">
                        <td class="list">1. '._lang('neat_wall').'</td>
                        <td class="list"></td>
                        <td class="list"></td>
                        <td class="list"></td>
                    </tr> 
                    <tr bgcolor="#FFF">
                        <td class="list">2. '._lang('clean').'</td>
                        <td class="list"></td>
                        <td class="list"></td>
                        <td class="list"></td>
                    </tr> 
                    <tr bgcolor="#FFF">
                        <td class="list">3. '._lang('ground').'</td>
                        <td class="list"></td>
                        <td class="list"></td>
                        <td class="list"></td>
                    </tr> 
                    <tr bgcolor="#FFF">
                        <td class="list">4. '._lang('leg_lock_seals').'</td>
                        <td class="list"></td>
                        <td class="list"></td>
                        <td class="list"></td>
                    </tr> 
                    </table>
                </td>
                <td valign="top">
                    <table border="0" width=210 style="font-size:12px; ">
                    <tr align=center>
                        <td>'._lang('tally_leader').'</td>
                        <td>'._lang('tally_man').'</td>
                        <td>'._lang('tally_security').'</td>
                    </tr> 
                    <tr align=center>
                        <td width=70 height=50 style="border:1px solid #000;">&nbsp;</td>
                        <td width=70 style="border:1px solid #000;">&nbsp;</td>
                        <td width=70 style="border:1px solid #000;">&nbsp;</td>
                    </tr> 
                    </table>
                </td>
            </tr>
            </table>
        </td>
    </tr>
</table>';


$footer = '<table width="100%" style="vertical-align: bottom;  font-size: 9pt; color: #666666;">
<tr>
<td width="50%" align="left">
        Print By : ' . tis620_to_utf8($printBy) . '  ,
        Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
        <td align="right">WMSP:' . $revision . ' , {PAGENO}/{nb}</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');

$head_table = '
<style>
    ' . $set_css_for_show_column . '
    td,th {
        font-family: garuda;
    }
    .tborder{
        /*border:1px solid #333333;*/
        border-top:0px solid #333333;
        border-right:1px solid #333333;
    }
    .tborder td,
    .tborder th{
        /*border-top:1px solid #333333;*/
        border-left:1px solid #333333;
        border-bottom:1px solid #333333;
        border-right:1px solid #333333;
        font-size:13px;
        padding:3px;
        font-family: garuda;
        text-align: left;
    }
    
  table td.list{  vertical-align: top;}
</style>';
//p($config_column);
$page = '';
$page.=$head_table;
$count_container = $tmp_count; //for use pagebreak

//p($config_column);
//p($datas,true);

if (!empty($datas)):
    //show to web
    $line=0;
    $colspan_header = $config_column + 8;  //สำหรับ colspan ในส่วน แสดง
    $container_no = 0;

    //**** Loop by container_no
    foreach($datas as $key=>$vals): 
        $line = $line+2;
        if($container_no > 0){
            $page .='<pagebreak />';
        }
        $page.= '<table class="tb_border" style="font-size:14px; " width="100%">
                <thead>
                <tr bgcolor= "#FFF" style="border-bottom:1px solid #333333;">
                        <th colspan="4" align="left">'._lang('tally_container_vannint_report').' <font size=2><b>('.tis620_to_utf8($key).' - '. tis620_to_utf8($vals['Cont_Size_Unit']).')</b></font></th>
                        <th align="left" colspan='.($config_column+2).'>'._lang('tally_arrivals_time').'______________________'._lang('tally_start_time').'__________________'._lang('tally_end_time').'___________</th>
                        <th align="center" colspan="2">'._lang('tally_stuffing_area_fac').'__________________'._lang('tally_unit').'___________________</th>
                </tr>
                <tr bgcolor="#B0C4DE" align=center>
                        <th width="150" style="border-left:1px solid #333333;">'._lang('product_code').'</th>
                        <th width="150" style="border-left:1px solid #333333;">'._lang('lot_batch').'</th>
                        <th width=350 style="border-left:1px solid #333333;">'._lang('product_name').'</th>
                        <th style="border-left:1px solid #333333;">'._lang('total_qty').'</th>
                        <th style="border-left:1px solid #333333;">'._lang('unit').'</th>';

                        for($k=1;$k<=$config_column;$k++){
                            $page.= '<th style="border-left:1px solid #333333;">'.$k.'</th>';
                        }
                       
                        
        $page .= '<th width="150" style="border-left:1px solid #333333;">'._lang('uom_qty').'</th>';
        $page .= '<th width="150" style="border-left:1px solid #333333;">'._lang('uom_unit_prod').'</th>';
        $page .='<th width="350" style="border-left:1px solid #333333;border-right:1px solid #333333;">'._lang('comment_for_wrapping').'</th></tr>
                </thead>';
        $page.='<tbody>';

//        p($vals['dup_pallet'],false);                    
//        p($vals['data'],true);                    
        //#Show list data.
        foreach($vals['data'] as $k_rows=>$val_rows){    // Gen rows                  
            $code             = $val_rows['code'];
            $lot_batch        = tis620_to_utf8($val_rows['lot_batch']);
            $Product_NameEN   = tis620_to_utf8($val_rows['Product_NameEN']);
            $total_qty        = $val_rows['total_qty'];
            $unit             = tis620_to_utf8($val_rows['unit']);
            $total_Uom_Qty    = $val_rows['total_Uom_Qty'];
            $unit_product     = tis620_to_utf8($val_rows['unit_product']);
            $remark           = $val_rows['remark'];

            $pallet_show = array();
            if(!empty($val_rows['pallet_list'])){
                foreach($val_rows['pallet_list'] as $k=>$v){
                    if(array_key_exists($k,$vals['dup_pallet'])){
                        $pallet_show[$k]['duplicate']= $vals['dup_pallet'][$k];
                    }
                    $pallet_show[$k]['pallet']  = $k;
                    $pallet_show[$k]['amount']  = $v;
                    //$pallet_show[$k]['position']= (array_search($k,$vals['totalAll_pallet'])!==FALSE)? array_search($k,$vals['totalAll_pallet']) + 1 :"";
                }
            }

            $pallet_sort = array_values($pallet_show);
            asort($pallet_sort);
            $pallet_sort = array_values($pallet_sort);
                    
            $showMaxPallet = ($config_column < count($pallet_sort))? count($pallet_sort): $config_column;
            $rowSpan = ceil($showMaxPallet/$config_column);
            
             

//            p(array_key_exists("I15-28639",$vals['dup_pallet']));
//                p(($showMaxPallet));
//                p(($config_column));
//                p(($rowSpan));
//                p(($config_column),true);
//                p(($vals['dup_pallet']),true);
//                p(($pallet_show));
//                p($sortPalletDup);
            
            

                $line++;
                $page .="<tr>";
                $page .="<td rowspan='{$rowSpan}' style='border-left:1px solid #333333;border-bottom:1px solid #333333;' align='center'>". $code ."</td>";
                $page .="<td rowspan='{$rowSpan}' style='border-left:1px solid #333333;border-bottom:1px solid #333333;'>". $lot_batch ."</td>";
                $page .="<td rowspan='{$rowSpan}' style='border-left:1px solid #333333;border-bottom:1px solid #333333;'>". $Product_NameEN ."</td>";
                $page .="<td rowspan='{$rowSpan}' style='border-left:1px solid #333333;border-bottom:1px solid #333333;' align='right'>". set_number_format($total_qty) ."</td>";
                $page .="<td rowspan='{$rowSpan}' style='border-left:1px solid #333333;border-bottom:1px solid #333333;'>". $unit ."</td>";

                $pageNewRows = "";
                $columnAll_MaxPallet = $rowSpan*$config_column;
                for($k=0;$k<$columnAll_MaxPallet;$k++){
                    
                    $push_pallet = !empty($pallet_sort[$k]['amount'])? set_number_format($pallet_sort[$k]['amount']) : "";
                    if($pallet_sort[$k]['duplicate']['count']>1){
//                        $push_pallet .= !empty($pallet_sort[$k]['position'])? "<br/>(".$pallet_sort[$k]['position'].")": "";
                        $push_pallet .= !empty($pallet_sort[$k]['duplicate']['sort'])? "<br/>(". $pallet_sort[$k]['duplicate']['sort'] .")": "";
                    }
                    $numMod = ceil(($k+1)/$config_column);
                    if($numMod>1){ //*** New rows for rowspan
                        if(($k%$config_column) == 0 && $k!=0 ){
                           $pageNewRows .="<tr>";
                           $line++;
                        }
                        $pageNewRows .= '<td align="right" style="border-left:1px solid #333333;border-bottom:1px solid #333333;" width="50"> '. $push_pallet  .' </td>';
                        if(($k+1)% $config_column ==0 ){
                            $pageNewRows .="</tr>"; 
                        }
                    }else{
                        $page.= '<td align="right" style="border-left:1px solid #333333;border-bottom:1px solid #333333;" width="50"> '. $push_pallet .' </td>';
                    }
                }

//                    p($columnAll_MaxPallet,false);
//                    p($rowSpan,false);
//                    p($rowSpan,true);

                $page .="<td rowspan='{$rowSpan}' align='right' style='border-left:1px solid #333333;border-bottom:1px solid #333333;'>". set_number_format($total_Uom_Qty) ."</td>";
                $page .="<td rowspan='{$rowSpan}' style='border-left:1px solid #333333;border-bottom:1px solid #333333;'>". $unit_product ."</td>";
                $page .="<td rowspan='{$rowSpan}' style='border-left:1px solid #333333;border-bottom:1px solid #333333; border-right:1px solid #333333; ' >". tis620_to_utf8($remark) ."</td>";
                $page .="</tr>";
                $page .= $pageNewRows;
        }


//p($test,true);


        //#Total
        $totalAll_qty      = array_sum($vals['totalAll_qty']);
        $totalAll_pallet   = count($vals['totalAll_pallet']);
        $totalAll_Uom_qty  = array_sum($vals['totalAll_Uom_qty']);
        
        $line++;
        $page.='<tr bgcolor="#FFF">';
        $page.='<td colspan="3" align="center" style="border-left:1px solid #333333;border-bottom:1px solid #333333;"><b>'._lang('total').'</b></td>';
        $page.='<td align="right" style="border-left:1px solid #333333;border-bottom:1px solid #333333;"><b>'.  set_number_format($totalAll_qty).'</b></td>';
        $page.='<td style="border-left:1px solid #333333;border-bottom:1px solid #333333;" align="center" colspan="'.($config_column+1).'"><b>รวม '.$totalAll_pallet.' Pallet</b></td>';
        $page.='<td align="right" style="border-left:1px solid #333333;border-bottom:1px solid #333333;"><b>'.  set_number_format($totalAll_Uom_qty).'</b></td>';
        $page.='<td colspan="2" style="border-left:1px solid #333333;border-right:1px solid #333333;border-bottom:1px solid #333333;">&nbsp;</td>';
        $page.='</tr>';
        $page.= '</tbody></table>';

//        if($line >= $line_per_page){
//            $page .="LIne ==> :".$line;
//            $page.='<pagebreak />';
//        }
        
        $container_no++;
    endforeach;
endif;


$page .= '
<style type="css/text">
   .tb_border{
        border-top:0px solid #333333;
        border-left:0px solid #333333;
        border-right:0px solid #333333;
        border-collapse: collapse;
        padding:0px;
        margin:0px;
    }
    td.list{
        border-top:1px solid #333333;
        border-left:1px solid #333333;
        border-bottom:1px solid #333333;
        border-right:1px solid #333333;
        vertical-align: middle;
        padding:3px;
    }
    .tb_border th{
        border-top:0px solid #333333;
        border-left:0px solid #333333;
        border-right:0px solid #333333;
        border-bottom:1px solid #333333;
    }
  
</style>';


// echo $header;
// echo $page;
// echo $footer;
// exit();
// 
$this->load->helper('file');
$stylesheet =  read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet,1);
$mpdf->WriteHTML($page); 

$filename = 'Tally-Sheet-' . date('Ymd') . '-' . date('His') . '.pdf';

$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F'); 

header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName); 

?>
