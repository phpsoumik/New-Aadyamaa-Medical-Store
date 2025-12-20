<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\DateFilters;
use App\Models\AccountStatement;
use App\Models\OptionTags;
use App\Models\Branch;
use App\Models\Transaction;
use App\Models\BankTransaction;
use App\Models\Stock;
use App\Models\ActivityLog;
use App\Models\Banks;
use App\Models\Profiler;
use App\Models\SubTransaction;


use App\Models\ReturnVouchers;

class StoreReportController extends Controller
{
	public function index()
	{
		$dt = new DateFilters();
		$datesList = $dt->get('filterList');

		$user =  new User();
		$stores = $user->getUserStores();

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
			'datesList'    => $datesList,
			'productTypes' => $productType,
			'brands'       => $brand,
			'brandSector'  => $brandSector,
			'categories'   => $category,
			'stores' 	   => $stores,
			'storeTaxes'   => $storeTaxes,
		];
	}

	public function saleReturn(Request $request)
	{

		$filters = json_decode($request->filters);

		if ($filters->storeID == 0) {
			$filters->storeID  = Auth::user()->branch_id;
		}

		if ($filters->type == 'Sales') {
			$reportType = 'INE';
		} else {
			$reportType = 'RFD';
		}

		$dt = new DateFilters();

		$dt->set('filter', $filters->filterType);
		$dt->set('date1', $filters->date1);
		$dt->set('date2', $filters->date2);
		$date1 = $dt->getTheDates()[0];
		$date2 = $dt->getTheDates()[1];

		$record = DB::table('pos_receipts')
			->join('pos_sub_receipts', 'pos_receipts.id', '=', 'pos_sub_receipts.pos_receipt_id')
			->join('profilers', 'profilers.id', '=', 'pos_receipts.profile_id')
			->join('users', 'users.id', '=', 'pos_receipts.created_by')
			->join('branches', 'branches.id', '=', 'pos_receipts.branch_id')
			->whereDate('pos_receipts.created_at', '>=', $date1)
			->whereDate('pos_receipts.created_at', '<=', $date2)
			->where('pos_receipts.type', '=', $reportType)
			->where('pos_receipts.branch_id', '=', $filters->storeID)
			->where(function ($query) use ($filters) {
				if ($filters->brandName != 'All') {
					$query->where('pos_sub_receipts.brand_name', '=', $filters->brandName);
				}

				if ($filters->sectorName != 'All') {
					$query->where('pos_sub_receipts.sector_name', '=', $filters->sectorName);
				}

				if ($filters->categoryName != 'All') {
					$query->where('pos_sub_receipts.category_name', '=', $filters->categoryName);
				}

				if ($filters->productType != 'All') {
					$query->where('pos_sub_receipts.product_type', '=', $filters->productType);
				}

				if ($filters->customerID != 0) {
					$query->where('pos_receipts.profile_id', '=', $filters->customerID);
				}

				if ($filters->userID != 0) {
					$query->where('pos_receipts.created_by', '=', $filters->userID);
				}

				if ($filters->batchNo != '') {
					$query->where('pos_sub_receipts.batch_no', '=', $filters->batchNo);
				}
			})
			->select(DB::raw(
				'branches.name as branch_name,
			 branches.code as branch_code,
			 profilers.account_title as customer_name,
			 profilers.contact_no as customer_contact,
			 users.name as user_name,
			 users.contact as user_contact,
			 pos_receipts.receipt_no,
			 pos_receipts.receipt_date,
			 pos_sub_receipts.*
			'
			))
			->get();

		return [
			'resultTitle' => 'From ' . date('D, d M  Y', strtotime($date1)) . ' -To- ' . date('D, d M  Y', strtotime($date2)),
			'record' => $record,
		];
	}

	public function taxReport(Request $request)
	{

		$filters = json_decode($request->filters);

		if ($filters->storeID == 0) {
			$filters->storeID  = Auth::user()->branch_id;
		}

		if ($filters->type == 'Sales') {
			$reportType = 'INE';
		} else if ($filters->type == 'Refund') {
			$reportType = 'RFD';
		} else if ($filters->type == 'Transfer') {
			$reportType = 'TRN';
		} else if ($filters->type == 'Purchase') {
			$reportType = 'PUR';
		} else if ($filters->type == 'Purchase Return') {
			$reportType = 'RPU';
		}

		$dt = new DateFilters();

		$dt->set('filter', $filters->filterType);
		$dt->set('date1', $filters->date1);
		$dt->set('date2', $filters->date2);
		$date1 = $dt->getTheDates()[0];
		$date2 = $dt->getTheDates()[1];

		$record = DB::table('pos_receipts')
			->join('pos_sub_receipts', 'pos_receipts.id', '=', 'pos_sub_receipts.pos_receipt_id')
			->join('profilers', 'profilers.id', '=', 'pos_receipts.profile_id')
			->join('users', 'users.id', '=', 'pos_receipts.created_by')
			->join('branches', 'branches.id', '=', 'pos_receipts.branch_id')
			->whereDate('pos_receipts.created_at', '>=', $date1)
			->whereDate('pos_receipts.created_at', '<=', $date2)
			->where('pos_receipts.type', '=', $reportType)
			->where('pos_receipts.branch_id', '=', $filters->storeID)
			->where(function ($query) use ($filters) {
				if ($filters->brandName != 'All') {
					$query->where('pos_sub_receipts.brand_name', '=', $filters->brandName);
				}

				if ($filters->sectorName != 'All') {
					$query->where('pos_sub_receipts.sector_name', '=', $filters->sectorName);
				}

				if ($filters->categoryName != 'All') {
					$query->where('pos_sub_receipts.category_name', '=', $filters->categoryName);
				}

				if ($filters->productType != 'All') {
					$query->where('pos_sub_receipts.product_type', '=', $filters->productType);
				}

				if ($filters->customerID != 0) {
					$query->where('pos_receipts.profile_id', '=', $filters->customerID);
				}

				if ($filters->userID != 0) {
					$query->where('pos_receipts.created_by', '=', $filters->userID);
				}

				if ($filters->batchNo != '') {
					$query->where('pos_sub_receipts.batch_no', '=', $filters->batchNo);
				}
			})
			->select(DB::raw(
				'branches.name as branch_name,
			branches.code as branch_code,
			profilers.account_title as customer_name,
			profilers.contact_no as customer_contact,
			users.name as user_name,
			users.contact as user_contact,
			pos_receipts.receipt_no,
			pos_receipts.receipt_date,
			pos_sub_receipts.*
			'
			))
			->get();

		return [
			'resultTitle' => 'From ' . date('D, d M  Y', strtotime($date1)) . ' -To- ' . date('D, d M  Y', strtotime($date2)),
			'record' => $record,
		];
	}

	public function purchasing(Request $request)
	{

		$filters = json_decode($request->filters);


		if ($filters->storeID == 0) {
			$filters->storeID  = Auth::user()->branch_id;
		}

		error_log(">>>> FILTERS PASSED FOR REPORT " . $filters->type);

		if ($filters->type == 'Purchase') {
			$reportType = 'PUR';
		} else if ($filters->type == 'Challan') {
			$reportType = 'CHAL';
		} else {
			$reportType = 'RPU';
		}



		$dt = new DateFilters();

		$dt->set('filter', $filters->filterType);
		$dt->set('date1', $filters->date1);
		$dt->set('date2', $filters->date2);
		$date1 = $dt->getTheDates()[0];
		$date2 = $dt->getTheDates()[1];

		$record = DB::table('pos_receipts')
			// ->join('pos_sub_receipts', 'pos_receipts.id', '=', 'pos_sub_receipts.pos_receipt_id')
			->join('profilers', 'profilers.id', '=', 'pos_receipts.profile_id')
			->join('users', 'users.id', '=', 'pos_receipts.created_by')
			->join('branches', 'branches.id', '=', 'pos_receipts.branch_id')
			->whereDate('pos_receipts.created_at', '>=', $date1)
			->whereDate('pos_receipts.created_at', '<=', $date2)
			->where('pos_receipts.type', '=', $reportType)
			->where('pos_receipts.branch_id', '=', $filters->storeID)
			// ->where('pos_receipts.profile_id','=',$filters->vendorName)

			->where(function ($query) use ($filters) {
				if ($filters->brandName != 'All') {
					$query->where('pos_sub_receipts.brand_name', '=', $filters->brandName);
				}

				if ($filters->sectorName != 'All') {
					$query->where('pos_sub_receipts.sector_name', '=', $filters->sectorName);
				}

				if ($filters->categoryName != 'All') {
					$query->where('pos_sub_receipts.category_name', '=', $filters->categoryName);
				}

				if ($filters->productType != 'All') {
					$query->where('pos_sub_receipts.product_type', '=', $filters->productType);
				}

				if ($filters->customerID != 0) {
					$query->where('pos_receipts.profile_id', '=', $filters->customerID);
				}

				if ($filters->userID != 0) {
					$query->where('pos_receipts.created_by', '=', $filters->userID);
				}

				if ($filters->batchNo != '') {
					$query->where('pos_sub_receipts.batch_no', '=', $filters->batchNo);
				}
			})
			->select(DB::raw(
				'branches.name as branch_name,
			 branches.code as branch_code,
			 profilers.account_title as customer_name,
			 profilers.contact_no as customer_contact,
			 users.name as user_name,
			 users.contact as user_contact,
			 pos_receipts.receipt_no,
			 pos_receipts.bill_no,
			 pos_receipts.receipt_date,
			 pos_receipts.total_bill
			'
			))
			->get();
		// pos_sub_receipts.*

		return [
			'resultTitle' => 'From ' . date('D, d M  Y', strtotime($date1)) . ' -To- ' . date('D, d M  Y', strtotime($date2)),
			'record' => $record,
		];
	}

	public function transfer(Request $request)
	{
		$filters = json_decode($request->filters);

		if ($filters->storeID == 0) {
			$filters->storeID  = Auth::user()->branch_id;
		}

		if ($filters->type == 'Transfer') {
			$reportType = 'TRN';
		}

		$dt = new DateFilters();

		$dt->set('filter', $filters->filterType);
		$dt->set('date1', $filters->date1);
		$dt->set('date2', $filters->date2);
		$date1 = $dt->getTheDates()[0];
		$date2 = $dt->getTheDates()[1];

		$record = DB::table('pos_receipts')
			->join('pos_sub_receipts', 'pos_receipts.id', '=', 'pos_sub_receipts.pos_receipt_id')
			->join('profilers', 'profilers.id', '=', 'pos_receipts.profile_id')
			->join('users', 'users.id', '=', 'pos_receipts.created_by')
			->join('branches', 'branches.id', '=', 'pos_receipts.branch_id')
			->join('transfer_stores', 'transfer_stores.receipt_id', '=', 'pos_receipts.id')
			->join('branches as tb', 'transfer_stores.branch_id', '=', 'tb.id')
			->whereDate('pos_receipts.created_at', '>=', $date1)
			->whereDate('pos_receipts.created_at', '<=', $date2)
			->where('pos_receipts.type', '=', $reportType)
			->where('pos_receipts.branch_id', '=', $filters->storeID)
			->where(function ($query) use ($filters) {
				if ($filters->customerID != 0) {
					$query->where('pos_receipts.profile_id', '=', $filters->customerID);
				}

				if ($filters->userID != 0) {
					$query->where('pos_receipts.created_by', '=', $filters->userID);
				}
			})
			->select(DB::raw(
				'branches.name as branch_name,
			 branches.code as branch_code,
			 tb.name as tb_name,
			 tb.code as tb_code,
			 profilers.account_title as customer_name,
			 profilers.contact_no as customer_contact,
			 users.name as user_name,
			 users.contact as user_contact,
			 pos_receipts.receipt_no,
			 pos_receipts.receipt_date,
			 pos_sub_receipts.*
			'
			))
			->get();

		return [
			'resultTitle' => 'From ' . date('D, d M  Y', strtotime($date1)) . ' -To- ' . date('D, d M  Y', strtotime($date2)),
			'record' => $record,
		];
	}

	public function performance(Request $request)
	{

		$filters = json_decode($request->filters);

		if ($filters->storeID == 0) {
			$filters->storeID  = Auth::user()->branch_id;
		}

		if ($filters->dimension == 'Purchase') {
			$reportType = 'PUR';
		} else {
			$reportType = 'RPU';
		}

		$dt = new DateFilters();

		$dt->set('filter', $filters->filterType);
		$dt->set('date1', $filters->date1);
		$dt->set('date2', $filters->date2);
		$date1 = $dt->getTheDates()[0];
		$date2 = $dt->getTheDates()[1];

		if ($filters->dimension == 'Customer') {
			$res = DB::table('pos_receipts')
				->join('profilers', 'profilers.id', '=', 'pos_receipts.profile_id')
				->whereDate('pos_receipts.created_at', '>=', $date1)
				->whereDate('pos_receipts.created_at', '<=', $date2)
				->where('pos_receipts.type', '=', $filters->reportType->value)
				->where('pos_receipts.branch_id', '=', $filters->storeID)
				->select(DB::raw(
					'SUM(pos_receipts.total_bill) as total_amount,
				profilers.account_title as customer_name,
				profilers.contact_no as customer_contact,
				profilers.email_address as email_address'
				))
				//->having(SUM('pos_receipts.total_bill'),$filters->condition,$filters->amountValue)
				->havingRaw("SUM(pos_receipts.total_bill) $filters->condition $filters->amountValue")
				->groupBy('profilers.id')
				->limit($filters->limit)
				->orderBy('total_amount', $filters->sort)
				->get();

			$list = [];
			if ($res != NULL) {
				foreach ($res as $k => $r) {
					$list[] = array(
						'ctr' 		 		    => $k + 1,
						'customer_name' 		=> $r->customer_name,
						'customer_contact' 		=> $r->customer_contact,
						'email_address'  		=> $r->email_address,
						'total_amount' 			=> $r->total_amount
					);
				}
			}
		} else if ($filters->dimension == 'Stores') {
			$res = DB::table('pos_receipts')
				->join('branches', 'branches.id', '=', 'pos_receipts.branch_id')
				->whereDate('pos_receipts.created_at', '>=', $date1)
				->whereDate('pos_receipts.created_at', '<=', $date2)
				->where('pos_receipts.type', '=', $filters->reportType->value)
				->where('pos_receipts.branch_id', '=', $filters->storeID)
				->select(DB::raw(
					'SUM(pos_receipts.total_bill) as total_amount,
				branches.name as name,
				branches.code as code,
				branches.email as email,
				branches.contact as contact'
				))
				->havingRaw("SUM(pos_receipts.total_bill) $filters->condition $filters->amountValue")
				->groupBy('branches.id')
				->limit($filters->limit)
				->orderBy('total_amount', $filters->sort)
				->get();

			$list = [];
			if ($res != NULL) {
				foreach ($res as $k => $r) {
					$list[] = array(
						'ctr' 		 		    => $k + 1,
						'code' 		 		    => $r->code,
						'name' 		 		    => $r->name,
						'contact' 		 		=> $r->contact,
						'email'  		 		=> $r->email,
						'total_amount' 			=> $r->total_amount
					);
				}
			}
		} else if ($filters->dimension == 'User') {
			$res = DB::table('pos_receipts')
				->join('users', 'users.id', '=', 'pos_receipts.created_by')
				->whereDate('pos_receipts.created_at', '>=', $date1)
				->whereDate('pos_receipts.created_at', '<=', $date2)
				->where('pos_receipts.type', '=', $filters->reportType->value)
				->where('pos_receipts.branch_id', '=', $filters->storeID)
				->select(DB::raw(
					'SUM(pos_receipts.total_bill) as total_amount,
				users.name as name,
				users.email as email,
				users.contact as contact'
				))
				->havingRaw("SUM(pos_receipts.total_bill) $filters->condition $filters->amountValue")
				->groupBy('users.id')
				->limit($filters->limit)
				->orderBy('total_amount', $filters->sort)
				->get();

			$list = [];
			if ($res != NULL) {
				foreach ($res as $k => $r) {
					$list[] = array(
						'ctr' 		 		    => $k + 1,
						'name' 		 		    => $r->name,
						'contact' 		 		=> $r->contact,
						'email'  		 		=> $r->email,
						'total_amount' 			=> $r->total_amount
					);
				}
			}
		} else if ($filters->dimension == 'Category') {
			$res = DB::table('pos_receipts')
				->join('pos_sub_receipts', 'pos_receipts.id', '=', 'pos_sub_receipts.pos_receipt_id')
				->whereDate('pos_receipts.created_at', '>=', $date1)
				->whereDate('pos_receipts.created_at', '<=', $date2)
				->where('pos_receipts.type', '=', $filters->reportType->value)
				->where('pos_receipts.branch_id', '=', $filters->storeID)
				->select(DB::raw(
					'SUM(pos_sub_receipts.sub_total) as total_amount,
				SUM(pos_sub_receipts.total_unit) as total_qty,
				pos_sub_receipts.category_name as name'
				))
				->havingRaw("SUM(pos_sub_receipts.sub_total) $filters->condition $filters->amountValue")
				->groupBy('name')
				->limit($filters->limit)
				->orderBy('total_amount', $filters->sort)
				->get();

			$list = [];
			if ($res != NULL) {
				foreach ($res as $k => $r) {
					$list[] = array(
						'ctr' 		 		    => $k + 1,
						'name' 		 		    => $r->name,
						'total_qty'  		 	=> $r->total_qty,
						'total_amount' 			=> $r->total_amount
					);
				}
			}
		} else if ($filters->dimension == 'Brand Sector') {
			$res = DB::table('pos_receipts')
				->join('pos_sub_receipts', 'pos_receipts.id', '=', 'pos_sub_receipts.pos_receipt_id')
				->whereDate('pos_receipts.created_at', '>=', $date1)
				->whereDate('pos_receipts.created_at', '<=', $date2)
				->where('pos_receipts.type', '=', $filters->reportType->value)
				->where('pos_receipts.branch_id', '=', $filters->storeID)
				->select(DB::raw(
					'SUM(pos_sub_receipts.sub_total) as total_amount,
				SUM(pos_sub_receipts.total_unit) as total_qty,
				pos_sub_receipts.sector_name as name'
				))
				->havingRaw("SUM(pos_sub_receipts.sub_total) $filters->condition $filters->amountValue")
				->groupBy('name')
				->limit($filters->limit)
				->orderBy('total_amount', $filters->sort)
				->get();

			$list = [];
			if ($res != NULL) {
				foreach ($res as $k => $r) {
					$list[] = array(
						'ctr' 		 		    => $k + 1,
						'name' 		 		    => $r->name,
						'total_qty'  		 	=> $r->total_qty,
						'total_amount' 			=> $r->total_amount
					);
				}
			}
		} else if ($filters->dimension == 'Brand') {
			$res = DB::table('pos_receipts')
				->join('pos_sub_receipts', 'pos_receipts.id', '=', 'pos_sub_receipts.pos_receipt_id')
				->whereDate('pos_receipts.created_at', '>=', $date1)
				->whereDate('pos_receipts.created_at', '<=', $date2)
				->where('pos_receipts.type', '=', $filters->reportType->value)
				->where('pos_receipts.branch_id', '=', $filters->storeID)
				->select(DB::raw(
					'SUM(pos_sub_receipts.sub_total) as total_amount,
				SUM(pos_sub_receipts.total_unit) as total_qty,
				pos_sub_receipts.brand_name as name'
				))
				->havingRaw("SUM(pos_sub_receipts.sub_total) $filters->condition $filters->amountValue")
				->groupBy('name')
				->limit($filters->limit)
				->orderBy('total_amount', $filters->sort)
				->get();

			$list = [];
			if ($res != NULL) {
				foreach ($res as $k => $r) {
					$list[] = array(
						'ctr' 		 		    => $k + 1,
						'name' 		 		    => $r->name,
						'total_qty'  		 	=> $r->total_qty,
						'total_amount' 			=> $r->total_amount
					);
				}
			}
		} else if ($filters->dimension == 'Product Type') {
			$res = DB::table('pos_receipts')
				->join('pos_sub_receipts', 'pos_receipts.id', '=', 'pos_sub_receipts.pos_receipt_id')
				->whereDate('pos_receipts.created_at', '>=', $date1)
				->whereDate('pos_receipts.created_at', '<=', $date2)
				->where('pos_receipts.type', '=', $filters->reportType->value)
				->where('pos_receipts.branch_id', '=', $filters->storeID)
				->select(DB::raw(
					'SUM(pos_sub_receipts.sub_total) as total_amount,
				SUM(pos_sub_receipts.total_unit) as total_qty,
				pos_sub_receipts.product_type as name'
				))
				->havingRaw("SUM(pos_sub_receipts.sub_total) $filters->condition $filters->amountValue")
				->groupBy('name')
				->limit($filters->limit)
				->orderBy('total_amount', $filters->sort)
				->get();

			$list = [];
			if ($res != NULL) {
				foreach ($res as $k => $r) {
					$list[] = array(
						'ctr' 		 		    => $k + 1,
						'name' 		 		    => $r->name,
						'total_qty'  		 	=> $r->total_qty,
						'total_amount' 			=> $r->total_amount
					);
				}
			}
		} else {
			$res = [];
		}

		return [
			'resultTitle' => 'From ' . date('D, d M  Y', strtotime($date1)) . ' -To- ' . date('D, d M  Y', strtotime($date2)),
			'record' => $list,
		];

		return [
			'resultTitle' => 'From ' . date('D, d M  Y', strtotime($date1)) . ' -To- ' . date('D, d M  Y', strtotime($date2)),
			'record' => [],
		];
	}

	public function generalJournal(Request $request)
	{
		$filters = json_decode($request->filters);

		if ($filters->storeID == 0) {
			$filters->storeID  = Auth::user()->branch_id;
		}


		$dt = new DateFilters();

		$dt->set('filter', $filters->filterType);
		$dt->set('date1', $filters->date1);
		$dt->set('date2', $filters->date2);
		$date1 = $dt->getTheDates()[0];
		$date2 = $dt->getTheDates()[1];

		$storeName = "";

		if ($filters->storeType == 'single_store') {
			$storeInfo = Branch::find($filters->storeID);
			$storeName = $storeInfo->name;

			$record = Transaction::with([
				'transactionEntries:sub_transactions.transaction_id,sub_transactions.account_name,sub_transactions.amount,sub_transactions.type',
				'branchName:id,name,code'
			])
				->where('branch_id', $filters->storeID)
				->whereDate('created_at', '>=', $date1)
				->whereDate('created_at', '<=', $date2)
				->orderBy('id', 'DESC')
				->get();
		} else {
			$record = Transaction::with([
				'transactionEntries:sub_transactions.transaction_id,sub_transactions.account_name,sub_transactions.amount,sub_transactions.type',
				'branchName:id,name,code'
			])
				->whereDate('created_at', '>=', $date1)
				->whereDate('created_at', '<=', $date2)
				->orderBy('id', 'DESC')
				->get();
		}


		return [
			'resultTitle' => 'From ' . date('D, d M  Y', strtotime($date1)) . ' -To- ' . date('D, d M  Y', strtotime($date2)),
			'storeName' => $storeName,
			'record' => $record,
		];
	}

	public function incomeStatement(Request $request)
	{
		$filters = json_decode($request->filters);

		if ($filters->storeID == 0) {
			$filters->storeID  = Auth::user()->branch_id;
		}

		$dt = new DateFilters();

		$dt->set('filter', $filters->filterType);
		$dt->set('date1', $filters->date1);
		$dt->set('date2', $filters->date2);
		$date1 = $dt->getTheDates()[0];
		$date2 = $dt->getTheDates()[1];

		$storeName = "";

		if ($filters->storeType == 'single_store') {

			$storeInfo = Branch::find($filters->storeID);
			$storeName = $storeInfo->name;

			$record = DB::select(
				DB::raw("SELECT MIN(sub_transactions.account_name) AS account_name,MIN(chart_accounts.account_nature) AS account_nature, 
				(SUM(CASE WHEN sub_transactions.type = 'Credit' AND chart_accounts.account_nature = 'Revenue' THEN sub_transactions.amount ELSE 0  END) -
				SUM(CASE WHEN sub_transactions.type = 'Debit' AND chart_accounts.account_nature = 'Revenue' THEN sub_transactions.amount  ELSE 0  END)) as 
				'total_revenue',
				(SUM(CASE WHEN sub_transactions.type = 'Debit' AND chart_accounts.account_nature = 'Expense' THEN sub_transactions.amount ELSE 0  END) -
				SUM(CASE WHEN sub_transactions.type = 'Credit' AND chart_accounts.account_nature = 'Expense' THEN sub_transactions.amount  ELSE 0  END)) AS 
				'total_expense' 
				FROM sub_transactions 
				JOIN transactions ON transactions.id = sub_transactions.transaction_id
				JOIN chart_accounts ON chart_accounts.id = sub_transactions.account_id 
				WHERE transactions.branch_id = :store_id  AND  transactions.created_at BETWEEN :date1 AND  :date2 AND (chart_accounts.account_nature = 'Revenue' OR chart_accounts.account_nature = 'Expense')
				GROUP BY sub_transactions.account_id"),
				array(
					'date1' => $date1,
					'date2'  => $date2,
					'store_id'   => $filters->storeID,
				)
			);
		} else {
			$record = DB::select(
				DB::raw("SELECT MIN(sub_transactions.account_name) AS account_name,MIN(chart_accounts.account_nature) AS account_nature, 
			(SUM(CASE WHEN sub_transactions.type = 'Credit' AND chart_accounts.account_nature = 'Revenue' THEN sub_transactions.amount ELSE 0  END) -
			SUM(CASE WHEN sub_transactions.type = 'Debit' AND chart_accounts.account_nature = 'Revenue' THEN sub_transactions.amount  ELSE 0  END)) as 
			'total_revenue',
			(SUM(CASE WHEN sub_transactions.type = 'Debit' AND chart_accounts.account_nature = 'Expense' THEN sub_transactions.amount ELSE 0  END) -
			SUM(CASE WHEN sub_transactions.type = 'Credit' AND chart_accounts.account_nature = 'Expense' THEN sub_transactions.amount  ELSE 0  END)) AS 
			'total_expense' 
			FROM sub_transactions 
			JOIN transactions ON transactions.id = sub_transactions.transaction_id
			JOIN chart_accounts ON chart_accounts.id = sub_transactions.account_id 
			WHERE  transactions.created_at BETWEEN :date1 AND  :date2 AND (chart_accounts.account_nature = 'Revenue' OR chart_accounts.account_nature = 'Expense')
			GROUP BY sub_transactions.account_id"),
				array(
					'date1' => $date1,
					'date2'  => $date2,
				)
			);
		}

		return [
			'resultTitle' => 'From ' . date('D, d M  Y', strtotime($date1)) . ' -To- ' . date('D, d M  Y', strtotime($date2)),
			'storeName' => $storeName,
			'record' => $record,
		];
	}

	public function trialBalance(Request $request)
	{
		$filters = json_decode($request->filters);

		if ($filters->storeID == 0) {
			$filters->storeID  = Auth::user()->branch_id;
		}


		$dt = new DateFilters();

		$dt->set('filter', $filters->filterType);
		$dt->set('date1', $filters->date1);
		$dt->set('date2', $filters->date2);
		$date1 = $dt->getTheDates()[0];
		$date2 = $dt->getTheDates()[1];

		$storeName = "";

		if ($filters->storeType == 'single_store') {

			$storeInfo = Branch::find($filters->storeID);
			$storeName = $storeInfo->name;

			$record = DB::select(
				DB::raw("SELECT MIN(sub_transactions.account_name) AS account_name,MIN(chart_accounts.account_nature) AS account_nature, 
				SUM(CASE WHEN sub_transactions.type = 'Debit' THEN sub_transactions.amount ELSE 0  END) as 'total_debit',
				SUM(CASE WHEN sub_transactions.type = 'Credit' THEN sub_transactions.amount  ELSE 0  END) as 'total_credit'
				FROM sub_transactions 
				JOIN transactions ON transactions.id = sub_transactions.transaction_id
				JOIN chart_accounts ON chart_accounts.id = sub_transactions.account_id 
				WHERE transactions.branch_id = :store_id  AND  transactions.created_at BETWEEN :date1 AND  :date2
				GROUP BY sub_transactions.account_id"),
				array(
					'date1' 	 => $date1,
					'date2'  	 => $date2,
					'store_id'   => $filters->storeID,
				)
			);
		} else {
			$record = DB::select(
				DB::raw("SELECT MIN(sub_transactions.account_name) AS account_name,MIN(chart_accounts.account_nature) AS account_nature, 
				SUM(CASE WHEN sub_transactions.type = 'Debit' THEN sub_transactions.amount ELSE 0  END) as 'total_debit',
				SUM(CASE WHEN sub_transactions.type = 'Credit' THEN sub_transactions.amount  ELSE 0  END) as 'total_credit'
				FROM sub_transactions 
				JOIN transactions ON transactions.id = sub_transactions.transaction_id
				JOIN chart_accounts ON chart_accounts.id = sub_transactions.account_id 
				WHERE  transactions.created_at BETWEEN :date1 AND  :date2
				GROUP BY sub_transactions.account_id"),
				array(
					'date1' 	 => $date1,
					'date2'  	 => $date2
				)
			);
		}

		return [
			'resultTitle' => 'From ' . date('D, d M  Y', strtotime($date1)) . ' -To- ' . date('D, d M  Y', strtotime($date2)),
			'storeName'   => $storeName,
			'record' 	  => $record,
		];
	}

	public function ledgerStatement(Request $request)
	{
		$filters = json_decode($request->filters);

		if ($filters->storeID == 0) {
			$filters->storeID  = Auth::user()->branch_id;
		}

		$dt = new DateFilters();

		$dt->set('filter', $filters->filterType);
		$dt->set('date1', $filters->date1);
		$dt->set('date2', $filters->date2);
		$date1 = $dt->getTheDates()[0];
		$date2 = $dt->getTheDates()[1];

		$storeName = "";

		if ($filters->storeType == 'single_store') {

			$storeInfo = Branch::find($filters->storeID);
			$storeName = $storeInfo->name;

			$record = DB::table('transactions')
				->join('sub_transactions', 'transactions.id', '=', 'sub_transactions.transaction_id')
				->join('chart_accounts', 'chart_accounts.id', '=', 'sub_transactions.account_id')
				->whereDate('sub_transactions.created_at', '>=', $date1)
				->whereDate('sub_transactions.created_at', '<=', $date2)
				->where('transactions.branch_id', '=', $filters->storeID)
				->where('chart_accounts.account_nature', '=', $filters->reportType)
				->select(
					'transactions.narration',
					'transactions.generated_source',
					'chart_accounts.account_code',
					'sub_transactions.*',
				)
				->orderBy('transactions.id', 'ASC')
				->get();

			$previous_record = DB::select(
				DB::raw("SELECT MIN(sub_transactions.account_id) AS account_id, 
				SUM(CASE WHEN sub_transactions.type = 'Debit' THEN sub_transactions.amount ELSE 0  END) as 'total_debit',
				SUM(CASE WHEN sub_transactions.type = 'Credit' THEN sub_transactions.amount  ELSE 0  END) as 'total_credit'
				FROM sub_transactions 
				JOIN transactions ON transactions.id = sub_transactions.transaction_id
				JOIN chart_accounts ON chart_accounts.id = sub_transactions.account_id 
				WHERE transactions.branch_id = :store_id  AND  sub_transactions.created_at < :date1 AND  chart_accounts.account_nature = :nature
				GROUP BY sub_transactions.account_id"),
				array(
					'date1' 	 => $date1,
					'nature' 	 => $filters->reportType,
					'store_id'   => $filters->storeID,
				)
			);
		} else {
			$record = DB::table('transactions')
				->join('sub_transactions', 'transactions.id', '=', 'sub_transactions.transaction_id')
				->join('chart_accounts', 'chart_accounts.id', '=', 'sub_transactions.account_id')
				->whereDate('sub_transactions.created_at', '>=', $date1)
				->whereDate('sub_transactions.created_at', '<=', $date2)
				->where('chart_accounts.account_nature', '=', $filters->reportType)
				->select(
					'transactions.narration',
					'transactions.generated_source',
					'chart_accounts.account_code',
					'sub_transactions.*',
				)
				->orderBy('transactions.id', 'ASC')
				->get();

			$previous_record = DB::select(
				DB::raw("SELECT MIN(sub_transactions.account_id) AS account_id, 
			SUM(CASE WHEN sub_transactions.type = 'Debit' THEN sub_transactions.amount ELSE 0  END) as 'total_debit',
			SUM(CASE WHEN sub_transactions.type = 'Credit' THEN sub_transactions.amount  ELSE 0  END) as 'total_credit'
			FROM sub_transactions 
			JOIN transactions ON transactions.id = sub_transactions.transaction_id
			JOIN chart_accounts ON chart_accounts.id = sub_transactions.account_id 
			WHERE  sub_transactions.created_at < :date1 AND  chart_accounts.account_nature = :nature
			GROUP BY sub_transactions.account_id"),
				array(
					'date1' 	 => $date1,
					'nature' 	 => $filters->reportType
				)
			);
		}


		return [
			'resultTitle' 		=> 'From ' . date('D, d M  Y', strtotime($date1)) . ' -To- ' . date('D, d M  Y', strtotime($date2)),
			'previousDate'   	=> date('D, d M  Y', strtotime($date1)),
			'storeName'   		=> $storeName,
			'record' 	  		=> $record,
			'previous_record' 	=> $previous_record
		];
	}

	public function bankStatement(Request $request)
	{

		$filters = json_decode($request->filters);

		if ($filters->storeID == 0) {
			$filters->storeID  = Auth::user()->branch_id;
		}

		$dt = new DateFilters();

		$dt->set('filter', $filters->filterType);
		$dt->set('date1', $filters->date1);
		$m = $dt->getTheMonthDates();
		$date1 = $m[0];
		$date2 = $m[1];

		$storeName = "";

		//BANK INFO
		$bankInfo = Banks::where('id', $filters->bank->id)->first();

		if ($filters->storeType == 'single_store') {

			$storeInfo = Branch::find($filters->storeID);
			$storeName = $storeInfo->name;

			$record = BankTransaction::with([
				'profileName:profilers.id,profilers.account_title as profileName',
			])
				->where('bank_id', $filters->bank->id)
				->where('branch_id', $filters->storeID)
				->whereDate('receipt_date', '>=', $date1)
				->whereDate('receipt_date', '<=', $date2)
				->orderBy('id', 'DESC')
				->get();

			$previous_bank = DB::select(
				DB::raw("SELECT (SUM(CASE WHEN sub_transactions.type = 'Debit' THEN sub_transactions.amount ELSE 0  END) - 
				SUM(CASE WHEN sub_transactions.type = 'Credit' THEN sub_transactions.amount  ELSE 0  END)) as 'total_bank'
				FROM sub_transactions 
				JOIN transactions ON transactions.id = sub_transactions.transaction_id
				WHERE transactions.branch_id = :store_id  AND  transactions.created_at < :date1 AND  sub_transactions.account_id = :account_id
				GROUP BY sub_transactions.account_id"),
				array(
					'date1' 	 => $date1,
					'store_id'   => $filters->storeID,
					':account_id' => 8,
				)
			);
		} else {
			$record = BankTransaction::with([
				'profileName:profilers.id,profilers.account_title as profileName',
			])
				->where('bank_id', $filters->bank->id)
				->whereDate('receipt_date', '>=', $date1)
				->whereDate('receipt_date', '<=', $date2)
				->orderBy('id', 'DESC')
				->get();

			$previous_bank = DB::select(
				DB::raw("SELECT (SUM(CASE WHEN sub_transactions.type = 'Debit' THEN sub_transactions.amount ELSE 0  END) - 
			SUM(CASE WHEN sub_transactions.type = 'Credit' THEN sub_transactions.amount  ELSE 0  END)) as 'total_bank'
			FROM sub_transactions 
			JOIN transactions ON transactions.id = sub_transactions.transaction_id
			WHERE  transactions.created_at < :date1 AND  sub_transactions.account_id = :account_id
			GROUP BY sub_transactions.account_id"),
				array(
					'date1' 	 => $date1,
					':account_id' => 8,
				)
			);
		}

		if ($previous_bank != NULL) {
			$beforeStatementAmount = $previous_bank[0]->total_bank;
		} else {
			$beforeStatementAmount = 0;
		}

		return [
			'resultTitle' 	              => 'From ' . date('D, d M  Y', strtotime($date1)) . ' -To- ' . date('D, d M  Y', strtotime($date2)),
			'beforeStatement'             => date('D, d M  Y', strtotime($date1)),
			'endStatement' 	              => date('D, d M  Y', strtotime($date2)),
			'beforeStatementAmount' 	  => $beforeStatementAmount,
			'record' 		  			  => $record,
			'storeName'   			  => $storeName,
			'openingBalance' 		  	  => $bankInfo->balance,
			'openingBalanceDate' 		  => $bankInfo->ending_date,
		];
	}

	public function accountStatement(Request $request)
	{
		$account_holder = '';
		$account_holder_phone = '';

		$filters = json_decode($request->filters);

		if ($filters->storeID == 0) {
			$filters->storeID  = Auth::user()->branch_id;
		}

		$dt = new DateFilters();

		$dt->set('filter', $filters->filterType);
		$dt->set('date1', $filters->date1);
		$dt->set('date2', $filters->date2);
		$date1 = $dt->getTheDates()[0];
		$date2 = $dt->getTheDates()[1];

		$as = new AccountStatement();

		$previous_date  = date('Y-m-d', strtotime($date1 . ' -1 day'));

		$as->set('date1', '2021-01-01');
		$as->set('date2', $previous_date);
		$as->set('storeID', $filters->storeID);
		$as->set('profileID', $filters->profileID);

		$prev_list = $as->get_user_transactions();
		$previous_balance = $as->sum_user_list_balance($prev_list);

		$as->set('date1', $date1);
		$as->set('date2', $date2);
		$as->set('storeID', $filters->storeID);
		$as->set('profileID', $filters->profileID);
		$as->set('totalBalance', $previous_balance);

		$t = $as->get_user_transactions();


		$storeName = "";

		if ($filters->profileID != 0) {
			$profilerInfo = Profiler::find($filters->profileID);
			$account_holder = $profilerInfo->account_title;
			$account_holder_phone = $profilerInfo->contact_no;


			$storeInfo = Branch::find($filters->storeID);
			$storeName = $storeInfo->name;
		}

		return [
			'resultTitle' 			  => 'From ' . date('D, d M  Y', strtotime($date1)) . ' -To- ' . date('D, d M  Y', strtotime($date2)),
			'record' 		  		  => $t,
			'account_holder' 		  => $account_holder,
			'account_holder_phone' 	  => $account_holder_phone,
			'store_name' 		  	  => $storeName,
			'before_balance' 		  => $previous_balance,
		];
	}

	//sam
	public function saveReturnVouchers(Request $request)
	{

		error_log("**** SAVING RETURN VOUCHERS **");
		// error_log("The request data is ".json_decode($request->returnList1));
		error_log("The supplier data is " . json_decode($request->supplierID));
		error_log("The total data is " . json_decode($request->totalAmount));

		$rList = json_decode($request->returnList);
		$counterEntry = json_decode($request->counterEntry);


		//$returnList = $request->returnList;
		$supplierID = json_decode($request->supplierID);
		$totalAmount = json_decode($request->totalAmount);


		// 	   'supplier_id',
		//     'voucher_number',
		//     'return_date',
		//     'product_name',
		//     'exp_date',
		//     'batch_no',
		//    'ret_quantity',
		//    'bill_no',
		//    'bill_date',
		//     'purchase_price',
		//     'tax1',
		//     'tax2',
		//     'total',
		// 


		DB::beginTransaction();

		try {

			// error_log('before loop '.$rList);

			error_log('type of return list  ' . gettype($rList));

			foreach ($rList as $r) {
				// error_log("Return Voucher: ".$r->color);
				$returnVoucher = new ReturnVouchers([
					'supplier_id' => $supplierID,
					'voucher_number' => $supplierID,
					'return_date' => date('Y-m-d'),
					'product_name' => $r->itemName,
					'exp_date' => $r->expiryDate,
					'batch_no' => $r->batchNo,
					'ret_quantity' => $r->tax3,
					'bill_no' => $r->billNo,
					'bill_date' => $r->receiptDate,
					'purchase_price' => $r->purchasePrice,
					'purchase_disc' => $r->purchaseDisc,
					'tax1' => $r->tax1,
					'tax2' => $r->tax2,
					'total' => $r->subTotal
				]);



				$returnVoucher->save();

				//now update the stock
				 $stockId = isset($r->stockID) ? $r->stockID : $r->stockId;
				 $stock = Stock::findOrFail($stockId);
				 $stock->qty = $stock->qty - ($r->tax3 * $stock->strip_size);
				 $stock->update();



			}

			// new code copy here 
			$transaction = new Transaction([
				'narration'         => 'expiry return',
				'generated_source'  => 'online',
				'branch_id'         => Auth::user()->branch_id,
			]);

			$transaction->save();

			foreach ($counterEntry as $item) {
				$accountId = isset($item->accountID) ? $item->accountID : $item->accountId;
				$accountHead = isset($item->accountHead) ? $item->accountHead : $item->accountHead;
				$amount = isset($item->amount) ? $item->amount : 0;
				$type = isset($item->type) ? $item->type : 'Debit';
				
				$subTransaction = new SubTransaction([
					'transaction_id'     => $transaction->id,
					'account_id'     	 => $accountId,
					'account_name'	 	 => $accountHead,
					'amount'      	     => $amount,
					'type'      		 => $type,
				]);

				$subTransaction->save();
			}

			$response = response()->json([
				'alert' => 'info',
				'msg'   => 'Return Voucher Successfully now',
			]);

			DB::commit();
		} catch (Exception $e) {
			DB::rollBack();

			$response = response()->json([
				'alert' => 'danger',
				'msg'   => $e
			]);

			throw $e;
		}
	}


	public function stockExpiryReport(Request $request)
	{
		$filters = json_decode($request->filters);
		//error_log("EXPIRY FILKTERS ".$filters);
		$date1 = $filters->date1;
		$date2 = $filters->date2;
		$supplier = $filters->customerID;

		error_log("expiry filters customer id  " . $supplier);
		error_log("The exp filter dates " . $date1 . ' ' . $date2);

		if ($filters->storeID == 0) {
			$filters->storeID  = Auth::user()->branch_id;
		}

		$cMonth = date('Y-m') . '-31';

		$expirySQL = "";

		//this is soumik code - back to original query but with proper stock sync
		if ($supplier > 0) {
			$expirySQL =	"SELECT psr.id,pr.receipt_no,pr.bill_no,psr.sub_total*0, p.account_title, psr.item_name, (psr.expiry_date), psr.batch_no, (pr.receipt_date),format( (s.qty/s.strip_size),0) as qty, psr.purchase_price, psr.purchase_disc, psr.tax_1, psr.tax_2, psr.tax_3,s.mrp,psr.stock_id as stockID FROM pos_sub_receipts psr, pos_receipts pr, profilers p, stocks s WHERE s.qty>0 and psr.stock_id=s.id and psr.expiry_date between '$date1' and '$date2' and pr.bill_no!='' and pr.id=psr.pos_receipt_id and pr.profile_id=p.id and pr.profile_id=$supplier order by psr.expiry_date DESC";
		} else {
			$expirySQL =	"SELECT psr.id,pr.receipt_no,pr.bill_no,psr.sub_total*0, p.account_title, psr.item_name, (psr.expiry_date), psr.batch_no, (pr.receipt_date),format( (s.qty/s.strip_size),0) as qty, psr.purchase_price, psr.purchase_disc, psr.tax_1, psr.tax_2, psr.tax_3,s.mrp,psr.stock_id as stockID FROM pos_sub_receipts psr, pos_receipts pr, profilers p, stocks s WHERE s.qty>0 and psr.stock_id=s.id and psr.expiry_date between '$date1' and '$date2' and pr.bill_no!='' and pr.id=psr.pos_receipt_id and pr.profile_id=p.id order by psr.expiry_date DESC";
		}
		$record = DB::select(DB::raw($expirySQL));


		// $record = Stock::with([
		// 	'branchDetails'
		// ])
		// ->where('branch_id','=', $filters->storeID)
		// ->where('status','=', 'Active')
		// // ->whereDate('expiry_date','<',$cMonth)
		// ->whereBetween('expiry_date',[$date1, $date2])
		// ->where('qty','>','0')

		// ->orderBy('expiry_date','DESC')
		// ->get();

		//error_log("final expiry data ".$record);
		//error_log("the expiry stock report data ". $record);
		return [
			'resultTitle' => '',
			'record' => $record,
			'supplierId' => $supplier,
		];
	}

	public function userReport(Request $request)
	{
		$filters = json_decode($request->filters);

		if ($filters->storeID == 0) {
			$filters->storeID  = Auth::user()->branch_id;
		}

		$record = User::join('branches', 'users.branch_id', '=', 'branches.id')
			->join('roles', 'users.role', '=', 'roles.id')
			->where('users.status', 'Active')
			->where('users.branch_id', $filters->storeID)
			->orderBy('users.id', 'DESC')
			->get(['users.*', 'branches.name as branchName', 'branches.code as branchCode', 'roles.name as roleName']);

		return [
			'resultTitle' => '',
			'record' => $record,
		];
	}

	public function stockReport(Request $request)
	{
		$filters = json_decode($request->filters);

		if ($filters->storeID == 0) {
			$filters->storeID  = Auth::user()->branch_id;
		}

		$record = Stock::with([
			'branchDetails'
		])
			->where('branch_id', '=', $filters->storeID)
			->orderBy('id', 'DESC')
			->get();

		return [
			'resultTitle' => '',
			'record' => $record,
		];
	}

		//this is soumik code [26-11-2025]
	public function stockAlertReport(Request $request)
{
    $filters = json_decode($request->filters);

    $branchId = Auth::user()->branch_id;
    $productName = isset($filters->productName) ? $filters->productName : '';

    $query = Stock::where('branch_id', '=', $branchId)
        ->whereRaw('qty <= min_stock');

    if (!empty($productName)) {
        $query->where('product_name', 'LIKE', '%' . $productName . '%');
    }

    $stocks = $query->orderBy('id', 'DESC')->get();

    // Load all suppliers at once
    $batchNos = $stocks->pluck('batch_no')->unique()->toArray();
    $suppliers = DB::table('pos_sub_receipts')
        ->join('pos_receipts', 'pos_sub_receipts.pos_receipt_id', '=', 'pos_receipts.id')
        ->join('profilers', 'pos_receipts.profile_id', '=', 'profilers.id')
        ->whereIn('pos_sub_receipts.batch_no', $batchNos)
        ->where('pos_receipts.type', 'PUR')
        ->select('pos_sub_receipts.batch_no', 'profilers.account_title')
        ->groupBy('pos_sub_receipts.batch_no', 'profilers.account_title')
        ->get()
        ->keyBy('batch_no');

    $record = [];
    $groupedBySupplier = [];
    
    foreach ($stocks as $stock) {
        $supplierName = isset($suppliers[$stock->batch_no]) ? $suppliers[$stock->batch_no]->account_title : 'Unknown Supplier';

        $item = [
            'productName' => $stock->product_name,
            'stripSize' => $stock->strip_size,
            'packSize' => $stock->pack_size,
            'batchNo' => $stock->batch_no,
            'qty' => $stock->qty,
            'expiryDate' => $stock->expiry_date,
            'minStock' => $stock->min_stock,
            'supplierName' => $supplierName
        ];

        $record[] = $item;
        
        if (!isset($groupedBySupplier[$supplierName])) {
            $groupedBySupplier[$supplierName] = [];
        }
        $groupedBySupplier[$supplierName][] = $item;
    }

    return [
        'resultTitle' => !empty($productName) ? 'Search results for: ' . $productName : 'All stock alerts',
        'record' => $record,
        'groupedBySupplier' => $groupedBySupplier,
    ];
}

	public function userActivityReport(Request $request)
	{
		$filters = json_decode($request->filters);

		if ($filters->storeID == 0) {
			$filters->storeID  = Auth::user()->branch_id;
		}

		$dt = new DateFilters();

		$dt->set('filter', $filters->filterType);
		$dt->set('date1', $filters->date1);
		$dt->set('date2', $filters->date2);
		$date1 = $dt->getTheDates()[0];
		$date2 = $dt->getTheDates()[1];


		$record = ActivityLog::with([
			'userDetails',
			'branchDetails'
		])
			->whereDate('created_at', '>=', $date1)
			->whereDate('created_at', '<=', $date2)
			->where('branch_id', '=', $filters->storeID)
			->orderBy('id', 'DESC')
			->get();


		return [
			'resultTitle' => 'From ' . date('D, d M  Y', strtotime($date1)) . ' -To- ' . date('D, d M  Y', strtotime($date2)),
			'record' => $record,
		];
	}

		// THIS IS SOUMIK CODE - 28-11-2025
	public function nonMovingItemsReport(Request $request)
	{
		$filters = json_decode($request->filters);
		$category = $filters->category;
		$period = $filters->period;
		$branchId = Auth::user()->branch_id;

		// Calculate date range based on period
		$dateCondition = '';
		$customMonth = isset($filters->customMonth) ? $filters->customMonth : null;
		
		switch ($period) {
			case 'current_month':
				$dateCondition = "AND DATE_FORMAT(pr.receipt_date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')";
				break;
			case 'previous_month':
				$dateCondition = "AND DATE_FORMAT(pr.receipt_date, '%Y-%m') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m')";
				break;
			case '2_months':
				$dateCondition = "AND pr.receipt_date >= DATE_SUB(NOW(), INTERVAL 2 MONTH)";
				break;
			case '3_months':
				$dateCondition = "AND pr.receipt_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
				break;
			case '6_months':
				$dateCondition = "AND pr.receipt_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
				break;
			case 'custom_month':
				if ($customMonth) {
					$dateCondition = "AND DATE_FORMAT(pr.receipt_date, '%Y-%m') = '$customMonth'";
				}
				break;
			case 'all':
			default:
				$dateCondition = '';
				break;
		}

		// Category filter
		$categoryCondition = '';
		if ($category !== 'all') {
			// Handle both Medicine and FMCG categories with ID mapping
			if ($category === 'Medicine') {
				$categoryCondition = "AND (s.category = 'Medicine' OR s.category = 'medicine' OR s.category = '108')";
			} elseif ($category === 'FMCG') {
				$categoryCondition = "AND (s.category = 'FMCG' OR s.category = 'fmcg' OR s.category = '107')";
			} else {
				$categoryCondition = "AND s.category = '$category'";
			}
		}

		$sql = "
			SELECT 
				s.product_name,
				CASE 
					WHEN s.category = '107' THEN 'FMCG'
					WHEN s.category = '108' THEN 'Medicine'
					WHEN s.category = 'FMCG' THEN 'FMCG'
					WHEN s.category = 'Medicine' THEN 'Medicine'
					ELSE COALESCE(NULLIF(s.category, ''), 'Uncategorized')
				END as category,
				s.qty as current_stock,
				s.sale_price,
				s.purchase_price,
				COALESCE(SUM(psr.total_unit), 0) as total_sale_qty,
				(s.qty * s.sale_price) as potential_value,
				COALESCE(DATE_FORMAT(MAX(pr.receipt_date), '%Y-%m-%d'), 'Never Sold') as last_sale_date,
				CASE 
					WHEN COALESCE(SUM(psr.total_unit), 0) = 0 THEN 'Dead Stock'
					WHEN COALESCE(SUM(psr.total_unit), 0) < 5 THEN 'Very Slow'
					WHEN COALESCE(SUM(psr.total_unit), 0) < 10 THEN 'Slow Moving'
					ELSE 'Normal'
				END as status,
				CASE 
					WHEN COALESCE(SUM(psr.total_unit), 0) = 0 THEN 4
					WHEN COALESCE(SUM(psr.total_unit), 0) < 5 THEN 3
					WHEN COALESCE(SUM(psr.total_unit), 0) < 10 THEN 2
					ELSE 1
				END as status_order
			FROM stocks s
			LEFT JOIN pos_sub_receipts psr ON s.id = psr.stock_id
			LEFT JOIN pos_receipts pr ON psr.pos_receipt_id = pr.id AND pr.type = 'INE' $dateCondition
			WHERE s.branch_id = $branchId 
				AND s.status = 'Active'
				AND s.product_name IS NOT NULL
				AND s.product_name != ''
				$categoryCondition
			GROUP BY s.id, s.product_name, s.category, s.qty, s.sale_price, s.purchase_price
			HAVING COALESCE(SUM(psr.total_unit), 0) = 0
			ORDER BY s.product_name ASC
		";

		$record = DB::select(DB::raw($sql));

		$periodText = [
			'current_month' => 'Current Month',
			'previous_month' => 'Previous Month',
			'2_months' => 'Last 2 Months',
			'3_months' => 'Last 3 Months',
			'6_months' => 'Last 6 Months',
			'custom_month' => $customMonth ? date('F Y', strtotime($customMonth . '-01')) : 'Custom Month',
			'all' => 'All Time'
		];

		$categoryText = $category === 'all' ? 'All Categories' : ucfirst($category);

		return [
			'resultTitle' => $categoryText . ' - ' . $periodText[$period],
			'record' => $record,
		];
	}

	// THIS IS SOUMIK CODE - 28-11-2025
	public function searchProductNames(Request $request)
	{
		$filters = json_decode($request->filters);
		$query = $filters->query;
		$branchId = Auth::user()->branch_id;

		$products = DB::table('stocks')
			->where('branch_id', $branchId)
			->where('status', 'Active')
			->where('product_name', 'LIKE', "%$query%")
			->whereNotNull('product_name')
			->where('product_name', '!=', '')
			->distinct()
			->limit(10)
			->pluck('product_name');

		return ['products' => $products];
	}

	// THIS IS SOUMIK CODE - 28-11-2025
	public function getBrands(Request $request)
	{
		$branchId = Auth::user()->branch_id;
		
		$brands = DB::table('option_tags as ot')
			->join('stocks as s', 's.brand', '=', 'ot.option_name')
			->where('ot.option_type', 'Brands')
			->where('ot.status', 'Active')
			->where('s.branch_id', $branchId)
			->where('s.status', 'Active')
			->distinct()
			->orderBy('ot.option_name', 'ASC')
			->select('ot.option_name as id', 'ot.option_name as name')
			->get();

		return ['brands' => $brands];
	}

	// THIS IS SOUMIK CODE - 28-11-2025
	public function topSellingProducts(Request $request)
	{
		$filters = json_decode($request->filters);
		$dateRange = isset($filters->dateRange) ? $filters->dateRange : '30_days';
		$limit = isset($filters->limit) ? $filters->limit : 30;
		$category = isset($filters->category) ? $filters->category : 'all';
		$brand = isset($filters->brand) ? $filters->brand : 'all';
		$branchId = Auth::user()->branch_id;

		$dateCondition = '';
		switch ($dateRange) {
			case '7_days':
				$dateCondition = "AND pr.receipt_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
				break;
			case '15_days':
				$dateCondition = "AND pr.receipt_date >= DATE_SUB(NOW(), INTERVAL 15 DAY)";
				break;
			case '30_days':
				$dateCondition = "AND pr.receipt_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
				break;
			case '3_months':
				$dateCondition = "AND pr.receipt_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
				break;
			case '6_months':
				$dateCondition = "AND pr.receipt_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
				break;
			case 'this_year':
				$dateCondition = "AND YEAR(pr.receipt_date) = YEAR(NOW())";
				break;
		}

		$sql = "
			SELECT 
				psr.item_name as product_name,
				CASE 
					WHEN s.category = '107' THEN 'FMCG'
					WHEN s.category = '108' THEN 'Medicine'
					ELSE COALESCE(s.category, 'N/A')
			END as category,
				COUNT(DISTINCT pr.id) as total_transactions,
				SUM(psr.total_unit) as total_quantity_sold,
				SUM(psr.sub_total) as total_revenue,
				AVG(psr.sub_total / psr.total_unit) as avg_sale_price,
				MAX(pr.receipt_date) as last_sale_date,
				COALESCE(s.qty, 0) as current_stock,
				CASE 
					WHEN SUM(psr.total_unit) >= 100 THEN 'Hot Selling'
					WHEN SUM(psr.total_unit) >= 50 THEN 'High Demand'
					WHEN SUM(psr.total_unit) >= 20 THEN 'Good Sales'
					ELSE 'Moderate'
				END as sales_status
			FROM pos_sub_receipts psr
			JOIN pos_receipts pr ON psr.pos_receipt_id = pr.id
			LEFT JOIN stocks s ON psr.stock_id = s.id AND s.branch_id = $branchId
			WHERE pr.type = 'INE'
				AND pr.branch_id = $branchId
				$dateCondition";

		// Category filter
		if ($category != 'all') {
			if ($category == '108') {
				// Medicine includes legacy category IDs
				$sql .= " AND (s.category = '108' OR s.category = '13' OR s.category = '14' OR s.category = 'Medicine')";
			} elseif ($category == '107') {
				// FMCG
				$sql .= " AND (s.category = '107' OR s.category = 'FMCG')";
			} else {
				$sql .= " AND s.category = '$category'";
			}
		}

		// Brand filter
		if ($brand != 'all') {
			$sql .= " AND s.brand = '$brand'";
		}

		$sql .= "
			GROUP BY psr.item_name, s.category, s.qty
			ORDER BY total_quantity_sold DESC
			LIMIT $limit
		";

		$topProducts = DB::select(DB::raw($sql));

		$periodText = [
			'7_days' => 'Last 7 Days',
			'15_days' => 'Last 15 Days',
			'30_days' => 'Last 30 Days',
			'3_months' => 'Last 3 Months',
			'6_months' => 'Last 6 Months',
			'this_year' => 'This Year'
		];

		return [
			'resultTitle' => 'Top Selling Products - ' . $periodText[$dateRange],
			'record' => $topProducts,
		];
	}

	// THIS IS SOUMIK CODE - 28-11-2025
	public function productSaleHistory(Request $request)
	{
		$filters = json_decode($request->filters);
		$productName = $filters->productName;
		$dateRange = $filters->dateRange;
		$category = isset($filters->category) ? $filters->category : 'all';
		$brand = isset($filters->brand) ? $filters->brand : 'all';
		$branchId = Auth::user()->branch_id;

		// Calculate date range
		$dateCondition = '';
		switch ($dateRange) {
			case '7_days':
				$dateCondition = "AND pr.receipt_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
				break;
				case '15_days':
				$dateCondition = "AND pr.receipt_date >= DATE_SUB(NOW(), INTERVAL 15 DAY)";
				break;
			case '30_days':
				$dateCondition = "AND pr.receipt_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
				break;
			case '3_months':
				$dateCondition = "AND pr.receipt_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
				break;
			case '6_months':
				$dateCondition = "AND pr.receipt_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
				break;
			case 'this_year':
				$dateCondition = "AND YEAR(pr.receipt_date) = YEAR(NOW())";
				break;
			default:
				$dateCondition = '';
		}

		// Category filter
		$categoryCondition = '';
		if ($category != 'all') {
			$categoryCondition = "AND s.category = '$category'";
		}

		// Brand filter
		$brandCondition = '';
		if ($brand != 'all') {
			$brandCondition = "AND s.brand = '$brand'";
		}

		$sql = "
			SELECT 
				pr.receipt_date,
				pr.receipt_no,
				psr.item_name as product_name,
				psr.total_unit as quantity,
				(psr.sub_total / psr.total_unit) as sale_price,
				psr.sub_total as total_amount,
				prof.account_title as customer_name,
				u.name as sold_by,
				CASE 
					WHEN s.category = '107' THEN 'FMCG'
					WHEN s.category = '108' THEN 'Medicine'
					ELSE COALESCE(s.category, 'N/A')
				END as category
			FROM pos_sub_receipts psr
			JOIN pos_receipts pr ON psr.pos_receipt_id = pr.id
			JOIN profilers prof ON pr.profile_id = prof.id
			JOIN users u ON pr.created_by = u.id
			LEFT JOIN stocks s ON psr.stock_id = s.id AND s.branch_id = $branchId
			WHERE psr.item_name LIKE '%$productName%'
				AND pr.type = 'INE'
				AND pr.branch_id = $branchId
				$dateCondition
				$categoryCondition
				$brandCondition
			ORDER BY pr.receipt_date DESC
		";

		$history = DB::select(DB::raw($sql));

		// Calculate summary
		$totalQuantity = array_sum(array_column($history, 'quantity'));
		$totalRevenue = array_sum(array_column($history, 'total_amount'));
		$avgPrice = $totalQuantity > 0 ? $totalRevenue / $totalQuantity : 0;
		$lastSaleDate = count($history) > 0 ? date('d M Y', strtotime($history[0]->receipt_date)) : 'N/A';
		$totalTransactions = count($history);

		// Get full product details first
		$productDetails = DB::table('stocks')
			->where('product_name', 'LIKE', "%$productName%")
			->where('branch_id', $branchId)
			->first(['product_name', 'category', 'brand', 'qty', 'strip_size', 'pack_size', 'min_stock']);

		// Get brand name - stocks.brand already contains the brand name or ID
		$brandName = 'N/A';
		if ($productDetails && $productDetails->brand) {
			// First check if brand is numeric (ID) or text (name)
			if (is_numeric($productDetails->brand)) {
				// It's an ID, fetch from option_tags
				$brandInfo = DB::table('option_tags')
					->where('id', $productDetails->brand)
					->where('option_type', 'Brands')
					->first(['option_name']);
				if ($brandInfo) {
					$brandName = $brandInfo->option_name;
				}
			} else {
				// It's already a brand name
				$brandName = $productDetails->brand;
			}
		}

		// Find cheapest supplier for this product
		$cheapestSupplier = DB::table('pos_sub_receipts as psr')
			->join('pos_receipts as pr', 'psr.pos_receipt_id', '=', 'pr.id')
			->join('profilers as p', 'pr.profile_id', '=', 'p.id')
			->where('psr.item_name', 'LIKE', "%$productName%")
			->where('pr.type', 'PUR')
			->where('pr.branch_id', $branchId)
			->select('p.account_title', DB::raw('MIN(psr.purchase_price) as min_price'))
			->groupBy('p.id', 'p.account_title')
			->orderBy('min_price', 'ASC')
			->first();

		// Top customer
		$customerSales = [];
		foreach ($history as $sale) {
			if (!isset($customerSales[$sale->customer_name])) {
				$customerSales[$sale->customer_name] = 0;
			}
			$customerSales[$sale->customer_name] += $sale->quantity;
		}
		arsort($customerSales);
		$topCustomer = count($customerSales) > 0 ? array_key_first($customerSales) : 'N/A';

		$category = 'N/A';
		if ($productDetails) {
			if ($productDetails->category == '107') $category = 'FMCG';
			elseif ($productDetails->category == '108') $category = 'Medicine';
			else $category = $productDetails->category;
		}

		// Check if product is in top selling list
		$salesStatus = 'Normal';
		$isTopSelling = false;
		if ($totalQuantity >= 100) {
			$salesStatus = 'Hot Selling';
			$isTopSelling = true;
		} elseif ($totalQuantity >= 50) {
			$salesStatus = 'High Demand';
			$isTopSelling = true;
		} elseif ($totalQuantity >= 20) {
			$salesStatus = 'Good Sales';
		}

		return [
			'history' => $history,
			'summary' => [
				'totalQuantity' => $totalQuantity,
				'totalRevenue' => $totalRevenue,
				'avgPrice' => $avgPrice,
				'lastSaleDate' => $lastSaleDate,
				'totalTransactions' => $totalTransactions,
				'topCustomer' => $topCustomer,
				'salesStatus' => $salesStatus,
				'brandName' => $brandName,
				'isTopSelling' => $isTopSelling,
			],
			'productInfo' => [
				'productName' => $productDetails->product_name ?? $productName,
				'category' => $category,
				'brand' => $brandName,
				'currentStock' => $productDetails->qty ?? 0,
				'stripSize' => $productDetails->strip_size ?? 1,
				'packSize' => $productDetails->pack_size ?? 0,
				'supplierName' => $cheapestSupplier->account_title ?? 'N/A',
				'reorderLevel' => $productDetails->min_stock ?? 0,
				'recommendedStrips' => $productDetails && $productDetails->qty < $productDetails->min_stock ? ceil(($productDetails->min_stock - $productDetails->qty) / ($productDetails->strip_size ?: 1)) : 0,
			],
		];
	}

	// THIS IS SOUMIK CODE - Bulk Order Voucher (29-11-2025)
	public function getBulkOrderVoucherData(Request $request)
	{
		$productNames = json_decode($request->productNames);
		$branchId = Auth::user()->branch_id;
		// echo '<pre>';
		// print_r($branchId);
		// echo '<pre/>';
		// die;

		$productsData = [];

		foreach ($productNames as $productName) {
			$product = DB::table('stocks')
				->where('product_name', 'LIKE', "%$productName%")
				->where('branch_id', $branchId)
				->first(['product_name', 'brand', 'qty', 'strip_size', 'pack_size', 'min_stock']);

			if (!$product) continue;

			$brandName = 'N/A';
			if ($product->brand) {
				if (is_numeric($product->brand)) {
					$brandInfo = DB::table('option_tags')
						->where('id', $product->brand)
						->where('option_type', 'Brands')
						->first(['option_name']);
					$brandName = $brandInfo ? $brandInfo->option_name : 'N/A';
				} else {
					$brandName = $product->brand;
				}
			}

			$supplier = DB::table('pos_sub_receipts as psr')
				->join('pos_receipts as pr', 'psr.pos_receipt_id', '=', 'pr.id')
				->join('profilers as p', 'pr.profile_id', '=', 'p.id')
				->where('psr.item_name', 'LIKE', "%$productName%")
				->where('pr.type', 'PUR')
				->where('pr.branch_id', $branchId)
				->select('p.account_title', 'p.id', DB::raw('MIN(psr.purchase_price) as min_price'))
				->groupBy('p.id', 'p.account_title')
				->orderBy('min_price', 'ASC')
				->first();

			$recommendedQty = 0;
			if ($product->qty < $product->min_stock) {
				$recommendedQty = ceil(($product->min_stock - $product->qty) / ($product->strip_size ?: 1));
			}

			$productsData[] = [
				'productName' => $product->product_name,
				'brandName' => $brandName,
				'stripSize' => $product->strip_size ?? 1,
				'packSize' => $product->pack_size ?? 0,
				'currentStock' => $product->qty ?? 0,
				'minStock' => $product->min_stock ?? 0,
				'recommendedQty' => $recommendedQty,
				'supplierName' => $supplier ? $supplier->account_title : 'N/A',
				'supplierId' => $supplier ? $supplier->id : null,
			];
		}

		$groupedBySupplier = [];
		foreach ($productsData as $product) {
			$supplierName = $product['supplierName'];
			if (!isset($groupedBySupplier[$supplierName])) {
				$groupedBySupplier[$supplierName] = [];
			}
			$groupedBySupplier[$supplierName][] = $product;
		}

		return [
			'groupedBySupplier' => $groupedBySupplier,
		];

	}
}
