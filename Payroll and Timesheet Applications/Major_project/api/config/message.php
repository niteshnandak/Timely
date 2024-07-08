<?php

return [
    'success' => [

        // LOGOUT
        'logged_out' => 'Successfully logged out!',
        'allDevices_logged_out' => 'Successfully logged out from all devices',

        // RESET PASSWORD
        'reset_mailsent_success' => 'Password reset link sent Successfully',
        'set_new_password' => 'Set your new password',
        'password_update_success' => 'Password Updated Successfully',

        //AFA Users
        'user_activate_success' => 'User Activated Successfully',
        'user_deactivate_success' => 'User Deactivated Successfully',
        'password_set_link' => 'Password Set link sent Successfully to user mail',
        'afauser_created_success' => 'AFA User created Successfully',
        'afauser_delete_success' => 'User Deleted Successfully',

        // Expense
        'expense_added_success' => 'Expense Added Successfully',
        'expense_updated_success' => 'Expense updated Succesfully',
        'expense_delete_success' => 'Expense Deleted Successfully',
        'expense_approved_success' => 'Expense Approved Successfully',

        // People
        'people_add_success' => 'People Created Successfully in the Organisation',
        'people_editPeople_success' => 'Update the People details',
        'people_edit_success' => 'People details edited Successfully',
        'people_delete_success' => 'People deleted Successfully from the Organisation',
        'people_status_success' => 'People Status Updated Successfully',

        //Registration
        'register_user' => 'Please check your Mail.',
        'verify_user_found' => 'user found.',
        'password_saved' => 'password saved successfully.',

        //organisation
        'organisation_create' => 'Organisation Created Succesfully.',
        'organisation_update' => 'Organisation Updated Successfully.',
        'organisation_delete' => 'Organisation Deleted Successfully.',
        'organisation_activated' => 'Organisation activated Successfully.',
        'organisation_deactivated' => 'Organisation deactivated Successfully.',
        'organisation_edit_success' => 'Organisation details Edited Succesfully',
        'org_logo_success' => 'Logo Uploaded Succesfully',

         // Company
         'company_add_success' => 'Company Added Successfully',
         'company_update_success' => 'Company updated successfully',
         'company_image_upload_success' => 'Logo uploaded successfully',
         'company_delete_success' => 'Company Deleted Successfully',
         'company_status_success' => 'Company is Activated',
         'company_status_success1' => 'Company is Deactivated',


        //  payroll
        'payroll_customer_created' => 'Invoices are Selected for the Batch',
        'payroll_batch_add_success' => 'Payroll Batch Added Successfully',
        'update_payroll_Process_success'=>'The Batch is scheduled for the Payroll run ',
        'payroll_rollback_success'=>'Payroll batch rolled back successfully',
        "payroll_started_status"=>"Payroll Run Started",
        "payroll_started_message"=>"Payroll Run Started for the people of the payroll batch",
        "payroll_ended_status"=>"Payroll Run Completed Successfully",
        "payroll_ended_message"=>"Payroll Run Completed for the people of the payroll batch successfully",
        'payroll_batch_delete_success' => 'Payroll batch has been deleted successfully'
,

        // task scheduler
        'task_scheduler_success' => 'Task completed successfully',
        'task_scheduler_success_message' => 'Task completed successfully without any errors',

        //invoice
        'invoice_create'=>'Invoice created Successfully.',
        'invoice_update'=>'Invoice updated Successfully.',
        'invoice_mail'=>'Please check your Mail.',
        'invoice_delete'=>'Invoice Deleted Succesfully.',


    ],
    'error' => [
        // LOGIN
        'account_not_activated' => 'Account is not activated',
        'account_deactivated' => 'Account is deactivated',
        'invalid_credentials' => 'Invalid login credentials',
        'exception_error' => 'Something went wrong',

        // RESET PASSWORD
        'user_not_found' => 'User does not exist',
        'account_not_activated_reset' => 'Account not activated, check your mail',
        'reset_mailsent_error' => 'Error Occured In Sending Email',
        'reset_link_expired' => 'Reset Link Expired',
        'invalid_user_error' => 'Invalid User Error',

        // AFA Users
        'no_users_found' => 'No users found',
        'afauser_update_error' => 'Failed to update AFA User',
        'afauser_delete_error' => 'Failed to delete AFA User',

        //Expense
        'no_expense_found' => 'No Expense data found',
        'expense_added_error' => 'Expense could not be added',
        'expense_updated_error' => 'Expense could not be updated',
        'expense_delete_error' => 'Expense could not be deleted',
        'expense_approved_error' => 'Expense could not be approved',

        //People Hub Payroll
        'payrolls_not_found' => 'No Payrolls Data found',


        //People
        'people_add_error' => 'An error occured in the people creation ',
        'people_editPeople_error' => 'People not found in the Organisation',
        'people_edit_error' => 'An error occured in the saving people detail changes ',
        'people_delete_error' => 'People not found in the organization ',
        'people_status_error' => 'An error Occured in Updating the status ',
        'people_not_found' => 'People not found in the Organisation',

        //company
        'company_error_fetching' => 'An error occurred while fetching companies',
        'company_not_found' => 'Company not found',
        'company_error_updating' => 'An error occurred while updating the company',
        'company_error_adding' => 'An error occurred while adding the company',
        'company_error_deleting' => 'An error occurred while deleting the company',
        'company_error_searching' => 'An error occurred while searching for companies',
        'company_error_updating_status' => 'An error occurred while updating the company status',
        'company_error_fetching_stats' => 'An error occurred while fetching company stats',
        'company_error_uploading_image' => 'An error occurred while uploading the image',

        //Registration
        'reg_validation' => 'Something went wrong try again after some time.',
        'register_user' => 'Registration is unsuccessfull.',
        'db_registration_failed' => 'User Registeration is unsuccessfull.',
        'verify_user_found' => 'user not found.',
        'user_already_set_password' => 'user already set his password.',
        'password_save_failed' => "password saving failed.",

        //organisation
        'organisation_update' => 'Failed to update the Organisation.',
        'organisation_not_found_delete' => 'Organisation not found.',
        'organisation_delete' => 'Deletion failed try again later.',
        'organisation_not_found' => 'Organisation not found.',
        'organisation_create' => 'Failed to create organisation',
        'organisation_edit_error' => 'Error While Editing Details',
        'org_logo_error' => 'Error while uploading Logo',


        //  payroll
        'payroll_unselected_invoices' => 'No Invoices are selected for this Batch',
        'payroll_report_error' => 'Cannot genertae report',
        'payroll_customer_error' => 'Error occured in selecting invoices',
        'payroll_batch_add_error' => 'Error occured while ading the company',
        'update_payroll_Process_error'=>'Error Occured in payroll run',
        'payroll_invoice_retrieve_error' => "Error while retrieving the data",
        'payroll_invoice_nodata' => "No invoices found for the selected customers",
        "payroll_rollback_error"=>"Latest Batch Should be rollbacked first, to rollback this batch",
        "payroll_batch_retrieve_error" => "Error occured while retrieving data",
        'payroll_batch_delete_error' => 'Error occured while trying to delete the Payroll batch',
        //payslip
        'pdf_view_error' => 'An error occurred while trying to view the PDF.',

        // Task scheduler
        'task_scheduler_error' => 'Task completed un-successfully',
        'task_scheduler_error_message' => 'There are some error while running the scheduler',

        //invoice
        'invoice_delete'=>'Something went wrong try after sometime.',
        'invoice_mail'=>'Try after sometime.',
        'invoice_patch_details'=>'Failed to fetch the edit patch data.',
        'invoice_selected_assignments'=>'Failed to get the selected Assignments.',
        'invoice_update'=>'Failed to update the invoice.',
        'invoice_create'=>'Failed to create the invoice.',
        'invoices_fetch'=>'Failed to fetch the invoices data.',
        'invoice_download' =>'Download invoice failed.',


    ]


];
