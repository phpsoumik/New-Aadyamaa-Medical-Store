<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OptionTags;
use App\Models\Branch;
use App\Models\Stock;
use App\Models\User;
use App\Models\Products;
use App\Exports\StocksExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\SubTransaction;
use App\Models\PosSubReceipt;

//IMPORT

use App\Imports\StocksImport;



class StockController extends Controller
{
	public function index()
	{
		$brand = OptionTags::where('status', 'Active')
			->where('option_type', 'Brands')
			->get();

		$brandSector = OptionTags::where('status', 'Active')
			->where('option_type', 'Brand Sectors')
			->get();

		$category = OptionTags::where('status', 'Active')
			->where('option_type', 'Category')
			->get();

		$productType = OptionTags::where('status', 'Active')
			->where('option_type', 'Products Type')
			->get();


		$storeTaxes = Branch::with([
			'taxName1:chart_accounts.id,chart_accounts.account_name as chartName',
			'taxName2:chart_accounts.id,chart_accounts.account_name as chartName',
			'taxName3:chart_accounts.id,chart_accounts.account_name as chartName',
		])
			->where('id', Auth::user()->branch_id)
			->get();

		return [
			'storeTaxes' => $storeTaxes,
			'productType' => $productType,
			'brand' => $brand,
			'brandSector' => $brandSector,
			'category' => $category
		];
	}


	public function stockList(Request $request)
	{
		$keyword = $request->keyword;

		if ($request->storeID == 0) {
			$request->storeID = Auth::user()->branch_id;
		}
		$keyword = str_replace(' ','', $keyword);

		// First get stocks without supplier info
		$stocks = DB::table('stocks')
			->where(function ($query) use ($keyword) {
				$query
					//->orWhere('stocks.product_name', 'LIKE', '%' . $keyword . '%')
					->whereRaw("REGEXP_REPLACE(stocks.product_name,'[^A-Za-z0-9]','') LIKE '$keyword%' ")

					->orWhere('stocks.generic', 'LIKE', '%' . $keyword . '%')
					->orWhere('stocks.batch_no', 'LIKE', '%' . $keyword . '%')
					->orWhere('stocks.status', 'LIKE', '%' . $keyword . '%');
			})
			->where('stocks.branch_id', $request->storeID)
			->select('stocks.*')
			->limit(20)
			->offset($request->start)
			->orderBy('stocks.id', 'DESC')
			->get();

		// Add supplier info to each stock item
		foreach ($stocks as $stock) {
			$supplier = DB::table('pos_sub_receipts as psr')
				->leftJoin('pos_receipts as pr', 'psr.pos_receipt_id', '=', 'pr.id')
				->leftJoin('profilers as p', 'pr.profile_id', '=', 'p.id')
				->where('psr.stock_id', $stock->id)
				->select('p.account_title as supplier_name')
				->first();
			
			$stock->supplier_name = $supplier ? $supplier->supplier_name : null;
		}
			//this is soumik code
			//$stocks = DB::table('stocks')
			//error_log('Sotcks returned >>>>')
		    // $stocks[0]->supplier='ABCD';
		   error_log($stocks);

		$totalRecords = DB::table('stocks')
			->where(function ($query) use ($keyword) {
				$query
					->orWhere('stocks.product_name', 'LIKE', '%' . $keyword . '%')
					->orWhere('stocks.generic', 'LIKE', '%' . $keyword . '%')
					->orWhere('stocks.batch_no', 'LIKE', '%' . $keyword . '%')
					->orWhere('stocks.status', 'LIKE', '%' . $keyword . '%');
			})
			->where('stocks.branch_id', $request->storeID)
			->count();

		$brand = OptionTags::where('status', 'Active')
			->where('option_type', 'Brands')
			->get();

		$brandSector = OptionTags::where('status', 'Active')
			->where('option_type', 'Brand Sectors')
			->get();

		$category = OptionTags::where('status', 'Active')
			->where('option_type', 'Category')
			->get();

		$productType = OptionTags::where('status', 'Active')
			->where('option_type', 'Products Type')
			->get();

		$user = new User();
		$stores = $user->getUserStores();

		$storeTaxes = Branch::with([
			'taxName1:chart_accounts.id,chart_accounts.account_name as chartName',
			'taxName2:chart_accounts.id,chart_accounts.account_name as chartName',
			'taxName3:chart_accounts.id,chart_accounts.account_name as chartName',
		])
			->where('id', Auth::user()->branch_id)
			->get();

			//now get supplier name for all the stock items


		return [
			'stores' => $stores,
			'records' => $stocks,
			'limit' => 20,
			'totalRecords' => $totalRecords,
			'storeTaxes' => $storeTaxes,
			'currentStoreID' => Auth::user()->branch_id,
			'productType' => $productType,
			'brand' => $brand,
			'brandSector' => $brandSector,
			'category' => $category
		];
	}

	public function getSupplierName($stocks){

		foreach($stocks as $s){
			//$expirySQL=	"SELECT psr.id,pr.receipt_no,pr.bill_no,psr.sub_total*0, p.account_title, psr.item_name, (psr.expiry_date), psr.batch_no, (pr.receipt_date), s.qty, psr.purchase_price, psr.purchase_disc, psr.tax_1, psr.tax_2, psr.tax_3 FROM `pos_sub_receipts` psr, pos_receipts pr, profilers p, stocks s WHERE psr.stock_id=s.id and psr.expiry_date between '$date1' and '$date2' and pr.bill_no!='' and pr.id=psr.pos_receipt_id and pr.profile_id=p.id and pr.profile_id=$supplier order by psr.expiry_date DESC;";
			
			// DB::select('SELECT psr.batch_no,psr.stock_id,psr.created_at, pr.profile_id FROM pos_sub_receipts psr, pos_receipts pr where  pr.bill_no='$billNo' and psr.pos_receipt_id=pr.id;')

		}

		
	}


	public function export()
	{
		return (new StocksExport)->download('sampleData.csv', ed::CSV, ['Content-Type' => 'text/csv']);
	}

	public function importStock(Request $request)
	{
		$request->validate([
			'image' => 'required',
		]);

		$fileRecord = Excel::toArray(null, $request->image);

		return response()->json($fileRecord[0]);
	}

	public function addTempStock(Request $request){

		DB::beginTransaction();
		//$itemLists = json_decode($request->item_list);
		error_log('>>>>>>>>>>>>>>>saving NEW ITEMS '.$request->item_list);
		$item=json_decode($request->item_list);

		try {


			$margin =(($item->mRP - $item->packPurchasePrice)/$item->mRP)*100;
			$stock = new Stock([
				'product_name'        => $item->productName,
				'generic'             => "",
				'barcode'             => $item->barcode,
				'type'                => '100',
				'description'         => round($margin, 2),
				'image'               => '',
				 'brand'               =>'1',
				'brand_sector'        => '1',
				 'category'            => $item->category,
				// 'side_effects'        => $stocked->side_effects,
				// 'pack_size'           => $item->packSize,
				'strip_size'          => $item->stripSize,
				'expiry_date'         => date('Y-m-d h:m:s',strtotime($item->expiryDate)),
				// 'expiry_date'         => $this->createDate($item->expiryDate),
				//'expiry_date'=>'2030-01-01',
				'qty'                 =>$item->stripSize*$item->packSize+$item->pata,
				// 'strip_size'          => 0,
				'pack_size'           => $item->packSize,
				'sale_price'          => $item->mRP,
				'purchase_price'      => $item->packPurchasePrice,
				'mrp'                 => $item->mRP,
				'batch_no'            => $item->batchNo,
				'tax_1'               => 6,
				'tax_2'               => 6,
				'tax_3'               =>0,
				'discount_percentage' =>0,
				'min_stock'           =>0,
				'item_location'       => 0,
				'created_by'          => Auth::user()->id,
				'status'              => 'Active',
				'branch_id'           => Auth::user()->branch_id
			]);			
			
			//error_log('saving NEW ITEMS '.$item);

			$stock->save();

	
			DB::commit();
			$response = response()->json([
						'alert' => 'info',
						'msg' => 'TEMP stock saved Successfully'
			]);

		
			
		} catch (\Exception $e) {
			DB::rollBack();

			$response = response()->json([
				'alert' => 'danger',
				'msg' => $e
			]);

			throw $e;

		}
		return $response;

	}

	//sam: added this method for add item
	public function saveNewItem(Request $request)
	{

		DB::beginTransaction();
		//$itemLists = json_decode($request->item_list);
		error_log('>>>>>>>>>>>>>>>saving NEW ITEMS '.$request->item_list);
		$item=json_decode($request->item_list);

		try {

		
			$stock = new Stock([
				'product_name'        => $item->product_name,
				'generic'             => $item->generic,
				'barcode'             => $item->barcode,
				'type'                => $item->type,
				'description'         => $item->description,
				'image'               => '',
				 'brand'               =>$item->brand,
				'brand_sector'        => $item->brand_sector,
				 'category'            => $item->category,
				// 'side_effects'        => $stocked->side_effects,
				// 'pack_size'           => $item->packSize,
				'strip_size'          => $item->strip_size,
				//'expiry_date'         => date('Y-m-d',strtotime($item->expiryDate)),
				// 'expiry_date'         => $this->createDate($item->expiryDate),
				'expiry_date'=>'2030-01-01',
				'qty'                 =>0,
				// 'strip_size'          => 0,
				'pack_size'           => 0,
				'sale_price'          => 0,
				'purchase_price'      => 0,
				'mrp'                 => 0,
				'batch_no'            => '',
				'tax_1'               => 0,
				'tax_2'               => 0,
				'tax_3'               =>0,
				'discount_percentage' =>0,
				'min_stock'           =>$item->min_stock,
				'item_location'       => $item->item_location,
				'created_by'          => Auth::user()->id,
				'status'              => 'Active',
				'branch_id'           => Auth::user()->branch_id
			]);			
			
			//error_log('saving NEW ITEMS '.$item);

			$stock->save();

	
					DB::commit();
					$response = response()->json([
						'alert' => 'info',
						'msg' => 'New Item saved Successfully'
					]);

		
			
		} catch (\Exception $e) {
			DB::rollBack();

			$response = response()->json([
				'alert' => 'danger',
				'msg' => $e
			]);

			throw $e;

		}
		return $response;

	}



	public function saveStock(Request $request)
	{
		$totalBill = 0.0;
		$totalDiscount = 0.0;
		$invoiceNo = '';
		$product = null;

		$request->validate([
			'item_list' => 'required',
		]);

		DB::beginTransaction();

		try {
			$itemLists = json_decode($request->item_list);

			if ($itemLists != NULL) {
				foreach ($itemLists as $item) {
					if ($item->productName == 'H') {
						$invoiceNo = $item->invoiceNo;

					} else if ($item->productName == 'F') {
						$totalBill = $item->total;

					}
					if ($product != NULL) {
						$response = response()->json([
							'alert' => 'danger',
							'msg' => 'Product name and Batch No already found'
						]);
					} else if ($item->productName != 'H' && $item->productName != 'F') {
						$stock = new Stock([
							'product_name' => strtoupper($item->productName),
							'generic' => strtoupper($item->genericName),
							'barcode' => $item->barcode,
							'type' => $item->productType,
							'description' => $item->description,
							'image' => 'default.jpg',
							//sam
							// 'brand'      		  => $item->brandName,
							// 'brand_sector'        => $item->brandSector,
							// 'category'      	  => $item->category,
							'brand' => '1',
							'brand_sector' => '1',
							'category' => '1',
							'side_effects' => $item->sideEffects,
							'expiry_date' => date('Y-m-d', strtotime($item->expiryDate)),
							'qty' => $item->quantity,
							'strip_size' => $item->stripSize,
							'pack_size' => $item->packSize,
							'sale_price' => $item->packSellingPrice,
							'purchase_price' => $item->packPurchasePrice,
							'mrp' => $item->mRP,
							'batch_no' => $item->batchNo,
							'tax_1' => $item->tax_1,
							'tax_2' => $item->tax_2,
							'tax_3' => $item->tax_3,
							'discount_percentage' => $item->discountPercentage,
							'min_stock' => $item->minimumStock,
							'item_location' => ($item->storeLocations == "" ? 'None' : $item->storeLocations),
							'created_by' => Auth::user()->id,
							'status' => 'Active',
							'branch_id' => Auth::user()->branch_id,
						]);

						$stock->save();


						$response = response()->json([
							'alert' => 'info',
							'msg' => 'New Stock saved Successfully'
						]);
					}
				}

				//create transaction
				$narration = 'CSV  Upload';

				$transaction = new Transaction([
					'narration' => $narration,
					'generated_source' => 'PUR',
					'branch_id' => Auth::user()->branch_id,
				]);

				$transaction->save();

				// //create sub transaction
				// $subTransaction = new SubTransaction([
				// 	'transaction_id'     => $transaction->id,
				// 	'account_id'     	 => $item->accountID,
				// 	'account_name'	 	 => $item->accountHead,
				// 	'amount'      	     => $item->amount,
				// 	'type'      		 => $item->type,
				// ]);

				// $subTransaction->save(); 


			} else {
				$response = response()->json([
					'alert' => 'danger',
					'msg' => 'Stock list is empty cannot upload'
				]);
			}

			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();

			$response = response()->json([
				'alert' => 'danger',
				'msg' => $e
			]);

			throw $e;
		}


		return $response;

	}



	public function update(Request $request)
	{

		$request->validate([
			'id' => ['required'],
			'product_name' => ['required'],
			// 'generic'               => ['required'],
			'type' => ['required'],
			// 'brand'                 => ['required'],
			// 'brand_sector'          => ['required'],
			// 'category'              => ['required'],
			'pack_size' => ['required'],
			'strip_size' => ['required'],
			// 'expiry_date'     		=> ['required'],
			'qty' => ['required'],
			// 'sale_price'         	=> ['required'],
			// 'pack_size'        		=> ['required'],
			'mrp' => ['required'],
			'batch_no' => ['required'],
			'tax_1' => ['required'],
			'tax_2' => ['required'],
			'tax_3' => ['required'],
			'discount_percentage' => ['required'],
			'min_stock' => ['required'],
			// 'item_location' 		=> ['required']
		]);

		$stock = Stock::find($request->id);

		//error_log(print_r($request->all()), true);

		$stock->update($request->all());

		return response()->json([
			'alert' => 'info',
			'msg' => 'Stock Item Updated Successfully'
		]);
	}

	public function show($id)
	{
		$stocks = DB::table('stocks')
			->where('stocks.id', $id)
			->select('stocks.*')
			->first();

		return response()->json($stocks);
	}

	//this is soumik code - improved search to show truly unique base product names only
	public function searchItems(Request $request)
	{
		error_log(">>>>ABOUT TO SEARCH ITEMS>>>>");
		$keyword = $request['keyword'];
		$keyword2 = str_replace(' ','', $keyword);

		/*
		select `stocks`.*, 
		`brand`.`option_name` as `bName`, 
		`brand_sector`.`option_name` as `bSector`, 
		`category`.`option_name` as `cName`, 
		`type`.`option_name` as `pType` from `stocks` 
		inner join `option_tags` as `brand` on `brand`.`id` = `stocks`.`brand` 
		inner join `option_tags` as `brand_sector` on `brand_sector`.`id` = `stocks`.`brand_sector` 
		inner join `option_tags` as `category` on `category`.`id` = `stocks`.`category` 
		inner join `option_tags` as `type` on `type`.`id` = `stocks`.`type` where `stocks`.`branch_id` = 1 and REPLACE('stocks.product_name',' ','') LIKE '%cil%' ;
		*/

		$stocks = DB::select("select `stocks`.*, 
		`brand`.`option_name` as `bName`, 
		`brand_sector`.`option_name` as `bSector`, 
		`category`.`option_name` as `cName`, 
		`type`.`option_name` as `pType` from `stocks` 
		inner join `option_tags` as `brand` on `brand`.`id` = `stocks`.`brand` 
		inner join `option_tags` as `brand_sector` on `brand_sector`.`id` = `stocks`.`brand_sector` 
		inner join `option_tags` as `category` on `category`.`id` = `stocks`.`category` 
		inner join `option_tags` as `type` on `type`.`id` = `stocks`.`type`
		where REPLACE(product_name,' ',\"\")  like '$keyword2%' or barcode='$keyword2' 
		or batch_no='$keyword2' 
		and stocks.qty>0
		group by product_name;");
		error_log("OBTAINED STOCKS FROM DB>>>");
		//for each matched stock get all the batch nos
		foreach ($stocks as $value) {
			# code...
			$productName = $value->product_name;
			$variations = $this->getProductVariations($productName);
			error_log("VARIATIONS ".$variations);
			//$value->mytext='test';
			//error_log("Product batch ".$value->mytext);
			$value->variations = $variations;
			//$value->totalQty = 500;
			$value->totalQty = $variations->totalQty;


		}
		error_log("RETURNING STOCKS WITH VARIATIONS>>>>>");
		return [
			'records' => $stocks
		];
	}

	function test(){
		error_log("CALLING TEST FUNCTION");
	}

	//this is soumik code - new method to get all variations of a base product
	public function getProductVariations($productName)
	{
		$total=0;
		//$baseProductName = $request['base_product_name'];
		//$generic = $request['generic'];
		$baseProductName = $productName;

		//this is soumik code - get all variations that start with the base product name
		$stocks = DB::table('stocks')
			->leftJoin('option_tags as brand', 'brand.id', '=', 'stocks.brand')
			->leftJoin('option_tags as brand_sector', 'brand_sector.id', '=', 'stocks.brand_sector')
			->leftJoin('option_tags as category', 'category.id', '=', 'stocks.category')
			->leftJoin('option_tags as type', 'type.id', '=', 'stocks.type')
			->where('stocks.branch_id', Auth::user()->branch_id)
			->where('stocks.status', 'Active')
			->where('stocks.product_name', '=', $baseProductName)
			->whereRaw(" `stocks`.`qty`>0")

			->select(
				'stocks.*',
				'brand.option_name as bName',
				'brand_sector.option_name as bSector',
				'category.option_name as cName',
				'type.option_name as pType'
			)
			->orderBy('stocks.product_name', 'ASC')
			->get();
				if(!$stocks->isEmpty()){
					foreach ($stocks as $value) {
					# code...
					$qty = $value->qty;
					error_log("Quantity of each variation ".$value->id.'>>>>'.$qty);
					$total +=$qty;
					//$value->mytext='test';
					//error_log("Product batch ".$value->mytext);
					$stocks->totalQty=$total;
					}
				}else{
					$stocks->totalQty=0;

				}

			return $stocks;

		// return [
		// 	'records' => $stocks
		// ];
	}

}
