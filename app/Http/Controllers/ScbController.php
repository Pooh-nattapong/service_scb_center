<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\SCB;
use Exception;

class ScbController extends Controller
{
    public function getBalance(Request $request)
    {

        try {
    
            $deviceid = $request->device;
            $pin = $request->pin;
            $acc_num = $request->acc_num;
            $scb = new SCB($deviceid, $pin, $acc_num);
            $login = $scb->login();
            // dd( $login);

            // dd($scb->eligiblebanks());
            if ($login) {
                $balance_bank = $scb->getBalance();
                // dd($balance_bank );
                $res['success'] = true;
                $res['data'] = $balance_bank['totalAvailableBalance'];
                $res['msg'] = 'ดึงข้อมูลสำเร็จ';

                
            } else {
                throw new Exception('SCB::ไม่สามารถติดต่อกับธนาคารได้ โปรดลองใหม่อีกครั้ง');
            }
        } catch (Exception $e) {
            $res['success'] = false;
            $res['data'] = null;
            $res['msg'] = $e->getMessage();
            return  response()->json($res, 400);
        }

        return response()->json($res, 200);
    }

    public function withdrawToBank(Request $request)
    {
        try {
            // dd($request->all());
            $bank_number_payee = $request->bank_number_payee;
            $back_code = $request->back_code;
            $amount = $request->amount;
            $deviceid = $request->device;
            $pin = $request->pin;
            $acc_num = $request->acc_num;
            $scb = new SCB($deviceid, $pin, $acc_num);
            $login = $scb->login();

            if ($login) {
                $transfer_verify = $scb->transfer_verify($bank_number_payee, $back_code, $amount);
                if ($transfer_verify['status']['code'] != 1000) {
                    throw new Exception($transfer_verify['status']['description'] . " code : " . $transfer_verify['status']['code']);
                }

                $transfer_confrim = $scb->transfer_confrim($bank_number_payee, $back_code, $amount, $transfer_verify);
                if ($transfer_confrim['status']['code'] != 1000) {
                    throw new Exception("Transfer_confrim ไม่สำเร็จ");
                }

                $res['success'] = true;
                $res['data'] = $transfer_confrim;
                $res['msg'] = 'ถอนเงินสำเร็จ';
            } else {
                throw new Exception('SCB::ไม่สามารถติดต่อกับธนาคารได้ โปรดลองใหม่อีกครั้ง');
            }
        } catch (Exception $e) {
            $res['success'] = false;
            $res['data'] = null;
            $res['msg'] = $e->getMessage();
            return  response()->json($res, 400);
        }

        return response()->json($res, 200);
    }


    public function withdrawToTmn(Request $request)
    {
        try {
            // dd($request->all());
            $bank_number_payee = $request->bank_number_payee;
            $amount = $request->amount;
            $deviceid = $request->device;
            $pin = $request->pin;
            $acc_num = $request->acc_num;
            $scb = new SCB($deviceid, $pin, $acc_num);
            $login = $scb->login();

            if ($login) {
                $amount = (int) $amount;
                if ($amount < 100) {
                    throw new Exception('ยอดถอนต้อง 100 บาทขึ้นไป ถึงสามารถถอนจาก SCB ไป วอเลทได้');
                }
                //เงื่อนไขนี้คือ รายการถอนมา เป็น บัญชี ทรูวอเลท แต่ ใช้ ธนาคาร scb ถอนเงิน
                // dd('เงื่อนไขนี้คือ รายการถอนมา เป็น บัญชี ทรูวอเลท แต่ ใช้ ธนาคาร scb ถอนเงิน',$accountTo ,$amount_withdraw);
                $twTopupVerify = $scb->twTopupVerify($amount, $bank_number_payee);
                if ($twTopupVerify['status']['code'] != 1000) {
                    throw new Exception($twTopupVerify['status']['description']);
                }

                $twTopupConfrim = $scb->twTopupConfrim($amount, $bank_number_payee, $twTopupVerify);
                // $twTopupConfrimdataMockup = [
                //     'status' => [
                //         'code' => 1000,
                //         'header' => '',
                //         'description' => 'สำเร็จ'
                //     ],
                //     'data' => [
                //         'paymentId' => '202306085MOhKz8rWExi0w9Mn',
                //         'tranDateTime' => '2023-06-08T12:01:42.214+07:00',
                //         'additionalMetaData' => [
                //             'paymentInfo' => [
                //                 [
                //                     'type' => 'MINI_QR',
                //                     'title' => 'ผู้รับเงินสามารถสแกนคิวอาร์โค้ดนี้เพื่อตรวจสอบสถานะการเติมเงิน',
                //                     'header' => null,
                //                     'description' => null,
                //                     'imageURL' => null,
                //                     'imageUrl' => null,
                //                     'QRstring' => '0046000600000101030140225202306085MOhKz8rWExi0w9Mn5102TH9104CD80'
                //                 ]
                //             ],
                //             'callbackUrl' => null,
                //             'merchantInfo' => null,
                //             'extraData' => null,
                //             'pointRedemption' => null
                //         ],
                //         'remainingPoint' => null
                //     ]
                // ];
                if ($twTopupConfrim['status']['code'] != 1000) {
                    throw new Exception($twTopupVerify['status']['description']);
                }
                $res['success'] = true;
                $res['data'] = $twTopupConfrim;
                $res['msg'] = 'ถอนเงินสำเร็จ';
            } else {
                throw new Exception('SCB::ไม่สามารถติดต่อกับธนาคารได้ โปรดลองใหม่อีกครั้ง');
            }
        } catch (Exception $e) {
            $res['success'] = false;
            $res['data'] = null;
            $res['msg'] = $e->getMessage();
            return  response()->json($res, 400);
        }

        return response()->json($res, 200);
    }




    public function getNameAcountScb(Request $request)
    {
        try {
            $bank_number_payee = $request->bank_number_payee;
            $back_code = $request->back_code;
            $deviceid = $request->device;
            $pin = $request->pin;
            $acc_num = $request->acc_num;
            $scb = new SCB($deviceid, $pin, $acc_num);
            $login = $scb->login();

            $scb = new SCB($deviceid, $pin, $acc_num);
            $login =  $scb->login();

            if (!$login) {
                throw new Exception("ล็อกอินไม่สำเร็จ");
            }

            $transfer_verify = $scb->transfer_verify($bank_number_payee, $back_code, 1);
            if ($transfer_verify['status']['code'] != 1000) {
                throw new Exception("กรุณาตรวจสอบธนาคารและเลขบัญชีให้ถูกต้อง");
            }

            $res['success'] = true;
            $res['data'] = $transfer_verify['data']['accountToName'];
            $res['msg'] = 'ดึงชื่อสำเร็จ';
        } catch (Exception $e) {
            $res['success'] = false;
            $res['data'] = null;
            $res['msg'] = $e->getMessage();
            return  response()->json($res, 400);
        }
        return response()->json($res, 200);
    }
}
