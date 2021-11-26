<?php ?>

<script>
    $(document).ready(function() {
        $('#defDataTable2').dataTable({
//            add for group Pallet_Code in dataTable
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
//            END add for group Pallet_Code in dataTable
        });
    });

    function exportFile(file_type) {
        if (file_type == 'EXCEL') {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/export_stock_transfer_to_excel")
        } else {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/export_stock_transfer_to_pdf")
        }
        $("#form_report").submit();
    }
</script>
<style>
    td.group{
        background:#E6F1F6;
    }
</style>
<form  method="post" target="_blank" id="form_report">
    <input type="hidden" name="fdate" value="<?php echo $search['fdate']; ?>" />
    <input type="hidden" name="tdate" value="<?php echo $search['tdate']; ?>" />
    <table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
        <thead>
            <tr>
                    <!--<th><?php //echo 'Date/�ѹ�����'; ?></th>--> <!-- Comment By Akkarapol, 12/09/2013, คอมเม้นต์ทิ้งเพราะเป็นภาษาต่างดาว-->
                <th>Pallet Code</th><!-- Add By Akkarapol, 12/09/2013, เพิ่มมาแทนตัวที่เป็นภาษาต่างดาว-->
                <th><?php echo 'ASN Date'; ?></th><!-- Add By Akkarapol, 12/09/2013, เพิ่มมาแทนตัวที่เป็นภาษาต่างดาว-->
                <!--<th><?php //echo $this->conv->tis620_to_utf8('Document No.'); ?></th>--><!-- Comment By Akkarapol, 12/09/2013, จะต้องใช้เป็น PO.NO แทน-->
                <th><?php echo $this->conv->tis620_to_utf8(_lang('document_no')); ?></th><!-- Add By Akkarapol, 12/09/2013, ใช้ PO.NO แทน Document No.-->
                <!--<th><?php //echo $this->conv->tis620_to_utf8('Supplier'); ?></th>--><!-- Comment By Akkarapol, 12/09/2013, จะต้องใช้เป็น Consignee แทน-->
                <th><?php echo $this->conv->tis620_to_utf8(_lang('consinee')); ?></th><!-- Add By Akkarapol, 12/09/2013, ใช้ Consignee แทน Supplier-->
                <th><?php echo $this->conv->tis620_to_utf8(_lang('product_code')); ?></th>
                <th><?php echo $this->conv->tis620_to_utf8(_lang('product_name')); ?></th>
                <th><?php echo $this->conv->tis620_to_utf8(_lang('reserve_qty')); ?></th>
                <th><?php echo $this->conv->tis620_to_utf8(_lang('transfer_date')); ?></th>
                <th><?php echo $this->conv->tis620_to_utf8(_lang('transfer_qty')); ?></th>
                <th><?php echo $this->conv->tis620_to_utf8(_lang('remark')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sumReceiveQty = 0;     //add $sumReservQty variable for calculate total Receive qty : by kik : 31-10-2013
            $sumDispatchQty = 0;    //add $sumReceiveQty variable for calculate total Dispatch qty : by kik : 31-10-2013

            foreach ($data as $key => $value) {
                $i = $key + 1;
                ?>
                <tr>
                    <td ><?php echo (!empty($value->Pallet_Code) ? 'Pallet Code : ' . $value->Pallet_Code : ''); ?></td> <!-- Add By Akkarapol, 04/02/2014, เพิ่ม column 'Pallet_Code' เข้าไปแสดงผลด้วย -->
                    <td ><?php echo $value->Estimate_Action_Date; ?></td>
                    <!--COMMENT BY POR 2013-10-08 เปลี่ยนจากแสดง Doc_Refer_Int เป็น Doc_Refer_Ext แทน
                    <!--<td><?php echo $value->Doc_Refer_Int; ?></td>-->
                    <!--END COMMENT-->

                    <!--BY POR 2013-10-08 แสดงค่า Doc_Refer_Ext-->
                    <!--START-->
                    <td><?php echo $value->Doc_Refer_Ext; ?></td>
                    <!--END-->
                    <!--<td><?php echo $value->supplier; ?></td>--><!-- Comment By Akkarapol, 12/09/2013, จะต้องใช้เป็น consignee แทน-->
                    <td><?php echo $value->consignee; ?></td><!-- Add By Akkarapol, 12/09/2013, ใช้ consignee แทน supplier-->
                    <td><?php echo $value->Product_Code; ?></td>
                    <!-- <td align="left"><?php echo $this->conv->tis620_to_utf8($value->Product_NameEN); ?></td> -->
                    <td style='text-align: left'><?php echo $value->Product_NameEN; ?></td>
                    <td style='text-align: right'><?php echo set_number_format($value->Reserv_Qty); ?> </td>
                    <td><?php echo $value->Actual_Action_Date; ?></td>

                    <td style='text-align: right'><?php echo set_number_format($value->Confirm_Qty); ?></td>
                    <td><?php echo $value->Remark; ?></td>
                </tr>

    <?php
    $sumReceiveQty+=@$value->Reserv_Qty;     // Add $sumReceiveQty for calculate total qty : by kik : 31-10-2013
    $sumDispatchQty+=@$value->Confirm_Qty;     // Add $sumDispatchQty for calculate total qty : by kik : 31-10-2013
}
?>

        </tbody>

        <!-- show total qty : by kik : 31-10-2013-->
        <tfoot>
            <tr>
                <th colspan="6" class ='ui-state-default indent'  style='text-align: center;'><b>Total</b></th>
                <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sumReceiveQty); ?></th>
                <th class ='ui-state-default indent' ></th>
                <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sumDispatchQty); ?></th>
                <th class ='ui-state-default indent' ></th>

            </tr>
        </tfoot>
        <!-- end show total qty : by kik : 31-10-2013-->


    </table>
    <!--COMMENT BY POR 2013-11-05 ยกเลิกการใช้ปุ่มแสดง report หน้านี้ แต่ไป ให้ไปแสดงใน workflow_template แทน
    <div align="center" style="margin-top:10px;">
            <input type="button" value="Export To PDF" class="button orange" onClick="exportFile('PDF')"  />
             &emsp;&emsp;
            <input type="button" value="Export To Excel" class="button orange" onClick="exportFile('EXCEL')" />
    </div>
    -->
</form>