<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Donation;
// use Veritrans_Config;
// use Veritrans_Snap;
// use Veritrans_Notification; 


class DonationController extends Controller
{

    Public function __construct()
    {
        \Midtrans\Config::$serverKey = config('service.midtrans.serverKey');
        \Midtrans\Config::$isProduction = config('service.midtrans.isProduction');
        \Midtrans\Config::$isSanitized = config('service.midtrans.isSanitized');
        \Midtrans\Config::$is3ds = config('service.midtrans.is3ds');
    }

    public function index()
    {
        $donations = Donation::orderBy('id', 'desc')->paginate(8);
        return view('welcome', compact('donations'));
    }
    
    public function create()
    {
        return view('donation');
    }

    public function store(Request $request)
    {
        \DB::transaction(function ()use($request) {
            $donation = Donation::create([
                'order_id'=> \Str::uuid(),
                'donor_name'=> $request->donor_name,
                'donor_email'=> $request->donor_email,
                'donation_type'=> $request->donation_type,
                'amount'=> floatval ($request->amount),
                'note'=>$request->note,
            ]);

            $payload =[
                'transaction_details'=> [
                    'order_id'=> $donation->order_id,
                    'gross_amount'=>$donation->amount,
                ],
                'customer_details'=> [
                    'first_name'=>$donation->donor_name,
                    'email'=>$donation->donor_email,
                     // 'phone'         => '08888888888',
                    // 'address'       => '',
                ],
                'item_details'=> [
                [
                    'id'=>$donation->donation_type,
                    'price'=>$donation->amount,
                    'quantity'=> 1,
                    'name'=>ucwords(str_replace('_','',$donation->donation_type))
                ]
            ]
        ];
        $snapToken = \Midtrans\Snap::getSnapToken($payload);
        $donation->snap_token = $snapToken;
        $donation->save();

        $this->response['snap_token'] = $snapToken;
     });
     
     return response()->json($this->response);
    }

    public function notification(Request $request)
    {
        $notif = new \Midtrans\Notification();

        \DB::transaction(function () use ($notif) {

            $transactionStatus = $notif->transaction_status;
            $paymentType = $notif->payment_type;
            $orderId = $notif->order_id;
            $fraud = $notif->fraud_status;
            $donation = Donation::where('order_id', $orderId)->frist();

            if ($transactionStatus == 'capture'){
                if ($paymentType == 'credit_card'){
                    
                    if($fraud == 'challenge'){
                        $donation->setStatusPending(); 
                    }else{
                        $donation->setStatusSuccsess();
                    }
                }
            }elseif ($transactionStatus == 'settlement'){
                $donation->setStatusSuccess();
            }elseif ($transactionStatus == 'pending'){
                $donation->setStatusPending();
            }elseif ($transactionStatus == 'deny'){
                $donation->setStatusFailed();
            }elseif ($transactionStatus == 'expire'){
                $donation->setStatusExpired();
            }elseif ($transactionStatus == 'cencel'){
                $donation->setStatusFailed();
            }
        });
        return;
    }
}
