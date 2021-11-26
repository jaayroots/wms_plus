<script>

    var form_name = '#form_<?php echo $form_name; ?>';
    var action_pdf = '<?php echo $action_pdf; ?>';
    var action_excel = '<?php echo $action_excel; ?>';

    $(document).ready(function() {

        $(form_name).submit(function() {
            if (validateForm() === true) {
                if ($(form_name).attr('action') == '') {
                    if (action_pdf != '') {
                        exportFile('PDF');
                    } else {
                        exportFile('EXCEL');
                    }
                }
            } else {
                return false;
            }
        });

    });

    function validateForm() {

        if (form_name == '#form_picking_job') {
            $(form_name).validate({
                rules: {
                    document_no: {required: true}
                }
            });
        }
        
        if (form_name == '#form_putaway_job') {
            $(form_name).validate({
                rules: {
                    document_no: {required: true}
                }
            });
        }

        return $(form_name).valid();
    }

    function exportFile(file_type) {

        if (validateForm() === true) {
            $(form_name).attr('target', "_blank");
            if (file_type == 'EXCEL') {
                $(form_name).attr('action', "<?php echo site_url(); ?>/" + action_excel)
            } else {
                $(form_name).attr('action', "<?php echo site_url(); ?>/" + action_pdf)
            }
            $(form_name).submit();

        }

    }

</script>
<style>
    #report{
        margin:5px;
        text-align:center;
    }
</style>
<TR class="content" style='height:100%' valign="top">
    <TD>

        <form class="<?php echo config_item("css_form"); ?>" action="" method="POST" id="form_<?php echo $form_name; ?>" name="form_<?php echo $form_name; ?>" >
            <fieldset style="margin:0px auto;">
                <legend> Criteria </legend>

                <?php if ($form_name == 'picking_job'): ?>
                    <table cellpadding="3" cellspacing="1" style="width:auto; margin:10px;" >
                        <tr>
                            <td style="min-width: 70px">Document_No</td>
                            <td style="min-width: 40px; text-align: center;"> : </td>
                            <td><?php echo form_input('document_no', '', 'id="document_no class="required" '); ?></td>
                        </tr>
                        <tr>
                            <td>Show Footer</td>
                            <td style="text-align: center;"> : </td>
                            <td><?php echo form_checkbox('showfooter', 'show', TRUE, 'id="showfooter"'); ?></td>
                        </tr>
                    </table>
                <?php endif; ?>

                <?php if ($form_name == 'putaway_job'): ?>
                    <table cellpadding="3" cellspacing="1" style="width:auto; margin:10px;" >
                        <tr>
                            <td style="min-width: 70px">Document_No</td>
                            <td style="min-width: 40px; text-align: center;"> : </td>
                            <td><?php echo form_input('document_no', '', 'id="document_no class="required" '); ?></td>
                        </tr>
                        <tr>
                            <td>Show Footer</td>
                            <td style="text-align: center;"> : </td>
                            <td><?php echo form_checkbox('showfooter', 'show', TRUE, 'id="showfooter"'); ?></td>
                        </tr>
                    </table>
                <?php endif; ?>

            </fieldset>
        </form>
    </TD>
</TR>