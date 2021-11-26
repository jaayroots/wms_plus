<?php
# Create by Ton! 20130902
?>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>
<script>
    $(document).ready(function() {
        $('#prodDataTable').dataTable({
            "bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
            "oSearch": {"sSearch": ""},
            "aoColumns": [
                {"sWidth": "","sClass": "center"},
                {"sWidth": "","sClass": "left"},
                {"sWidth": "","sClass": "left"},
                {"sWidth": "","sClass": "left"},
                {"sWidth": "","sClass": "left"},
                {"sWidth": "","sClass": "left"},
                {"sWidth": "","sClass": "left"},
                {"sWidth": "","sClass": "left"},
                {"sWidth": "","sClass": "center"},
                {"sWidth": "","sClass": "center"},
            ],
            "sPaginationType": "full_numbers"});
    });
</script>

<table id="prodDataTable" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="prodDataTable_info">
    <thead>
        <tr>
            <th>Running No.</th>
            <th>Warehouse</th>
            <th>Zone</th>
            <th>Location</th>
            <th>Category</th>
            <th>Code</th>
            <th>Product Name</th>
            <th>Status</th>
            <th>EDIT</th>
            <th>DEL</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $count = 1;
        if (count($paRlueList) > 0) {
            foreach ($paRlueList as $value) {
                ?>
                <tr>
                    <td><?php echo $count ?></td>
                    <td><?php echo $this->encode->tis620_to_utf8($value->warehouse); ?></td>
                    <td><?php echo $this->encode->tis620_to_utf8($value->zone); ?></td>
                    <td><?php echo $this->encode->tis620_to_utf8($value->Location_Code); ?></td>
                    <td><?php echo $this->encode->tis620_to_utf8($value->category); ?></td>
                    <td><?php echo $this->encode->tis620_to_utf8($value->code); ?></td>
                    <td><?php echo $this->encode->tis620_to_utf8($value->name); ?></td>
                    <td><?php echo $this->encode->tis620_to_utf8($value->Product_status); ?></td>
                    <td><a ONCLICK="openForm('user','putaway/editProductToLocation?id=<?php echo $value->Location_Id; ?>','A','')" ><?php echo img("css/images/icons/edit.png"); ?></a<?php img("css/images/icons/edit.png"); ?></td>
                    <td><a ONCLICK="confirmDelete(<?php echo $value->Id; ?>,<?php echo $value->Location_Id; ?>);" ><?php echo img("css/images/icons/del.png"); ?></a></td>
                </tr>
                <?php
                $count++;
            }
        }
        ?>
    </tbody>
</table>
