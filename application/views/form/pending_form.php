<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.datepicker.js" ?>"></script>-->
<script>
    var form_name = "form_pending";
    $(document).ready(function() {
        initProductTable();
    });
    function initProductTable() {
        $('#showOrderTable').dataTable({
            "bJQueryUI": true,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "bAutoWidth": false,            
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>'
        });
    }

    function postRequestAction(module, sub_module, action_value, next_state, elm) {
		curent_flow_action = $(elm).data('dialog');
    	if (confirm("Are you sure to action " + action_value + "?")) {
            var f = document.getElementById(form_name);
            var actionType = document.createElement("input");
            actionType.setAttribute('type', "hidden");
            actionType.setAttribute('name', "action_type");
            actionType.setAttribute('value', action_value);
            f.appendChild(actionType);

            var toStateNo = document.createElement("input");
            toStateNo.setAttribute('type', "hidden");
            toStateNo.setAttribute('name', "next_state");
            toStateNo.setAttribute('value', next_state);
            f.appendChild(toStateNo);

            var data_form = $("#" + form_name).serialize();
            var message = "";
            $.post("<?php echo site_url(); ?>" + "/" + module + "/" + sub_module, data_form, function(data) {
                switch (data.status) {
                    case 'C001':
                        message = "Save Unlock Pending  Complete";
                        break;
                    case 'C002':
                        message = "Confirm Unlock Pending  Complete";
                        break;
                    case 'C003':
                        message = "Approve Unlock Pending  Complete";
                        break;
                    case 'E001':
                        message = "Save Unlock Pending Incomplete";
                        break;
                }
                alert(message);
                url = "<?php echo site_url(); ?>/flow/flowPendingList";
                redirect(url)
            }, "json");
        }
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/flow/flowPendingList";
            redirect(url)
        }
    }

</script>
<style>
    #myModal {
        width: 900px;	/* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -450px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */
    }
</style>
<div class="well">
    <form id="form_pending" method=post action="" class="">
<?php if (!isset($process_type)) {
    $process_type = $data_form['process_type'];
} ?>
<?php echo form_hidden('process_id', $process_id); ?>
<?php echo form_hidden('present_state', $present_state); ?>
<?php echo form_hidden('user_id', $user_id); ?>
<?php echo form_hidden('process_type', $process_type); ?>
<?php echo form_hidden('owner_id', $owner_id); ?>
        <fieldset class="well" >
            <legend>&nbsp;List of Pending Document &nbsp;</legend>
            <table width="100%" cellpadding="2" cellspacing="2">
                <tr>
                    <td>
                        <table align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showOrderTable" >
                            <thead>
                                <tr>
                                    <th>Selection</th>
<!--                                    <th>Reference External</th>
                                    <th>Reference Internal</th>-->
                                    <th>External Document</th>
                                    <th>Internal Document</th>
                                    <th>Document No.</th>
                                    <th>Receive Date</th>
                                    <!-- <td>View</td> -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($order_list)) {
                                    $str_body = "";
                                    foreach ($order_list as $rows) {
                                        $str_body .= "<tr>";
                                        $str_body .= "<td><input type=checkbox id=chkBoxVal name=chkBoxVal[] value='" . $rows->Document_No . "' ></td>";
                                        $str_body .= "<td>" . $rows->Doc_Refer_Ext . "</td>";
                                        $str_body .= "<td>" . $rows->Doc_Refer_Int . "</td>";
                                        $str_body .= "<td>" . $rows->Document_No . "</td>";
                                        $str_body .= "<td>" . $rows->Receive_Date . "</td>";
                                        //$str_body .= "<td><a ONCLICK=\"openForm()\" >".img("css/images/icons/view.png")."</a></td>";
                                        $str_body .= "</tr>";
                                    }
                                    echo $str_body;
                                }
                                ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </fieldset>
    </form>
</div>