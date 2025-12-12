<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Branch;
use App\Models\PosReceipt;
use App\Models\ThermalReceipt;
use App\Models\Transaction;
use App\Models\SubTransaction;
use App\Models\PosSubReceipt;
use App\Models\ReceiptPayment;
use App\Models\Stock;
use App\Models\TransferStore;
use App\Models\Profiler;
use App\Models\DateFilters;
use App\Models\Printer;
use App\Models\PrinterReceipt;
use App\Models\SubReceipt;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;


class PosController extends Controller
{
    public function index()
    {

        $user =  new User(); 
        $stores = $user->getUserStores();
        
        $storeTaxes = Branch::with([
            'taxName1:chart_accounts.id,chart_accounts.account_name as chartName',
            'taxName2:chart_accounts.id,chart_accounts.account_name as chartName',
            'taxName3:chart_accounts.id,chart_accounts.account_name as chartName',
        ])
        ->where('id',Auth::user()->branch_id)
        ->get();

        $defaultCustomer = Profiler::where('account_type','Default Customer')
        ->first();
        
        return [
            'defaultCustomer' => $defaultCustomer,
            'storeTaxes' => $storeTaxes,
            'stores' => $stores,
            'currentUserID' => Auth::user()->id,
            'storeName' => $storeTaxes[0]->name
        ];
    } 

    public function updateReceipt(Request $request){

        error_log("&&& UPDATE RECEIPT CALLED ....");
        error_log("the receipt no passed is ".$request->receiptNo);
        DB::beginTransaction();
        try {
        // find the transaction from pos_receipts table and update
        $receipt = PosReceipt::with([
            'profileName:profilers.id,profilers.account_title as accountName',
        ])
        ->where('receipt_no', $request->receiptNo)
        ->where('type','INE')
        ->first();

        error_log(message: 'receipt found '.$receipt);
        //total_gross
        //total_tendered
        //total change
        //total_tax1
        //total_tax2
        //total_tax
       error_log($request->total_bill);
       error_log($request->total_tendered);
        error_log($request->total_change);
        error_log($receipt->transaction_id);
        error_log($receipt->receipt_no);

        $transID=$receipt->transaction_id;
        $receiptNo = $receipt->receipt_no;
        $receipt->total_gross_amt=$request->total_gross_amt;
        $receipt->total_tendered=$request->total_tendered;
        $receipt->total_bill=$request->total_bill;
        $receipt->total_change= $request->total_change;
        $receipt->total_tax1=$request->total_tax1;
        $receipt->total_tax2= $request->total_tax2;
        $receipt->total_tax=$request->total_tax;
        // $receipt->payment_type=$request->payment_method;
        $receipt->update();



       
        //echo "the exisitng transaction : ".$request.transa

        //find all entries for the transaction in sub_receipts table and delete them
        $receiptItems = PosSubReceipt::with([
            'stockDetail:stocks.id,stocks.qty,stocks.sale_price',
        ])
        ->where('pos_receipt_id', $receipt->id)
        ->get();

        foreach($receiptItems as $item)
        {


           //$receiptItem = PosSubReceipt::find($item->id);
           error_log(' DELETING RECEIPT ITEM '.$item->stock_id);
           error_log(' DELETING RECEIPT ITEM '.$item->total_unit);

          // $item->total_unit = 

            $item->delete();
            //$receiptItem->update();
            //MANAGING STOCKS
            $s = new Stock();
            $s->addReduceStock($item->stock_id,$item->total_unit,'RFD');
            
        }


        //re-create the entries with the same trans id 
        $itemLists = json_decode($request->item_list);

        foreach($itemLists as $item)
                {
                    error_log('no of units '.$item->unit);
                    if($item->unit == ''){
                        $item->unit=0;
                    }
                    $PosSubReceipt = new PosSubReceipt([
                        'pos_receipt_id'    => $receipt->id,
                        'mode'    			=> $item->mode,
                        'stock_id'    		=> $item->stockID,
                        'item_name'       	=> $item->productName,
                        'generic_name'    	=> $item->generic,
                        'item_description'  => $item->itemDescription,
                        'unit'        		=> $item->unit,
                        'total_unit'        => $item->totalUnit,
                        'free_unit'   		=> $item->freeUnit,
                        'supplier_bonus'   	=> $item->supplierBonus,
                        'batch_no'   		=> $item->batchNo,
                        'pack_size'   		=> $item->packSize,
                        'sheet_size'   		=> $item->sheetSize,
                        'purchase_price'   	=> $item->purchasePrice,
                        'selling_price'   	=> $item->sellingPrice,
                        'mrp'   			=> $item->mrp,
                        'brand_name'   		=> $item->brandName,
                        'sector_name'   	=> $item->sectorName,
                        'category_name'   	=> $item->categoryName,
                        'product_type'   	=> $item->productType,
                        'expiry_date'   	=> Date('Y-m-d',strtotime($item->expiryDate)),
                        'item_disc'   		=> $item->itemDisc,
                        'purchase_disc'   	=> 0,
                        'after_disc'   	    => 0,
                        'tax_1'   			=> $item->tax1,
                        'tax_2'   			=> $item->tax2,
                        'tax_3'   			=> $item->tax3,
                        'sub_total'   	    => $item->subTotal,
                    ]);

                    $PosSubReceipt->save();


             //MANAGING STOCKS
                    $s = new Stock();
                    error_log(('re adding stock '.$item->totalUnit));
                    $s->addReduceStock($item->stockID,$item->totalUnit,'INE');

                }
  

        DB::commit();

        $response = response()->json([
            'alert' =>'info',
            'msg'   =>'Receipt Created Successfully now',
            'rno'   => $receiptNo

        ]);

        }catch (\Exception $e) {
            DB::rollBack();
            
            $response = response()->json([
                'alert' =>'danger',
                'msg'   => $e,

            ]);

            throw $e;
        }

        return $response;

    }
    public function saveReceipt(Request $request)
    {
        error_log("&&& SAVE RECEIPT CALLED ....");
        $request->validate([
            'profile_id'   		 => ['required'],
            'payment_list'   	 => ['required'],
            'discount'   		 => ['required'],
            'total_tendered'   	 => ['required'],
            'total_change'   	 => ['required'],
            'total_gross_amt'    => ['required'],
            'total_bill'   		 => ['required'],
            'total_tax1'   		 => ['required'],
            'total_tax2'   		 => ['required'],
            'total_tax3'   		 => ['required'],
            'total_tax'   		 => ['required'],
            'payment_method'   	 => ['required'],
            'status'  			 => ['required'],
            'type'   			 => ['required'],
            'item_list'   		 => ['required'],
            'counter_entry'      => ['required'],
        ]);

        DB::beginTransaction();

        try
        {
            $itemLists = json_decode($request->item_list);
            $counterEntry = json_decode($request->counter_entry);
            $paymentLists = json_decode($request->payment_list);

            if($itemLists != NULL)
            {

                $narration = ($request->description == '' ? 'Transaction occurred from pos screen' : $request->description);
                    
                $transaction = new Transaction([
                    'narration'         => $narration,
                    'generated_source'  => $request->type,
                    'branch_id'         => Auth::user()->branch_id,
                ]);
                
                $transaction->save();

                foreach($counterEntry as $item)
                {
                    $subTransaction = new SubTransaction([
                        'transaction_id'     => $transaction->id,
                        'account_id'     	 => $item->accountID,
                        'account_name'	 	 => $item->accountHead,
                        'amount'      	     => $item->amount,
                        'type'      		 => $item->type,
                    ]);
                    
                    $subTransaction->save();

                }

                

                //create a profile
                if($request->patient_details !=null){
                    $item = new Profiler([
                        'account_title' =>$request->patient_details,
                        'email_address' =>'',
                        'contact_no' =>$request->description,
                        'national_id'=>'',
                        'address' =>$request->address,
                        'description'=>'',
                        'account_type'=>'Customer',
                        'status' => 'Active',
                        'created_user' => '1'

                    ]); 
                
                $item->save();
            }
                //create receipt
                
                $t = new  PosReceipt();
                $receiptNo =  $t->generateID($request->type);
            
                $receiptItem 			= new PosReceipt([
                    'transaction_id' 	=> $transaction->id,
                    'receipt_no'     	=> $receiptNo,
                    'discount'   		=> $request->discount,
                    'profile_id' 		=> $request->profile_id,
                    'payment_method'    => $request->payment_method,
                    'total_gross_amt'   => $request->total_gross_amt,
                    'total_bill' 		=> $request->total_bill,
                    'total_tendered'   	=> $request->total_tendered,
                    'total_change'   	=> $request->total_change,
                    'total_tax1'   	 	=> $request->total_tax1,
                    'total_tax2'   	 	=> $request->total_tax2,
                    'total_tax3'   	 	=> $request->total_tax3,
                    'total_tax'   	 	=> $request->total_tax,
                    'description'   	=> $request->description,
                    'doctor_details'   	=> $request->doctor_details,
                    'patient_details'   => $request->patient_details,
                    'bill_no'           => '',
                    'created_by'      	=> Auth::user()->id,
                    'receipt_date'      => date('Y-m-d'),
                    'return_receipt'    => $request->search_receipt_no,
                    'type'      		=> $request->type,
                    'status'         	=> $request->status,
                    'branch_id'      	=> Auth::user()->branch_id,
                ]);

                $receiptItem->save();

                //IF USED CARDS
                $t->passBankTransaction($transaction->id,$receiptNo,$narration,$paymentLists,$request->profile_id);

                foreach($itemLists as $item)
                {
                    //error_log('>>>>>ITEM FOR SUBRECEIPT >>>'.$item);
                    $PosSubReceipt = new PosSubReceipt([
                        'pos_receipt_id'    => $receiptItem->id,
                        'mode'    			=> $item->mode,
                        'stock_id'    		=> $item->stockID,
                        'item_name'       	=> $item->productName,
                        'generic_name'    	=> $item->generic,
                       'item_description'  => $item->itemDescription,
                        'unit'        		=> $item->unit,
                        'total_unit'        => $item->totalUnit,
                        'free_unit'   		=> $item->freeUnit,
                        'supplier_bonus'   	=> $item->supplierBonus,
                        'batch_no'   		=> $item->batchNo,
                        'pack_size'   		=> $item->packSize,
                        'sheet_size'   		=> $item->sheetSize,
                        'purchase_price'   	=> $item->purchasePrice,
                        'selling_price'   	=> $item->sellingPrice,
                        'mrp'   			=> $item->mrp,
                        'brand_name'   		=> $item->brandName,
                        'sector_name'   	=> $item->sectorName,
                        'category_name'   	=> $item->categoryName,
                        'product_type'   	=> $item->productType,
                        'expiry_date'   	=> Date('Y-m-d',strtotime($item->expiryDate)),
                        'item_disc'   		=> $item->itemDisc,
                        'purchase_disc'   	=> 0,
                        'after_disc'   	    => 0,
                        'tax_1'   			=> $item->tax1,
                        'tax_2'   			=> $item->tax2,
                        'tax_3'   			=> $item->tax3,
                        'sub_total'   	    => $item->subTotal,
                    ]);
                    
                    $PosSubReceipt->save();

                    //MANAGING STOCKS
                    $s = new Stock();
                    $s->addReduceStock($item->stockID,$item->totalUnit,$request->type);
                }
                
                if($paymentLists != NULL)
                {
                    foreach($paymentLists as $item)
                    {
                        $payments = new ReceiptPayment([
                            'transaction_id'    	=> $transaction->id,
                            'receipt_id'    		=> $receiptItem->id,
                            'account_no'    		=> $item->accountNo,
                            'auth_code'    			=> $item->authCode,
                            'card_balance'	 		=> $item->cardBalance,
                            'change'       			=> $item->change,
                            'entry_mode'    		=> $item->entryMode,
                            'gift_card_ref'  		=> $item->giftCardRef,
                            'host_response'        	=> $item->hostResponse,
                            'payment_type'        	=> $item->paymentType,
                            'round_off'   			=> $item->roundOff,
                            'tendered'   			=> $item->tendered,
                            'terminal_id'   		=> $item->terminalId,
                            'trans_amount'   		=> $item->transAmount,
                            'trans_date'   			=> date('Y-m-d'),
                            'trans_id'   			=> $item->transId,
                            'trans_ref'   			=> $item->transRef,
                            'trans_status'   		=> $item->transStatus,
                            'trans_time'   			=> date('H:i:s'),
                            'trans_total_amount'   	=> $item->transTotalAmount,
                            'trans_type'   			=> $item->transType,
                            'source_type'   	    => $request->type,
                            'description'   	    => $narration,
                            'receipt_no'   	    	=> $receiptNo,
                            'created_by'   	    	=> Auth::user()->id,
                            'branch_id'   	    	=> Auth::user()->branch_id,
                        ]);
                        
                        $payments->save();
                    }
                }

                //ADD TRANSFER STORE IF TRANSFER
                if($request->type == 'TRN')
                {
                    $stockStore = new TransferStore([
                        'receipt_id'   => $receiptItem->id,
                        'branch_id'   => $request->transfer_store_id,
                    ]);
                    
                    $stockStore->save();
                }


                //get the thermal receipt
                $thermal_printer = Config::get('constant.thermal_printer');
                
                if($thermal_printer == 'ON')
                {
                    error_log("&&&&& THERMAP PRINTER ".$thermal_printer."...RECEIPT ID ".$receiptItem->id);

                   // $this->thermalReceipt($receiptItem->id);
                   //sam: create ther receipt

                }
                
                $response = response()->json([
                    'alert' =>'info',
                    'msg'   =>'Receipt Created Successfully now',
                    'rno'   => $receiptNo
                ]);

            }
            else
            {
                $response = response()->json([
                    'alert' =>'danger',
                    'msg'   =>'Receipt cannot be created'
                ]);
            }

            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollBack();

            $response = response()->json([
                'alert' =>'danger',
                'msg'   => $e
            ]);

            throw $e;
        }


        return $response;
       
    }

    public function thermalReceipt($receiptId)
    {
        //get store info
        $storeInfo = Branch::where('id',Auth::user()->branch_id)->first();

        
        $printerInfo = Printer::where('branch_id',Auth::user()->branch_id)
        ->where('default_printer','Yes')
        ->first();

        $receiptInfo = PosReceipt::where('id',$receiptId)->first();

        $customerInfo = Profiler::where('id',$receiptInfo->profile_id)->first();

        $paymentInfo = ReceiptPayment::where('receipt_id',$receiptId)->get();

        $statements  = PrinterReceipt::where('branch_id',Auth::user()->branch_id)
        ->where('status','Active')
        ->get();

        $itemList  = PosSubReceipt::where('pos_receipt_id',$receiptId)
        ->get();
        error_log("PRINTER INFO ..".$printerInfo);
        if($printerInfo != NULL)
        {
            error_log("PRINTER SETTING FOUND");
            //GENERATE RECEIPT
            $receipt_info = array(
                'printer_name' => $printerInfo->printer_name,
                'branch_name' => $storeInfo->name,
                'branch_code' => $storeInfo->code,
                'address' => $storeInfo->address,
                'license_no' => $storeInfo->license_no,
                'telephone' => $storeInfo->contact,
                'receipt_no' => $receiptInfo->receipt_no,
                'type' => $receiptInfo->type,
                'customer' => $customerInfo->account_title,
                'customer_tel' => $customerInfo->contact_no,
                'date_time' => date('d-m-Y H:i A',strtotime($receiptInfo->created_at)),
                'emp_id' => Auth::user()->name,
                'description' =>  $receiptInfo->description,
                'doctor_details' =>  $receiptInfo->doctor_details,
                'patient_details' =>  $receiptInfo->patient_details,
                'sub_total' => $receiptInfo->total_gross_amt,
                'total_discount' => $receiptInfo->discount,
                'total' => $receiptInfo->total_bill,
                'total_tax' => $receiptInfo->total_tax,
                'invoice_payment' => $paymentInfo,
                'tendered' => $receiptInfo->total_tendered,
                'change' => $receiptInfo->total_change,
                'items' => $itemList,
                'statements' => $statements,
            );
            error_log("RECEIPT DETAILS ". print_r($receipt_info, true));
            $r = new  ThermalReceipt();
            $r->generate_print($receipt_info);
        }
    }

    public function searchReceipt(Request $request)
    {

        // echo $request;
        $request->validate([
            'receipt_no' => ['required'],
        ]);

        $receipt = PosReceipt::with([
            'profileName:profilers.id,profilers.account_title as accountName',
        ])
        ->where('receipt_no', $request->receipt_no)
        ->where('type','INE')
        ->first();

        error_log('receipt found '.$receipt);
        
        $receiptItems = PosSubReceipt::with([
            'stockDetail:stocks.id,stocks.qty,stocks.sale_price',
        ])
        ->where('pos_receipt_id', $receipt->id)
        ->get();
        
        return [
        'receipt' => $receipt,
        'receiptItems' => $receiptItems
        ];
    }
    
    public function searchPurchaseReceipt(Request $request)
    {
        $request->validate([
            'receipt_no' => ['required'],
        ]);
        error_log("Receipt no to search for >>>>".$request->receipt_no);
        $receipt = PosReceipt::with([
            'profileName:profilers.id,profilers.account_title as accountName',
        ])
        ->where('receipt_no', $request->receipt_no)
        // ->where('type','PUR')
        // ->orWhere('type','CHAL')
        ->first();

        error_log(' Receipt Found '.$receipt);
        
        $receiptItems = PosSubReceipt::with([
            'stockDetail:stocks.id,stocks.qty,stocks.sale_price',
        ])
        ->where('pos_receipt_id', $receipt->id)
        ->get();
        
        return [
        'receipt' => $receipt,
        'receiptItems' => $receiptItems
        ];
    }

    public function updatePurchaseReceipt(Request $request){
        error_log(">>>> UPDATE PURCHASE RECEIPT CALLED >>>>".$request->searchReceiptNo);
        Log::info($request);
        error_log("please delete this updated log");

       

        DB::beginTransaction();

        try
        {

            //get the receipt
            $purchaseReceipt = PosReceipt::with([
                'profileName:profilers.id,profilers.account_title as accountName',
            ])

            ->where('receipt_no', $request->search_receipt_no)
            ->where('type','PUR')
            ->first();
            error_log(message: 'purchase receipt found '.$purchaseReceipt);

            //update the receipt
            // $transID=$receipt->transaction_id;
            if($purchaseReceipt==null){
                error_log("NULL receipt");
            }
            $receiptNo = $purchaseReceipt->receipt_no;
            error_log("ASsigned receipt no ".$receiptNo);
            $purchaseReceipt->total_gross_amt=$request->total_gross_amt;
            $purchaseReceipt->total_tendered=$request->total_tendered;
            $purchaseReceipt->total_bill=$request->total_bill;
            $purchaseReceipt->total_bill='200';
            $purchaseReceipt->total_change= $request->total_change;
            $purchaseReceipt->total_tax1=$request->total_tax1;
            $purchaseReceipt->total_tax2= $request->total_tax2;
            $purchaseReceipt->total_tax=$request->total_tax;
            $purchaseReceipt->payment_method=$request->payment_method;
            $purchaseReceipt->update();
    
            error_log("1. >>>>>>>PURCHASE RECEIPT UPDATED");
             //find all entries for the transaction in sub_receipts table and delete them
         $receiptItems = PosSubReceipt::with([
            'stockDetail:stocks.id,stocks.qty,stocks.sale_price',
         ])
        ->where('pos_receipt_id', $purchaseReceipt->id)
        ->get();

        foreach($receiptItems as $item)
        {


           //$receiptItem = PosSubReceipt::find($item->id);
           error_log(' DELETING RECEIPT ITEM '.$item->stock_id);
           error_log(' DELETING RECEIPT ITEM '.$item->total_unit);

          // $item->total_unit = 

            $item->delete();
            error_log("2. >>>>>>>DELETTING SUB RECEIPTS..".$item);

          
            
        }

            $itemLists = json_decode($request->item_list);
            $counterEntry = json_decode($request->counter_entry);

            if($itemLists != NULL)
            {
               
                foreach($itemLists as $item)
                {
                    //for multi row with empty data
                    if($item->productName !=null){

                    //sam 18/06/24
                    $item->totalUnit = $item->packSize * $item->sheetSize+$item->freeUnit*$item->sheetSize;
                    //$item->sellingPrice = $item->itemDescription;
                    
                    $stock_id = $item->stockID;
                    error_log(">>>>>>>>Stock id of  products ".$stock_id);

                    $PosSubReceipt = new PosSubReceipt([
                        'pos_receipt_id'    => $purchaseReceipt->id,
                        'mode'    			=> $item->mode,
                        'stock_id'    		=> $stock_id,
                        'item_name'       	=> $item->productName,
                        'generic_name'    	=> $item->generic,
                        'item_description'  => $item->itemDescription,
                        'unit'        		=> $item->unit,
                        'total_unit'        => $item->totalUnit,
                        'free_unit'   		=> $item->freeUnit,
                        'supplier_bonus'   	=> $item->supplierBonus,
                        'batch_no'   		=> $item->batchNo,
                        'pack_size'   		=> $item->packSize,
                        'sheet_size'   		=> $item->sheetSize,
                        'purchase_price'   	=> $item->purchasePrice,
                        'selling_price'   	=> $item->sellingPrice,
                        'mrp'   			=> $item->mrp,
                        'brand_name'   		=> $item->brandName,
                        'sector_name'   	=> 12   ,
                        'category_name'   	=> 13,
                        'product_type'   	=> $item->productType,
                        //'expiry_date'   	=> Date('Y-m-d',strtotime($item->expiryDate)),
                        'expiry_date'   	=> $this->createDate($item->expiryDate),
                        'item_disc'   		=> $item->cusDisc,
                        'purchase_disc'   	=> $item->itemDisc,
                        'after_disc'   	    => $item->purchaseAfterDisc,
                        'tax_1'   			=> $item->tax1,
                        'tax_2'   			=> $item->tax2,
                        'tax_3'   			=> $item->tax3,
                        'sub_total'   	    => $item->subTotal,
                    ]);
                    if( $request->bill_no!=''){

                        $PosSubReceipt->save();
                        error_log(">>>> SUB RECEIPT UPDATED ");
                    }

                    //MANAGING STOCKS
                    $s = new Stock();

                    $s->addReduceStock($item->stockID,$item->totalUnit,'PUR');

                  }
                }
                
                // $narration =  ($request->description == '' ? 'Transaction occurred from purchase screen' : $request->description);

                // $transaction = new Transaction([
                //     'narration'     => $narration,
                //     'generated_source' => $request->type,
                //     'branch_id'      => Auth::user()->branch_id,
                // ]);
                
                // if( $request->bill_no!=''){
                //     $transaction->save();
                //     error_log(">>>>  TRANS SAVED ");


                // }

                // foreach($counterEntry as $item)
                // {
                // 	$subTransaction = new SubTransaction([
                // 		'transaction_id'     => $transaction->id,
                // 		'account_id'     	 => $item->accountID,
                // 		'account_name'	 	 => $item->accountHead,
                // 		'amount'      	     => $item->amount,
                // 		'type'      		 => $item->type,
                // 	]);
                //     if( $request->bill_no!=''){

                // 	    $subTransaction->save();
                //         error_log(">>>> SUB TRANS SAVED ");

                //     }
                // }
                
                //sam
                //$il = implode($itemLists);
                error_log(">>>> UPDATE PURCHASE RECEIPT FINISHED ");

                $response = response()->json([
                    'alert' =>'info',
                    'msg'   =>'Purcahse Receipt Updated Successfully',
                    'rno'   => '1234'
                ]);

            }
            else
            {
                $response = response()->json([
                    'alert' =>'danger',
                    'msg'   =>'Receipt cannot be created'
                ]);
            }

            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollBack();

            $response = response()->json([
                'alert' =>'danger',
                'msg'   => $e
            ]);

            throw $e;
        }


        return $response;

    }

    public function savePurchaseReceipt(Request $request)
    {
        error_log(">>>> SAVE PURCHASE RECEIPT CALLED ");
        $request->validate([
            'profile_id'   		 => ['required'],
            'payment_list'   	 => ['required'],
            'discount'   		 => ['required'],
            'total_tendered'   	 => ['required'],
            'total_change'   	 => ['required'],
            'total_gross_amt'    => ['required'],
            'total_bill'   		 => ['required'],
            'total_tax1'   		 => ['required'],
            'total_tax2'   		 => ['required'],
            'total_tax3'   		 => ['required'],
            'total_tax'   		 => ['required'],
            'payment_method'   	 => ['required'],
            'status'  			 => ['required'],
            'type'   			 => ['required'],
            'item_list'   		 => ['required'],
            'counter_entry'   	 => ['required'],
        ]);

        DB::beginTransaction();

        try
        {
            $itemLists = json_decode($request->item_list);
            $counterEntry = json_decode($request->counter_entry);
            $paymentLists = json_decode($request->payment_list);

            //error_log("ITEM LIST ".$itemLists);

            if($itemLists != NULL)
            {
                $narration =  ($request->description == '' ? 'Transaction occurred from purchase screen' : $request->description);

                $transaction = new Transaction([
                    'narration'     => $narration,
                    'generated_source' => $request->type,
                    'branch_id'      => Auth::user()->branch_id,
                ]);
                error_log("RECEIPT NO ".$request->bill_no);
                if( $request->bill_no!='' || $request->type=='CHAL'){
                    $transaction->save();
                    error_log(">>>>  TRANS SAVED ");


                }

                foreach($counterEntry as $item)
                {
                	$subTransaction = new SubTransaction([
                		'transaction_id'     => $transaction->id,
                		'account_id'     	 => $item->accountID,
                		'account_name'	 	 => $item->accountHead,
                		'amount'      	     => $item->amount,
                		'type'      		 => $item->type,
                	]);
                    error_log("RECEIPT NO ".$request->bill_no);

                    if( $request->bill_no!='' || $request->type=='CHAL'){

                	    $subTransaction->save();
                        error_log(">>>> SUB TRANS SAVED ");

                    }
                }
                

                $t = new  PosReceipt();
                $receiptNo =  $t->generateID($request->type);
                error_log(">>>> GENERATED RECEIPT NO ".$receiptNo.'>>>');


                $receiptItem 			= new PosReceipt([
                    'transaction_id' 	=> $transaction->id,
                    'receipt_no'     	=> $receiptNo,
                    'discount'   		=> $request->discount,
                    'profile_id' 		=> $request->profile_id,
                    'payment_method'    => $request->payment_method,
                    'total_gross_amt'   => $request->total_gross_amt,
                    'total_bill' 		=> $request->total_bill,
                    'total_tendered'   	=> $request->total_tendered,
                    'total_change'   	=> $request->total_change,
                    'total_tax1'   	 	=> $request->total_tax1,
                    'total_tax2'   	 	=> $request->total_tax2,
                    'total_tax3'   	 	=> $request->total_tax3,
                    'total_tax'   	 	=> $request->total_tax,
                    'description'   	=> $request->description,
                    'doctor_details'   	=> '',
                    'patient_details'   => '',
                    'bill_no'           => $request->bill_no,
                    'created_by'      	=> Auth::user()->id,
                    // 'receipt_date'      => date('Y-m-d'),
                    'receipt_date'      =>$this->createBillDate($request->bill_date),
                    'return_receipt'    => $request->search_receipt_no,
                    'type'      		=> $request->type,
                    'status'         	=> $request->status,
                    'branch_id'      	=> Auth::user()->branch_id,
                ]);
                error_log(">>>> RECEIPT ITEM ".$receiptItem);
                error_log("RECEIPT NO ".$request->bill_no);

                if( $request->bill_no!='' || $request->type=='CHAL'){

                    $receiptItem->save();
                    error_log(">>>> RECEIPT SAVED ");
                }


                 //IF USED CARDS
                $t->passBankTransaction($transaction->id,$receiptNo,$narration,$paymentLists,$request->profile_id);

                foreach($itemLists as $item)
                {
                    //for multi row with empty data
                    if($item->productName !=null){
                    //MANAGING STOCKS
                    $s = new Stock();

                    //sam 18/06/24
                    $item->totalUnit = $item->packSize * $item->sheetSize+$item->freeUnit*$item->sheetSize;
                    //$item->sellingPrice = $item->itemDescription;
                    if($item->stockID!=0){
                        $stock_id = $s->addReducePurchaseStock($item,$request->type);

                    }else{
                        error_log(">>>ITEM NOT IN MASTER");
                        $response = response()->json([
                            'alert' =>'danger',
                            'msg'   => 'The Item '.$item->productName.' is not in Item Master',
                        ]);

                        return $response;
                    }

                    $PosSubReceipt = new PosSubReceipt([
                        'pos_receipt_id'    => $receiptItem->id,
                        'mode'    			=> $item->mode,
                        'stock_id'    		=> $stock_id,
                        'item_name'       	=> $item->productName,
                        'generic_name'    	=> $item->generic,
                        'item_description'  => $item->itemDescription,
                        'unit'        		=> $item->unit,
                        'total_unit'        => $item->totalUnit,
                        'free_unit'   		=> $item->freeUnit,
                        'supplier_bonus'   	=> $item->supplierBonus,
                        'batch_no'   		=> $item->batchNo,
                        'pack_size'   		=> $item->packSize,
                        'sheet_size'   		=> $item->sheetSize,
                        'purchase_price'   	=> $item->purchasePrice,
                        'selling_price'   	=> $item->sellingPrice,
                        'mrp'   			=> $item->mrp,
                        'brand_name'   		=> $item->brandName,
                        'sector_name'   	=> 12   ,
                        'category_name'   	=> 13,
                        'product_type'   	=> $item->productType,
                        //'expiry_date'   	=> Date('Y-m-d',strtotime($item->expiryDate)),
                        'expiry_date'   	=> $this->createDate($item->expiryDate),
                        'item_disc'   		=> $item->cusDisc,
                        'purchase_disc'   	=> $item->itemDisc,
                        'after_disc'   	    => $item->purchaseAfterDisc,
                        'tax_1'   			=> $item->tax1,
                        'tax_2'   			=> $item->tax2,
                        'tax_3'   			=> $item->tax3,
                        'sub_total'   	    => $item->subTotal,
                    ]);
                    if( $request->bill_no!='' || $request->type=='CHAL'){

                        $PosSubReceipt->save();
                        error_log(">>>> SUB RECEIPT SAVED ");
                    }

                  }
                }
                
                if($paymentLists != NULL)
                {
                    foreach($paymentLists as $item)
                    {
                        $payments = new ReceiptPayment([
                            'transaction_id'    	=> $transaction->id,
                            'receipt_id'    		=> $receiptItem->id,
                            'account_no'    		=> $item->accountNo,
                            'auth_code'    			=> $item->authCode,
                            'card_balance'	 		=> $item->cardBalance,
                            'change'       			=> $item->change,
                            'entry_mode'    		=> $item->entryMode,
                            'gift_card_ref'  		=> $item->giftCardRef,
                            'host_response'        	=> $item->hostResponse,
                            'payment_type'        	=> $item->paymentType,
                            'payment_type'        	=> 'purchase',
                            'round_off'   			=> $item->roundOff,
                            'tendered'   			=> $item->tendered,
                            'terminal_id'   		=> $item->terminalId,
                            'trans_amount'   		=> $item->transAmount,
                            'trans_date'   			=> date('Y-m-d'),
                            'trans_id'   			=> $item->transId,
                            'trans_ref'   			=> $item->transRef,
                            'trans_status'   		=> $item->transStatus,
                            'trans_time'   			=> date('H:i:s'),
                            'trans_total_amount'   	=> $item->transTotalAmount,
                            'trans_type'   			=> $item->transType,
                            'source_type'   	    => $request->type,
                            'description'   	    => $narration,
                            'receipt_no'   	    	=> $receiptNo,
                            'created_by'   	    	=> Auth::user()->id,
                            'branch_id'   	    	=> Auth::user()->branch_id,
                        ]);
                        if( $request->bill_no!='' || $request->type=='CHAL'){

                             $payments->save();
                        }
                    }
                    error_log(">>>> SAVE  RECEIPT PAYMENT FINISHED ");


                }
                
                //sam
                //$il = implode($itemLists);
                error_log(">>>> SAVE PURCHASE RECEIPT FINISHED ");

                $response = response()->json([
                    'alert' =>'info',
                    'msg'   =>'Sale Receipt Updated Successfully',
                    'rno'   => '1234'
                ]);

            }
            else
            {
                $response = response()->json([
                    'alert' =>'danger',
                    'msg'   =>'Receipt cannot be created'
                ]);
            }

            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollBack();

            $response = response()->json([
                'alert' =>'danger',
                'msg'   => $e
            ]);

            throw $e;
        }


        return $response;
       
    }

    protected function createDate($d){
  
        $string =explode('/',$d);
        $month=$string[0];
        $year=$string[1];
        $cdate=$year.'-'.$month.'-1';
           return date('Y-m-d',strtotime($cdate));
        //echo '<br/>';     
        
        
    }

    protected function createBillDate($d){
        error_log(">>>creating bill data ".$d);
        $string =explode('/',$d);
        $day=$string[0];
        $month=$string[1];
        $year=$string[2];
        
        $cdate=$year.'-'.$month.'-'.$day;
        error_log(">>>bill date".$cdate);
           return date('Y-m-d',strtotime($cdate));
        //echo '<br/>';     
        
        
    }

    public function transactions(Request $request)
	{
		$filters = json_decode($request->filters);
	
		if($filters->storeID == 0)
		{
			$filters->storeID  = Auth::user()->branch_id;
		}

		$dt = new DateFilters();
		
		$dt->set('filter',$filters->filterType);
		$dt->set('date1',$filters->date1);
		$dt->set('date2',$filters->date2);
		$date1 = $dt->getTheDates()[0];
		$date2 = $dt->getTheDates()[1];

        if($filters->type != 'ASR')
        {
            
            $options = PosReceipt::with([
                'branch:id,name as branchName,code as branchCode',
                'userName:id,name as userName',
                'profileName:profilers.id,profilers.account_title as profileName',
                'receiptBalance'
            ])
            ->where('type', $filters->type)
            ->where('receipt_no','LIKE','%'.$filters->keyword.'%')
            ->where('branch_id',$filters->storeID)
            ->whereDate('receipt_date','>=', $date1)
            ->whereDate('receipt_date','<=', $date2)
            ->limit(20)
            ->offset($request->start)
            ->orderBy('id','DESC')
            ->get();
            
            
            $totalRecords = PosReceipt::where('type', $filters->type)
            ->where('receipt_no','LIKE','%'.$filters->keyword.'%')
            ->whereDate('receipt_date','>=', $date1)
            ->whereDate('receipt_date','<=', $date2)
            ->where('branch_id',$filters->storeID)
            ->count();
        }
        else
        {
            $options = PosReceipt::with([
                'branch:id,name as branchName,code as branchCode',
                'userName:id,name as userName',
                'profileName:profilers.id,profilers.account_title as profileName',
                'receiptBalance',
                'transferBranch'
            ])
            ->whereHas("transferBranch",function($q) use($filters){
                $q->where("branch_id","=",$filters->storeID);
            })
            ->where('receipt_no','LIKE','%'.$filters->keyword.'%')
            ->whereDate('receipt_date','>=', $date1)
            ->whereDate('receipt_date','<=', $date2)
            ->where('type', 'TRN')
            ->where(function ($query) {
                $query->where('status','=','Stock Left')
                ->orWhere('status','=','Stock Received');
            })
            ->limit(20)
            ->offset($request->start)
            ->orderBy('id','DESC')
            ->get();
            
            
            $totalRecords = PosReceipt::with([
                'transferBranch'
            ])
            ->whereHas("transferBranch",function($q) use($filters){
                $q->where("branch_id","=",$filters->storeID);
            })
            ->where('receipt_no','LIKE','%'.$filters->keyword.'%')
            ->whereDate('receipt_date','>=', $date1)
            ->whereDate('receipt_date','<=', $date2)
            ->where('type', 'TRN')
            ->where('status','Stock Left')
            ->count();
        }
		

		
		
		return [
			'records' => $options,
			'limit' => 20,
			'totalRecords' => $totalRecords,
			'statement' => 'Transactions between '.date('d-m-Y',strtotime($date1)).'-TO-'.date('d-m-Y',strtotime($date2)),
		];
	} 

    public function posPayments(Request $request)
    {
        $request->validate([
            'receipt_id'   		 => ['required'],
            'payment_list'   	 => ['required'],
            'counter_list'   	 => ['required'],
            'type'   	         => ['required'],
        ]);

        DB::beginTransaction();

        try
        {
           
            $paymentLists = json_decode($request->payment_list);
            $counterEntry = json_decode($request->counter_list);
            $receipt      = PosReceipt::find($request->receipt_id);

            if($request->type == 'INE')
			{
				$narration = 'Received pos invoice payment';
			}
			else if($request->type == 'RFD')
			{
				$narration = 'Paid pos refund payment';
			}
			else if($request->type == 'TRN')
			{
				$narration = 'Received pos transfer payment';
			}
            else if($request->type == 'PUR')
			{
				$narration = 'Paid purchase stock payment';
			}
            else if($request->type == 'RPU')
			{
				$narration = 'Received return purchase stock payment';
			}

            $transaction = new Transaction([
				'narration'         => $narration,
				'generated_source'  => $request->type,
				'branch_id'         => Auth::user()->branch_id,
			]);

			$transaction->save();

			foreach($counterEntry as $item)
			{
				$subTransaction = new SubTransaction([
					'transaction_id'     => $transaction->id,
					'account_id'     	 => $item->accountID,
					'account_name'	 	 => $item->accountHead,
					'amount'      	     => $item->amount,
					'type'      		 => $item->type,
				]);

				$subTransaction->save();
			}


			//IF USED CARDS
			$t = new  PosReceipt();
			$t->passBankTransaction($transaction->id,$receipt->receipt_no,$narration,$paymentLists,$receipt->profile_id);

            if($paymentLists != NULL)
            {
                foreach($paymentLists as $item)
                {
                    $payments = new ReceiptPayment([
                        'transaction_id'    	=> $transaction->id,
                        'receipt_id'    		=> $request->receipt_id,
                        'account_no'    		=> $item->accountNo,
                        'auth_code'    			=> $item->authCode,
                        'card_balance'	 		=> $item->cardBalance,
                        'change'       			=> $item->change,
                        'entry_mode'    		=> $item->entryMode,
                        'gift_card_ref'  		=> $item->giftCardRef,
                        'host_response'        	=> $item->hostResponse,
                        'payment_type'        	=> $item->paymentType,
                        'round_off'   			=> $item->roundOff,
                        'tendered'   			=> $item->tendered,
                        'terminal_id'   		=> $item->terminalId,
                        'trans_amount'   		=> $item->transAmount,
                        'trans_date'   			=> date('Y-m-d'),
                        'trans_id'   			=> $item->transId,
                        'trans_ref'   			=> $item->transRef,
                        'trans_status'   		=> 'Active',
                        'trans_time'   			=> date('H:i:s'),
                        'trans_total_amount'   	=> $item->transTotalAmount,
                        'trans_type'   			=> $item->transType,
                        'source_type'   	    => $request->type,
                        'description'   	    => $narration,
                        'receipt_no'   	    	=> $receipt->receipt_no,
                        'created_by'   	    	=> Auth::user()->id,
                        'branch_id'   	    	=> Auth::user()->branch_id,
                    ]);
                    
                    $payments->save();
                }
            }


            $response = response()->json([
                'alert' =>'info',
                'msg'   =>'Payment Created Successfully'
            ]);


            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollBack();

            $response = response()->json([
                'alert' =>'danger',
                'msg'   => $e
            ]);

            throw $e;
        }


        return $response;
       
    }
    
    public function stockLeft(Request $request)
    {
        $request->validate([
            'receipt_id'   => ['required'],
        ]);

        DB::beginTransaction();

        try
        {
           
            $receipt = PosReceipt::find($request->receipt_id);
            $receipt->status    = 'Stock Left';
            $receipt->update();

            $response = response()->json([
                'alert' =>'info',
                'msg'   =>'Stock Status Changed Successfully'
            ]);

            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollBack();

            $response = response()->json([
                'alert' =>'danger',
                'msg'   => $e
            ]);

            throw $e;
        }

        return $response;
    }
    
    public function voidStock(Request $request)
    {
        $request->validate([
            'receipt_id'   => ['required'],
        ]);

        DB::beginTransaction();

        try
        {
           
            $receipt = PosReceipt::find($request->receipt_id);
            $receipt->status    = 'Void';
            $receipt->payment_method    = 'Void';
            $receipt->total_gross_amt    = 0;
            $receipt->total_bill    = 0;
            $receipt->total_tendered    = 0;
            $receipt->total_change    = 0;
            $receipt->total_tax1    = 0;
            $receipt->total_tax2    = 0;
            $receipt->total_tax3    = 0;
            $receipt->total_tax    = 0;
            $receipt->update();

            //REVERSE ACCOUNTING ENTRIES
            if($receipt->type == 'INE')
			{
				$narration = 'Transaction occurred from void invoice';
			}
			else if($receipt->type == 'RFD')
			{
				$narration = 'Transaction occurred from void refund';
			}
			else if($receipt->type == 'TRN')
			{
				$narration = 'Transaction occurred from void transfer';
			}
            else if($receipt->type == 'PUR')
			{
				$narration = 'Transaction occurred from void purchase';
			}
            else if($receipt->type == 'RPU')
			{
				$narration = 'Transaction occurred from void purchase return';
			}

            $transaction = new Transaction([
				'narration'         => $narration,
				'generated_source'  => $receipt->type,
				'branch_id'         => Auth::user()->branch_id,
			]);

			$transaction->save();

            $counterEntry = SubTransaction::Where('transaction_id',$receipt->transaction_id)->get();

            if($counterEntry != NULL)
            {
                foreach($counterEntry as $item)
                {
                    if($item->type == 'Credit')
                    {
                        $subTransaction = new SubTransaction([
                            'transaction_id'     => $transaction->id,
                            'account_id'     	 => $item->account_id,
                            'account_name'	 	 => $item->account_name,
                            'amount'      	     => $item->amount,
                            'type'      		 => 'Debit',
                        ]);

                        $subTransaction->save();
                    }
                }

                foreach($counterEntry as $item)
                {
                    if($item->type == 'Debit')
                    {
                        $subTransaction = new SubTransaction([
                            'transaction_id'     => $transaction->id,
                            'account_id'     	 => $item->account_id,
                            'account_name'	 	 => $item->account_name,
                            'amount'      	     => $item->amount,
                            'type'      		 => 'Credit',
                        ]);

                        $subTransaction->save();
                    }
                }
            }


            //TRANSFEREE STOCK
            $receiptItems = PosSubReceipt::where('pos_receipt_id',$request->receipt_id)->get();

            if($receiptItems != NULL)
            {
                foreach($receiptItems as $item)
                {
                    $stock = Stock::find($item->stock_id);

                    if($receipt->type == 'INE' OR $receipt->type == 'RFD' OR $receipt->type == 'RPU')
                    {
                        $stock->qty =  $stock->qty + $item->total_unit;
                    }
                    else
                    {
                        $stock->qty =  $stock->qty - $item->total_unit;
                    }
                   
                    $stock->update();

                    $receiptItem = PosSubReceipt::find($item->id);
                    $receiptItem->unit =  0;
                    $receiptItem->total_unit =  0;
                    $receiptItem->sub_total =  0;
                    $receiptItem->free_unit =  0;
                    $receiptItem->supplier_bonus =  0;
                    $receiptItem->item_disc =  0;
                    $receiptItem->purchase_disc =  0;
                    $receiptItem->after_disc =  0;
                    $receiptItem->update();
                }
            }

            $response = response()->json([
                'alert' =>'info',
                'msg'   =>'Receipt voided Successfully'
            ]);

            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollBack();

            $response = response()->json([
                'alert' =>'danger',
                'msg'   => $e
            ]);

            throw $e;
        }

        return $response;
    }
    
    public function stockSaved(Request $request)
    {
        $request->validate([
            'receipt_id'   => ['required'],
        ]);

        DB::beginTransaction();

        try
        {
           
            $receipt = PosReceipt::find($request->receipt_id);
            $receipt->status    = 'Stock Received';
            $receipt->update();

            //TRANSFEREE STOCK
            $receiptItems = PosSubReceipt::where('pos_receipt_id',$request->receipt_id)->get();

            //TRANSFEREE STORE
            $transferStoreInfo = TransferStore::where('receipt_id',$request->receipt_id)->first();

            if($receiptItems != NULL AND $transferStoreInfo != NULL)
            {
                foreach($receiptItems as $item)
                {
                    $stockRecord = Stock::where('product_name',$item->item_name)
                    ->where('batch_no',$item->batch_no)
                    ->where('expiry_date',$item->expiry_date)
                    ->where('branch_id',$transferStoreInfo->branch_id)
                    ->first();

                 
                    if($stockRecord != NULL)
                    {
                      //  $stock = Stock::find($stockRecord->id);
                      $stockRecord['qty'] =  $stockRecord['qty'] + $item->total_unit;
                      $stockRecord->update();
                    }
                    else
                    {
                        $stock = Stock::find($item->stock_id);

                        $stock = new Stock([
							'product_name'    	  => strtoupper($stock->product_name),
							'generic'     	  	  => strtoupper($stock->generic),
							'barcode'	 	 	  => $stock->barcode,
							'type'      	  	  => $stock->type,
							'description'         => $stock->description,
							'image'        		  => $stock->image,
							'brand'      		  => $stock->brand,
							'brand_sector'        => $stock->brand_sector,
							'category'      	  => $stock->category,
							'side_effects'        => $stock->side_effects,
							'pack_size'      	  => $stock->pack_size,
							'strip_size'      	  => $stock->strip_size,
							'expiry_date'     	  => $stock->expiry_date,
							'qty'	 	 		  => $item->total_unit,
							'sale_price'      	  => $stock->sale_price,
							'purchase_price'      => $stock->purchase_price,
							'mrp'      		 	  => $stock->mrp,
							'batch_no'      	  => $stock->batch_no,
							'tax_1'      		  => $stock->tax_1,
							'tax_2'      		  => $stock->tax_2,
							'tax_3'      		  => $stock->tax_3,
							'discount_percentage' => $stock->discount_percentage,
							'min_stock'      	  => $stock->min_stock,
							'item_location'       => $stock->item_location,
							'created_by'      	  => Auth::user()->id,
							'status'      		  => 'Active',
							'branch_id'      	  => $transferStoreInfo->branch_id,
						]);

						$stock->save();
                    }
                }
            }
            else
            {
                DB::rollBack();

                $response = response()->json([
                    'alert' =>'danger',
                    'msg'   => 'Failed to complete  the process'
                ]);
            }

            $response = response()->json([
                'alert' =>'info',
                'msg'   =>'Stock Status Changed Successfully'
            ]);

            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollBack();

            $response = response()->json([
                'alert' =>'danger',
                'msg'   => $e
            ]);

            throw $e;
        }

        return $response;
    }

    //this is soumik code - enhanced search for POS sale page with better matching
    public function searchItem(Request $request)
    {
        $request->validate([
            'keyword' => ['required', 'min:1']
        ]);

        $keyword = strtoupper(trim($request->keyword)); // Convert to uppercase for better matching
        $branchId = Auth::user()->branch_id;

        try {
            // Search stocks with enhanced case-insensitive query
            $stocks = Stock::select(
                'id',
                'product_name',
                'generic',
                'batch_no',
                'qty',
                'strip_size',
                'pack_size',
                'sale_price',
                'mrp',
                'expiry_date',
                'barcode',
                'tax_1',
                'tax_2',
                'tax_3',
                'discount_percentage',
                'purchase_price'
            )
            ->where('branch_id', $branchId)
            ->where('status', 'Active')
            ->where('qty', '>', 0)
            ->where(function($query) use ($keyword) {
                $query->whereRaw('UPPER(product_name) LIKE ?', ['%' . $keyword . '%'])
                      ->orWhereRaw('UPPER(generic) LIKE ?', ['%' . $keyword . '%'])
                      ->orWhere('barcode', 'LIKE', '%' . $keyword . '%')
                      ->orWhereRaw('REPLACE(UPPER(product_name), " ", "") LIKE ?', ['%' . str_replace(' ', '', $keyword) . '%']);
            })
            ->orderByRaw('CASE WHEN UPPER(product_name) LIKE ? THEN 1 ELSE 2 END', [$keyword . '%'])
            ->orderBy('product_name', 'ASC')
            ->limit(50)
            ->get();

            return response()->json([
                'success' => true,
                'records' => $stocks,
                'debug' => [
                    'keyword' => $keyword,
                    'branch_id' => $branchId,
                    'count' => $stocks->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching items: ' . $e->getMessage(),
                'records' => [],
                'error' => $e->getTraceAsString()
            ], 500);
        }
    }

    //this is soumik code - get batch variations for specific product
    public function getBatchVariations(Request $request)
    {
        $request->validate([
            'product_name' => ['required']
        ]);

        $productName = $request->product_name;
        $branchId = Auth::user()->branch_id;

        try {
            // Get all batches for the selected product
            $batches = Stock::select(
                'id',
                'product_name',
                'generic',
                'batch_no',
                'qty',
                'strip_size',
                'pack_size',
                'sale_price',
                'mrp',
                'expiry_date',
                'purchase_price',
                'tax_1',
                'tax_2',
                'tax_3',
                'discount_percentage'
            )
            ->where('branch_id', $branchId)
            ->where('status', 'Active')
            ->where('qty', '>', 0)
            ->whereRaw('UPPER(product_name) = ?', [strtoupper($productName)])
            ->orderBy('expiry_date', 'DESC') // Latest expiry first
            ->get();

            return response()->json([
                'success' => true,
                'records' => $batches,
                'product_name' => $productName
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting batch variations: ' . $e->getMessage(),
                'records' => []
            ], 500);
        }
    }

    public function getPosReceipt(Request $request)
    {
        $request->validate([
			'id' => ['required']
		]);

        $receipt = PosReceipt::with([
			'profileName:profilers.id,profilers.account_title as profileName',
		])
		->where('id',$request->id)
		->get()->first();


		$receiptList = PosReceipt::find($request->id)->itemList;

        $storeDetail = Branch::with([
			'taxName1:chart_accounts.id,chart_accounts.account_name as chartName',
			'taxName2:chart_accounts.id,chart_accounts.account_name as chartName',
			'taxName3:chart_accounts.id,chart_accounts.account_name as chartName',
		])
		->where('id',$receipt['branch_id'])
		->first();

        $tStoreDetails = TransferStore::with([
            'transferBranch:branches.id,branches.name'
		])
		->where('receipt_id',$request->id)
		->get()->first();
		
		$response   = response()->json([
			'receipt'        => $receipt,
			'receiptList'    => $receiptList,
			'storeDetail'    => $storeDetail,
			'tStoreDetails'  => $tStoreDetails,
		]);

		return $response;
    }
}
