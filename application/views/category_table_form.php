<?php
# Tang edit style table 10/05/2013
?>

<!-- Comment Out by Ton! 20130814 -->
<!--<script language="JavaScript" type="text/javascript" src="<?php //echo base_url() . "datatables/media/js/jquery.dataTables.editable.js"       ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php //echo base_url() . "datatables/media/js/jquery.jeditable.js"       ?>"></script>-->

<script>
    $(document).ready(function() {
        $('#defDataTable2').dataTable({
            "bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
            "oSearch": {"sSearch": ""},
            "aoColumns": [
                {"sWidth": "50px;", "sClass": "center"},
                {"sWidth": "150px;", "sClass": "left"},
                {"sWidth": "", "sClass": "left"}
            ],
            "sPaginationType": "full_numbers"});
    });

    $('#check_all').change(function() {
        if ($('#check_all').is(':checked') == true) {
            $('input[id^=code-]').attr('checked', true);
        } else {
            $('input[id^=code-]').attr('checked', false);
        }
    });
</script>

<table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
    <?php
    $extra_disabled = "";
    $extra_active = "";
    if ($mode == 'V'):
        $extra_disabled = " disabled=disabled ";
    endif;
    ?>

    <thead>
        <tr>
            <th>Select All <input type="checkbox" name="check_all" id="check_all" <?php echo $extra_disabled ?>></th>
            <th>Category Code</th>
            <th>Category Name</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 1;
        foreach ($cateList as $value) :
            ?>
            <tr>
                <td><input type="checkbox" name="category_code[]" id="code-<?php echo $i; ?>" value="<?php echo $value->Dom_ID; ?>"<?php if (in_array($value->Dom_ID, $cateIDList)) echo 'checked' ?> <?php echo $extra_disabled ?>/></td>
                <td><?php echo $this->encode->tis620_to_utf8($value->Dom_Code); ?></td>
                <td><?php echo $this->encode->tis620_to_utf8($value->Dom_EN_Desc); ?></td>
            </tr>
            <?php
            $i++;
        endforeach;
        ?>
    </tbody>
</table>
