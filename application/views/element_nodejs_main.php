<?php if ($this->config->item('node_js_active')): ?>
    <script src="<?php echo $this->config->item('node_js_url'); ?>/socket.io/socket.io.js"></script>
    <script>
        var uniqid = '<?php echo uniqid() ?>';
        var socket = io.connect('<?php echo $this->config->item('node_js_url'); ?>',{secure: true});
        var db_config = {
            server: '<?php echo str_replace(',1433', '', str_replace('tcp:', '', $this->session->userdata('db_hostname'))); ?>',
            user: '<?php echo $this->session->userdata('db_username'); ?>',
            password: '<?php echo $this->session->userdata('db_password'); ?>',
            database: '<?php echo $this->session->userdata('db_database'); ?>'
        };
        var user_id = '<?php echo $this->session->userdata('user_id'); ?>';
        var username = '<?php echo $this->session->userdata('username'); ?>';
        var time = '<?php echo time(); ?>';

    <?php if ($this->uri->uri_string == 'users/login'): ?>



    <?php else: ?>

            
            socket.emit('add_me_to_users', db_config, uniqid, username);

            socket.emit('user_authen_initial', db_config, uniqid, user_id, time);
            socket.on('user_authen_initial', function(data) {
                if (data.uniqid == uniqid) {
                    if(flag_re_login != -1){
                        flag_re_login = -1;
                        var mess = '';
                        var login_timeout_alert = 'Login Expire You Want to Re-Login ?';
                        mess += '<div id="div_unsuccess">';
                        mess += '<h4>critical</h4>- ' + login_timeout_alert + '</div>';
                        $('#div_for_alert_message').html(mess);
                        $('#btn_confirm_alert_message').attr('Onclick','re_login_already('+data.Log_Id+');');
                        $('#btn_confirm_alert_message').show();
                        $('#div_for_modal_message').modal('show').css({
                            'margin-left': function() {
                                return ($(window).width() - $(this).width()) / 2;
                            }
                        });
                        start_count = 60;
                        countdown();
                    }
                }
            });
            
            function re_login_already(Log_Id){
                socket.emit('re_login_already', db_config, uniqid, Log_Id);
            }
            
            socket.on('re_login_already', function(data) {
                if (data.uniqid == uniqid) {
                    flag_re_login = 1;
                    clearTimeout(timer);
                    $('#span_text_modal_alert').text('');
                    var mess = '';
                    var re_login_alert = 'Re-Login Success.';
                    mess += '<div id="div_success">';
                    mess += '<h4>success</h4>- ' + re_login_alert + '</div>';
                    $('#div_for_alert_message').html(mess);
                    $('#btn_confirm_alert_message').hide();
                    $('#div_for_modal_message').modal('show').css({
                        'margin-left': function() {
                            return ($(window).width() - $(this).width()) / 2;
                        }
                    });
                    
                    // Auto Hide
                    setTimeout(function() {
                        $("#div_for_modal_message").modal('hide');
                    }, config_auto_hide);
                    // End
                    

                }
            });
            
            
            
        
            socket.on('update_est_balance', function(data) {
//            console.log(data);
                if (JSON.stringify(data.db_config) == JSON.stringify(db_config)) {
                    $(".est_of_inbound_"+data.inbound_id).html(set_number_format(data.est_balance));
                }
            });
            
     

    <?php endif; ?>

    </script>
<?php endif; ?>