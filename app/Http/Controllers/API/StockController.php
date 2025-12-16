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
use App\Models\RequestedItem;
use App\Models\Notification;

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

						// Check for pending medicine requests and create notifications
						$this->checkPendingOrders($stock->product_name, $stock->id);

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
		$branchId = Auth::user()->branch_id;

		try {
			// Get stock items with requested_items info joined
			$stocks = DB::table('stocks')
				->leftJoin('option_tags as brand', 'brand.id', '=', 'stocks.brand')
				->leftJoin('option_tags as brand_sector', 'brand_sector.id', '=', 'stocks.brand_sector')
				->leftJoin('option_tags as category', 'category.id', '=', 'stocks.category')
				->leftJoin('option_tags as type', 'type.id', '=', 'stocks.type')
				->leftJoin('requested_items', function($join) {
					$join->on('stocks.id', '=', 'requested_items.stock_id')
						 ->where('requested_items.order_status', '=', 'received')
						 ->where('requested_items.status', '=', 'Active');
				})
				->leftJoin('profilers', 'requested_items.customer_id', '=', 'profilers.id')
				->where('stocks.branch_id', $branchId)
				->where('stocks.status', 'Active')
				->where('stocks.qty', '>', 0)
				->where(function($query) use ($keyword, $keyword2) {
					$query->whereRaw("REPLACE(stocks.product_name, ' ', '') LIKE ?", [$keyword2 . '%'])
						  ->orWhere('stocks.barcode', '=', $keyword2)
						  ->orWhere('stocks.batch_no', '=', $keyword2)
						  ->orWhere('stocks.product_name', 'LIKE', '%' . $keyword . '%')
						  ->orWhere('stocks.generic', 'LIKE', '%' . $keyword . '%');
				})
				->select(
					'stocks.*',
					'brand.option_name as bName',
					'brand_sector.option_name as bSector',
					'category.option_name as cName',
					'type.option_name as pType',
					DB::raw("'stock' as item_type"),
					DB::raw("CASE WHEN requested_items.id IS NOT NULL THEN 1 ELSE 0 END as is_requested"),
					'requested_items.customer_id',
					'profilers.account_title as customer_name',
					'profilers.contact_no as customer_phone',
					DB::raw("COALESCE(requested_items.advance_payment, 0) as advance_payment"),
					'requested_items.id as requested_item_id'
				)
				->groupBy('stocks.product_name')
				->orderByRaw('is_requested DESC, stocks.product_name ASC')
				->limit(20)
				->get();

			// Get pending requested items (for purchasing page)
			$requestedItems = DB::table('requested_items')
				->where('order_status', 'pending')
				->where('status', 'Active')
				->where('medicine_name', 'LIKE', '%' . $keyword . '%')
				->select(
					DB::raw("0 as id"),
					'medicine_name as product_name',
					DB::raw("'' as generic"),
					DB::raw("'' as barcode"),
					DB::raw("'' as batch_no"),
					DB::raw("0 as qty"),
					DB::raw("0 as strip_size"),
					DB::raw("0 as pack_size"),
					DB::raw("0 as sale_price"),
					DB::raw("0 as mrp"),
					DB::raw("0 as purchase_price"),
					DB::raw("'2030-01-01' as expiry_date"),
					DB::raw("0 as tax_1"),
					DB::raw("0 as tax_2"),
					DB::raw("0 as tax_3"),
					DB::raw("0 as discount_percentage"),
					DB::raw("'' as bName"),
					DB::raw("'' as bSector"),
					DB::raw("'' as cName"),
					DB::raw("'' as pType"),
					DB::raw("'requested' as item_type"),
					DB::raw("1 as is_requested"),
					DB::raw("NULL as customer_id"),
					DB::raw("NULL as customer_name"),
					DB::raw("NULL as customer_phone"),
					DB::raw("0 as advance_payment"),
					DB::raw("NULL as requested_item_id")
				)
				->groupBy('medicine_name')
				->limit(10)
				->get();

			// Merge requested items at the top
			$allItems = $requestedItems->concat($stocks);

			error_log("OBTAINED STOCKS FROM DB>>>");
			
			//for each matched stock get all the batch nos
			foreach ($allItems as $value) {
				if ($value->item_type == 'stock') {
					$productName = $value->product_name;
					$variations = $this->getProductVariations($productName);
					$value->variations = $variations;
					$value->totalQty = $variations->totalQty ?? 0;
				} else {
					$value->variations = collect([]);
					$value->totalQty = 0;
				}
			}
			
			error_log("RETURNING STOCKS WITH VARIATIONS>>>>>");
			return [
				'records' => $allItems
			];
			
		} catch (\Exception $e) {
			error_log("Error in searchItems: " . $e->getMessage());
			return [
				'records' => [],
				'error' => $e->getMessage()
			];
		}
	}

	function test(){
		error_log("CALLING TEST FUNCTION");
	}

	//this is soumik code - new method to get all variations of a base product
	public function getProductVariations($productName)
	{
		$total = 0;
		$baseProductName = $productName;

		try {
			//this is soumik code - get all variations that start with the base product name
			$stocks = DB::table('stocks')
				->leftJoin('option_tags as brand', 'brand.id', '=', 'stocks.brand')
				->leftJoin('option_tags as brand_sector', 'brand_sector.id', '=', 'stocks.brand_sector')
				->leftJoin('option_tags as category', 'category.id', '=', 'stocks.category')
				->leftJoin('option_tags as type', 'type.id', '=', 'stocks.type')
				->where('stocks.branch_id', Auth::user()->branch_id)
				->where('stocks.status', 'Active')
				->where('stocks.product_name', '=', $baseProductName)
				->where('stocks.qty', '>', 0)
				->select(
					'stocks.*',
					'brand.option_name as bName',
					'brand_sector.option_name as bSector',
					'category.option_name as cName',
					'type.option_name as pType'
				)
				->orderBy('stocks.product_name', 'ASC')
				->get();

			if (!$stocks->isEmpty()) {
				foreach ($stocks as $value) {
					$qty = $value->qty;
					error_log("Quantity of each variation " . $value->id . '>>>>' . $qty);
					$total += $qty;
				}
				$stocks->totalQty = $total;
			} else {
				// Create empty collection with totalQty property
				$stocks = collect([]);
				$stocks->totalQty = 0;
			}

			return $stocks;
			
		} catch (\Exception $e) {
			error_log("Error in getProductVariations: " . $e->getMessage());
			$stocks = collect([]);
			$stocks->totalQty = 0;
			return $stocks;
		}
	}

	/**
	 * Check for pending medicine orders and create notifications
	 */
	private function checkPendingOrders($medicineName, $stockId)
	{
		// Get all pending requested items
		$pendingOrders = RequestedItem::where('order_status', 'pending')
			->where('status', 'Active')
			->whereNotNull('customer_id')
			->get();

		foreach ($pendingOrders as $order) {
			// Check if medicine names match using smart matching
			if ($this->isMedicineMatch($medicineName, $order->medicine_name)) {
				// Update order status to received
				$order->update([
					'order_status' => 'received',
					'received_date' => now()->toDateString(),
					'stock_id' => $stockId
				]);

				// Create notification for customer
				$message = "Your requested medicine '{$order->medicine_name}' has arrived. Please contact us to collect your order.";
				
				Notification::create([
					'customer_id' => $order->customer_id,
					'requested_item_id' => $order->id,
					'message' => $message,
					'type' => 'medicine_arrived',
					'status' => 'unread'
				]);
			}
		}
	}

	/**
	 * Smart medicine name matching with multiple strategies
	 */
	private function isMedicineMatch($stockMedicine, $requestedMedicine)
	{
		// Clean both names (remove spaces, convert to lowercase)
		$stock = strtolower(str_replace(' ', '', $stockMedicine));
		$requested = strtolower(str_replace(' ', '', $requestedMedicine));

		// Strategy 1: Exact match
		if ($stock === $requested) {
			return true;
		}

		// Strategy 2: One contains the other
		if (strpos($stock, $requested) !== false || strpos($requested, $stock) !== false) {
			return true;
		}

		// Strategy 3: Similar length and characters (for typos)
		if (abs(strlen($stock) - strlen($requested)) <= 2) {
			$similarity = 0;
			similar_text($stock, $requested, $similarity);
			// If 80% or more similar, consider it a match
			if ($similarity >= 80) {
				return true;
			}
		}

		// Strategy 4: Check if first 5 characters match (for common prefixes)
		if (strlen($stock) >= 5 && strlen($requested) >= 5) {
			if (substr($stock, 0, 5) === substr($requested, 0, 5)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get pending requested medicines for autocomplete
	 */
	public function getPendingRequestedMedicines(Request $request)
	{
		$query = $request->get('query', '');
		
		if (strlen($query) < 2) {
			return response()->json([]);
		}
		
		$requestedMedicines = RequestedItem::where('order_status', 'pending')
			->where('status', 'Active')
			->where('medicine_name', 'LIKE', '%' . $query . '%')
			->whereNotNull('customer_id')
			->select('medicine_name', 'quantity', 'customer_id')
			->with('customer:id,account_title,contact_no')
			->distinct()
			->limit(10)
			->get()
			->map(function($item) {
				return [
					'medicine_name' => $item->medicine_name,
					'display' => $item->medicine_name . ' (Requested by: ' . ($item->customer ? $item->customer->account_title : 'Unknown') . ')',
					'customer_name' => $item->customer ? $item->customer->account_title : 'Unknown',
					'customer_phone' => $item->customer ? $item->customer->contact_no : '',
					'quantity' => $item->quantity,
					'is_requested' => true
				];
			});
			
		return response()->json($requestedMedicines);
	}

	/**
	 * Search medicine for new item modal - includes requested items and existing products
	 */
	public function searchMedicineForNewItem(Request $request)
	{
		$keyword = $request->get('keyword', '');
		
		if (strlen($keyword) < 2) {
			return response()->json(['records' => []]);
		}

		try {
			$results = [];

			// Get requested medicines (pending orders)
			$requestedMedicines = DB::table('requested_items')
				->where('order_status', 'pending')
				->where('status', 'Active')
				->where('medicine_name', 'LIKE', '%' . $keyword . '%')
				->select(
					'medicine_name',
					DB::raw("1 as is_requested")
				)
				->groupBy('medicine_name')
				->limit(10)
				->get();

			// Get existing product names from stocks
			$existingProducts = DB::table('stocks')
				->where('product_name', 'LIKE', '%' . $keyword . '%')
				->where('status', 'Active')
				->select(
					'product_name as medicine_name',
					DB::raw("0 as is_requested")
				)
				->groupBy('product_name')
				->limit(10)
				->get();

			// Merge results - requested items first
			$allResults = $requestedMedicines->concat($existingProducts);

			return response()->json([
				'records' => $allResults
			]);

		} catch (\Exception $e) {
			error_log("Error in searchMedicineForNewItem: " . $e->getMessage());
			return response()->json([
				'records' => [],
				'error' => $e->getMessage()
			]);
		}
	}

}
