<?php

namespace App\Http\Controllers;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Exception\PayPalConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use PayPal\Api\PaymentExecution;

class PaymentController extends Controller
{
    private $apiContext;

    public function __construct()
    {
        $payPalConfig = Config::get('paypal');
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                $payPalConfig['client_id'],
                $payPalConfig['secret']
            )
        );

        $this->apiContext->setConfig($payPalConfig['settings']);
    }

    public function payWithPayPal()
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setTotal('3.99');
        $amount->setCurrency('USD');

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        // $transaction->setDescription('See your payment');

        $callbackUrl = url('/paypal/status');
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($callbackUrl) // complete pay
            ->setCancelUrl($callbackUrl); // cancel pay

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions(array($transaction))
            ->setRedirectUrls($redirectUrls);

        try {
            $payment->create($this->apiContext);
            return redirect()->away($payment->getApprovalLink());
        } catch (PayPalConnectionException $ex) {
            echo $ex->getData();
        }
    }
    public function payPalStatus(Request $request)
    {
        //dd($request->all());
        $paymentId = $request->get('paymentId');
        $token = $request->get('token');
        $payerId = $request->get('PayerID');

        if (!$paymentId || !$token || !$payerId) {
            return response()->json([
                'error' => 'Payment failed',
            ]);
        }

        $payment = Payment::get($paymentId, $this->apiContext); //find object payment
        
        $execution = new PaymentExecution();//ejecutar el pago
        $execution->setPayerId($payerId);

        $result = $payment->execute($execution, $this->apiContext);
        dd($result);

    }

}
