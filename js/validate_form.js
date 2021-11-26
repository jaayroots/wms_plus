/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var global_rowData = '';
var global_return = new Object();

function check_receive_type() {

    // Set Variable
    var receive_type = $("#receive_type").val();
    var is_pending = $("[name='is_pending']").prop('checked');
    var is_repackage = $("[name='is_repackage']").prop('checked');
    var return_val = new Object();
    var tmp_return = new Object();

    return_val.critical = [];
    return_val.warning = [];
    return_val.success = [];

    for (i in global_rowData) {
        var line_number = parseInt(i) + 1;
        if (receive_type === 'RCV001') { // Receive Type == Normal

            if (is_pending) {
                prod_status = global_rowData[i][ci_prod_status];
                prod_sub_status = global_rowData[i][ci_prod_sub_status];
                if (prod_status != "PENDING") {
                    tmp_return = {
                        message: "Product Status Must be 'Pending' Only!! , Check in line No. " + line_number + " ",
                        row: i,
                        col: global_ci_prod_status_label
                    };
                    return_val.critical.push(tmp_return);
                }
                if (prod_sub_status == global_code_sub_status_return || prod_sub_status == global_code_sub_status_repackage) {
                    tmp_return = {
                        message: "Product Sub Status 'Return' or 'Repackage' not Allow  !! , Check in line No. " + line_number + " ",
                        row: i,
                        col: global_ci_prod_sub_status_label
                    };
                    return_val.critical.push(tmp_return);
                }
            } else {
                prod_status = global_rowData[i][ci_prod_status];
                prod_sub_status = global_rowData[i][ci_prod_sub_status];
                if (prod_status == "PENDING") {
                    tmp_return = {
                        message: "Product Status 'Pending' not Allow !! , Check in line No. " + line_number + " ",
                        row: i,
                        col: global_ci_prod_status_label
                    };
                    return_val.critical.push(tmp_return);
                }
                if (prod_sub_status == global_code_sub_status_return || prod_sub_status == global_code_sub_status_repackage) {
                    tmp_return = {
                        message: "Product Sub Status 'Return' or 'Repackage' not Allow  !! , Check in line No. " + line_number + " ",
                        row: i,
                        col: global_ci_prod_sub_status_label
                    };
                    return_val.critical.push(tmp_return);
                }
            }

        } else if (receive_type === 'RCV002') { // Receive Type == Return

            if (is_repackage) {
                prod_status = global_rowData[i][ci_prod_status];
                prod_sub_status = global_rowData[i][ci_prod_sub_status];
                if (prod_status == "PENDING") {
                    tmp_return = {
                        message: "Product Status 'Pending' not Allow !! , Check in line No. " + line_number + " ",
                        row: i,
                        col: global_ci_prod_status_label
                    };
                    return_val.critical.push(tmp_return);
                }
                if (prod_sub_status != global_code_sub_status_repackage) {
                    tmp_return = {
                        message: "Product Sub Status Must be 'Repackage' Only!! , Check in line No. " + line_number + " ",
                        row: i,
                        col: global_ci_prod_sub_status_label
                    };
                    return_val.critical.push(tmp_return);
                }
            } else {
                prod_status = global_rowData[i][ci_prod_status];
                prod_sub_status = global_rowData[i][ci_prod_sub_status];
                if (prod_status == "PENDING") {
                    tmp_return = {
                        message: "Product Status 'Pending' not Allow !! , Check in line No. " + line_number + " ",
                        row: i,
                        col: global_ci_prod_status_label
                    };
                    return_val.critical.push(tmp_return);
                }
                if (prod_sub_status != global_code_sub_status_return) {
                    tmp_return = {
                        message: "Product Sub Status Must be 'Return' Only!! , Check in line No. " + line_number + " ",
                        row: i,
                        col: global_ci_prod_sub_status_label
                    };
                    return_val.critical.push(tmp_return);
                }
            }

        } else { // Receive Type == Adjust and Other.
            prod_status = global_rowData[i][ci_prod_status];
            prod_sub_status = global_rowData[i][ci_prod_sub_status];
            if (prod_status == "PENDING") {
                tmp_return = {
                    message: "Product Status 'Pending' not Allow !! , Check in line No. " + line_number + " ",
                    row: i,
                    col: global_ci_prod_status_label
                };
                return_val.critical.push(tmp_return);
            }
            if (prod_sub_status == global_code_sub_status_return || prod_sub_status == global_code_sub_status_repackage) {
                tmp_return = {
                    message: "Product Sub Status 'Return' or 'Repackage' not Allow  !! , Check in line No. " + line_number + " ",
                    row: i,
                    col: global_ci_prod_sub_status_label
                };
                return_val.critical.push(tmp_return);
            }
        }
    }

    global_return.status = 'validation';
    global_return.return_val = return_val;

//    validation_data();

}


function check_dispatch_type_when_submit_form() {  
    
    // Set Variable
    var dp_type_txt = $.trim($('#dispatch_type_select :selected').text());
    var dp_type = dp_type_link_prod_status[dp_type_txt];
    var return_val = new Object();
    var tmp_return = new Object();

    return_val.critical = [];
    return_val.warning = [];
    return_val.success = [];
    
    for (i in global_rowData) {
        var line_number = parseInt(i) + 1;
        
        if (dp_type != undefined) {
            if (dp_type != global_rowData[i][global_ci_prod_status_label]) {
                tmp_return = {
                    message: "You choose Dispatch Type = '"+dp_type_txt+"', This mean Product Status Must be '"+dp_type+"' Only!! , Check in line No. " + line_number + " ",
                    row: i,
                    col: global_ci_prod_status_label
                };
                return_val.critical.push(tmp_return);
            }
        }
        
    }

    global_return.status = 'validation';
    global_return.return_val = return_val;

//    validation_data();
}