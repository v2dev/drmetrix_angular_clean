<?php

   function alert_email($user_name, $user_id, $frequency='weekly') {
     ob_start();
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
                     <table align="center" class="wrapper header" style="background: #fff; border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                        <tbody>
                           <tr style="padding: 0; text-align: left; vertical-align: top;">
                              <td class="text-center table-header" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; background: #202b39; border-collapse: collapse !important; color: #ffffff; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: bold; hyphens: auto; line-height: 1.3; margin: 0; padding: 8px 0; text-align: center; vertical-align: top; word-wrap: break-word;">
                                 New Alerts
                              </td>
                           </tr>
                           <tr style="padding: 0; text-align: left; vertical-align: top;">
                              <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
                                 <div class="alerts-table-scroll" style="min-width: 1900px; overflow-x: auto;">
                                    <table align="center" style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                       <tbody>
                                          <tr style="padding: 0; text-align: left; vertical-align: top;">
                                             <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
                                                <table class="row" style="border-collapse: collapse; border-spacing: 0; padding: 0; position: relative; text-align: left; vertical-align: top; width: 100%;">
                                                   <thead>
                                                      <tr class="table-head" style="background: #e5e5e5; color: #202b39; padding: 0; text-align: left; vertical-align: top;">
                                                         <th class="columns first" style="Margin: 0 auto; color: #666665; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 10px !important; text-align: left; vertical-align: bottom; width: 15%;"><label style="border-right: 1px dotted #707883 !important; display: block; margin-top: 10px; padding-bottom: 5px !important; padding-right: 10px !important;">Brands</label></th>
                                                         <th class="columns" style="Margin: 0 auto; color: #666665; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 10px !important; text-align: left; vertical-align: bottom; width: 15%;"><label style="border-right: 1px dotted #707883 !important; display: block; margin-top: 10px; padding-bottom: 5px !important; padding-right: 10px !important;">Advertiser</label></th>
                                                         <th class="columns" style="Margin: 0 auto; color: #666665; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 10px !important; text-align: left; vertical-align: bottom;"><label style="border-right: 1px dotted #707883 !important; display: block; margin-top: 10px; padding-bottom: 5px !important; padding-right: 10px !important;">Creative</label></th>
                                                         <?php /* network column removed 
                                                         <th class="columns" style="Margin: 0 auto; color: #666665; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 10px !important; text-align: left; vertical-align: bottom;"><label style="border-right: 1px dotted #707883 !important; display: block; margin-top: 10px; padding-bottom: 5px !important; padding-right: 10px !important;">Network</label></th>
                                                         */?>
                                                          <th class="columns" style="Margin: 0 auto; color: #666665; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 10px !important; text-align: left; vertical-align: bottom;"><label style="border-right: 1px dotted #707883 !important; display: block; margin-top: 10px; padding-bottom: 5px !important; padding-right: 10px !important;">Language</label></th>                      
                                                         <th class="columns" style="Margin: 0 auto; color: #666665; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 10px !important; text-align: left; vertical-align: bottom;"><label style="border-right: 1px dotted #707883 !important; display: block; margin-top: 10px; padding-bottom: 5px !important; padding-right: 10px !important;">Duration</label></th>
                                                         <th class="columns" style="Margin: 0 auto; color: #666665; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 10px !important; text-align: left; vertical-align: bottom;"><label style="border-right: 1px dotted #707883 !important; display: block; margin-top: 10px; padding-bottom: 5px !important; padding-right: 10px !important;">Category</label></th>
                                                         <th class="columns" style="Margin: 0 auto; color: #666665; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 10px !important; text-align: left; vertical-align: bottom;"><label style="border-right: 1px dotted #707883 !important; display: block; margin-top: 10px; padding-bottom: 5px !important; padding-right: 10px !important;">Sub Category</label></th>
                                                         <th class="columns" style="Margin: 0 auto; color: #666665; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 10px !important; text-align: left; vertical-align: bottom;"><label style="border-right: 1px dotted #707883 !important; display: block; margin-top: 10px; padding-bottom: 5px !important; padding-right: 10px !important;">Airings</label></th>
                                                         <th class="columns" style="Margin: 0 auto; color: #666665; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 10px !important; text-align: left; vertical-align: bottom;"><label style="border-right: none; display: block; margin-top: 10px; padding-bottom: 5px !important; padding-right: 10px !important;">Edit</label></th>
                                                         <th class="columns" style="Margin: 0 auto; color: #666665; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 10px !important; text-align: left; vertical-align: bottom;"><label style="border-right: none; display: block; margin-top: 10px; padding-bottom: 5px !important; padding-right: 10px !important;">Website</label></th>
                                                         <th class="columns alert-types" style="Margin: 0 auto; background: #707883; color: #666665; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 10px !important; text-align: left; vertical-align: bottom;">
                                                            <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                                               <tbody>
                                                                  <tr style="padding: 0; text-align: left; vertical-align: top;">
                                                                     <td colspan="4" class="text-center columns" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #fff; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 0 5px !important; padding-bottom: 0; padding-left: 0 !important; padding-right: 0 !important; text-align: center; vertical-align: bottom; word-wrap: break-word;"><b>Alert Types</b></td>
                                                                  </tr>
                                                                  <tr class="row" style="padding: 0; text-align: left; vertical-align: top;">
                                                                     <td class="large-3 columns text-center" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #fff; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 0 5px !important; padding-bottom: 0; padding-left: 0 !important; padding-right: 0 !important; text-align: center; vertical-align: bottom; width: 25%; word-wrap: break-word;"><label style="border-right: 1px dotted #707883 !important; display: block; margin-top: 10px; padding-bottom: 5px !important; padding-right: 5px !important;">Advertiser</label></td>
                                                                     <td class="large-3 columns text-center" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #fff; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 0 5px !important; padding-bottom: 0; padding-left: 0 !important; padding-right: 0 !important; text-align: center; vertical-align: bottom; width: 25%; word-wrap: break-word;"><label style="border-right: 1px dotted #707883 !important; display: block; margin-top: 10px; padding-bottom: 5px !important; padding-right: 5px !important;">Brand</label></td>
                                                                     <td class="large-3 columns text-center" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #fff; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 0 5px !important; padding-bottom: 0; padding-left: 0 !important; padding-right: 0 !important; text-align: center; vertical-align: bottom; width: 25%; word-wrap: break-word;"><label style="border-right: none; display: block; margin-top: 10px; padding-bottom: 5px !important; padding-right: 5px !important;">Category</label></td>
                                                                     <td class="large-3 columns text-center" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #fff; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 0 5px !important; padding-bottom: 0; padding-left: 0 !important; padding-right: 0 !important; text-align: center; vertical-align: bottom; width: 25%; word-wrap: break-word;"><label style="border-right: 1px dotted #707883 !important; display: block; margin-top: 10px; padding-bottom: 5px !important; padding-right: 5px !important;">Network</label></td>
                                                                  </tr>
                                                               </tbody>
                                                            </table>
                                                         </th>
                                                      </tr>
                                                   </thead>
                                                   <tbody class="table-content">
                                                      <?php
                                                         require_once dirname(__FILE__) . '/../config.php';
                                                         require_once dirname(__FILE__) . '/../queries.php';
                                                         require_once dirname(__FILE__) . '/../functions.php';
                                                         
                                                         $base_url             = "http://".HOST ."/drmetrix/ranking?data=";
                                                         $base_tracking_url    = "http://".HOST ."/drmetrix/tracking?data=";
                                                         $base_video_url       = "http://".HOST ."/drmetrix/video/";
                                                         $images_url           = "http://".HOST ."/drmetrix/api/email-template/images/";
                                                         $unsubscribe_url       = "http://".HOST ."/drmetrix/unsubscribe?".base64_encode("user_id=".$user_id);
                                                         $tracking_url = "http://".HOST ."/drmetrix/tracking";
                                                         if ($frequency == 'weekly') {
                                                           $result               = getLastMediaWeek();
                                                         } else {
                                                           $result               = array('sd'=> date("Y-m-d", strtotime('-1 days')), 'ed'=> date("Y-m-d", strtotime('-1 days')));
                                                           // $result               = array('sd'=> '2018-01-28', 'ed'=> '2018-01-29');
                                                         }
                                                         $params['start_date'] = $result['sd'];
                                                         $params['end_date']   = $result['ed'];
                                                         $params['frequency']   = $frequency;
                                                         extract($params);
                                                         $where                = " WHERE ( creative.class != 'BRAND' AND creative.adv_assigned BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59')";
                                                         $user_tracking_details  = getTrackingDataForUser($user_id, $frequency);
                                                         $and_cond               = $user_tracking_details['query'];
                                                         
                                                         if ($user_tracking_details['query'] == '') {
                                                           ob_end_clean();
                                                           return '';
                                                         }

                                                         $where                  .= $and_cond;
                                                         $params['where']        = $where;
                                                         $params['frequency']    = $frequency;
                                                         $result                 = get_query_result('__query_get_tracking_alerts_data', $params, 'FETCH_OBJ');
                                                         if(count($result) == 0) {
                                                            ob_end_clean();
                                                            return '';
                                                         }

                                                         foreach ($result as $index => $row) {
                                                            $data['advertiser']  = $row->adv_id;
                                                            $data['brand']       = $row->brand_id;
                                                            $data['category']    = $row->main_sub_category_id;
                                                            $data['network']     = $row->network_code;
                                                            $data['creative_id'] = $row->creative_id;
                                                            $row->language       = $row->spanish == 0 ? 'EN' : 'ES';;
                                                            $data['class']       = $row->class;
                                                            $data['type']        = $row->type;
                                                            $data['length']      = $row->duration;
                                                            $data['retail_report']= $row->retail_report;
                                                            $return_row[$row->creative_id] = check_new_record_for_tracking($row->new_advertiser, $row->new_brand, $row->new_creative, $data, $user_id, $frequency);
                                                         }

                                                         // show($return_row);
                                                         $array_sum = check_associative_array_blank($return_row); 

                                                         if($array_sum == 0) {
                                                            ob_end_clean();
                                                            return '';
                                                         }

                                                         if(count($result) > 0) {
                                                           foreach ($result as $index => $row) {
                                                            $website               = '-';
                                                            if (count($return_row[$row->creative_id]) == 0) {
                                                               //skipping record
                                                               continue;
                                                            }

                                                            $adv_display_name          = str_replace("&","XXX",$row->display_name);
                                                            $brand_display_name        = str_replace("&","XXX",$row->brand_name);
                                                            $creative_display_name     = str_replace("&","XXX",$row->creative_name);

                                                             $brand_url_params       = base64_encode ("adv_name=".$adv_display_name."&adv_id=".$row->adv_id."&call_from=brand&call_id=".$row->brand_id."&call_name=".$brand_display_name."&duration=".$row->duration);
                                                             $creative_url_params    = base64_encode("adv_name=".$adv_display_name."&adv_id=".$row->adv_id."&call_from=creatives&call_id=".$row->creative_id."&call_name=".$creative_display_name."&duration=".$row->duration);
                                                             $advertiser_url_params  = base64_encode("adv_name=".$adv_display_name."&adv_id=".$row->adv_id."&call_from=adv&call_id=&call_name=&duration=".$row->duration);

                                                             if (checkStatus("category", $row->main_sub_category_id, $user_tracking_details, $return_row[$row->creative_id]) == '') {
                                                               $cat_id = -100; //skip category from edit link, if it is not applicable
                                                             } else {
                                                               $cat_id = $row->main_sub_category_id;                           
                                                             }

                                                            if($row->website != ''){
                                                              $website = '<a href="http://'.$row->website.'" target="_blank" style="Margin: 0; color: #2199e8; font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 1.3; margin: 0; padding: 0; text-align: left; text-decoration: none;"><img src = "'.$images_url.'globe_01.png" style="-ms-interpolation-mode: bicubic; border: none; clear: both; display: block; max-width: 100%; outline: none; text-decoration: none; width: auto;"></a>';
                                                            }
                                                             $edit_link              = $base_tracking_url.base64_encode(getTrackingEditLink(array('advertiser' => $row->adv_id, 'brand' => $row->brand_id, 'category' => $cat_id, 'network' => $row->network_code), $user_tracking_details));
                                                             $video_url = $base_video_url.base64_encode("creative_id=".$row->creative_id."&airing_id=0000&date=".time()."&only=creative&network_code=".base64_encode($row->network_code))."?video=1";
                                                             echo '<tr  style="padding: 0; text-align: left; vertical-align: top;">';
                                                             if ($row->new_brand == 'NEW') {
                                                               $new_brand = '<img class="new-img" style="-ms-interpolation-mode: bicubic; clear: both; display: inline-block; float: left !important; max-width: 100%; outline: none; padding-right: 5px !important; text-decoration: none; vertical-align: middle !important; width: auto;" src="'.$images_url.'new.svg" />';
                                                             } else {
                                                               $new_brand = '';
                                                             }
                                                             if ($row->new_advertiser == 'NEW') {
                                                               $new_advertiser = '<img class="new-img" style="-ms-interpolation-mode: bicubic; clear: both; display: inline-block; float: left !important; max-width: 100%; outline: none; padding-right: 5px !important; text-decoration: none; vertical-align: middle !important; width: auto;" src="'.$images_url.'new.svg" />';
                                                             } else {
                                                               $new_advertiser = '';
                                                             }
                                                             echo '<td class="columns custom-column-width" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 5px !important; text-align: left; vertical-align: middle; width: 10%; word-wrap: break-word;">'.$new_brand.'<a class="custom-width-data" href="'.$base_url.$brand_url_params.'" target="_blank" style="Margin: 0; color: #2199e8; display: inline-block; font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 1.3; margin: 0; max-width: 240px; overflow: hidden; padding: 0; text-align: left; text-decoration: none; text-overflow: ellipsis; white-space: nowrap;">'.$row->brand_name.'</a></td>
                                                               <td class="columns custom-column-width" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 5px !important; text-align: left; vertical-align: middle; width: 10%; word-wrap: break-word;">'.$new_advertiser.'<a class="custom-width-data" href="'.$base_url.$advertiser_url_params.'" target="_blank" style="Margin: 0; color: #2199e8; display: inline-block; font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 1.3; margin: 0; max-width: 240px; overflow: hidden; padding: 0; text-align: left; text-decoration: none; text-overflow: ellipsis; white-space: nowrap;">'. $row->display_name.'</a></td>
                                                               <td class="columns custom-column-width" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 5px !important; text-align: left; vertical-align: middle; width: 20%; word-wrap: break-word;"><a class="custom-width-data" href="'.$base_url.$creative_url_params.'" target="_blank" style="Margin: 0; color: #2199e8;font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 1.3; margin: 0; padding: 0; text-align: left; text-decoration: none; margin-right:10px; ">'.$row->creative_name.'</a><a class="creative-video" href="'.$video_url. '" target="_blank" style="Margin: 0; color: #00beff; float: right; font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 1.3; margin: 0; padding: 0; padding-right: 10px !important; text-align: left; text-decoration: none;"><img src="'.$images_url.'email-play-icon.png" style="-ms-interpolation-mode: bicubic; border: none; clear: both; display: block; max-width: 100%; outline: none; text-decoration: none; width: auto; margin-left: 10px !important;"></a></td> <td class="columns" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 10px !important; text-align: center; vertical-align: middle; word-wrap: break-word;">'. $row->language . '</td>
                                                               '
                                                               /*
                                                               <td class="columns" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 10px 16px; padding-bottom: 0; padding-left: 12px; padding-right: 10px !important; text-align: left; vertical-align: middle; word-wrap: break-word;">'. get_all_display_networks_from_code($row->network_code, $user_tracking_details, 'network_name').'</td>'
                                                               */
                                                               .'
                                                               <td class="columns" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 10px 5px 0; text-align: center; vertical-align: middle; word-wrap: break-word;">'. $row->duration . '</td>
                                                               <td class="columns" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 10px 5px 0; text-align: left; vertical-align: middle; word-wrap: break-word;">'.get_category_details($row->main_sub_category_id, 'category_name').'</td>
                                                               <td class="columns" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 10px 5px 0; text-align: left; vertical-align: middle; word-wrap: break-word;">'. get_category_details($row->main_sub_category_id, 'sub_category_name').'</td>
                                                               <td class="columns" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 10px 5px 0; text-align: center; vertical-align: middle; word-wrap: break-word;">'. $row->airings_count.'</td>
                                                               <td class="columns" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 10px 5px 0; text-align: center; vertical-align: middle; word-wrap: break-word;"><a target="_blank" href="'.$edit_link.'" style="Margin: 0; color: #2199e8; font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 1.3; margin: 0; padding: 0; text-align: left; text-decoration: none;"><img src="'.$images_url.'email-edit-icon.png" style="-ms-interpolation-mode: bicubic; border: none; clear: both; display: block; max-width: 100%; outline: none; text-decoration: none; width: auto;"/></a></td>
                                                               <td class="columns" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 10px 5px 0; text-align: center; vertical-align: middle; word-wrap: break-word;">'.$website.'</td>
                                                               <td class="columns" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0 auto; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0 auto; padding: 10px 5px 0; text-align: left; vertical-align: middle; word-wrap: break-word;">
                                                                   <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                                                     <tbody>
                                                                       <tr class="row" style="padding: 0; text-align: left; vertical-align: top;">
                                                                         <td class="large-3 " style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 10px 5px; text-align: center; vertical-align: top; width: 25%; word-wrap: break-word;">'. checkStatus("advertiser", $row->adv_id, $user_tracking_details) .'</td>
                                                                         <td class="large-3 " style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 10px 5px; text-align: center; vertical-align: top; width: 25%; word-wrap: break-word;">'. checkStatus("brand", $row->brand_id, $user_tracking_details) . '</td>
                                                                         <td class="large-3 " style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 10px 5px; text-align: center; vertical-align: top; width: 25%; word-wrap: break-word;">'. checkStatus("category", $row->main_sub_category_id, $user_tracking_details, $return_row[$row->creative_id]) .'</td>
                                                                         <td class="large-3 " style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 10px 5px;  text-align: center; vertical-align: top; width: 25%; word-wrap: break-word;">'. checkStatus("network", get_all_networks_from_code($row->network_code, 'network_id'), $user_tracking_details,$return_row[$row->creative_id]) .'</td>
                                                                       </tr>
                                                                     </tbody>
                                                                   </table>
                                                                 </td>
                                                               </tr>';}} ?>
                                                   </tbody>
                                                </table>
                                             </td>
                                          </tr>
                                       </tbody>
                                    </table>
                                 </div>
                              </td>
                           </tr>
                        </tbody>
                     </table>
                     <table align="left" class="new-indicatior" style="border-collapse: collapse; border-spacing: 0; margin-top: 20px; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                        <tbody>
                           <tr style="background: #f4f4f4; padding: 0; text-align: left; vertical-align: top;">
                              <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 10px 16px; text-align: left; vertical-align: top; word-wrap: break-word;"><img class="new-img" src="<?php echo $images_url?>new.svg" style="-ms-interpolation-mode: bicubic; clear: both; display: inline-block; float: left !important; max-width: 100%; outline: none; padding-right: 5px !important; text-decoration: none; vertical-align: middle !important; width: auto;"><span style="font-size: 16px; font-weight: 600; padding-left: 10px !important;">New Brand/Advertiser</span></td>
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
                                                         <a href="<?php echo $unsubscribe_url; ?>"style="Margin: 0; color: #2199e8; font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 1.3; margin: 0; padding: 0; text-align: left; text-decoration: none;">Unsubscribe from this list</a> | 
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
