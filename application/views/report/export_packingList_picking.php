<?php
date_default_timezone_set('Asia/Bangkok');
$mpdf = new mPDF('UTF-8');
$settings = native_session::retrieve();
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 35, 40, 5, 5);
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)

#============== Start Header ===========================
    $header = '<table border="0" width="100%" style="vertical-align: bottom; font-family: serif;  color: #000000;">
                <tr>
                    <td height="30" width="450">
                        <img style="width:150px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                    </td>
                </tr>
               </table>';
    
    $header .= '<table  align="left"  border="0" style="padding-left:100px;">'; 
    
        $header .= '<tr><td style="vertical-align: top;">Document No. : </td>';
        $header .= '</td><td style="font-size:14px; vertical-align: top;">';
        
            foreach ($order_info['Document_No'] as $document):
                $header .= $document.'<br>';
            endforeach;
            
        $header .= '</td></tr>';
    
    $header .= '</table>';
   
#--------------- End Header ----------------------------

#============== Start Footer ===========================
    $footer .= '<div style="width: 20%;float:left; font-size:12px;" >
                    <div >Build by   : '.iconv("TIS-620", "UTF-8", $pallet_info[0]->Create_By_Name).'</div>
                    <div>Build date : '.$pallet_info[0]->Create_Date.'</div> 
                </div>';
    $footer .= '<div style="width: 20%;float:right;">
                    <div>Approve:......................................</div>
                    <div style="padding:2px 0 2px 10px;">
                    (
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    )
                    </div>
                    <div>............../............../.....................</div> 
                </div>
                </div>';

    $footer .= '<table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 9pt; color: #666666;">
					<tr>
                                        <td width="50%" align="left">
                                                Print By : ' . iconv("TIS-620", "UTF-8", $printBy) . '  , 
                                                Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
						<td align="right">WMS:'.$revision.' , {PAGENO}/{nb}</td>
					</tr>
					</table>';
           
#--------------- End Footer ----------------------------

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');

$page = '';
 $page .= '<table align="center" border="0" width="100%">
                    <tr align="center"><td align="center"><font size="5"><b>รายละเอียดสินค้าที่นำเข้าเก็บในคลัง ฯ</b></font></td></tr>
               </table>';
    
# head_table is header of table
    $head_table = '<table align="center" width="100%" cellspacing="0" border="1">
                        <thead>
                            <tr style="border:1px;">';
                            foreach ($column as $keyH => $h) {
                                $cssBorder = '';
                                if ($keyH == 0):
                                    $cssBorder = 'border-left:1px solid black;border-radius-corner: 5px;';
                                elseif($keyH == count($column)-1):
                                    $cssBorder = 'border-right:1px solid black;border-radius-corner: 5px;';
                                endif;
                                $head_table.='<th style="border-bottom:1px solid black;border-top:1px solid black;' . $cssBorder . '">' . $h . '</th>';
                            }

    $head_table.='          </tr>
                        </thead>
                  <tbody>';



# calculate page number 
# max_line is number of table row in 1 page

$page.=$head_table;

$sum_confirm_qty = 0;
$sumPriceUnit    = 0;
$sumPrice        = 0;

foreach ($datas as $key => $data):
            $cssStyle = 'style="font-size:12px;border-bottom: 1px solid black;"';
            
            $sum_confirm_qty = $sum_confirm_qty + $data['Confirm_Qty'];
            $sumPriceUnit = $sumPriceUnit + $data['Price_Per_Unit'];
            $sumPrice = $sumPrice + ($data['Confirm_Qty']*$data['Price_Per_Unit']);
            
                $page.='<tr>
                                <td align="center" width="50" ' . $cssStyle . '>' . ($key+1) . '</td>
                                <td align="center" width="90" ' . $cssStyle . '>' . $data['Product_Code'] . '</td>
                                <td align="left" width="150" ' . $cssStyle . '>' . iconv("TIS-620", "UTF-8", $data['Product_NameEN']) . '</td>
                                <td align="left" width="90" ' . $cssStyle . '>' . $data['Status_Value'] . '</td>
                                <td align="left" width="90" ' . $cssStyle . '>' .$data['Sub_Status_Value'] . '</td>
                                <td align="center" width="50" ' . $cssStyle . '>' . $data['Product_Lot'] . '</td>
                                <td align="center" width="50" ' . $cssStyle . '>' . $data['Product_Serial'] . '</td>
                                <td align="center" width="50" ' . $cssStyle . '>' . $data['Product_Mfd'] . '</td>
                                <td align="center" width="50" ' . $cssStyle . '>' . $data['Product_Exp'] . '</td>
                                <td align="right" width="60" ' . $cssStyle . '>' . set_number_format($data['Confirm_Qty']) . '</td>
                                <td align="center" width="60" ' . $cssStyle . '>' . $data['Unit_Value'] . '</td>';

                                #ADD BY POR 2014-01-14 เพิ่ม price per unit
                                if($statusprice == TRUE):
                                    $page.='
                                    <td align="right" width="60" ' . $cssStyle . '>' . set_number_format($data['Price_Per_Unit']) . '</td>
                                    <td align="center" width="60" ' . $cssStyle . '>' . $data['Unit_Price_value'] . '</td>
                                    <td align="right" width="60" ' . $cssStyle . '>' . set_number_format(($data['Confirm_Qty']*$data['Price_Per_Unit'])) . '</td>
                                    ';
                                endif;
                                                
                $page.='</tr>';

endforeach;

$page.='            <tr>
                            <td colspan ="9" align="center" ' . $cssStyle . '><b> Total </b></td>
                            <td align="right" ' . $cssStyle . '>' . set_number_format($sum_confirm_qty) . '</td>
                            <td align="center" ' . $cssStyle . '> </td>';

                if($statusprice == TRUE):

$page.='
                            <td align="right" ' . $cssStyle . '>' . set_number_format($sumPriceUnit) . '</td>
                            <td align="center" ' . $cssStyle . '> </td>
                            <td align="right" ' . $cssStyle . '>' . set_number_format($sumPrice) . '</td>';
                endif;
                
                
$page.='            </tr>
               </tbody>
            </table>';

//====================================== Start PICKING ====================================================

$page .= '<table align="center" border="0" width="100%" style="padding-top:20px;">
                    <tr align="center"><td align="center"><font size="5"><b>รายละเอียดสินค้าที่นำออกจากคลัง ฯ</b></font></td></tr>
          </table>';

    # head_table is header of table



$head_table_picking = '<table width="80%" cellspacing="0" border="1" align="center" >
                            <thead>
                                <tr style="border:1px;">
                                    <th width="10%" style="border-bottom:1px solid black;border-top:1px solid black;">Item No.</th>
                                    <th width="10%" style="border-bottom:1px solid black;border-top:1px solid black;">No.</th>
                                    <th style="border-bottom:1px solid black;border-top:1px solid black;">Expose Qty.</th>
                                    <th style="border-bottom:1px solid black;border-top:1px solid black;">Balance Qty.</th>';

#check config price per unit for show price product picking
if($statusprice):
$head_table_picking.='              <th style="border-bottom:1px solid black;border-top:1px solid black;">Expose Price.</th>
                                    <th style="border-bottom:1px solid black;border-top:1px solid black;">Balance Price.</th>';
endif;


$head_table_picking.='              <th style="border-bottom:1px solid black;border-top:1px solid black;">Picking Date</th>
                                </tr>
                            </thead>
                            <tbody>';
    
$page .= $head_table_picking;

foreach ($picking_details as $k => $picking_detail) {
    $remain_qty = $picking_detail['Confirm_Qty'];
    $remain_price = $picking_detail['Confirm_Qty']*$picking_detail['Price_Per_Unit'];
        
    $no = 1;
    
    foreach ($picking_detail['pallet_detail'] as $key => $data ):
        $remain_qty = $remain_qty-$data['Confirm_Qty'];
        $remain_price=$remain_price-($data['Confirm_Qty']*$picking_detail['Price_Per_Unit']);
    
        $page .= '         <tr>
                                <td align="center" ' . $cssStyle . '>'.$k.'</td>
                                <td align="right" ' . $cssStyle . '>'.$no.'</td>
                                <td align="right" ' . $cssStyle . '>'.set_number_format($data['Confirm_Qty']).'</td>
                                <td align="right" ' . $cssStyle . '>'.set_number_format($remain_qty).'</td>   ';
#check config price per unit for show price product picking
if($statusprice):
        $page.='                <td align="right" ' . $cssStyle . '>'.set_number_format($data['Confirm_Qty']*$picking_detail['Price_Per_Unit']).'</td>
                                <td ' . $cssStyle . '>'.set_number_format($remain_price).'</td>';
endif;

        $page .= '              <td align="center" ' . $cssStyle . '>'.$data['Create_Date'].'</td>
                            </tr>';
        
        $k = "";
        $no++;
    endforeach;
}

                
$page .= '                  </tbody>
                        </table>';

//====================================== End PICKING ======================================================

$page .= '<div style="width: 20%; float:right; padding-top:20px; text-align:center; font-size:12px;">
            <div>DREX CHEM</div>';

    foreach ($order_info['Doc_Refer_Ext'] as $doc_refer_ext):
        $page .= "<div>(PO#".$doc_refer_ext.")</div>";
    endforeach;

$page .= '</div>';


$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);

$mpdf->WriteHTML($page);

//$mpdf->Output($strSaveName, 'I'); // *** file name

$strSaveName = $settings['uploads']['upload_path'] . 'picking-packing-list-'. $pallet_id. '-' . date('YmdHi') . '.pdf'; 
$tmp = $mpdf->Output($strSaveName, 'F'); 
   
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $strSaveName . "\"");
readfile($strSaveName);          
                

?>