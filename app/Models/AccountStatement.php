<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AccountStatement extends Model
{
    use HasFactory;

    private $date1 = '';
    private $date2 = '';
    private $storeID = '';
    private $profileID = '';
    private $totalBalance = 0;



    public function get($name)
    {
        return $this->$name;
    }

    public function set($name, $value)
    {
        $this->$name = $value;
    }


    public function get_user_transactions()
    {
        error_log(("GETTING ACCOUNT STATEMENT >>>>>>>>"));
        $pos = DB::table('pos_receipts')
            ->whereDate('pos_receipts.created_at', '>=', $this->date1)
            ->whereDate('pos_receipts.created_at', '<=', $this->date2)
            ->where('pos_receipts.branch_id', '=', $this->storeID)
            ->where('pos_receipts.profile_id', '=', $this->profileID)
            ->select(DB::raw('
			 pos_receipts.receipt_no as receipt_no,
			 pos_receipts.type as type,
			 pos_receipts.description as description,
			 pos_receipts.created_at as date,
			 pos_receipts.total_bill as amount,
             pos_receipts.transaction_id as transaction_id
			'))
            ->get();

        $vouchers = DB::table('vouchers')
            ->whereDate('vouchers.created_at', '>=', $this->date1)
            ->whereDate('vouchers.created_at', '<=', $this->date2)
            ->where('vouchers.branch_id', '=', $this->storeID)
            ->where('vouchers.type', '!=', 'JRV')
            ->where('vouchers.profile_id', '=', $this->profileID)
            ->select(DB::raw('
			vouchers.voucher_no as receipt_no,
			vouchers.type as type,
			vouchers.account_type as account_type,
			vouchers.memo as description,
			vouchers.created_at as date,
			vouchers.total_amount as payment
		'))
            ->get();

        $receipts = DB::table('receipts')
            ->whereDate('receipts.created_at', '>=', $this->date1)
            ->whereDate('receipts.created_at', '<=', $this->date2)
            ->where('receipts.branch_id', '=', $this->storeID)
            ->where('receipts.profile_id', '=', $this->profileID)
            ->select(DB::raw('
			receipts.receipt_no as receipt_no,
			receipts.type as type,
			receipts.description as description,
			receipts.created_at as date,
			receipts.total_bill as amount
		'))
            ->get();

        $payments = DB::table('receipt_payments')
            ->leftJoin('pos_receipts', 'pos_receipts.id', '=', 'receipt_payments.receipt_id')
            ->whereDate('receipt_payments.created_at', '>=', $this->date1)
            ->whereDate('receipt_payments.created_at', '<=', $this->date2)
            ->where('receipt_payments.branch_id', '=', $this->storeID)
            ->where('pos_receipts.profile_id', '=', $this->profileID)
            ->select(DB::raw('
			receipt_payments.receipt_no as receipt_no,
			receipt_payments.source_type as type,
			receipt_payments.description as description,
			receipt_payments.created_at as date,
			receipt_payments.trans_total_amount as payment
		'))
            ->get();


        $receipt_payments  = DB::table('receipt_payments')
            ->leftJoin('receipts', 'receipts.id', '=', 'receipt_payments.receipt_id')
            ->whereDate('receipt_payments.created_at', '>=', $this->date1)
            ->whereDate('receipt_payments.created_at', '<=', $this->date2)
            ->where('receipt_payments.branch_id', '=', $this->storeID)
            ->where('receipts.profile_id', '=', $this->profileID)
            ->select(DB::raw('
			receipt_payments.receipt_no as receipt_no,
			receipt_payments.source_type as type,
			receipt_payments.description as description,
			receipt_payments.created_at as date,
			receipt_payments.trans_total_amount as payment
		'))
            ->get();


        $bank_transactions = DB::table('bank_transactions')
            ->join('bank_transaction_payees', 'bank_transactions.id', '=', 'bank_transaction_payees.bank_transaction_id')
            ->whereDate('bank_transactions.created_at', '>=', $this->date1)
            ->whereDate('bank_transactions.created_at', '<=', $this->date2)
            ->where('bank_transactions.branch_id', '=', $this->storeID)
            ->where('bank_transactions.entity', '=', 'Yes')
            ->where('bank_transaction_payees.profile_id', '=', $this->profileID)
            ->select(DB::raw('
			bank_transactions.receipt_no as receipt_no,
			bank_transactions.type as type,
			bank_transactions.description as description,
			bank_transactions.created_at as date,
			bank_transactions.amount as payment'))
            ->get();

        $t  = [];

        if ($pos != NULL) {
            foreach ($pos as $e) {
                $exp = DB::select(DB::raw("SELECT amount FROM sub_transactions WHERE account_name like 'Expiry%' and transaction_id = '$e->transaction_id'"));
                $expAdjustAmount = $exp != null ? $exp[0]->amount : 0;
                // $exp = DB::table('sub_transactions')
                // ->selectRaw('amount')
                // ->where('account_name', '<>', 1)
                // ->groupBy('status')
                // ->get();

                $t[] =  array(
                    'date' => $e->date,
                    'receipt_no' => $e->receipt_no,
                    'description' => $e->description,
                    'type' => $e->type,
                    'amount' => $e->amount,
                    'payment' => 0,
                    'balance' => 0,
                    'expadjust' => $expAdjustAmount,
                );
            }
        }


        if ($vouchers != NULL) {
            foreach ($vouchers as $e) {
                $payment = 0;
                $amount  = 0;

                if ($e->type == 'EXV') {
                    $amount  = $e->payment;
                    $payment = $e->payment;
                } else if ($e->type == 'CRV') {
                    $amount  = 0;
                    $payment = $e->payment;
                } else if ($e->type == 'DBV') {
                    $amount  = 0;
                    $payment = $e->payment;
                } else if ($e->type == 'OPB') {
                    if ($e->accountType == 'Debit') {
                        //when cash is receivable
                        $amount  = $e->payment;
                        $payment = 0;
                    } else {
                        //credit when cash is payable
                        $amount  = 0;
                        $payment = $e->payment;
                    }
                }

                $t[] =  array(
                    'date' => $e->date,
                    'receipt_no' => $e->receipt_no,
                    'description' => $e->description,
                    'type' => $e->type,
                    'amount' => $amount,
                    'payment' => $payment,
                    'balance' => 0
                );
            }
        }

        if ($receipts != NULL) {
            foreach ($receipts as $e) {
                $t[] =  array(
                    'date' => $e->date,
                    'receipt_no' => $e->receipt_no,
                    'description' => $e->description,
                    'type' => $e->type,
                    'amount' => $e->amount,
                    'payment' => 0,
                    'balance' => 0
                );
            }
        }

        if ($payments != NULL) {
            foreach ($payments as $e) {
                $t[] =  array(
                    'date' => $e->date,
                    'receipt_no' => $e->receipt_no,
                    'description' => $e->description,
                    'type' => $e->type,
                    'amount' => 0,
                    'payment' => $e->payment,
                    'balance' => 0
                );
            }
        }

        if ($receipt_payments != NULL) {
            foreach ($receipt_payments as $e) {
                $t[] =  array(
                    'date' => $e->date,
                    'receipt_no' => $e->receipt_no,
                    'description' => $e->description,
                    'type' => $e->type,
                    'amount' => 0,
                    'payment' => $e->payment,
                    'balance' => 0
                );
            }
        }

        if ($bank_transactions != NULL) {
            foreach ($bank_transactions as $e) {
                $t[] =  array(
                    'date' => $e->date,
                    'receipt_no' => $e->receipt_no,
                    'description' => $e->description,
                    'type' => $e->type,
                    'amount' => 0,
                    'payment' => $e->payment,
                    'balance' => 0
                );
            }
        }

        usort($t, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        $list = [];

        if ($t != NULL) {
            foreach ($t as $e) {

                $this->processAmountBalance($e['type'], $e['amount']);
                $e['balance'] =  $this->totalBalance;

                $this->processPaymentBalance($e['type'], $e['payment']);
                $e['balance'] =  $this->totalBalance;

                $list[] = $e;
            }
        }

        //add the expiry returns
        // $expReturns = DB::table('return_vouchers')
        //     ->whereDate('return_vouchers.created_at', '>=', $this->date1)
        //     ->whereDate('return_vouchers.created_at', '<=', $this->date2)
        //     ->where('return_vouchers.supplier_id', '=', $this->profileID)
        //     ->groupBy('return_vouchers.bill_no')
            // ->select(DB::raw('
            // 	return_vouchers.receipt_id as receipt_id,
            // 	return_vouchers.created_at as date,
            // 	return_vouchers.return_vouchersy as payment
            // '))
            // ->get();
    //      $expReturns = DB::table('return_vouchers')
    // ->whereDate('return_vouchers.created_at', '>=', $this->date1)
    // ->whereDate('return_vouchers.created_at', '<=', $this->date2)
    // ->where('return_vouchers.supplier_id', '=', $this->profileID)
    // ->select('return_vouchers.bill_no')
    // ->groupBy('return_vouchers.bill_no')
    // ->get();
    
    //this is soumik code       
    $expReturns = DB::table('return_vouchers')
    ->whereDate('return_vouchers.created_at', '>=', $this->date1)
    ->whereDate('return_vouchers.created_at', '<=', $this->date2)
    ->where('return_vouchers.supplier_id', '=', $this->profileID)
    ->select(
        'return_vouchers.bill_no',
        DB::raw('MAX(return_vouchers.created_at) as created_at'),
        DB::raw('SUM(return_vouchers.total) as total')
    )
    ->groupBy('return_vouchers.bill_no')
    ->get();

   


        if ($expReturns != NULL) {
            foreach ($expReturns as $e) {


                $list[] = array(
                    'date' => $e->created_at,
                    'receipt_no' => $e->bill_no,
                    'description' => 'Expiry Return',
                    'type' => 'EXPR',
                    'amount' => $e->total,
                    'payment' => 0,
                    'balance' => 0
                );
            }
        }
        return $list;
    }

    public function processAmountBalance($type, $amount)
    {
        if ($type == 'INE' || $type == 'TRN' || $type == 'RPU' || $type == 'SLS' || $type == 'INV' || $type == 'OPB') {
            $this->totalBalance = $this->totalBalance + (float) $amount;
        } else if ($type == 'RFD' || $type == 'PUR' || $type == 'RFR') {
            $this->totalBalance = $this->totalBalance - (float) $amount;
        }
    }

    public function processPaymentBalance($type, $payment)
    {
        if ($type == 'INE' || $type == 'TRN' || $type == 'RPU' || $type == 'SLS' || $type == 'INV' || $type == 'DBV' || $type == 'REF' || $type == 'OPB') {
            $this->totalBalance = $this->totalBalance  - (float) $payment;
        } else if ($type == 'RFD' || $type == 'PUR' || $type == 'RFR' || $type == 'CRV' || $type == 'CHQ' || $type == 'FTR') {
            $this->totalBalance = $this->totalBalance  + (float) $payment;
        }
    }

    public function sum_user_list_balance($list)
    {
        $b = 0;

        if ($list != NULL) {
            $list = array_reverse($list);
            $b = $list[0]['balance'];
        }

        return $b;
    }
}
