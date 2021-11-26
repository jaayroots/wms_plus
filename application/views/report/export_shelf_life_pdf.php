<?php
date_default_timezone_set('Asia/Bangkok') ;
$settings = native_session::retrieve();
$mpdf=new mPDF('UTF-8'); 
$mpdf=new mPDF('th','A4-L','11','',5,5,20,25,5,5); 
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
	$header = '
		<table width="100%" style="vertical-align: bottom; font-family:arial; font-size: 16px; color: #000000;">
                    <tr>
                        <td height="30" width="200">
                            <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                        </td>
                        <td style="text-align: center;font-weight: bold;">
                            <font size="5"><b>'.$text_header.'</b></font>
                        </td>
                        <td width="200">
                        </td>
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
				<tr>
					
					<th rowspan="2" width="100" class="border-top">'._lang('product_code').'</th>
					<th rowspan="2" width="200" class="border-top">'._lang('product_name').'</th>
					<th colspan="7" class="border-top">'._lang('shelf_life').'</th>
					<th rowspan="2" class="border-top">'._lang('total').'</th>
				</tr>
				<tr>
						<th>3M</th>
                        <th>6M</th>
                        <th>9M</th>
                        <th>1Y</th>
                        <th>1.5Y</th>
                        <th>2Y</th>
                        <th> >2Y</th>
				</tr>
				';
				
		$head_table.='
				
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
					$value->total = $value->threeMonth+$value->sixMonth+$value->nineMonth+$value->oneYear+$value->oneHalfYear+$value->twoYear+$value->moreTwoYear;
                                        
                                        $treemonth=($value->threeMonth!=0)?set_number_format($value->threeMonth):'';
                                        $sixMonth=($value->sixMonth!=0)?set_number_format($value->sixMonth):'';
                                        $nineMonth=($value->nineMonth!=0)?set_number_format($value->nineMonth):'';
                                        $oneYear=($value->oneYear!=0)?set_number_format($value->oneYear):'';
                                        $oneHalfYear=($value->oneHalfYear!=0)?set_number_format($value->oneHalfYear):'';
                                        $twoYear=($value->twoYear!=0)?set_number_format($value->twoYear):'';   
                                        $moreTwoYear=($value->moreTwoYear!=0)?set_number_format($value->moreTwoYear):'';                                
                                        
					$page.='
					<tr>
						<td>'.$value->Product_Code.'</td>
						<td>'.iconv("TIS-620", "UTF-8", $value->Product_NameEN).'</td>
						<td style="text-align:right;">'.$treemonth.'</td>
						
						<td style="text-align:right;">'.$sixMonth.'</td>
						<td style="text-align:right;">'.$nineMonth.'</td>
						<td style="text-align:right;">'.$oneYear.'</td>
						<td style="text-align:right;">'.$oneHalfYear.'</td>
						<td style="text-align:right;">'.$twoYear.'</td>
						<td style="text-align:right;">'.$moreTwoYear.'</td>
						<td style="text-align:right;">'.set_number_format($value->total).'</td>
						
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
                
                // Add By Akkarapol, 24/10/2013, เพิ่ม row ชื่อว่า Total เข้าไปอีก 1 row เพื่อแสดง จำนวนรวมของ Item ใน Shelf Life นั้นๆ
                $sum = array();
                foreach($data as $key_dt => $dt):
                    $sum['threeMonth'] = $sum['threeMonth']+$dt->threeMonth;
                    $sum['sixMonth'] = $sum['sixMonth']+$dt->sixMonth;
                    $sum['nineMonth'] = $sum['nineMonth']+$dt->nineMonth;
                    $sum['oneYear'] = $sum['oneYear']+$dt->oneYear;
                    $sum['oneHalfYear'] = $sum['oneHalfYear']+$dt->oneHalfYear;
                    $sum['twoYear'] = $sum['twoYear']+$dt->twoYear;
                    $sum['moreTwoYear'] = $sum['moreTwoYear']+$dt->moreTwoYear;
                    $sum['total'] = $sum['total']+$dt->total;
                endforeach;
                    $page.='<tr>';
                    $page.='<th align="center" colspan="2">Total</th>';                    
                    $total_sum = 0;
                    foreach($sum as $key_s => $s):
                        $page.='<th style="text-align:right;">'.set_number_format($s).'</th>';
                    $total_sum = $total_sum + $s;
                    endforeach;                    
//                    $page.='<th align="center">'.$total_sum.'</th>';
                    $page.='</tr>';
                // END Add By Akkarapol, 24/10/2013, เพิ่ม row ชื่อว่า Total เข้าไปอีก 1 row เพื่อแสดง จำนวนรวมของ Item ใน Shelf Life นั้นๆ
                    
		$page.='</tbody>
			</table>';
//		echo $page;

        
		$this->load->helper('file');
		$stylesheet =  read_file('../libraries/pdf/style.css');
		$mpdf->WriteHTML($stylesheet,1);
//		$page = iconv("TIS-620","UTF-8",$page);
		$mpdf->WriteHTML($page);

//		$mpdf->Output($strSaveName,'I');	// *** file name

                $filename = 'Shelf-Life-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
                $strSaveName = $settings['uploads']['upload_path'] . $filename;
                $tmp = $mpdf->Output($strSaveName, 'F'); 
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-disposition: attachment; filename=\"" . $filename . "\"");
                readfile($strSaveName); 

?>