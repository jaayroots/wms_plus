<script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/validate_data.js") ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/validate_form.js") ?>"></script>

<?php if ($this->config->item('node_js_active')): ?>
    <script src="<?php echo $this->config->item('node_js_url'); ?>/socket.io/socket.io.js"></script>
    <script>
//        var socket = io.connect('<?php echo $this->config->item('node_js_url'); ?>');
//        var db_config = {
//            server: '<?php // echo $this->session->userdata('db_hostname'); ?>',
//            user: '<?php // echo $this->session->userdata('db_username'); ?>',
//            password: '<?php // echo $this->session->userdata('db_password'); ?>',
//            database: '<?php // echo $this->session->userdata('db_database'); ?>'
//        };
        var global_flow_id = '<?php echo @$flow_id; ?>';

        socket.on('confirm_approve_flow', function(data) {

            var mess = '';
            if (data.flow_id != '') {
                if (JSON.stringify(data.db_config) == JSON.stringify(db_config)) {
                    if (data.flow_id == '<?php echo @$flow_id ?>') {
                        flag_confirm_save_data = false;
                        var confirm_approve_flow_alert = 'This flow had ' + data.activity + ', Activity by "' + data.activity_by + '"';
                        mess += '<div id="div_unsuccess">';
                        mess += '<h4>critical</h4>- ' + confirm_approve_flow_alert + '</div>';
                        $('#div_for_alert_message').html(mess);
                        $('#div_for_modal_message').modal('show').css({
                            'margin-left': function() {
                                return ($(window).width() - $(this).width()) / 2;
                            }
                        });
                        $('input[name=action_type]').hide();
                    }
                }
            }
        });
    </script>

<?php endif; ?>
<script>

    var flag_redirect = false;
    var flag_confirm_save_data = true;
    var flag_re_login = 0;

    // if not set overide use default is false;
    if(typeof flag_refresh === 'undefined'){
        var flag_refresh = false;
    };

    $(document).ready(function() {

        $('#div_for_modal_message').on('hidden.bs.modal', function() {
            if (flag_redirect != false) {

                if (<?php echo ($this->config->item('node_js_active') ? 1 : 0); ?>) {
                    socket.emit('confirm_approve_flow', db_config, global_flow_id, curent_flow_action, '<?php echo $this->session->userdata('username'); ?>');
                }

                window.onbeforeunload = null;
                redirect(flag_redirect);

            }else if(flag_re_login == -1){
                window.onbeforeunload = null;
                redirect("<?php echo site_url(); ?>/authen/logout");
            } else if (flag_refresh) {
                location.reload();
            }
        });

    });

    function check_confirm_save_data() {
        if (flag_confirm_save_data) {
            confirm_save_data();
        } else {
            window.onbeforeunload = null;
            redirect(redirect_after_save);
        }
    }


</script>

<style>
    #div_for_modal_message {
        display: none;
        margin-left: 0;
        top: 55%;
        left:0;
        max-width: 70%;
        width: auto;
    }

    #div_for_alert_message h4{
        text-transform:capitalize;
    }

    #div_for_alert_message{
        padding: 5px;
    }

    #div_warning{
        color:#DB3D14;
        background-color: #FDF8EC;
        border:1px solid;
        border-color: #f9dd97;
        border-left: 10px solid #DB3D14;
        padding: 5px;
        margin: 5px;
        min-height: 50px;
        min-width: 250px;
    }

    #div_success{
        color:#00781a;
        background-color: #F3FAEF;
        border:1px solid;
        border-color: #ade18f;
        border-left: 10px solid #00781a;
        padding: 5px;
        margin: 5px;
        min-height: 50px;
        min-width: 250px;
    }

    #div_unsuccess{
        color:#BB0000;
        background-color: #FCF4F2;
        border:1px solid;
        border-color: #f8d1c7;
        border-left: 10px solid #BB0000;
        padding: 5px;
        margin: 5px;
        min-height: 50px;
        min-width: 250px;
    }

    #div_txtalert{
        color:black;
        background-color: #e8e6e9;
        border:1px solid;
        border-color: #bbb6be;
        border-left: 10px solid gray;
        padding: 5px;
        margin: 5px;
        min-height: 50px;
        min-width: 250px;
    }

    #div_textalert{
        color:#BB0000;
        background-color: #FCF4F2;
        border:1px solid;
        border-color: #f8d1c7;
        padding: 10px;
        margin: 5px;
    }

    .modal-footer{
        padding: 10px 10px 10px;
    }
    .lable_err{
        border: 1px solid #FF0000 !important;
    }

    #div_for_alert_message h4{
        text-transform:capitalize;
    }

    #confirm_text {
        font-size:1.1em;
        padding:8px;
        font-weight:bold;
    }

</style>
<div  class="modal bigsize fade" id="div_for_modal_message" tabindex="-1" role="dialog" aria-labelledby="div_for_modal_message_LargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-body" id="div_for_alert_message"  class="well" style="max-width: none;">
                ...
            </div>
            <div class="modal-footer">
                <span id="span_text_modal_alert" style="margin:0 20px 0 0;font-weight: bold;font-size: 1.3em; color: red;"></span>
                <button type="button" class="btn btn-default" id="btn_confirm_alert_message" onclick="check_confirm_save_data();">Confirm</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>