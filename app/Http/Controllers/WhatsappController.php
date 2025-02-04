<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Space\Updater;
use App\Models\Space\SiteApi;

class WhatsappController extends Controller
{
    public function sendPdf(Request $request)
    {
        if (config('app.env') !== 'production') {
            return response()->json(['error' => 'Whatsapp only works in the production.']);
        }
        $data = $request->data;
        $params = array(
            'token' => config('omtbiz.whatsapp_token'),
            'to' => $data->number,
            'filename' => $data->fileName . '.pdf',
            'document' =>  $data->filePath,
            'caption' => $data->fileName
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.ultramsg.com/".config('omtbiz.whatsapp_instance_id')."/messages/document",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return response()->json(['error' => $response]);
        } else {
            return response()->json(['success' => $response]);
        }
    }
}
