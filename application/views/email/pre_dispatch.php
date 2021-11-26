<!DOCTYPE html>
<html>
    <head>
        <title>Support Email Template</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <style type="text/css">
            /* CLIENT-SPECIFIC STYLES */
            body, table, td, a{-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;}
            table, td{mso-table-lspace: 0pt; mso-table-rspace: 0pt;}
            img{-ms-interpolation-mode: bicubic;}

            /* RESET STYLES */
            img{border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none;}
            table{border-collapse: collapse !important;}
            body{height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important;}

            /* iOS BLUE LINKS */
            a[x-apple-data-detectors] {
                color: inherit !important;
                text-decoration: none !important;
                font-size: inherit !important;
                font-family: inherit !important;
                font-weight: inherit !important;
                line-height: inherit !important;
            }

            .padding {padding: 10px;}

            /* MOBILE STYLES */
            @media screen and (max-width: 525px) {

                /* ALLOWS FOR FLUID TABLES */
                .wrapper {
                    width: 100% !important;
                    max-width: 100% !important;
                }

                /* ADJUSTS LAYOUT OF LOGO IMAGE */
                .logo img {
                    margin: 0 auto !important;
                }

                /* USE THESE CLASSES TO HIDE CONTENT ON MOBILE */
                .mobile-hide {
                    display: none !important;
                }

                .img-max {
                    max-width: 100% !important;
                    width: 100% !important;
                    height: auto !important;
                }

                /* FULL-WIDTH TABLES */
                .responsive-table {
                    width: 100% !important;
                }

                /* UTILITY CLASSES FOR ADJUSTING PADDING ON MOBILE */
                .padding {
                    padding: 10px 5% 15px 5% !important;
                }

                .padding-meta {
                    padding: 30px 5% 0px 5% !important;
                    text-align: center;
                }

                .padding-copy {
                    padding: 10px 5% 10px 5% !important;
                    text-align: center;
                }

                .no-padding {
                    padding: 0 !important;
                }

                .section-padding {
                    padding: 50px 15px 50px 15px !important;
                }

                /* ADJUST BUTTONS ON MOBILE */
                .mobile-button-container {
                    margin: 0 auto;
                    width: 100% !important;
                }

                .mobile-button {
                    padding: 15px !important;
                    border: 0 !important;
                    font-size: 16px !important;
                    display: block !important;
                }

            }

            /* ANDROID CENTER FIX */
            div[style*="margin: 16px 0;"] { margin: 0 !important; }
        </style>
    </head>
    <body style="margin: 0 !important; padding: 0 !important;">
        <!-- HEADER -->
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td bgcolor="#ffffff" align="center" style="padding: 15px;">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 800px;" class="responsive-table">
                        <tr>
                            <td>Overview<br/>
                                <table border="1" style="border: 1px solid; width: 100%;">
                                    <tr>
                                        <th class="padding">Document No.</th>
                                        <th class="padding">All Item</th>
                                        <th class="padding">Success</th>
                                        <th class="padding">Failure</th>
                                    </tr>
                                    <?php foreach ($header as $idx => $val) : ?>
                                    <tr>
                                        <td class="padding"><?php echo $val->Doc_Refer_Ext ?></td>
                                        <td class="padding"><?php echo $val->sum_order ?></td>
                                        <td class="padding"><?php echo $val->sum_success ?></td>
                                        <td class="padding"><?php echo $val->sum_unsuccess ?></td>
                                    </tr>
                                    <?php endforeach;?>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>Detail<br/>
                                <table border="1" style="border: 1px solid; width: 100%;">
                                    <tr>
                                        <th class="padding">Document No.</th>
                                        <th class="padding">Material No.</th>
                                        <th class="padding">Qty</th>
                                        <th class="padding">Remark</th>
                                    </tr>
                                    <?php foreach ($detail as $idx => $val) : ?>
                                    <tr>
                                        <td class="padding"><?php echo $val['Doc_Refer_Ext'] ?></td>
                                        <td class="padding"><?php echo $val['Product_Code'] ?> (<?php echo $val['Product_Name'] ?>)</td>
                                        <td class="padding"><?php echo $val['Reserv_Qty'] ?></td>
                                        <td class="padding"><?php echo $val['IMP_Remark'] ?></td>
                                    </tr>
                                    <?php endforeach;?>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
