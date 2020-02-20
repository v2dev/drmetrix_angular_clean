<?php

function alert_email($user_email, $user_name, $user_id, $frequency='weekly', $report_filter, $name_of_report_filter, $id) {
    ob_start();

    $email             = $user_email;
    $images_url        = "http://".HOST ."/drmetrix/api/email-template/images/";
    $unsubscribe_url   = "http://".HOST ."/drmetrix/unsubscribe?".base64_encode("kind=subscribe_email&type=filter&type_id=$id&user_id=$user_id");
    $tracking_url      = "http://".HOST ."/drmetrix/tracking";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <meta name="viewport" content="width=device-width">
   </head>
   <body style="-moz-box-sizing: border-box; -ms-text-size-adjust: 100%; -webkit-box-sizing: border-box; -webkit-text-size-adjust: 100%; Margin: 0; box-sizing: border-box; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0; min-width: 100%; padding: 0; text-align: left; width: 100% !important;">
      <style>
         @media only screen {
         html {
         min-height: 100%;
         background: #fff;
         }
         }
         @media only screen and (max-width: 596px) {
         .small-float-center {
         margin: 0 auto !important;
         float: none !important;
         text-align: center !important;
         }
         .small-text-center {
         text-align: center !important;
         }
         .small-text-left {
         text-align: left !important;
         }
         .small-text-right {
         text-align: right !important;
         }
         }
         @media only screen and (max-width: 596px) {
         .hide-for-large {
         display: block !important;
         width: auto !important;
         overflow: visible !important;
         max-height: none !important;
         font-size: inherit !important;
         line-height: inherit !important;
         }
         }
         @media only screen and (max-width: 596px) {
         table.body table.container .hide-for-large,
         table.body table.container .row.hide-for-large {
         display: table !important;
         width: 100% !important;
         }
         }
         @media only screen and (max-width: 596px) {
         table.body table.container .callout-inner.hide-for-large {
         display: table-cell !important;
         width: 100% !important;
         }
         }
         @media only screen and (max-width: 596px) {
         table.body table.container .show-for-large {
         display: none !important;
         width: 0;
         mso-hide: all;
         overflow: hidden;
         }
         }
         @media only screen and (max-width: 596px) {
         table.body img {
         width: auto;
         height: auto;
         }
         table.body center {
         min-width: 0 !important;
         }
         table.body .container {
         width: 95% !important;
         }
         table.body .columns,
         table.body .column {
         height: auto !important;
         -moz-box-sizing: border-box;
         -webkit-box-sizing: border-box;
         box-sizing: border-box;
         padding-left: 16px !important;
         padding-right: 16px !important;
         }
         table.body .columns .column,
         table.body .columns .columns,
         table.body .column .column,
         table.body .column .columns {
         padding-left: 0 !important;
         padding-right: 0 !important;
         }
         table.body .collapse .columns,
         table.body .collapse .column {
         padding-left: 0 !important;
         padding-right: 0 !important;
         }
         td.small-1,
         th.small-1 {
         display: inline-block !important;
         width: 8.33333% !important;
         }
         td.small-2,
         th.small-2 {
         display: inline-block !important;
         width: 16.66667% !important;
         }
         td.small-3,
         th.small-3 {
         display: inline-block !important;
         width: 25% !important;
         }
         td.small-4,
         th.small-4 {
         display: inline-block !important;
         width: 33.33333% !important;
         }
         td.small-5,
         th.small-5 {
         display: inline-block !important;
         width: 41.66667% !important;
         }
         td.small-6,
         th.small-6 {
         display: inline-block !important;
         width: 50% !important;
         }
         td.small-7,
         th.small-7 {
         display: inline-block !important;
         width: 58.33333% !important;
         }
         td.small-8,
         th.small-8 {
         display: inline-block !important;
         width: 66.66667% !important;
         }
         td.small-9,
         th.small-9 {
         display: inline-block !important;
         width: 75% !important;
         }
         td.small-10,
         th.small-10 {
         display: inline-block !important;
         width: 83.33333% !important;
         }
         td.small-11,
         th.small-11 {
         display: inline-block !important;
         width: 91.66667% !important;
         }
         td.small-12,
         th.small-12 {
         display: inline-block !important;
         width: 100% !important;
         }
         .columns td.small-12,
         .column td.small-12,
         .columns th.small-12,
         .column th.small-12 {
         display: block !important;
         width: 100% !important;
         }
         table.body td.small-offset-1,
         table.body th.small-offset-1 {
         margin-left: 8.33333% !important;
         Margin-left: 8.33333% !important;
         }
         table.body td.small-offset-2,
         table.body th.small-offset-2 {
         margin-left: 16.66667% !important;
         Margin-left: 16.66667% !important;
         }
         table.body td.small-offset-3,
         table.body th.small-offset-3 {
         margin-left: 25% !important;
         Margin-left: 25% !important;
         }
         table.body td.small-offset-4,
         table.body th.small-offset-4 {
         margin-left: 33.33333% !important;
         Margin-left: 33.33333% !important;
         }
         table.body td.small-offset-5,
         table.body th.small-offset-5 {
         margin-left: 41.66667% !important;
         Margin-left: 41.66667% !important;
         }
         table.body td.small-offset-6,
         table.body th.small-offset-6 {
         margin-left: 50% !important;
         Margin-left: 50% !important;
         }
         table.body td.small-offset-7,
         table.body th.small-offset-7 {
         margin-left: 58.33333% !important;
         Margin-left: 58.33333% !important;
         }
         table.body td.small-offset-8,
         table.body th.small-offset-8 {
         margin-left: 66.66667% !important;
         Margin-left: 66.66667% !important;
         }
         table.body td.small-offset-9,
         table.body th.small-offset-9 {
         margin-left: 75% !important;
         Margin-left: 75% !important;
         }
         table.body td.small-offset-10,
         table.body th.small-offset-10 {
         margin-left: 83.33333% !important;
         Margin-left: 83.33333% !important;
         }
         table.body td.small-offset-11,
         table.body th.small-offset-11 {
         margin-left: 91.66667% !important;
         Margin-left: 91.66667% !important;
         }
         table.body table.columns td.expander,
         table.body table.columns th.expander {
         display: none !important;
         }
         table.body .right-text-pad,
         table.body .text-pad-right {
         padding-left: 10px !important;
         }
         table.body .left-text-pad,
         table.body .text-pad-left {
         padding-right: 10px !important;
         }
         table.menu {
         width: 100% !important;
         }
         table.menu td,
         table.menu th {
         width: auto !important;
         display: inline-block !important;
         }
         table.menu.vertical td,
         table.menu.vertical th,
         table.menu.small-vertical td,
         table.menu.small-vertical th {
         display: block !important;
         }
         table.menu[align="center"] {
         width: auto !important;
         }
         table.button.small-expand,
         table.button.small-expanded {
         width: 100% !important;
         }
         table.button.small-expand table,
         table.button.small-expanded table {
         width: 100%;
         }
         table.button.small-expand table a,
         table.button.small-expanded table a {
         text-align: center !important;
         width: 100% !important;
         padding-left: 0 !important;
         padding-right: 0 !important;
         }
         table.button.small-expand center,
         table.button.small-expanded center {
         min-width: 0;
         }
         }
      </style>
      <table class="body" data-made-with-foundation="" style="Margin: 0; background: #fff; border-collapse: collapse; border-spacing: 0; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; height: 100%; line-height: 1.3; margin: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
         <tbody>
            <tr style="padding: 0; text-align: left; vertical-align: top;">
               <td class="float-center" align="center" valign="top" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #0a0a0a; float: none; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 0; text-align: center; vertical-align: top; word-wrap: break-word;">
                  <center data-parsed="" style="min-width: 580px; width: 100%;">
                     <!-- move the above styles into your custom stylesheet -->
                     <!-- logo and heading -->
                     <table align="center" style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                        <tbody>
                           <tr style="padding: 0; text-align: left; vertical-align: top;">
                              <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
                                 <table class="row logo-container" style="border-collapse: collapse; border-spacing: 0; margin: 20px 0; padding: 0; position: relative; text-align: left; vertical-align: top; width: 100%;">
                                    <tbody>
                                       <tr style="padding: 0; text-align: left; vertical-align: top;">
                                          <th style="Margin: 0; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0; padding: 0; text-align: left;">
                                             <table class="logo-with-contents" style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top;">
                                                <tbody>
                                                   <tr style="padding: 0; text-align: left; vertical-align: top;">
                                                      <th class="small-12 columns" style="Margin: 0 auto; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 0; padding-bottom: 16px; text-align: left;">
                                                         <img class="small-float-center logo-img" src="<?php echo "http://".HOST;?>/drmetrix/assets/img/logo_email_new.png" style="-ms-interpolation-mode: bicubic; border: 1px solid #ccc; clear: both; display: block;outline: none; text-decoration:
                                                            none;"/>
                                                      </th>
                                                      <th class="small-12 columns" style="Margin: 0 auto; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 40px; text-align: left; vertical-align: middle;">
                                                         <h4 class="small-text-center name-details" style="Margin: 0; Margin-bottom: 10px; color: #202b39; font-family: Helvetica, Arial, sans-serif; font-size: 28px; font-weight: normal; line-height: 1.3; margin: 0; margin-bottom: 10px; padding: 0; text-align: left; word-wrap: normal;">Hi <?php echo $user_name;?>,</h4>
                                                         <p class="small-text-center sub-details" style="Margin: 0; Margin-bottom: 10px; color: #202b39; font-family: Helvetica, Arial, sans-serif; font-size: 20px; font-weight: normal; line-height: 1.3; margin: 0; margin-bottom: 10px; padding: 0; text-align: left;">Here is your
                                                            <?php echo $frequency?> alert update for <?php echo date("M d, Y")?>
                                                         </p>
                                                      </th>
                                                   </tr>
                                                </tbody>
                                             </table>
                                          </th>
                                       </tr>
                                    </tbody>
                                 </table>
                              </td>
                           </tr>
                        </tbody>
                     </table>
                     <!-- /logo and heading -->
                     <!-- table -->
						<p>
							Here is your <?php echo $frequency; ?> report for <?php echo $report_filter; ?> named <strong><?php echo $name_of_report_filter; ?></strong>.
						</p>
                     <!-- table -->
                     <table align="left" class="new-indicatior" style="border-collapse: collapse; border-spacing: 0; margin-top: 20px; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                        <tbody>
                           <tr style="background: #f4f4f4; padding: 0; text-align: left; vertical-align: top;">
                              <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 10px 16px; text-align: left; vertical-align: top; word-wrap: break-word;">
                                <!--img class="new-img" src="<?php echo $images_url?>email-new-icon.png" style="-ms-interpolation-mode: bicubic; clear: both; display: inline-block; float: left !important; max-width: 100%; outline: none; padding-right: 5px !important; text-decoration: none; vertical-align: middle !important; width: auto;">
                                <span style="font-size: 16px; font-weight: 600; padding-left: 10px !important;">New Brand/Advertiser</span-->
								Download Link to your Report: <a href="<?php
                                    echo $download_file_path = 'http://' . HOST . '/drmetrix/api/index.php/downloadClientFiles?code=' . base64_encode("email=$email&id=$id");
                                    ?>"><?php echo $name_of_report_filter; ?></a>
                              </td>
                           </tr>
                        </tbody>
                     </table>
                     <!-- /table -->
                     <!-- footer -->
                     <table bgcolor="#8a8a8a" class="wrapper header table-footer" align="center" style="background: #fff; border-collapse: collapse; border-spacing: 0; margin-top: 100px; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                        <tbody>
                           <tr style="padding: 0; text-align: left; vertical-align: top;">
                              <td class="wrapper-inner" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 20px; text-align: left; vertical-align: top; word-wrap: break-word;">
                                 <table align="center" class="container" style="Margin: 0 auto; background: #fff; border-collapse: collapse; border-spacing: 0; margin: 0 auto; padding: 0; text-align: inherit; vertical-align: top; width: 580px;">
                                    <tbody>
                                       <tr style="padding: 0; text-align: left; vertical-align: top;">
                                          <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
                                             <table class="row collapse" style="border-collapse: collapse; border-spacing: 0; display: table; padding: 0; position: relative; text-align: left; vertical-align: top; width: 100%;">
                                                <tbody>
                                                   <tr style="padding: 0; text-align: left; vertical-align: top;">
                                                      <th class="small-12 large-12 columns first text-center link" valign="middle" style="Margin: 0 auto; color: #00beff !important; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 0; padding-bottom: 0; padding-left: 0; padding-right: 0; text-align: center; vertical-align: bottom; width: 588px;">
                                                         <!-- <a href="<?php //echo $unsubscribe_url; ?>"style="Margin: 0; color: #2199e8; font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 1.3; margin: 0; padding: 0; text-align: left; text-decoration: none;">Unsubscribe from this list</a> |  -->
                                                         <a href="<?php echo $tracking_url; ?>"style="Margin: 0; color: #2199e8; font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 1.3; margin: 0; padding: 0; text-align: left; text-decoration: none;">Configure alerts</a>
                                                      </th>
                                                   </tr>
                                                   <tr style="padding: 0; text-align: left; vertical-align: top;">
                                                      <th class="small-12 large-12 columns first text-center" valign="middle" style="Margin: 0 auto; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 0; padding-bottom: 0; padding-left: 0; padding-right: 0; text-align: center; vertical-align: bottom; width: 588px;">
                                                         DRMetrix - 27710 Jefferson Avenue, Suite 206 Temecula, CA 92590 USA
                                                      </th>
                                                   </tr>
                                                </tbody>
                                             </table>
                                          </td>
                                       </tr>
                                    </tbody>
                                 </table>
                              </td>
                           </tr>
                        </tbody>
                     </table>
                     <!-- /footer -->
                  </center>
               </td>
            </tr>
         </tbody>
      </table>
   </body>
</html>
<?php $output = ob_get_contents(); ob_end_clean(); return $output; } ?>