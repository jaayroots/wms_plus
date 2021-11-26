<?php
#Tang change style table 10/05/2013
?>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>

<script>
    $(document).ready(function() {
        var oTable=$('#defDataTable2').dataTable({
            "bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
            "oSearch": {"sSearch": ""},
            "aoColumns": [
                {"sWidth": "50px;","sClass": "center"},
                {"sWidth": "150px;","sClass": "left"}
                
            ],
            "sPaginationType": "full_numbers"
        });
	
        $('#check_all').click(function(){
            var selected = new Array();
            $(oTable.fnGetNodes()).find(':checkbox').each(function () {
                $this = $(this);
                $this.attr('checked', 'checked');
                selected.push($this.val());
            });
        });

        $('#uncheck_all').click(function(){
            //$('input[id^=code-]').attr('checked',false);
            var selected = new Array();
            $(oTable.fnGetNodes()).find(':checkbox').each(function () {
                $this = $(this);
                $this.attr('checked', false);
                selected.push($this.val());
            });
        });
		
    });

	        
    function checkLocationAlready(){
        if ($('#location_already').val()=='1') {
            alert("location already.");
            $('input[id^=code-]').attr('checked',false);
        }
    }

</script>
<div style="margin:10px auto;">
    <input type="button" name="check_all" id="check_all" value="Select All" class="btn success" data-toggle="modal" role="button" />
    <input type="button" name="uncheck_all" id="uncheck_all" value="Unselect All" class="btn success" data-toggle="modal" role="button" />
</div>
<table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
    <input type="hidden" id="location_already" name="location_already" value="<?php echo $location_already ?>"/>
    <thead>
        <tr>
            <!--<th>Select All</th>--> <!-- Comment By Akkarapol, 04/09/2013, เปลี่ยนจาก Select All เป็น Please Select--> 
            <th>Please Select</th> <!-- Edit By Akkarapol, 04/09/2013, เปลี่ยนจาก Select All เป็น Please Select--> 
            <th>Storage Code</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 1;
        if (count($storage_detailList) > 0) {
            foreach ($storage_detailList as $value) {
                ?>
                <tr>
                    <td><input type="checkbox" name="storage_detail_id[]" id="code-<?php echo $i; ?>" value="<?php echo $value->Storage_Detail_Id; ?>"/></td><!--onclick="checkLocationAlready()" Comment Out by Ton! 20130517 function not complete--> 
                    <td><?php echo $this->encode->tis620_to_utf8($value->Storage_Code); ?></td>
                </tr>
                <?php $i++;
            }
        }
        ?>
    </tbody>
</table>