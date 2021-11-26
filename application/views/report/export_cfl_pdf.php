<?php
date_default_timezone_set('Asia/Bangkok') ;
$settings = native_session::retrieve();
//$mpdf=new mPDF('UTF-8'); 
$mpdf=new mPDF('th','A4-L','11','',5,5,20,25,5,5); 
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
	$header = '
		<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
		
		<tr>
                    <td height="30" width="200">
                            <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                    </td>
                    <td style="text-align: center;font-weight: bold;">'.iconv("TIS-620","UTF-8",'Location Report').'</td>
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
        //Comment By Por 2013-10-14 ไปเรียก _lang ที่กิ๊กสร้างไว้แทน
        /*
        $head_table='	
           <style>
                .tborder{
                        border:1px solid #333333;
                }

                .tborder td,
                .tborder th{
                        font-size:13px;
                        border-right:1px solid #333333;
                        border-bottom:1px solid #333333;
                        padding:5px 2px 5px 2px;
                }

                table.tborder thead tr th{
                        border-bottom:1px solid #666666;
                }
          </style>
          <table width="100%" cellspacing="0" border-style="1px solid #666666;font-size:12px;" class="tborder">
                <thead>
                        <tr style="border:1px solid #333333;">';
                        foreach($column as $h){
                                $head_table.='<th >'.$h.'</th>';
                        }

                        $head_table.='
                        </tr>
                 </thead>
                    <tbody>';
	*/
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
                        padding:5px 2px 5px 2px;
                }

                table.tborder thead tr th{
                        border-bottom:1px solid #666666;
                }
            </style>

            <table width="100%" cellspacing="0" style="border:1px solid #666666;font-size:10px;font-family: arial; " class="tborder">
                <thead>
                    <tr style="border:1px solid #333333;" bgcolor="#B0C4DE">
                        <th width="7%" height="35">'._lang('date').'</th>
                        <th width="9%">'._lang('document_no').'</th>
                        <th width="9%">'._lang('doc_refer_int').'</th>
                        <th width="9%">'._lang('doc_refer_ext').'</th>
                        <th width="10%">'._lang('product_code').'</th>
                        <th width="20%">'._lang('product_name').'</th>
                        <th width="7%">'._lang('product_status').'</th>
                        <th width="7%">'._lang('lot').'</th>
                        <th width="7%">'._lang('serial').'</th>
                        <th width="5%">'._lang('quantity').'</th>
                        <th width="5%">'._lang('unit').'</th>';
                        //ADD BY POR 2014-06-18 price per unit
                        if($statusprice):
                            $head_table.= '
                            <th width="10%">' . _lang('price_per_unit') . '</th>
                            <th width="10%">' . _lang('unit_price') . '</th>
                            <th width="10%">' . _lang('all_price') . '</th>
                            ';   
                        endif;
                        //END ADD

                        $head_table.='<th width="10%">'._lang('suggest_location').'</th>
                        <th width="10%">'._lang('actual_location').'</th>
                        <th width="15%">'._lang($activity.' By').'</th>';
        
                        if($this->config->item('build_pallet')):
                            $head_table .= '<th width="10%">'._lang('pallet_code').'</th>';
                        endif;
                        
                        $head_table .= '<th width="10%">'._lang('remark').'</th>
                    </tr>
                </thead>
                <tbody>';
        
		$page='';
		########## start loop show page ########
		$count_row=count($data);

		# calculate page number 
		# max_line is number of table row in 1 page
//		$max_line=30;  
//		$totalpage = (int) ($count_row / $max_line);
                
//		if (($count_row % $max_line) != 0) {
//				$totalpage += 1;
//		}
                $page.=$head_table;	
//		for($i=1;$i<=$totalpage;$i++){
//				
//			# �ӹǹ�ҵ��˹觢ͧ array ����������ʴ��ź�÷Ѵ�á�����˹��
//			# start ��͵��˹��������
//			# stop ��͵��˹�����ش�ͧ�����ŷ����ʴ��˹�ҹ�� �
//			if($i==1){
//				$start=0;
//				$stop=$max_line-1;
//			}
//			else{
//				$start = $max_line * ($i - 1);
//				$stop = ($max_line * $i) -1;
//			}
			# �����ǹ�ٻ�ʴ�������������
//			for($j=$start;$j<=$stop;$j++){
                         foreach ($data as $j=>$value) {
				if($j<$count_row){                                      
					$k=$j+1;
					$bg='style="text-align:center;"';
					if($data[$j]->Suggest_Location_Id!=$data[$j]->Actual_Location_Id){
						$bg='style="color:#FF0000;text-align:center;"';
					}
					$page.='
					<tr>
						<td align="center">'.$data[$j]->Act_Date.'</td>
                                                <td align="center">'.$data[$j]->Document_No.'</td>
                                                <td align="center">'.$data[$j]->Doc_Refer_Int.'</td>
                                                <td align="center">'.$data[$j]->Doc_Refer_Ext.'</td>
						<td align="center">'.$data[$j]->Product_Code.'</td>
						<td>'.iconv("TIS-620","UTF-8",$data[$j]->Product_NameEN).'</td>
						<td>'.$data[$j]->Product_Status.'</td>
						<td>'.iconv("TIS-620","UTF-8",$data[$j]->Product_Lot).'</td>
						<td>'.iconv("TIS-620","UTF-8",$data[$j]->Product_Serial).'</td>
						<td style="text-align:right;">'.set_number_format($data[$j]->qty).'</td>
						<td style="text-align:center;">'.$data[$j]->Unit_Value.'</td>';
                                                
                                                //ADD BY POR 2014-06-20 price per unit
                                                if($statusprice):
                                                   $page.='
                                                        <td style="text-align:right;">'.set_number_format($data[$j]->Price_Per_Unit).'</td>
                                                        <td style="text-align:center;">'.$data[$j]->Unit_price.'</td>
                                                        <td style="text-align:right;">'.set_number_format($data[$j]->All_price).'</td>
                                                   '; 
                                                endif;
                                                //END ADD
                                        
                                                $page.='
						<td '.$bg.' >'.$data[$j]->Suggest_Location_Id.'</td>
						<td '.$bg.' >'.$data[$j]->Actual_Location_Id.'</td>
						<td>'.iconv("TIS-620","UTF-8",$data[$j]->Put_by).'</td>';
                                                    
                                                if($this->config->item('build_pallet')):
                                                    $page.= '<td>'.$data[$j]->Pallet_Code.'</td>';
                                                endif;
                                                
						$page.= '<td>'.iconv("TIS-620","UTF-8",$data[$j]->Remark).'</td>
					</tr>
					';	
					
					//$final_balance=$data[$j]['Balance_Qty'];
				}				
			}
			
//			if($i!=$totalpage){
//				//$page.='<pagebreak />';
//			}
			# end of show table 30 rows
			
//		} // close loop page
		$page.='</tbody>
			</table>';
		//echo $page;

                $this->load->helper('file');
                $stylesheet =  read_file('../libraries/pdf/style.css');
                $mpdf->WriteHTML($stylesheet,1);
                $mpdf->WriteHTML($page);
//                $mpdf->Output($strSaveName,'I');	// *** file name ***//

                $filename = 'Confirm-Location-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
                $strSaveName = $settings['uploads']['upload_path'] . $filename;
                $tmp = $mpdf->Output($strSaveName, 'F'); 
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-disposition: attachment; filename=\"" . $filename . "\"");
                readfile($strSaveName); 
        
//		$this->load->helper('file');
//		$stylesheet =  read_file('../libraries/pdf/style.css');
//		$mpdf->WriteHTML($stylesheet,1);
//		$page = iconv("TIS-620","UTF-8",$page);
//		$mpdf->WriteHTML($page);
//
//		$mpdf->Output($strSaveName,'I');	// *** file name

?>