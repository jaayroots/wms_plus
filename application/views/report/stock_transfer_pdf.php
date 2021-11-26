<?php 
date_default_timezone_set('Asia/Bangkok') ;
$mpdf=new mPDF('UTF-8'); 
$mpdf=new mPDF('th','A4-L','11','',5,5,20,25,5,5); 
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
	$header = '
		<table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 16px; color: #000000;">
		
		<tr>
			<td height="30" width="200">
                        <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                    </td>
                    <td style="text-align: center;font-weight: bold;">'.iconv("TIS-620","UTF-8",'Stock Transfer Report').' ('.$search['fdate'].' - '.$search['tdate'].')</td>
                        <td width="200"> </td>
		</tr>
		</table>
				
		';
		//ADD BY POR 2013-12-18 แสดงส่วนท้าย(เซ็นชื่อ) ตอนกด Approve Receive
                if($showfooter=='show'):
                $footer .= '
                <div style="width:100%;font-size:13px;">
                <div style="width: 25%;float:left;">
                    <div>Shipper:......................................</div>
                    <div style="padding:2px 0 2px 10px;">
                    (
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    )
                    </div>
                    <div>............../............../.....................</div> 
                </div>
                <div style="width: 25%;float:left;">
                    <div>Car Plate No..............................</div>
                    <div style="padding:5px 0">Driver:.........................................</div>
                </div>
                <div style="width: 30%;float:left;">
                    <div>Auditor:......................................</div>
                    <div style="padding:2px 0 2px 10px;">
                    (
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    )
                    </div>
                    <div>............../............../.....................</div> 
                </div>
                <div style="width: 20%;float:left;">
                    <div>Approve:......................................</div>
                    <div style="padding:2px 0 2px 10px;">
                    (
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    )
                    </div>
                    <div>............../............../.....................</div> 
                </div>
                </div>
                ';
                endif;
                //END IF

		$footer .= '<table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 9pt; color: #666666;">
					<tr>
                                        <td width="50%" align="left">
                                                Print By : ' . iconv("TIS-620", "UTF-8", $printBy) . '  , 
                                                Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
						<td align="right">WMS:'.$revision.' , {PAGENO}/{nb}</td>
					</tr>
					</table>';
		
//		$header = iconv("TIS-620","UTF-8",$header); // Comment By Akkarapol, 12/09/2013, คอมเม้นต์ทิ้งเพราะถ้าเอาไว้มันจะไม่สามารถ Generate PDF ได้
//		$footer = iconv("TIS-620","UTF-8",$footer); // Comment By Akkarapol, 12/09/2013, คอมเม้นต์ทิ้งเพราะถ้าเอาไว้มันจะไม่สามารถ Generate PDF ได้
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
				font-size:13px;
			}
		  </style>
		  <table width="100%" cellspacing="0" border-style="1px solid #666666;font-size:12px;" class="tborder">
			<thead>
				<tr style="border:1px solid #333333;">
					<th width="70" height="35">'.iconv("TIS-620","UTF-8",_lang('no')).'</th>
					<th width="100">'.iconv("TIS-620","UTF-8",_lang('po_no')).'</th>
					<th width="170">'.iconv("TIS-620","UTF-8",_lang('consinee')).'</th>
					<th width="95">'.iconv("TIS-620","UTF-8",_lang('product_code')).'</th>
					<th >'.iconv("TIS-620","UTF-8",_lang('product_name')).'</th>
					<th width="70">'.iconv("TIS-620","UTF-8",_lang('receive_qty')).'</th>
					<th width="70">'.iconv("TIS-620","UTF-8",_lang('transfer_date')).'</th>
					<th width="70">'.iconv("TIS-620","UTF-8",_lang('transfer_qty')).'</th>
                                        
					';
		 if($price_per_unit == TRUE){
                          $head_table.='<th width="70">'.iconv("TIS-620","UTF-8",_lang('price_per_unit')).'</th>
                                        <th width="70">'.iconv("TIS-620","UTF-8",_lang('unit_price')).'</th>
                                        <th width="70">'.iconv("TIS-620","UTF-8",_lang('all_price')).'</th>';
                 }
                if($this->config->item('build_pallet')):
                     $head_table.='
                              <th width="70">'.iconv("TIS-620","UTF-8",_lang('pallet_code')).'</th>';
                endif;
                          $head_table.='
                              <th width="150">'.iconv("TIS-620","UTF-8",_lang('remark')).'</th>
                                    </tr>
                            </thead>
                            <tbody>	';	
                          
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
			# �ӹǹ�ҵ��˹觢ͧ array ����������ʴ��ź�÷Ѵ�á�����˹��
			# start ��͵��˹��������
			# stop ��͵��˹�����ش�ͧ�����ŷ����ʴ��˹�ҹ�� �
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
                                        $page.='<tr>
                                                    <td align="center">'.$value->Estimate_Action_Date.'</td>
                                                    <td align="center">'.$value->Doc_Refer_Ext.'</td>
                                                    <td >'.iconv("TIS-620","UTF-8",$value->consignee).'</td>
                                                    <td align="center">'.$value->Product_Code.'</td>
                                                    <td>'.iconv("TIS-620","UTF-8",$value->Product_NameEN).'</td>
                                                    <td align="right">'.set_number_format($value->Reserv_Qty).'</td>
                                                    <td align="center">'.$value->Actual_Action_Date.'</td>
                                                    <td align="right">'.set_number_format($value->Confirm_Qty).'</td>';
                                        
                                        if($price_per_unit == TRUE){
                                           $page.='
                                            
                                                    <td align="right">'.set_number_format($value->Price_Per_Unit).'</td>
                                                    <td align="center">'.iconv("TIS-620","UTF-8",$value->Unit_Price_value).'</td>
                                                    <td align="right">'.set_number_format($value->All_Price).'</td>
                                                    
                                            ';	 
                                        }
                                        
                                        $page.=    '<td align="center">'.iconv("TIS-620","UTF-8",$value->Pallet_Code).'</td>
                                            <td>'.iconv("TIS-620","UTF-8",$value->Remark).'</td>
                                            </tr>';
                                        
					//$final_balance=$data[$j]['Balance_Qty'];
				}	
                                
			}
			$page.='</tbody>
			</table>';
//			if($i!=$totalpage){
//				$page.='<pagebreak />';
//			}
			# end of show table 30 rows
			
//		} // close loop page

        
		$this->load->helper('file');
		$stylesheet =  read_file('../libraries/pdf/style.css');
		$mpdf->WriteHTML($stylesheet,1);
//		$page = iconv("TIS-620","UTF-8",$page); // Comment By Akkarapol, 12/09/2013, คอมเม้นต์ทิ้งเพราะถ้าเอาไว้มันจะไม่สามารถ Generate PDF ได้
		$mpdf->WriteHTML($page);

		$mpdf->Output($strSaveName,'I');	// *** file name ***//
?>