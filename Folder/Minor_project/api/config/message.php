<?php

return [
    'success' => [
        // LOGIN
        'register_success'=>'Check Your Email Address And Set The  Password',
        'setpasswordpage_info' => "Set Your Password",
        'setpassword_success' => "Password Has Been Set Successfully",
        'login_success' => 'Logged in Successfully',
        'logout_success' => 'Logged out Successfully',
        'reset_validate_success' =>'Set your New Password',
        'forgot_password_success' =>' Password reset link has been sent to your Email Address',
        'forgot_reset_password_success' =>'Password Updated Succesfully',

        // Email Invoice
        'mail_sent_success' =>'Email sent Successfully',

        //PDF Generation
        'pdf_generated_success' => 'Invoice Generated successfully',
        // PDF Delete
        'pdf_delete_success' => 'Invoiced PDF Deleted successfully',

        // Invoice Delete
        'invoice_delete_success' => 'Invoice Deleted Succesfully',
        // New Manual Invoice
        'invoice_new_success' => 'New Invoice Created',

    ],
    'error' => [
        // LOGIN
        'register_error' =>  "Error Occured In Sending Email ",
        'setpasswordpage_error' => "Expired, Contact The Admin",
        'setpassword_error' => "Expired, Contact The Admin",
        'login_error'=>'Invalid Email/Password',
        'logout_error'=>'Error Logging Out',
        'token_error'=>'Token Is Not Provided',
        'error.api_error'=>'Unauthenticated User',
        'reset_validate_error' =>'Invalid User Error Occured',
        'forgot_password_user_error' =>'Invalid User Email Address',
        'forgot_password_error' =>'Error Occured In Sending Email',
        'forgot_reset_password_error' =>'User Invalid Error Occured',

        //Invoices
        'invoice_fetch_error' => "Can't fetch invoices",
        // Invoice new Manual
        'invoice_new_error' => "Error Occured, Please try again",

        //PDF Generation
        'pdf_generated_error' => 'Failed to Generate Invoice',
        //PDF View
        'pdf_view_error' => 'Failed to view Invoice',
        //PDF Download
        'pdf_download_error' => 'Failed to download Invoice',
        // PDF Delete
        'pdf_delete_error' => 'Failed to delete Invoice',

        // Email Invoice
        'mail_failed_invoice_generated_file_error' =>'Invoice is not generated!',
        'mail_failed_pdf_not_exist_error' =>'Failed to mail Invoice',
        'mail_failed_error' =>'Failed to send email. Please try again later.',

    ]


    ];
