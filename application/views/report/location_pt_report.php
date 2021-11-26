<script>
    
    var built_pallet = '<?php echo $this->config->item('build_pallet'); ?>';
    var statusprice = '<?php echo $statusprice; ?>'; //ADD BY POR 2014-06-20 รับค่าสถานะของ price per unit
    var search_activity = '<?php echo $search['activity'] ?>';
    var ci_pallet_id = 15;  
    var ci_history_location = 11;  
    
    //ADD BY POR 2014-06-20 set index price per unit
    var price_per_unit = 8;
    var unit_price = 9;
    var all_price = 10;
    //END ADD
    

    $(document).ready(function() {
        oTable = $('#defDataTable2').dataTable({
            "fnDrawCallback": function(oSettings) {
                if (oSettings.aiDisplay.length == 0)
                {
                    return;
                }

                var nTrs = $('#defDataTable2 tbody tr');
                var iColspan = nTrs[0].getElementsByTagName('td').length;
                var sLastGroup = "";
                for (var i = 0; i < nTrs.length; i++)
                {
                    var iDisplayIndex = oSettings._iDisplayStart + i;
                    var sGroup = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[0];
                    if (sGroup != sLastGroup)
                    {
                        var nGroup = document.createElement('tr');
                        var nCell = document.createElement('td');
                        nCell.colSpan = iColspan;
                        nCell.className = "group left_text";
                        nCell.innerHTML = sGroup;
                        nGroup.appendChild(nCell);
                        nTrs[i].parentNode.insertBefore(nGroup, nTrs[i]);
                        sLastGroup = sGroup;
                    }
                }
            },
            "aoColumnDefs": [
                {"bVisible": false, "aTargets": [0]}
            ],
            "aaSortingFixed": [[0, 'asc']],
            "aaSorting": [[1, 'asc']],
            "sPaginationType": "full_numbers",
            "bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
            "oSearch": {"sSearch": ""},
            //"sDom": 'lfr<"giveHeight"t>ip'
        });
    });
        
        if(!built_pallet){ // check config built_pallet if It's false then hide a column Pallet Code
            $('#defDataTable2').dataTable().fnSetColumnVis(ci_pallet_id, false);
        }
        
        if(search_activity != 'Relocation'){ // check Activity not 'Relocation' then hide a column History Location
            $('#defDataTable2').dataTable().fnSetColumnVis(ci_history_location, false);
        }
        
        if(!statusprice){
            $('#defDataTable2').dataTable().fnSetColumnVis(price_per_unit, false);
            $('#defDataTable2').dataTable().fnSetColumnVis(unit_price, false);
            $('#defDataTable2').dataTable().fnSetColumnVis(all_price, false); 
        }

    function exportFile(file_type) {
        if (file_type == 'EXCEL') {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/exportCmLocationExcel")
        } else if (file_type == 'PRINT') {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/exportCmLocationPrint")
        } else {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/exportCmLocationPdf")
        }
        $("#form_report").submit();
    }
</script>
<style>
    td.group{
        background:#E6F1F6;
    }

    .table_report{
        table-layout: fixed;
        margin-left: 0;
        max-width: none;
        width: 100%;
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        border-top-right-radius: 0 !important;
        margin-bottom: 0 !important;
    }

    .table_report tbody {
            width: 1000px;
            overflow: auto;
    }

    .table_report th {
        padding: 3px 5px;
        background: -moz-linear-gradient(center top , #013953, #002232) repeat scroll 0 0 transparent !important;
        background: -webkit-gradient(linear, center top, center bottom, from(#013953), to(#002232)) !important;
        border-left: 1px solid #D0D0D0 !important;
        border-radius: 0 0 0 0 !important;
        color : white !important;
    }

    table.table_report tr:nth-child(odd) td{
        background-color: #E2E4FF;
    }

    table.table_report tr:nth-child(even) td{
        background-color: #FFFFFF;
    }

    table.table_report td {
        border-right: 1px solid #D0D0D0;
        padding: 3px 5px;
    }

    table.table_report td {
        border-right: 1px solid #D0D0D0;
        padding: 3px 5px;
    }
</style>
<form  method="post" target="_blank" id="form_report">
    <input type="hidden" name="fdate" value="<?php echo $search['fdate']; ?>" />
    <input type="hidden" name="tdate" value="<?php echo $search['tdate']; ?>" />
    <input type="hidden" name="activity" value="<?php echo $search['activity']; ?>" />
    <input type="hidden" name="product_id" value="<?php echo $search['product_id']; ?>" />
    <input type="hidden" name="doc_value" value="<?php echo $search['doc_value']; ?>" />
    <input type="hidden" name="doc_type" value="<?php echo $search['doc_type']; ?>" />
    <!-- <table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info"> -->
    <div class='Tables_wrapper'>
        <div class=" fg-toolbar ui-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix">
            <div id="defDataTable2_length" class="dataTables_length">
                <?php echo $display_items_per_page?>
            </div>
            <div class="dataTables_filter" id="defDataTable2_filter">
                <label>
                    <!--Search: <input type="text" aria-controls="defDataTable2">-->
                </label>
            </div>
        </div>
        <!--<div id="table-wrapper" style="width: <?php echo ($search['w'] - 30)?>px;">-->
        <div id="table-wrapper" >
    <table  class ='well table_report' cellpadding="0" cellspacing="0" border="0" aria-describedby="defDataTable_info">
        <thead>
            <tr>
                <th style="width:80px;"><?php echo _lang('date'); ?></th>
                <th style="width:100px;"><?php echo _lang('document_no'); ?></th>
                <th style="width:100px;"><?php echo _lang('doc_refer_int'); ?></th>
                <th style="width:100px;"><?php echo _lang('doc_refer_ext'); ?></th>
                <th style="width:80px;"><?php echo _lang('product_code'); ?></th>
                <th style="width:150px;"><?php echo _lang('product_name'); ?></th>
                <th style="width:80px;"><?php echo _lang('lot'); ?></th>
                <th style="width:80px;"><?php echo _lang('serial'); ?></th>
                <th style="width:80px;"><?php echo _lang('qty'); ?></th>
                <th style="width:80px;"><?php echo _lang('unit'); ?></th>
                
                <!--ADD BY POR 2014-06-20 กำหนดให้แสดง price per unit ด้วย-->
                <th style="width:80px;"><?php echo _lang('price_per_unit'); ?></th>
                <th style="width:80px;"><?php echo _lang('unit_price'); ?></th>
                <th style="width:80px;"><?php echo _lang('all_price'); ?></th>
                <!--END ADD-->
                
                <th style="width:80px;"><?php echo _lang('from_location'); ?></th>
                <th style="width:80px;"><?php echo _lang('suggest_location'); ?></th>
                <th style="width:80px;"><?php echo _lang('confirm_location'); ?></th>
                <th style="width:80px;"><?php echo $search['txtby']; ?></th>
                <th style="width:80px;"><?php echo _lang('pallet_code'); ?></th>
                <th style="width:80px;"><?php echo _lang('remark'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sum_all=0;
            
            //ADD BY POR 2014-06-20 เพิ่มให้แสดง price_per_unit
            $sum_price = 0;
            $sum_all_price = 0;
            //END ADD
            foreach ($data as $key => $value) {
                foreach ($value as $key2 => $row) {
                    foreach ($row as $col) {
                        $style = '';
                        if ($col->Suggest_Location_Id != $col->Actual_Location_Id) {
                            $style = ' style="color:red"';
                        }
                        ?>			
                        <tr>
                            <td style="width:80px;"><?php echo $key; ?></td>
                            <td style="width:100px;"><?php echo $key2; ?></td>
                            <td style="width:100px;"><?php echo $col->Doc_Refer_Int; ?></td>
                            <td style="width:100px;"><?php echo $col->Doc_Refer_Ext; ?></td>
                            <td style="width:80px;"><?php echo $col->Product_Code; ?></td>
                            <td style="text-align:left;" style="width:150px;"><?php echo $this->conv->tis620_to_utf8($col->Product_NameEN); ?></td>
                            <td style="width:80px;"><?php echo $col->Product_Lot; ?></td>
                            <td style="width:80px;"><?php echo $col->Product_Serial; ?></td>
                            <td style="text-align:right;" style="width:80px;"><?php echo set_number_format($col->qty); ?></td>
				                    
                            <!--Edit By Akkarapol, 13/01/2013, เปลี่ยนจากการนำ Unit_Id มาแสดงที่หน้าจอ เป็นแสดงด้วย Unit_Value แทน-->
                            <!--<td><?php //echo $col->Unit_Id; ?></td>-->
                            <td style="width:80px;"><?php echo $col->Unit_Value; ?></td>
                            
                            <!--ADD BY POR 2014-06-20 price per unit-->
                            <td style="text-align:right;" style="width:80px;"><?php echo set_number_format($col->Price_Per_Unit); ?></td>
                            <td style="width:80px;"><?php echo $col->Unit_price; ?></td>
                            <td style="text-align:right;" style="width:80px;"><?php echo set_number_format($col->All_price); ?></td>
                            <!--END ADD-->
                            
                            <td style="width:80px;"><?php echo @$col->history_location; ?></td>
                            <td style="width:80px;" <?php echo $style; ?>><?php echo $col->Suggest_Location_Id; ?></td>
                            <td style="width:80px;" <?php echo $style; ?>><?php echo $col->Actual_Location_Id; ?></td>
                            <td style="text-align:left;" style="width:80px;"><?php echo $this->conv->tis620_to_utf8($col->Put_by); ?></td>
                            <td style="text-align:left;" style="width:80px;"><?php echo $col->Pallet_Code; ?></td>
                            <td style="text-align:left;" style="width:80px;"><?php echo $this->conv->tis620_to_utf8($col->Remark); ?></td>
                         </tr>
                        <?php
                        
                        $sum_all+=@$col->qty;
                        
                        //ADD BY POR 2014-06-20
                        $sum_price +=$col->Price_Per_Unit ;
                        $sum_all_price +=$col->All_price ;
                        //END ADD
                
                    } // close loop detail
                } // close loop doc no
            }
            ?>
        </tbody>
        
        <!-- show total qty : by kik : 31-10-2013-->
            <tfoot>
                     <tr>
                            <th colspan="8" class ='ui-state-default indent'  style='text-align: center;'><b>Total</b></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_all);?></th>
                            <!--ADD BY POR 2014-06-20 price per unit-->
                            <th></th>
                            <th class ='ui-state-default indent' style='text-align: right;'><?php echo set_number_format($sum_price); ?></th>
                            <th></th>
                            <th class ='ui-state-default indent' style='text-align: right;'><?php echo set_number_format($sum_all_price); ?></th>
                            <!--END ADD-->
                            <th colspan="6" ></th>

                     </tr>
            </tfoot>
        <!-- end show total qty : by kik : 31-10-2013-->
                          
    </table>
    </div>  
    </div>
    <div id="pagination" class="fg-toolbar ui-toolbar ui-widget-header ui-corner-bl ui-corner-br ui-helper-clearfix">
        <div class="dataTables_info" id="defDataTable2_info">Showing <?php echo $low+1 ?> to <?php echo $show_to ?> of <?php echo $items_total;?> entries</div>
        <div style="padding:3px;"class="dataTables_paginate fg-buttonset ui-buttonset fg-buttonset-multi ui-buttonset-multi paging_full_numbers" id="defDataTable2_paginate">
            <?php echo $pagination?>
        </div>
    </div>
    <div align="center" style="margin-top:10px;">
        <!--COMMENT BY POR 2013-11-26 ปิดไว้ก่อนเนื่องจากยังไม่ได้เรียกใช้-->
        <!--Add By Akkarapol, 10/09/2013, เพิ่มเพื่อตรวจสอบว่า ถ้ามาตามที่ต้องการจะให้แสดงปุ่ม Print โดยรับค่ามาจาก controller/report.php-->
        <?php //if ($showButtonPrint): ?>
            <!--<input type="button" value="Print" class="button orange" onClick="exportFile('PRINT')"  />-->
            <!--&emsp;&emsp;-->
        <?php //endif; ?>
        <!--END Add By Akkarapol, 10/09/2013, เพิ่มเพื่อตรวจสอบว่า ถ้ามาตามที่ต้องการจะให้แสดงปุ่ม Print โดยรับค่ามาจาก controller/report.php-->
        <!--END COMMENT-->
        
        <!--COMMENT BY POR 2013-11-05 ยกเลิกการใช้ปุ่มแสดง report หน้านี้ แต่ไป ให้ไปแสดงใน workflow_template แทน 
        <input type="button" value="Export To PDF" class="button orange" onClick="exportFile('PDF')"  />
        &emsp;&emsp;
        <input type="button" value="Export To Excel" class="button orange" onClick="exportFile('EXCEL')" />
        -->
    </div>
</form>