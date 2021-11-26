<?php
date_default_timezone_set('Asia/Bangkok') ;
$settings = native_session::retrieve();
$mpdf=new mPDF('th','A4-L','11','',5,5,20,25,5,5); 
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
/*
$header = '
        <table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">

        <tr>
                <td height="30" width="450">
                    <img style="width:150px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                </td>
                <td align="left" height="30">'.iconv("TIS-620","UTF-8",'Location History Report').'</td>
        </tr>
        </table>

        ';
 */
$header = '
<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
    <tr>
        <td height="30" width="200">
            <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
        </td>
        <td style="text-align: center;font-weight: bold;">'.iconv("TIS-620","UTF-8",'Location History Report').' '.date("M Y").'</td>
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

# head_table is header of table
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
                border-bottom:1px solid #333333;
                font-size:10px;
        }
  </style>

    <table width="100%" cellspacing="0" style="border:1px solid #666666;font-size:10px;font-family: arial;" class="tborder">
        <thead>
                <tr style="border:1px solid #333333;">
                        <th height="35">'._lang('product_code').'</th>
                        <th width="20%">'._lang('product_name').'</th>
                        <th>'._lang('lot').'</th>
                        <th>'._lang('serial').'</th>
                        <th>'._lang('document_external').'</th>';
                            
                        if($this->config->item('build_pallet')):
                            $head_table .= '<th>'._lang('pallet_code').'</th>';
                        endif;
                        
                        $head_table .= '<th>'._lang('date').'</th>
                        <th>'._lang('location').'</th>
                        <th>'._lang('qty').'</th>';
                        //ADD BY POR 2014-06-23 กำหนดให้แสดง price per unit ด้วย
                        if($statusprice):
                            $head_table .='<th>'._lang('price_per_unit').'</th>
                            <th>'._lang('unit_price').'</th>
                            <th>'._lang('all_price').'</th>';
                        endif;
                        //END ADD
                        
                        $head_table .='
                        <th>'._lang('date').'</th>
                        <th>'._lang('location').'</th>
                        <th>'._lang('qty').'</th>';
                        /*
                        //ADD BY POR 2014-06-23 กำหนดให้แสดง price per unit ด้วย
                        if($statusprice):
                            $head_table .='<th>'._lang('price_per_unit').'</th>
                            <th>'._lang('unit_price').'</th>
                            <th>'._lang('all_price').'</th>';
                        endif;
                        //END ADD
                         */
                        
                        $head_table .='
                        <th>'._lang('date').'</th>
                        <th>'._lang('location').'</th>
                        <th>'._lang('qty').'</th>';
                        /*
                        //ADD BY POR 2014-06-23 กำหนดให้แสดง price per unit ด้วย
                        if($statusprice):
                            $head_table .='<th>'._lang('price_per_unit').'</th>
                            <th>'._lang('unit_price').'</th>
                            <th>'._lang('all_price').'</th>';
                        endif;
                        //END ADD
                        */
                        $head_table .='
                        <th>'._lang('date').'</th>
                        <th>'._lang('location').'</th>
                        <th>'._lang('qty').'</th>';
                        /*
                        //ADD BY POR 2014-06-23 กำหนดให้แสดง price per unit ด้วย
                        if($statusprice):
                            $head_table .='<th>'._lang('price_per_unit').'</th>
                            <th>'._lang('unit_price').'</th>
                            <th>'._lang('all_price').'</th>';
                        endif;
                        //END ADD
                        */
                        $head_table .='
                        <th>'._lang('date').'</th>
                        <th>'._lang('location').'</th>
                        <th>'._lang('qty').'</th>';
                        /*
                        //ADD BY POR 2014-06-23 กำหนดให้แสดง price per unit ด้วย
                        if($statusprice):
                            $head_table .='<th>'._lang('price_per_unit').'</th>
                            <th>'._lang('unit_price').'</th>
                            <th>'._lang('all_price').'</th>';
                        endif;
                        //END ADD
                        */
                        $head_table .='
                </tr>
        </thead>
        <tbody>	';

//		<th height="35">'.iconv("TIS-620","UTF-8",'Product Code').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Product Name').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Lot').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Serial').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Doc_Refer_Ext').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Date').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Location').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Qty').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Date').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Location').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Qty').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Date').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Location').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Qty').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Date').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Location').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Qty').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Date').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Location').'</th>
//                <th >'.iconv("TIS-620","UTF-8",'Qty').'</th>

    $page='';
    ########## start loop show page ########
    $count_row=count($data);

    # calculate page number 
    # max_line is number of table row in 1 page
//    $max_line=30;  
//    $totalpage = (int) ($count_row / $max_line);
//    if (($count_row % $max_line) != 0) {
//        $totalpage += 1;
//    }

            $page.=$head_table;
//    for($i=1;$i<=$totalpage;$i++){
//            if($i==1){
//                    $start=0;
//                    $stop=$max_line-1;
//            }
//            else{
//                    $start = $max_line * ($i - 1);
//                    $stop = ($max_line * $i) -1;
//            }
//            for($j=$start;$j<=$stop;$j++){
                foreach ($data as $j=>$value) {
                    if($j<$count_row){
                            $k=$j+1;
                            $page.='
                            <tr>
                                    <td>'.iconv("TIS-620","UTF-8",$data[$j]->Product_Code).'</td>
                                    <td>'.iconv("TIS-620","UTF-8",$data[$j]->Product_NameEN).'</td>
                                    <td align="center">'.iconv("TIS-620","UTF-8",$data[$j]->Product_Lot).'</td>
                                    <td align="center">'.iconv("TIS-620","UTF-8",$data[$j]->Product_Serial).'</td>
                                    <td align="center">'.iconv("TIS-620","UTF-8",$data[$j]->Document_No).'</td>';
                            
                                    if($this->config->item('build_pallet')):
                                        $page .= '<td align="center">'.iconv("TIS-620","UTF-8",$data[$j]->Pallet_Code).'</td>';
                                    endif;
                                    
                                    $qty4= (!empty($data[$j]->actual4))?set_number_format($data[$j]->qty4):'';
                                    $qty3= (!empty($data[$j]->actual3))?set_number_format($data[$j]->qty3):'';
                                    $qty2= (!empty($data[$j]->actual2))?set_number_format($data[$j]->qty2):'';
                                    $qty1= (!empty($data[$j]->actual1))?set_number_format($data[$j]->qty1):'';
                                    
                                    $page .= '<td align="center">'.$data[$j]->currentdate.'</td>
                                    <td align="center">'.iconv("TIS-620","UTF-8",$data[$j]->actual_now).'</td>
                                    <td align="right">'.set_number_format($data[$j]->current_qty).'</td>';
                                    //ADD BY POR 2014-06-18 price per unit
                                    if($statusprice):
                                        $page.='<td valign="top" align="right">' . set_number_format($data[$j]->current_price) . '</td>
                                                <td valign="top" align="center">' . $data[$j]->current_Unit_price. '</td>
                                                <td valign="top" align="right">' . set_number_format($data[$j]->current_All_price) . '</td>
                                                ';
                                    endif;
                                    //END ADD
                                    $page .='
                                    <td align="center">'.$data[$j]->date4.'</td>
                                    <td align="center">'.iconv("TIS-620","UTF-8",$data[$j]->actual4).'</td>
                                    <td align="right">'.$qty4.'</td>';
                                    /*
                                    //ADD BY POR 2014-06-18 price per unit
                                    if($statusprice):
                                        $page.='<td valign="top" align="right">' . set_number_format($data[$j]->price4) . '</td>
                                                <td valign="top" align="center">' . $data[$j]->Unit_price4. '</td>
                                                <td valign="top" align="right">' . set_number_format($data[$j]->All_price4) . '</td>
                                                ';
                                    endif;
                                    //END ADD
                                    */
                                    $page .='
                                    <td align="center">'.$data[$j]->date3.'</td>
                                    <td align="center">'.iconv("TIS-620","UTF-8",$data[$j]->actual3).'</td>
                                    <td align="right">'.$qty3.'</td>';
                                    /*
                                    //ADD BY POR 2014-06-18 price per unit
                                    if($statusprice):
                                        $page.='<td valign="top" align="right">' . set_number_format($data[$j]->price3) . '</td>
                                                <td valign="top" align="center">' . $data[$j]->Unit_price3. '</td>
                                                <td valign="top" align="right">' . set_number_format($data[$j]->All_price3) . '</td>
                                                ';
                                    endif;
                                    //END ADD
                                    */
                                    $page .='
                                    <td align="center">'.$data[$j]->date2.'</td>
                                    <td align="center">'.iconv("TIS-620","UTF-8",$data[$j]->actual2).'</td>
                                    <td align="right">'.$qty2.'</td>';
                                    /*
                                    //ADD BY POR 2014-06-18 price per unit
                                    if($statusprice):
                                        $page.='<td valign="top" align="right">' . set_number_format($data[$j]->price2) . '</td>
                                                <td valign="top" align="center">' . $data[$j]->Unit_price2. '</td>
                                                <td valign="top" align="right">' . set_number_format($data[$j]->All_price2) . '</td>
                                                ';
                                    endif;
                                    //END ADD
                                    */
                                    $page .='
                                    <td align="center">'.$data[$j]->date1.'</td>
                                    <td align="center">'.iconv("TIS-620","UTF-8",$data[$j]->actual1).'</td>
                                    <td align="right">'.$qty1.'</td>';
                                    /*
                                    //ADD BY POR 2014-06-18 price per unit
                                    if($statusprice):
                                        $page.='<td valign="top" align="right">' . set_number_format($data[$j]->price1) . '</td>
                                                <td valign="top" align="center">' . $data[$j]->Unit_price1. '</td>
                                                <td valign="top" align="right">' . set_number_format($data[$j]->All_price1) . '</td>
                                                ';
                                    endif;
                                    //END ADD
                                    */
                                    $page .='
                            </tr>
                            ';	
                    }				
            }
            $page.='</tbody>
            </table>';
            if($i!=$totalpage){
                    $page.='<pagebreak />';
            }
            # end of show table 30 rows

//    } // close loop page
    //echo $page;
    
    $this->load->helper('file');
    $stylesheet =  read_file('../libraries/pdf/style.css');
    $mpdf->WriteHTML($stylesheet,1);
    $mpdf->WriteHTML($page);

//    $mpdf->Output($strSaveName,'I');	// *** file name ***//

    $filename = 'Location-History-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
    $strSaveName = $settings['uploads']['upload_path'] . $filename;
    $tmp = $mpdf->Output($strSaveName, 'F'); 
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");
    readfile($strSaveName); 
?>