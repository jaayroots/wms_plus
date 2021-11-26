<?php
date_default_timezone_set('Asia/Bangkok') ;
$settings = native_session::retrieve();
$mpdf=new mPDF('UTF-8'); 
$mpdf=new mPDF('th','A4-L','11','',5,5,20,25,5,5); 
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
	$header = '
		<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
		
		<tr>
                    <td height="30" width="200">
                        <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                    </td>
                    <td style="text-align: center;font-weight: bold;">'.iconv("TIS-620","UTF-8",'Counting Report').'</td>
                    <td width="200"> </td>
		</tr>
		</table>
				
		';
		

		$footer = '
			<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
				<tr>
    					<td width="50%" align="left">
            						Print By : ' . iconv("TIS-620", "UTF-8", $printBy) . '  , 
            						Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
            					<td align="right">{PAGENO}/{nb}</td>
    				</tr>
			</table>';
		
		$header = iconv("TIS-620","UTF-8",$header);
		$footer = iconv("TIS-620","UTF-8",$footer);
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
				<tr style="border:1px solid #333333;">';
				foreach($column as $h){
					$head_table.='<th >'.$h.'</th>';
				}
				
		$head_table.='
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
//			# �����ǹ�ٻ�ʴ�������������
//			for($j=$start;$j<=$stop;$j++){
                foreach ($data as $j=>$value) {
				if($j<$count_row){
					$k=$j+1;
					$bg='';
					if($data[$j]->Reserv_Qty!="" && $data[$j]->Confirm_Qty!=""){
						//$variat=ceil((($data[$j]->Reserv_Qty - $data[$j]->Confirm_Qty)/ $data[$j]->Reserv_Qty)*100); //COMMENT BY POR 2013-11-04 เนื่องจากได้ผลเป็นจำนวนเต็มจึงไม่ใช้
                                                $variat=(($data[$j]->Reserv_Qty-$data[$j]->Confirm_Qty)/ (int) $data[$j]->Reserv_Qty)*100; //ADD BY POR 2013-11-04 แสดงค่าเป็นทศนิยม 2 ตำแหน่ง ซึ่งใกล้เคียงกับค่าความจริงที่สุด
                                        }
					else{
						$variat=0;
					}
                                        
//                                        Comment By Akkarapol, 19/11/2013, คอมเม้นต์ออกเพราะจะเอา บรรทัด Actual_Qty ออก เนื่องจากไม่ต้องแสดงแล้ว
//					$page.='
//					<tr align="center">
//						<td>'.$data[$j]->Actual_Action_Date.'</td>
//						<td>'.$data[$j]->Product_Code.'</td>
//						<td align="left">'.$data[$j]->Product_NameEN.'</td>
//						<td align="right" width=30>'.number_format((int) $data[$j]->Actual_Qty).'</td>
//						<td align="right" width=30>'.number_format((int) $data[$j]->Reserv_Qty).'</td>
//						<td align="right" width=30>'.number_format((int) $data[$j]->Confirm_Qty).'</td>
//						<td align="left" width=200>'.$data[$j]->Count_By.'</td>
//						<td align="right">'.number_format($variat,2,".",",").'</td>
//						
//					</tr>
//					';	
                                        
                                        $page.='
					<tr align="center">
						<td>'.$data[$j]->Actual_Action_Date.'</td>
						<td>'.$data[$j]->Product_Code.'</td>
						<td align="left">'.$data[$j]->Product_NameEN.'</td>
						<td align="right" width=30>'.set_number_format($data[$j]->Reserv_Qty).'</td>
						<td align="right" width=30>'.set_number_format($data[$j]->Confirm_Qty).'</td>
						<td align="left" width=200>'.$data[$j]->Count_By.'</td>
						<td align="right">'.set_number_format($variat).'</td>
						
					</tr>
					';	
					
					//$final_balance=$data[$j]['Balance_Qty'];
				}				
			}
			
			if($i!=$totalpage){
				//$page.='<pagebreak />';
			}
			# end of show table 30 rows
			
//		} // close loop page
		$page.='</tbody>
			</table>';
		//echo $page;

        
		$this->load->helper('file');
		$stylesheet =  read_file('../libraries/pdf/style.css');
		$mpdf->WriteHTML($stylesheet,1);
		$page = iconv("TIS-620","UTF-8",$page);
		$mpdf->WriteHTML($page);

//		$mpdf->Output($strSaveName,'I');	// *** file name

                $filename = 'Counting-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
                $strSaveName = $settings['uploads']['upload_path'] . $filename;
                $tmp = $mpdf->Output($strSaveName, 'F'); 
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-disposition: attachment; filename=\"" . $filename . "\"");
                readfile($strSaveName); 

?>