<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;
use App\Models\MailTrack;
use Carbon\Carbon;

class EmailTrackingController extends Controller
{
    public function track(Request $request)
    {
        $message_id = $request->message_id;
        $inv_id = $request->inv_id;

        Log::info("Email opened for invoice ID: $inv_id with Message ID: $message_id");

        $mail_record = MailTrack::where('type_id', $inv_id)
            ->where('message_id', $message_id)
            ->first();

        $opens = $mail_record->opens;

        MailTrack::where('type_id', $inv_id)
            ->where('message_id', $message_id)
            ->update([
                'opened_at' => Carbon::now(),
                'opens' => $opens + 1,
            ]);

        // update the database
        Invoice::where('invoice_id', $inv_id)->update(['email_status' => 'opened']);

        // return a 1x1 transparent pixel
        $pixel = base64_decode(
            'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
        );

        return response($pixel)->header('Content-Type', 'image/gif');
    }

    public function trackLink(Request $request)
    {
        $message_id = $request->message_id;
        $inv_id = $request->inv_id;
        $redirectUrl = $request->redirect_url;

        Log::info("Link opened for invoice ID: $inv_id with Message ID: $message_id");

        $mail_record = MailTrack::where('type_id', $inv_id)
            ->where('message_id', $message_id)
            ->first();

        $clicks = $mail_record->clicks;

        MailTrack::where('type_id', $inv_id)
            ->where('message_id', $message_id)
            ->update([
                'clicked_at' => now(),
                'clicks' => $clicks + 1,
            ]);

        return redirect()->to($redirectUrl);
    }

    
}
