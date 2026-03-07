<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappController extends Controller
{
    public function sendPdf(Request $request)
    {
        // if (config('app.env') !== 'production') {
        //     return response()->json(['error' => 'Whatsapp only works in the production.']);
        // }

        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $request->filePath);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore SSL verification if needed

        // $pdfContent = curl_exec($ch);
        // $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // curl_close($ch);

        // if ($httpStatus !== 200 || $pdfContent === false) {
        //     die("Failed to fetch the file. HTTP Status: $httpStatus");
        // }

        // // Pdf Convert to Base64
        // $base64 = base64_encode($pdfContent);

        // $params = array(
        //     'token' => config('omtbiz.whatsapp_token'),
        //     'to' => $request->number,
        //     'filename' => $request->fileName.'.pdf',
        //     'document' => $base64,
        //     'caption' => $request->fileName
        // );
        // Log::info('Sending request to create pdf', [
        //     'to' => $request->number,
        //     'filename' => $request->fileName.'.pdf',
        //     'document' => $request->filePath,
        // ]);

        // $curl = curl_init();
        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => "https://api.ultramsg.com/".config('omtbiz.whatsapp_instance_id')."/messages/document",
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_CUSTOMREQUEST => "POST",
        //     CURLOPT_POSTFIELDS => json_encode($params),
        //     CURLOPT_HTTPHEADER => [
        //         'Content-Type: application/json',
        //     ],
        // ));

        // $response = curl_exec($curl);
        // $err = curl_error($curl);

        // curl_close($curl);
        // if ($err) {
        //     return response()->json(['error' => $err]);
        // } else {
        //     return response()->json(['success' => $response]);
        // }

        $client = new Client();
        $apiKey = config('omtbiz.whatsapp_token');
        $url = 'https://www.wasenderapi.com/api/send-message';

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'to' => $request->number,
                    'text' => $request->fileName.'.pdf',
                    'documentUrl' => $request->filePath,
                ]
            ]);

            return response()->json(['success' => $response]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
