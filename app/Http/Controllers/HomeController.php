<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function updateToken(Request $request){
        try{
            auth()->user()->update(['fcm_token'=>$request->token]);
            return response()->json([
                'success'=>true
            ]);
        }catch(\Exception $e){
            report($e);
            return response()->json([
                'success'=>false
            ],500);
        }
    }

    public function sendMessage(Request $request)
    {
        try{
            $sender = auth()->user();
            $url = 'https://fcm.googleapis.com/fcm/send';
            $receiver = User::find($request['data']['receiver_id']);
            $FcmToken = $receiver->fcm_token;

            $serverKey = 'AAAAamHhdvc:APA91bHlVia_RjvaRa8SlrkYbEk_NpAJxiyeOWLi3yQyOLRiP1d9AbGPLb_cg1BUWQJPRwOS25MVDjwwjkbZUPwd0GIbBu2ljEzNx_HaKuhMDs5zrHLMjOwLowdZqYQsbOGZFXuvFOZl';

            $message = Message::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'text' => $request['data']['text']
            ]);

            $data = [
                "registration_ids" => [$FcmToken],
                "data" => [
                    "title" => 'New Message from ' . $sender->name,
                    'body' => [
                        'message' => $message,
                        'sender' => $sender,
                        'receiver' => $receiver
                    ]
                ]
            ];
            $encodedData = json_encode($data);

            $headers = [
                'Authorization:key=' . $serverKey,
                'Content-Type: application/json',
            ];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
            // Execute post
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }
            // Close connection
            curl_close($ch);

            return response()->json([
                'success'=>true,
                'message' => $message,
                'sender' => $sender,
                'receiver' => $receiver
            ]);
        }catch(\Exception $e){
            report($e);
            return response()->json([
            'success'=> $e
            ],500);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
}
