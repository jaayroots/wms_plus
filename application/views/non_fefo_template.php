<?php include "header.php" ?>
<TR class="content" style='height:100%' valign="top">
    <TD>
        <table class="roundedTable" border="0" cellpadding="0" cellspacing="0" >
            <thead>
                <tr align="left">
                    <th align="left">{menu_title} <i class="icon-list icon-white"></i> </th>
                </tr> 
            </thead>
            <tbody>
                <!-- <tr><td>{datatable}</td></tr> -->
                <tr><td>{form}</td></tr>

            </tbody>
        </table>
    </TD>
</TR>
<?php $this->load->view('element_modal_message_alert'); ?>
<?php include "footer.php" ?>
 <!-- <input type="button" id="btn_export_excel" class="button orange" value= "Export To Excel" style="margin: 10px;"> -->
 <div style="width: 100%; height: 30px; background-color: #C7C8C1; position: fixed; top: 100%;  margin-top: -50px; text-align: center; padding: 10px;">
        <!-- &nbsp;{button_action}&emsp; -->
        <?php 
        
                        if(isset($button_export_excel)){
                            echo '{button_export_excel}';}
        ?>
<script>

     function exportFileExcel() {
        window.open('<?php echo site_url(); ?>/non_fefo_report/exportExcelNonFEFO');

    }
</script>
