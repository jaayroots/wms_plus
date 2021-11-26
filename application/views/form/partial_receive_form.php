<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.datepicker.js" ?>"></script>-->
<script>
    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'Unlock Partial'; // เปลี่ยนให้เป็นตาม Flow ที่ทำอยู่เช่น Pre-Dispatch, Adjust Stock, Stock Transfer เป็นต้น
    var data_table_id_class = ''; // เปลี่ยนให้เป็นตาม id หรือ class ของ data table โดยถ้าเป็น id ก็ใส่ # เพิ่มไป หรือถ้าเป็น class ก็ใส่ . เพิ่มไปเหมือนการเรียกใช้ด้วย javascript ปกติ
    var redirect_after_save = site_url + "/partial_receive"; // เปลี่ยนให้เป็นตาม url ของหน้าที่ต้องการจะให้ redirect ไป โดยปกติจะเป็นหน้า list ของ flow นั้นๆ
    var global_module = '';
    var global_sub_module = '';
    var global_action_value = '';
    var global_next_state = '';
    var global_data_form = '';

    var form_name = "action_form";
    $(document).ready(function() {
        initProductTable();
    });
    function initProductTable() {
        $('#showOrderTable').dataTable({
            "bJQueryUI": true,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>'
        });
    }

    function postRequestAction(module, sub_module, action_value, next_state, elm) {
	global_module = module;
	global_sub_module = sub_module;
	global_action_value = action_value;
	global_next_state = next_state;
	curent_flow_action = $(elm).data('dialog');

//        if (confirm("Are you sure to action " + action_value + "?")) {
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

            global_data_form = $("#" + form_name).serialize();
            var message = "";

            var mess = '<div id="confirm_text"> Are you sure to do following action : ' + curent_flow_action + '?</div>';
            $('#div_for_alert_message').html(mess);
            $('#div_for_modal_message').modal('show').css({
                'margin-left': function() {
                    return ($(window).width() - $(this).width()) / 2;
                }
            });
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/flow/flowPartialReceiveList";
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
    <form id="action_form" method=post action="" class="">
        <?php if (!isset($process_type)) {
            $process_type = $data_form['process_type'];
        } ?>
        <?php echo form_hidden('process_id', $process_id); ?>
        <?php echo form_hidden('present_state', $present_state); ?>
        <?php echo form_hidden('user_id', $user_id); ?>
<?php echo form_hidden('process_type', $process_type); ?>
<?php echo form_hidden('owner_id', $owner_id); ?>
        <fieldset class="well" >
            <legend>&nbsp;List of Partial Receive Document &nbsp;</legend>  <!--Edit By Akkarapol, 16/09/2013, แก้ไขจากคำว่า Pending เป็นคำว่า Partial Receive-->
            <table width="100%" cellpadding="2" cellspacing="2">
                <tr>
                    <td>
                        <table align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showOrderTable" >
                            <thead>
                                <tr>
                                    <th>Selection</th>
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
                                        $str_body .= "<tr class=\"list_row_click select_list\">";
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
<script>
var flag_refresh = true;
</script>
<?php $this->load->view('element_modal_message_alert'); ?>