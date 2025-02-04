<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappController extends Controller
{
    public function sendPdf(Request $request)
    {
        if (config('app.env') !== 'production') {
            return response()->json(['error' => 'Whatsapp only works in the production.']);
        }

        // $params = array(
        //     'token' => config('omtbiz.whatsapp_token'),
        //     'to' => $request->number,
        //     'filename' => $request->fileName . '.pdf',
        //     'document' =>  $request->filePath,
        //     'caption' => $request->fileName
        // );


        $params=array(
            'token' => '2k9fbxoja78jqcxh',
            'to' => '+919649408735',
            'filename' => 'hello.pdf',
            'document' => 'http://85.31.236.157/invoices/pdf/nCcRdMqhmtcG3vMVcf9n6pROpNjj2kutRQlPsEZcaXhKjCY59JxoD5yJCly9',
            'caption' => 'document caption'
            );
            Log::info('Sending request to create pdf', [
                'params' => $params,
            ]);
            $client = new Client();
            $headers = [
              'Content-Type' => 'application/x-www-form-urlencoded'
            ];
            $options = ['form_params' =>$params ];
            $request = new Psr7Request('POST', 'https://api.ultramsg.com/instance66542/messages/document', $headers);
            $res = $client->sendAsync($request, $options)->wait();


            return response()->json(['success' => $res->getBody()]);

    }
}
