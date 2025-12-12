import ExceptionHandling from './ExceptionHandling.js'
import { useStore, ActionTypes } from "../store";
import instance from './index';

export default class StoreReports {

	getFilterList() {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/reports_filter_list';
		return instance().get(api)
			.then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}

	salesRefundReport(postObj) {
		const f = JSON.stringify(postObj);
		
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/sales_return_report';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	
	taxReport(postObj) {
		const f = JSON.stringify(postObj);
		
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/tax_report';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	
	userActivityReport(postObj) {
		const f = JSON.stringify(postObj);
		
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/user_activity_report';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	
	stockExpiryReport(postObj) {
		const f = JSON.stringify(postObj);
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/expiry_report';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}

	//sam
	saveReturnVoucher(postObj, supplierID, total, returnList, counterEntry){
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = "/api/save_return_voucher";
		console.log(JSON.stringify(returnList));

		const formData = new FormData();
		formData.append('returnList', JSON.stringify(returnList));
		formData.append('counterEntry', JSON.stringify(counterEntry));
		formData.append('totalAmount', total);
		formData.append('supplierID', supplierID);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	
	userReport(postObj) {
		const f = JSON.stringify(postObj);
		
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/user_report';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	
	stockReport(postObj) {
		const f = JSON.stringify(postObj);
		
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/stock_report';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	
	stockAlertReport(postObj) {
		const f = JSON.stringify(postObj);
		
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/stock_alert_report';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	
	purchaseReport(postObj) {
		const f = JSON.stringify(postObj);
		
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/purchasing_report';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	
	performanceReport(postObj) {
		const f = JSON.stringify(postObj);
		
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/performance_report';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	
	generalJournal(postObj) {
		const f = JSON.stringify(postObj);
		
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/general_journal';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	
	incomeStatement(postObj) {
		const f = JSON.stringify(postObj);
		
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/income_statement';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	
	trialBalance(postObj) {
		const f = JSON.stringify(postObj);
		
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/trial_balance';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	
	ledgerStatement(postObj) {
		const f = JSON.stringify(postObj);
		
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/ledger_statement';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			});
	}
	
	bankStatement(postObj) {
		const f = JSON.stringify(postObj);
		
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/bank_statement';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			});
	}
	
	getAllBanks() {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/get_all_bank';
	
		return instance()({
			url: api,
		}).then(res => res.data)
		.catch((e) => ExceptionHandling.HandleErrors(e))
		.finally(() => {
			store.dispatch(ActionTypes.PROGRESS_BAR, false);
		});
	}

	accountStatement(postObj) {
		const f = JSON.stringify(postObj);
		
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/account_statement';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			});
	}

	getStockIds() {
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/get_stock_ids';

		return instance().get(api)
			.then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			});
	}

	// THIS IS SOUMIK CODE - 28-11-2025
	searchProductNames(postObj) {
		const f = JSON.stringify(postObj);
		
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/search_product_names';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			});
	}

	// THIS IS SOUMIK CODE - 28-11-2025
	productSaleHistory(postObj) {
		const f = JSON.stringify(postObj);
		
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/product_sale_history';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			});
	}

	// THIS IS SOUMIK CODE - 28-11-2025
	nonMovingItemsReport(postObj) {
		const f = JSON.stringify(postObj);
		
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/non_moving_items_report';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			});
	}

	// THIS IS SOUMIK CODE - 28-11-2025
	topSellingProducts(postObj) {
		const f = JSON.stringify(postObj);
		
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/top_selling_products';
		const formData = new FormData();

		formData.append('filters', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			});
	}

	// THIS IS SOUMIK CODE - 28-11-2025
	getBrands() {
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/get_brands';

		return instance().get(api)
			.then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			});
	}

	// THIS IS SOUMIK CODE - Bulk Order Voucher (10-12-2025)
	getBulkOrderVoucherData(postObj) {
		const f = JSON.stringify(postObj.productNames);
		
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/bulk_order_voucher_data';
		const formData = new FormData();

		formData.append('productNames', f);

		return instance()({
			method: 'post',
			url: api,
			data: formData,
		}).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			});
	}
}