<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappController extends Controller
{
    public function sendPdf(Request $request)
    {
        if (config('app.env') !== 'production') {
            return response()->json(['error' => 'Whatsapp only works in the production.']);
        }

        $params = array(
            'token' => config('omtbiz.whatsapp_token'),
            'to' => $request->number,
            'filename' => $request->fileName.'.pdf',
            'document' => $request->filePath,
            'caption' => $request->fileName
        );
        Log::info('Sending request to create pdf', [
            'to' => $request->number,
            'filename' => $request->fileName.'.pdf',
            'document' => $request->filePath,
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.ultramsg.com/".config('omtbiz.whatsapp_instance_id')."/messages/document",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        dd($response, $err);
        if ($err) {
            return response()->json(['error' => $err]);
        } else {
            return response()->json(['success' => $response]);
        }
    }
}
