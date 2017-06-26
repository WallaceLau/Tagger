<?php

namespace App\Http\Controllers\Email;

use Illuminate\Http\Request;
use Mail;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class EmailController extends Controller
{
    //
    public function sendNotification(Request $request){
        Mail::send('emails.notification',[], function ($message) {
            $message->from('info@cherishdigital.com', 'Cherish Customer Service');
            $message->to('wallacelau@i-chargesolutions.com', 'Wallace Lau')->subject('Testing Email sent from SMTP');
            //$message->setBody('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Excel To HTML using codebeautify.org</title></head><body><!DOCTYPE html><html><head><meta charset="UTF-8"><title>Excel To HTML using codebeautify.org</title></head><body><html><head><title>Cherish Customer Service</title></head><body><p1>From: </p1>Peter Wong<p>Message: </p>Cannot read the QR Code</body></html></body></html></body></html>','text/html');
        });
    }
}
