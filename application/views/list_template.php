<?php include "header.php" ?>
<TR class="content" style='height:100%' valign="top">
    <TD>
        <table class="roundedTable" border="0" cellpadding="0" cellspacing="0" >
            <thead>
                <tr align="left">
                    <th align="left">{menu_title} <i class="icon-list icon-white"></i> <SPAN style="float: right;">{button_add}</SPAN></th>
                </tr>
            </thead>
            <tbody>
                <tr><td></td></tr>
                <tr><td>{datatable}</td></tr>
                <?php if (isset($consolidate_picking) && ($consolidate_picking == TRUE) || isset($quick_picking_approve) && ($quick_picking_approve == TRUE) || isset($delivery_note) && ($delivery_note == TRUE) ): ?>

                    <tr>
                        <td align="center" style="position: relative;">
                            <?php if (@$quick_picking_approve): ?>
                                &nbsp; <input type="button" class="button orange quick_picking_approve" value="Quick Approve">
                            <?php endif; ?>
                            <?php if (isset($consolidate_picking) && $consolidate_picking == TRUE): ?>
                                &nbsp; <a href="#" class="button orange print_group_picking" target="_blank" style="text-decoration: none; color: white;">Consolidate Picking</a>
                            <?php endif; ?>
                            <?php if (isset($delivery_note) && $delivery_note == TRUE): ?>
                                &nbsp; <a href="#" class="button orange print_dn_picking" target="_blank" style="text-decoration: none; color: white;">Delivery Note</a>
                            <?php endif; ?>
                        </td>
                    </tr>

                <?php endif; ?>		

                            <?php if (isset($_xml['quick_dispatch_approve']) && ($_xml['quick_dispatch_approve'] == TRUE) && ($_xml['delivery_note'] == TRUE)) : ?>
                                <tr><td align="center">&nbsp; <input type="button" class="button orange quick_dispatch_approve" value="Quick Approve" title="Approve All"> &nbsp; <a href="#" class="button orange print_group_picking" target="_blank" style="text-decoration: none; color: white;">Consolidate Picking</a> </td></tr>
                            <?php endif;?>

            </tbody>
        </table>
    </TD>
</TR>
<?php $this->load->view('element_modal_message_alert'); ?>
<?php include "footer.php" ?>

<SCRIPT>
    $(document).ready(function () {
        $(".checkbox_all").click(function () {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });
    });
</script>
