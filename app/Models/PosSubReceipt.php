<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosSubReceipt extends Model
{
	use HasFactory;
	protected $fillable = [
		'pos_receipt_id',
		'mode',
		'stock_id',
		'item_name',
		'generic_name',
		'item_description',
		'unit',
		'total_unit',
		'free_unit',
		'supplier_bonus',
		'batch_no',
		'pack_size',
		'sheet_size',
		'purchase_price',
		'selling_price',
		'mrp',
		'brand_name',
		'sector_name',
		'category_name',
		'product_type',
		'expiry_date',
		'item_disc',
		'purchase_disc',
		'after_disc',
		'tax_1',
		'tax_2',
		'tax_3',
		'sub_total',
	];

	public function stockDetail()
	{	
		return $this->belongsTo(Stock::class,'stock_id');
	}
}
