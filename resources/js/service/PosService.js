import ExceptionHandling from './ExceptionHandling.js'
import { useStore, ActionTypes } from "../store";
import instance from './index';

export default class PosService {

	getItems() {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/pos_initialization';
		return instance()(
			{
				method: 'get',
				url: api,
			}
		).then(res => res.data)
		.catch((e) => ExceptionHandling.HandleErrors(e))
		.finally(() => {
			store.dispatch(ActionTypes.PROGRESS_BAR, false);
		})
	}

	searchItem(keyword) {
		console.log('PosService searchItem called with keyword:', keyword);
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/search_items';
		const formData = new FormData();
		formData.append('keyword', keyword);

		return instance()(
			{
				method: 'post',
				url: api,
				data: formData,
			}
		).then(res => {
			console.log('Search API response:', res.data);
			return res.data;
		})
		.catch((e) => {
			console.error('Search API error:', e);
			ExceptionHandling.HandleErrors(e);
			return { records: [], error: e.message || 'Search failed' };
		})
		.finally(() => {
			store.dispatch(ActionTypes.PROGRESS_BAR, false);
		})
	}

	saveItem(postObj,paymentList, stateObj, savedItemList,counterEntry) {
		//alert("poservice saveItem");

		// for(var i=0;i<savedItemList.length;i++){
		console.log("type of savedItemList "+typeof savedItemList);
			console.log("saved item list "+savedItemList);

		// }
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		let api = '/api/save_pos_receipt';
		const formData = new FormData();
		formData.append('profile_id',postObj.profileID);
		formData.append('payment_list',JSON.stringify(paymentList));
		formData.append('discount',postObj.discount);
		formData.append('total_paid',postObj.totalPaid);
		formData.append('total_tendered',postObj.totalTendered);
		formData.append('total_change',postObj.totalChange);
		formData.append('total_gross_amt',postObj.totalGrossAmt);
		formData.append('total_bill',postObj.totalBill);
		formData.append('total_tax1',postObj.totalTax1);
		formData.append('total_tax2',postObj.totalTax2);
		formData.append('total_tax3',postObj.totalTax3);
		formData.append('total_tax',postObj.totalTax);
		formData.append('description',postObj.description);
		formData.append('payment_method',postObj.paymentMethod);
		formData.append('doctor_details',postObj.doctorDetails);
		formData.append('patient_details',postObj.patientDetails);
		formData.append('status',postObj.status);
		//alert(postObj.totalTendered);
		if(postObj.type=='MDF'){
			api='/api/update_pos_receipt'
		}
		//alert('search receipt no in JS'+postObj.searchReceiptNo);
		formData.append('receiptNo', postObj.searchReceiptNo);
		formData.append('type',postObj.type);
		formData.append('search_receipt_no',postObj.searchReceiptNo);
		formData.append('transfer_store_id',stateObj.transferStoreID);
		formData.append('item_list',JSON.stringify(savedItemList));
		formData.append('counter_entry',JSON.stringify(counterEntry));
		//alert(JSON.stringify(savedItemList));
		//alert("calling api "+api);
		return instance()(
			{
				method: 'post',
				url: api,
				data: formData,
			}
		).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	/**
	 * Called from purchase menu
	 * @param {} postObj 
	 * @param {*} paymentList 
	 * @param {*} savedItemList 
	 * @param {*} counterEntry 
	 * @returns 
	 */
	savePurchaseItem(postObj,paymentList,savedItemList,counterEntry) {
      if(paymentList!=null){
		paymentList.paymentType='myown';
	  } 
	  
	   // alert("posservice : savePurchaseItem"+JSON.stringify(savedItemList)+".. type .."+postObj.type);
		//alert ("payment list in posservice" +paymentList);    // alerts {"myProp":"Hello"};
		// alert("postobj " +JSON.stringify(postObj));
		// alert(postObj.profileID);
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		let api = '/api/save_purchase_receipt';
		const formData = new FormData();
		formData.append('profile_id',postObj.profileID);
		formData.append('payment_list',JSON.stringify(paymentList));
		formData.append('discount',postObj.discount);
		formData.append('total_paid',postObj.totalPaid);
		formData.append('total_tendered',postObj.totalTendered);
		formData.append('total_change',postObj.totalChange);
		formData.append('total_gross_amt',postObj.totalGrossAmt);
		formData.append('total_bill',postObj.totalBill);
		formData.append('total_tax1',postObj.totalTax1);
		formData.append('total_tax2',postObj.totalTax2);
		formData.append('total_tax3',postObj.totalTax3);
		formData.append('total_tax',postObj.totalTax);
		formData.append('description',postObj.description);
		formData.append('payment_method',postObj.paymentMethod);
		formData.append('bill_no',postObj.billNo);
		//alert(postObj.billDate);
		formData.append('bill_date',postObj.billDate);
		formData.append('status',postObj.status);
		formData.append('type',postObj.type);
		formData.append('search_receipt_no',postObj.searchReceiptNo);
		formData.append('item_list',JSON.stringify(savedItemList));
		formData.append('counter_entry',JSON.stringify(counterEntry));
		//alert("save purchase item "+JSON.stringify(counterEntry));
		// alert("form data "+formData);
		// for(let [name, value] of formData) {
		// 	alert(`${name} = ${value}`); // key1 = value1, then key2 = value2
		//   }
		if(postObj.type=='MDF'){
			api='/api/update_purchase_receipt';
			//alert("calling update receipt method ");

		}
		//alert("calling api "+api);
		return instance()(
			{
				method: 'post',
				url: api,
				data: formData,
			}
		).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	
	getReceiptItems(ReceiptNo) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/search_pos_receipt';
		const formData = new FormData();
		formData.append('receipt_no',ReceiptNo);
		
		return instance()(
			{
				method: 'post',
				url: api,
				data: formData,
			}
		).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	
	getPurchaseItems(ReceiptNo) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/search_purchase_receipt';
		const formData = new FormData();
		formData.append('receipt_no',ReceiptNo);
		
		return instance()(
			{
				method: 'post',
				url: api,
				data: formData,
			}
		).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}

	transactionList(postObj,start) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/pos_transaction_list';
		const formData = new FormData();
		formData.append('filters', JSON.stringify(postObj));
		formData.append('start', start);
		return instance()(
			{
				method: 'post',
				url: api,
				data: formData,
			}
		).then(res => res.data)
		.catch((e) => ExceptionHandling.HandleErrors(e))
		.finally(() => {
			store.dispatch(ActionTypes.PROGRESS_BAR, false);
		})
	}
	
	savePayment(postObj,paymentList,counterEntries) {
		//alert('posservice savepayment');
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/pos_payments';
		const formData = new FormData();
		formData.append('payment_list', JSON.stringify(paymentList));
		formData.append('counter_list', JSON.stringify(counterEntries));
		formData.append('receipt_id', postObj.id);
		formData.append('type', postObj.type);
		return instance()(
			{
				method: 'post',
				url: api,
				data: formData,
			}
		).then(res => res.data)
		.catch((e) => ExceptionHandling.HandleErrors(e))
		.finally(() => {
			store.dispatch(ActionTypes.PROGRESS_BAR, false);
		})
	}
	
	stockLeft(postObj) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/stock_transfer_status';
		const formData = new FormData();
		formData.append('receipt_id', postObj.id);
		return instance()(
			{
				method: 'post',
				url: api,
				data: formData,
			}
		).then(res => res.data)
		.catch((e) => ExceptionHandling.HandleErrors(e))
		.finally(() => {
			store.dispatch(ActionTypes.PROGRESS_BAR, false);
		})
	}
	
	voidStock(postObj) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/transaction_void';
		const formData = new FormData();
		formData.append('receipt_id', postObj.id);
		return instance()(
			{
				method: 'post',
				url: api,
				data: formData,
			}
		).then(res => res.data)
		.catch((e) => ExceptionHandling.HandleErrors(e))
		.finally(() => {
			store.dispatch(ActionTypes.PROGRESS_BAR, false);
		})
	}
	
	saveStock(postObj) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/save_transfer_stock';
		const formData = new FormData();
		formData.append('receipt_id', postObj.id);
		return instance()(
			{
				method: 'post',
				url: api,
				data: formData,
			}
		).then(res => res.data)
		.catch((e) => ExceptionHandling.HandleErrors(e))
		.finally(() => {
			store.dispatch(ActionTypes.PROGRESS_BAR, false);
		})
	}

	getReceiptData(receiptID) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/get_pos_receipt';
		const formData = new FormData();
		formData.append('id', receiptID);

		return instance()(
			{
				method: 'post',
				url: api,
				data: formData,
			}
		).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
}