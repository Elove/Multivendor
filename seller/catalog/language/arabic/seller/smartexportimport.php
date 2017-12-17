<?php
// Heading
$_['heading_title']     = 'Import Products';
// Text
$_['text_success']      = 'Success: You have successfully imported your  products!';
$_['text_nochange']     = 'No server data has been changed.';
$_['text_log_details']  = 'See also \'System &gt; Error Logs\' for more details.';
// Entry
$_['entry_restore']     = 'Import from spreadsheet file:';
$_['entry_description'] = 'Manage your products Use the Smart Import from a EXCEL file.';
$_['entry_exportway_sel'] = 'please select the way you want to export your proucts:';
$_['entry_start_id'] = 'product start id:';
$_['entry_end_id'] = 'product end id:';
$_['entry_start_index'] = 'count per batch:';
$_['entry_end_index'] = 'Serial of batch :';
// Button labels
$_['text_acount']     = 'Account';
$_['button_import']     = 'Upload';
$_['button_export']     = 'SmartExport';
$_['button_export_pid']     = 'export by prouct id';
$_['button_export_page']     = 'export by batch';
//Error
$_['error_exist_product'] = 'The product ID %s already exists in your database, Please Check the Excle file!';
$_['error_permission']          = 'Warning: You do not have permission to modify imports!';
$_['error_upload']              = 'Uploaded file is not a valid spreadsheet file or its values are not in the expected formats!';
$_['error_sheet_count']         = 'Import: Invalid number of worksheets, 8 worksheets expected';
$_['error_categories_header']   = 'Import: Invalid header in the Categories worksheet';
$_['error_filtergroups_header']   = 'Import: Invalid header in the Filter Group worksheet';
$_['error_filters_header']   = 'Import: Invalid header in the Filter worksheet';
$_['error_products_header']     = 'Import: Invalid header in the Products worksheet';
$_['error_descriptions_header']     = 'Import: Invalid header in the Descriptions worksheet';
$_['error_additionalimages_header']     = 'Import: Invalid header in the additional images worksheet';
$_['error_product_options_header']      = 'Import: Invalid header in the ProductOptions worksheet';
$_['error_options_header']      = 'Import: Invalid header in the Options worksheet';
$_['error_option_values_header']      = 'Import: Invalid header in the OptionValues worksheet';
$_['error_attributes_header']   = 'Import: Invalid header in the Attributes worksheet';
$_['error_specials_header']     = 'Import: Invalid header in the Specials worksheet';
$_['error_discounts_header']    = 'Import: Invalid header in the Discounts worksheet';
$_['error_rewards_header']      = 'Import: Invalid header in the Rewards worksheet';
$_['error_select_file']         = 'Import: Please select a file before clicking \'Import\'';
$_['error_post_max_size']       = 'Import: File size is greater than %1 (see PHP setting \'post_max_size\')';
$_['error_upload_max_filesize'] = 'Import: File size is greater than %1 (see PHP setting \'upload_max_filesize\')';
$_['error_pid_no_data']         = 'no product between start id to end id.';
$_['error_page_no_data']        = 'no more product data.';
$_['error_param_not_number']        = 'parameters must be number.';
?>