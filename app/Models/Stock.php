<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Stock extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_name',
        'generic',
        'barcode',
        'type',
        'description',
        'image',
        'brand',
        'brand_sector',
        'category',
        'side_effects',
        'pack_size',
        'strip_size',
        'expiry_date',
        'qty',
        'strip_size',
        'pack_size',
        'sale_price',
        'purchase_price',
        'mrp',
        'batch_no',
        'tax_1',
        'tax_2',
        'tax_3',
        'discount_percentage',
        'min_stock',
        'item_location',
        'created_by',
        'status',
        'branch_id',
        'hsn'
    ];

    public function productDetail()
	{
		return $this->belongsTo(Products::class,'product_id');
	}

    public function addReduceStock($stockID,$totalUnit,$type)
    {
        $stock = Stock::findOrFail($stockID);
        error_log("SOLD QUANTITY>>>>>>> ".$stock);

        if($type == 'INE' OR $type == 'TRN')
        {
            $stock->qty = $stock->qty - $totalUnit;
            $stock->pack_size = intdiv($stock->qty, $stock->strip_size);
        }
        else if($type == 'RFD')
        {
            $stock->qty = $stock->qty + $totalUnit;
            $stock->pack_size = intdiv($stock->qty, $stock->strip_size);

        }
       
        $stock->save();
    }
    
    public function addReducePurchaseStock($item,$type)
    {
        error_log(">>>>>>ADD PURCHASE STOCK ");
        error_log(">>>purchase after disc " .$item->purchaseAfterDisc);
        error_log(">>>expiry date " .$item->expiryDate);

        if($item->productType=='GST'){
            $item->productType=100;
        }else{
            $item->productType=101;

        }

        if($item->tax1==NULL){
            $item->tax1= 0;
        }
        if($item->tax2==NULL){
            $item->tax2= 0;
        }

        $stock_id = 0;

        if($type == 'RPU')
        {
            $stock = Stock::findOrFail($item->stockID);
            $stock->qty = $stock->qty - $item->totalUnit;
            $stock->save();

            $stock_id = $stock->id;
        }
        else if($type == 'PUR')
        {
            //CHECK BATCH AND EXPIRY
            $stock = Stock::where('expiry_date',$this->createDate($item->expiryDate))
            ->where('batch_no',$item->batchNo)
            ->where('purchase_price',$item->purchaseAfterDisc)
            ->first();

           

            if($stock != NULL)
            {
                error_log(">>>STOCK FOUND>>>".$stock->qty);
                $stock->qty = $stock->qty + $item->totalUnit;
                $stock->update();
                $stock_id = $stock->id;
                error_log(">>>>STOCK UPDATED>>>>".$item->totalUnit);
            }
            else
            {
               // $stocked = Stock::findOrFail($item->stockID);

                // $stock = new Stock([
                //     'product_name'        => $item->productName,
                //     'generic'             => $item->generic,
                //     'barcode'             => $stocked->barcode,
                //     'type'                => $stocked->type,
                //     'description'         => ($stocked->itemDescription == "" ? 'None' : $stocked->itemDescription),
                //     'image'               => $stocked->image,
                //     'brand'               => $stocked->brand,
                //     'brand_sector'        => $stocked->brand_sector,
                //     'category'            => $stocked->category,
                //     'side_effects'        => $stocked->side_effects,
                //     'pack_size'           => $item->packSize,
                //     'strip_size'          => $item->sheetSize,
                //     'expiry_date'         => date('Y-m-d',strtotime($item->expiryDate)),
                //     'qty'                 => $item->totalUnit,
                //     'strip_size'          => $item->sheetSize,
                //     'pack_size'           => $item->packSize,
                //     'sale_price'          => $item->sellingPrice,
                //     'purchase_price'      => $item->purchaseAfterDisc,
                //     'mrp'                 => $item->mrp,
                //     'batch_no'            => $item->batchNo,
                //     'tax_1'               => $item->tax1,
                //     'tax_2'               => $item->tax2,
                //     'tax_3'               => $item->tax3,
                //     'discount_percentage' => $item->cusDisc,
                //     'min_stock'           => $stocked->min_stock,
                //     'item_location'       => ($stocked->item_location == "" ? 'None' : $stocked->item_location),
                //     'created_by'          => Auth::user()->id,
                //     'status'              => 'Active',
                //     'branch_id'           => Auth::user()->branch_id
                // ]);

                $stock = new Stock([
                    'product_name'        => $item->productName,
                    'generic'             => $item->generic,
                    'barcode'             => $item->barcode,
                    'type'                => $item->productType,
                    'description'         => $item->description,
                    'image'               => '',
                     'brand'               =>'1',
                    'brand_sector'        => '3',
                     'category'            => '13',
                    // 'side_effects'        => $stocked->side_effects,
                    'pack_size'           => $item->packSize,
                    'strip_size'          => $item->sheetSize,
                    //'expiry_date'         => date('Y-m-d',strtotime($item->expiryDate)),
                    'expiry_date'         => $this->createDate($item->expiryDate),

                    'qty'                 => $item->totalUnit,
                    'strip_size'          => $item->sheetSize,
                    'pack_size'           => $item->packSize,
                    'sale_price'          => $item->sellingPrice,
                    'purchase_price'      => $item->purchaseAfterDisc,
                    'mrp'                 => $item->mrp,
                    'batch_no'            => $item->batchNo,
                    'tax_1'               => $item->tax1,
                    'tax_2'               => $item->tax2,
                    'tax_3'               => $item->tax3,
                    'discount_percentage' => $item->itemDisc,
                    'min_stock'           =>0,
                    'item_location'       => '',
                    'created_by'          => Auth::user()->id,
                    'status'              => 'Active',
                    'branch_id'           => Auth::user()->branch_id
                ]);

                $stock->save();
                error_log(">>>>>>STOCK SAVED...");
                $stock_id = $stock->id;
            }
        }

        return $stock_id;
    }

    public function branchDetails()
	{
		return $this->belongsTo(Branch::class,'branch_id');
	}

 public function createDate($d){
  
$string =explode('/',$d);
$month=$string[0];
$year=$string[1];
$cdate=$year.'-'.$month.'-1';
   return date('Y-m-d',strtotime($cdate));
//echo '<br/>';     


    }
}
