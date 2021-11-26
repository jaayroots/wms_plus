<?php
?>

<script>    
        
var built_pallet = '<?php echo $this->config->item('build_pallet'); ?>';
var statusprice = '<?php echo $statusprice; ?>'; //ADD BY POR 2014-06-20 รับค่าสถานะของ price per unit

//--#Comment 2013-11-05 By Akkarapol #2235 แก้ไขให้แสดง page ให้ถูกต้อง รวมทั้ง sort ให้สามารถใช้งานได้ แถมยังกด next page ไปดูหน้าอื่น ข้อมูลก็ไม่เสีย แม้แต่เวลาที่เลือกให้แสดง item กี่ชิ้น เช่นเลือก 100, 1000, หรือแม้แต่ All ก็ไม่ให้มันเละ แบบที่เคยเป็นตารางเบี้ยวๆ ด้านซ้าย ด้านขวาที่มีจำนวนไม่เท่ากัน ก็ให้มันเป็นปกติได้
//#-- START --
//
//$(document).ready(function() {
// 
// var oTable = $('#tbreport').dataTable({
//		"bJQueryUI": true,
//                
//                //--#Comment 2013-09-06 By Por#2235 แก้ไขให้แสดง page ให้ถูกต้อง
//                //#-- START --
//                /*
//                "sScrollY": "100%",
//		"sScrollX": "150%",
//		//"sScrollXInner": "200%",
//		"bScrollCollapse": false,
//		"bPaginate": false,
//                */
//                //#-- END --
//                
//                //#2235 แก้ไขให้แสดง page ให้ถูกต้อง
//                //#DATE:2013-09-06
//                //#BY:POR
//                //-- START --
//                "bSort": true,
//                "sScrollY": "100%",
//                "sScrollX": "150%",
//		"sPaginationType": "full_numbers",
//                //-- END --
//                "bScrollCollapse" : true,
//                "aoColumns": [
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    { "bSortable": false },
//                    ] 
//	});
//    new FixedColumns( oTable, {                    
//                "iLeftColumns": 5,
//		"iLeftWidth": 650
//	});
//});
//
//#-- END --



//#2235 แก้ไขให้แสดง page ให้ถูกต้อง รวมทั้ง sort ให้สามารถใช้งานได้ แถมยังกด next page ไปดูหน้าอื่น ข้อมูลก็ไม่เสีย แม้แต่เวลาที่เลือกให้แสดง item กี่ชิ้น เช่นเลือก 100, 1000, หรือแม้แต่ All ก็ไม่ให้มันเละ แบบที่เคยเป็นตารางเบี้ยวๆ ด้านซ้าย ด้านขวาที่มีจำนวนไม่เท่ากัน ก็ให้มันเป็นปกติได้
//#DATE:2013-11-05
//#BY:Akkarapol
//-- START --
$(document).ready(function() {      

        var oTable = $('#tbreport').dataTable({
            "sScrollY": "100%",
            "sScrollX": "100%",
            "bAutoWidth": false,
            "sScrollXInner": "200%", 
            "iDisplayLength": 100,
            "sPaginationType": "full_numbers",
            "fnRowCallback": function() {
                $('tr').each(function() {
                    if ($(this).hasClass('inactive')) {
                        $(this).css('background', '#fccfcf');
                        $(this).find('.sorting_1').each(function() {
                            $(this).css('background', '#fccfcf');
                        });
                    }
                });
            },           
            "aoColumns": [
               null,
               null,
               null,
               null,
               null,
               {sClass:'td_pallet_code'},
                <?php echo $parser_ao_column; ?>
            ]           
        });
        new FixedColumns(oTable, {
            "iLeftColumns": 6,
            "iLeftWidth": 750,
            "aiWidths":[100,215,100,100,135,100] // Add By Akkarapol, 05/11/2013, เพิ่ม "aiWidths" เพื่อการจัดการความกว้างของแต่ละ column ที่อยู่ใน FixedColumns
        });
        
        if(!built_pallet){  // check config built_pallet if it is false then hide a column Pallet Code
            $('.td_pallet_code').hide();
        }
        
        //ADD BY POR 2014-06-20 
        if(!statusprice){
            $('.td_price_per_unit').hide();
        }
        //END ADD
    });
//#-- END --




$("#doc_ref_td").val();

   function exportFile(file_type){
		if(file_type=='EXCEL'){
                        //COMMENT BY POR 2013-10-16 เนื่องจากไม่มี function exportLocationHistoryReportToExcel อยู่จริง และได้แก้ไขเป็น exportReportHistoryReportToExcel แทน
                        // $("#form_report").attr('action',"<?php echo site_url(); ?>"+"/report/exportLocationHistoryReportToExcel")
                        
                        //START BY POR 2013-10-16 แก้ไขให้เรียกใช้ function ให้ถูกต้อง
                        $("#form_report").attr('action',"<?php echo site_url(); ?>"+"/report/exportReportHistoryReportToExcel")
                        //END
		}else{
			$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/report/exportLocationHistoryReportToPdf")
		}
		$("#form_report").submit();
  }
 
 function getParam(){
            var doc_type = $('#doc_type').val();
            var serial = $('#serial').val();
            var ref_value = $('#ref_value').val();
            var date_from = $('#date_from').val();
            var product_id = $('#product_id').val();
            
            var pallet_code = $('#pallet_code').val();
            
            var current_location = $('#current_location :selected').val();
            var lot = $('#lot').val();

            if (product_id == "" && current_location == "" && lot == "" && serial == "" && doc_type == "" && serial == "" && doc_type == "") {
                alert('Please select at least 1 condition');
                $('#report').html('Please select at least 1 condition');
            }
            else {

                $('#report').html('<img src="<?php echo base_url() ?>/images/ajax-loader.gif" />');
                $.ajax({
                    type: 'post',
                    url: '<?php echo site_url(); ?>/report/exportLocationHistoryReportToPDF', // in here you should put your query 
                    data: 'doc_type=' + doc_type + '&serial=' + serial + '&ref_value=' + ref_value + '&date_from='
					+ date_from + '&product_id=' + product_id + '&current_location=' + current_location + '&lot=' + lot + '&pallet_code=' + pallet_code,
                    success: function(data)
                    {
                        //alert(data);
                        $("#report").html(data);
                    }
                });
            }
 
 }
</script>

<?php // p($data) ;?>
<div id="container" style="width:100%; margin:0 auto;">
<form method="post" target="_blank" id="form_report" target="_blank">
<input type="hidden" name="product_id" id="product_id" value="<?php echo $condition_value["product_id"]; ?>">
<input type="hidden" name="current_location" id="current_location" value="<?php echo $condition_value["current_location"]; ?>">
<input type="hidden" name="lot" id="lot" value="<?php echo $condition_value["lot"]; ?>">
<input type="hidden" name="serial" id="serial" value="<?php echo $condition_value["serial"]; ?>">
<input type="hidden" name="doc_type" id="doc_type" value="<?php echo $condition_value["doc_type"]; ?>">
<input type="hidden" name="ref_value" id="ref_value" value="<?php echo $condition_value["ref_value"]; ?>">
<input type="hidden" name="date_from" id="date_from" value="<?php echo $condition_value["date_from"]; ?>">
<input type="hidden" name="pallet_code" id="pallet_code" value="<?php echo $condition_value["pallet_code"]; ?>">
<table id="tbreport" cellpadding="0" cellspacing="0" border="1" class="well" style="max-width: none">
	<thead>
		<tr>
			<th width="100"><?php echo _lang('product_code'); ?></th>
			<th width="250"><?php echo _lang('product_name'); ?></th>
			<th width="100"><?php echo _lang('lot'); ?></th>
			<th width="100"><?php echo _lang('serial'); ?></th>
			<th width="100"><?php echo _lang(strtolower($doc_ref_td)); ?></th>
			<th width="100"><?php echo _lang('pallet_code'); ?></th>
			<th><?php echo _lang('date'); ?></th>
			<th><?php echo _lang('location'); ?></th>
			<th><?php echo _lang('qty'); ?></th>
                        <!--ADD BY POR 2014-06-20 กำหนดให้แสดง price per unit ด้วย-->
                        <th><?php echo _lang('price_per_unit'); ?></th>
                        <th><?php echo _lang('unit_price'); ?></th>
                        <th><?php echo _lang('all_price'); ?></th>
                        <!--END ADD-->
			<th><?php echo _lang('date'); ?></th>
			<th><?php echo _lang('location'); ?></th>
			<th><?php echo _lang('qty'); ?></th>
                        <!--ADD BY POR 2014-06-20 กำหนดให้แสดง price per unit ด้วย-->
                        <th><?php echo _lang('price_per_unit'); ?></th>
                        <th><?php echo _lang('unit_price'); ?></th>
                        <th><?php echo _lang('all_price'); ?></th>
                        <!--END ADD-->
			<th><?php echo _lang('date'); ?></th>
			<th><?php echo _lang('location'); ?></th>
			<th><?php echo _lang('qty'); ?></th>
                        <!--ADD BY POR 2014-06-20 กำหนดให้แสดง price per unit ด้วย-->
                        <th><?php echo _lang('price_per_unit'); ?></th>
                        <th><?php echo _lang('unit_price'); ?></th>
                        <th><?php echo _lang('all_price'); ?></th>
                        <!--END ADD-->
			<th><?php echo _lang('date'); ?></th>
			<th><?php echo _lang('location'); ?></th>
			<th><?php echo _lang('qty'); ?></th>
                        <!--ADD BY POR 2014-06-20 กำหนดให้แสดง price per unit ด้วย-->
                        <th><?php echo _lang('price_per_unit'); ?></th>
                        <th><?php echo _lang('unit_price'); ?></th>
                        <th><?php echo _lang('all_price'); ?></th>
                        <!--END ADD-->
			<th><?php echo _lang('date'); ?></th>
			<th><?php echo _lang('location'); ?></th>
			<th><?php echo _lang('qty'); ?></th>
                        <!--ADD BY POR 2014-06-20 กำหนดให้แสดง price per unit ด้วย-->
                        <th><?php echo _lang('price_per_unit'); ?></th>
                        <th><?php echo _lang('unit_price'); ?></th>
                        <th><?php echo _lang('all_price'); ?></th>
                        <!--END ADD-->
		</tr>
	</thead>
	<tbody>
		<?php
		$i=1;
                $sum_all_current_qty=0;
                $sum_all_qty1=0;
                $sum_all_qty2=0;
                $sum_all_qty3=0;
                $sum_all_qty4=0;
                
                //ADD BY POR 2014-06-20 price per unit
                $sum_current_price = 0;
                $sum_current_all_price = 0;
                
                $sum_price1 = 0;
                $sum_all_price1=0;
                
                $sum_price2 = 0;
                $sum_all_price2=0;
                
                $sum_price3 = 0;
                $sum_all_price3=0;
                
                $sum_price4 = 0;
                $sum_all_price4=0;
                //END ADD
                
		$doc_ref_td = $condition_value["doc_type"];
		foreach($data as $cols){
		?>
		<tr>
			<td><?php echo $cols->Product_Code;?></td>
			<td style="text-align: left;"><?php echo $this->conv->tis620_to_utf8($cols->Product_NameEN) ?></td>
			<td><?php echo $cols->Product_Lot ?></td>
			<td><?php echo $cols->Product_Serial ?></td>
			<td id="doc_ref_td"> <?php echo $cols->doc_type; ?></td>
			<td><?php echo $cols->Pallet_Code ?></td>
			<td style="background-color: #FFFFCC;"><?php echo $cols->currentdate ?></td>
			<td style="background-color: #FFFFCC;"><?php echo $cols->actual_now ?></td>
			<td style="background-color: #FFFFCC;text-align:right;"><?php echo set_number_format($cols->current_qty); ?></td>
                        
                        <!--ADD BY POR 2014-06-20 price per unit-->
                        <td style="background-color: #FFFFCC;text-align:right;"><?php echo  (!empty($cols->current_price))?set_number_format($cols->current_price):''; ?></td>
			<td style="background-color: #FFFFCC;"><?php echo (!empty($cols->current_Unit_price))?$cols->current_Unit_price:''; ?></td>
                        <td style="background-color: #FFFFCC;text-align:right;"><?php echo (!empty($cols->current_All_price))?set_number_format($cols->current_All_price):''; ?></td>
                        <!--END ADD-->
                        
                        <td style="background-color: #FFEFD5;"><?php echo $cols->date4 ?></td>
			<td style="background-color: #FFEFD5;"><?php echo $cols->actual4 ?></td>
			<td style="background-color: #FFEFD5;text-align:right;" align="right"><?php  echo (!empty($cols->actual4))? set_number_format($cols->qty4):''; ?></td>
                        
                        <!--ADD BY POR 2014-06-20 price per unit-->
                        <td style="background-color: #FFFFCC;text-align:right;"><?php echo (!empty($cols->price4))?set_number_format($cols->price4):''; ?></td>
			<td style="background-color: #FFEFD5;"><?php echo (!empty($cols->Unit_price4))?$cols->Unit_price4:''; ?></td>
                        <td style="background-color: #FFFFCC;text-align:right;"><?php echo (!empty($cols->All_price4))?set_number_format($cols->All_price4):''; ?></td>
                        <!--END ADD-->
			
                        <td style="background-color: #FFF5EE;"><?php echo $cols->date3 ?></td>
			<td style="background-color: #FFF5EE;"><?php echo $cols->actual3 ?></td>
			<td style="background-color: #FFF5EE;text-align:right;" align="right"><?php echo (!empty($cols->actual3))? set_number_format($cols->qty3):''; ?></td>
                        
                        <!--ADD BY POR 2014-06-20 price per unit-->
                        <td style="background-color: #FFFFCC;text-align:right;"><?php echo (!empty($cols->price3))?set_number_format($cols->price3):''; ?></td>
			<td style="background-color: #FFEFD5;"><?php echo (!empty($cols->Unit_price3))?$cols->Unit_price3:''; ?></td>
                        <td style="background-color: #FFFFCC;text-align:right;"><?php echo (!empty($cols->All_price3))?set_number_format($cols->All_price3):''; ?></td>
                        <!--END ADD-->
			
                        <td style="background-color: #FFFACD;"><?php echo $cols->date2 ?></td>
			<td style="background-color: #FFFACD;"><?php echo $cols->actual2 ?></td>
			<td style="background-color: #FFFACD;text-align:right;" align="right"><?php echo (!empty($cols->actual2))? set_number_format($cols->qty2):''; ?></td>
                        
                        <!--ADD BY POR 2014-06-20 price per unit-->
                        <td style="background-color: #FFFFCC;text-align:right;"><?php echo (!empty($cols->price2))?set_number_format($cols->price2):''; ?></td>
			<td style="background-color: #FFEFD5;"><?php echo (!empty($cols->Unit_price2))?$cols->Unit_price2:''; ?></td>
                        <td style="background-color: #FFFFCC;text-align:right;"><?php echo (!empty($cols->All_price2))?set_number_format($cols->All_price2):''; ?></td>
                        <!--END ADD-->
			
                        <td style="background-color: #FFFFAA;"><?php echo $cols->date1 ?></td>
			<td style="background-color: #FFFFAA;"><?php echo $cols->actual1 ?></td>
			<td style="background-color: #FFFFAA;text-align:right;"><?php echo (!empty($cols->actual1))? set_number_format($cols->qty1):''; ?></td>
                        
                        <!--ADD BY POR 2014-06-20 price per unit-->
                        <td style="background-color: #FFFFCC;text-align:right;"><?php echo (!empty($cols->price1))?set_number_format($cols->price1):''; ?></td>
			<td style="background-color: #FFEFD5;"><?php echo (!empty($cols->Unit_price1))?$cols->Unit_price1:''; ?></td>
                        <td style="background-color: #FFFFCC;text-align:right;"><?php echo (!empty($cols->All_price1))?set_number_format($cols->All_price1):''; ?></td>
                        <!--END ADD-->
	    <?php 
                $i++;
                $sum_all_current_qty+=$cols->current_qty;
                $sum_all_qty1+=$cols->qty1;
                $sum_all_qty2+=$cols->qty2;
                $sum_all_qty3+=$cols->qty3;
                $sum_all_qty4+=$cols->qty4;
                
                $sum_current_price+=$cols->current_price;
                $sum_current_all_price+=$cols->current_All_price;  
                $sum_price1+=$cols->price1;
                $sum_all_price1+=$cols->All_price1;              
                $sum_price2+=$cols->price2;
                $sum_all_price2+=$cols->All_price2;              
                $sum_price3+=$cols->price3;
                $sum_all_price3+=$cols->All_price3;               
                $sum_price4+=$cols->price4;
                $sum_all_price4+=$cols->All_price4;         
                } ?>
                </tr>
		</tbody>
                
        <!-- show total qty : by kik : 31-10-2013-->
            <tfoot>
                     <tr>
                            <th colspan="6" class ='ui-state-default indent'  style='text-align: center;'><b>Total</b></th>
                            <th colspan="2"></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_all_current_qty);?></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_current_price);?></th>
                            <th></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_current_all_price);?></th>
                            <th colspan="2"></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_all_qty4);?></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_price4);?></th>
                            <th></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_all_price4);?></th>
                            <th colspan="2"></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format(@$sum_all_qty3);?></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_price3);?></th>
                            <th></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_all_price3);?></th>
                            <th colspan="2"></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format(@$sum_all_qty2);?></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_price2);?></th>
                            <th></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_all_price2);?></th>
                            <th colspan="2"></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format(@$sum_all_qty1);?></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_price1);?></th>
                            <th></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_all_price1);?></th>
                            <!--<th colspan="5" ></th>-->

                     </tr>
            </tfoot>
        <!-- end show total qty : by kik : 31-10-2013-->
        
</table>
</form>
</div>
<!--COMMENT BY POR 2013-11-05 ยกเลิกการใช้ปุ่มแสดง report หน้านี้ แต่ไป ให้ไปแสดงใน workflow_template แทน
<div style="margin:0 auto;">
        <input type="button" value="Export To PDF" class="button orange" onClick="exportFile('PDF');"  />&emsp;&emsp;
	<input type="button" value="Export To Excel" class="button orange" onClick="exportFile('EXCEL');" />
</div>
-->