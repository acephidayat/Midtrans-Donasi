<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Donation;
use Veritrans_Config;
use Veritrans_Snap;
use Veritrans_Notification; 


class DonationController extends Controller
{

    Public function __construct()
    {
        Veritrans_Config::$serverKey = config('service.midtrans.serverKey');
        Veritrans_Config::$isProduction = config('service.midtrans.isProduction');
        Veritrans_Config::$isSanitized = config('service.midtrans.isSanitized');
        Veritrans_Config::$is3ds = config('service.midtrans.is3ds');
    }

    public function index()
    {
        $donations = Donation::orderBY('id', 'DESC')->paginate(8);
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
                'donation_code'=>'SANDBOX-'. uniqid(),
                'donor_name'=> $request->donor_name,
                'donor_email'=> $request->donor_email,
                'donation_type'=> $request->donation_type,
                'amount'=> floatval ($request->amount),
                'note'=>$request->note,
            ]);

            $payload =[
                'transaction_details'=> [
                    'order_id'=> $donation->donation_code,
                    'gross_amount'=>$donation->amount,
                ],
                'customer_details'=> [
                    'first_name'=>$donation->name,
                    'email'=>$donation->email,
                ],
                'item_details'=> [
                [
                    'id'=>$donation->donation_type,
                    'price'=>$donation->amount,
                    'quantity'=>1,
                    'name'=>ucwords(str_replace('-','',$donation->donation_type))
                ]
            ]
        ];
        $snapToken = Veritrans_Snap::getSnapToken($payload);
        $donation->snap_token = $snapToken;
        $donation->save();

        $this->response['snap_token'] = $snapToken;
     });
     
     return response()->json($this->response);
    }

    public function notification()
    {
        $notif = new Veritrans_Notification();
        \DB::transaction(function () use ($notif) {
            $transactionStatus = $notif->transaction_status;
            $paymentType = $notif->payment_type;
            $orderId = $notif->order_id;
            $fraudStatus = $notif->fraudStatus;
            $donation = Donation::where('donation_code', $orderId)->frist();

            if ($transactionStatus == 'capture'){
                if ($paymentType == 'credit_card'){
                    if($fraudStatus == 'challenge'){
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
