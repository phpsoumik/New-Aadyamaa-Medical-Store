import ExceptionHandling from './ExceptionHandling.js'
import { useStore, ActionTypes } from "../store";
import instance from './index';

export default class ReceiptService {

	getItems(postObj,start) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/receipt_transaction_list';
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

	save(postObj,stateObj, counterObj) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/add_receipt_transaction';
		const formData = new FormData();
		formData.append('profile_id', postObj.profileID);
		formData.append('profile_name', stateObj.selectedProfile);
		formData.append('receipt_date', stateObj.receiptDate);
		formData.append('receipt_due_date', postObj.receiptDueDate);
		formData.append('memo', stateObj.description);
		formData.append('type', postObj.type);
		formData.append('item_list', JSON.stringify(stateObj.itemList));
		formData.append('counter_entry', JSON.stringify(counterObj));
		formData.append('status', postObj.status);
		formData.append('total_tax1', postObj.totalTax1);
		formData.append('total_tax2', postObj.totalTax2);
		formData.append('total_tax3', postObj.totalTax3);
		formData.append('total_gross', postObj.totalGross);
		formData.append('total_discount', postObj.totalDiscount);
		formData.append('total_tax', postObj.totalTax);
		formData.append('total_bill', postObj.totalBill);
		formData.append('payment_list', JSON.stringify(postObj.paymentType));
		formData.append('total_paid', postObj.totalAmount);

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
	
	saveInvoice(postObj,stateObj, counterObj) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/add_receipt_transaction';
		const formData = new FormData();
		formData.append('profile_id', postObj.profileID);
		formData.append('profile_name', stateObj.selectedProfile);
		formData.append('receipt_date', stateObj.receiptDate);
		formData.append('receipt_due_date', stateObj.receiptDueDate);
		formData.append('memo', stateObj.description);
		formData.append('type', postObj.type);
		formData.append('item_list', JSON.stringify(stateObj.itemList));
		formData.append('counter_entry', JSON.stringify(counterObj));
		formData.append('status', postObj.status);
		formData.append('total_tax1', postObj.totalTax1);
		formData.append('total_tax2', postObj.totalTax2);
		formData.append('total_tax3', postObj.totalTax3);
		formData.append('total_gross', postObj.totalGross);
		formData.append('total_discount', postObj.totalDiscount);
		formData.append('total_tax', postObj.totalTax);
		formData.append('total_bill', postObj.totalBill);
		formData.append('payment_method', postObj.paymentType);
		formData.append('payment_list', JSON.stringify(postObj.paymentType));
		formData.append('total_paid', postObj.totalAmount);

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

	update(postObj,stateObj,counterObj) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/edit_receipt_transaction';
		const formData = new FormData();
		formData.append('id', postObj.id);
		formData.append('profile_id', postObj.profileID);
		formData.append('profile_name', stateObj.selectedProfile);
		formData.append('receipt_date', stateObj.receiptDate);
		formData.append('receipt_due_date', postObj.receiptDueDate);
		formData.append('memo', stateObj.description);
		formData.append('type', postObj.type);
		formData.append('item_list', JSON.stringify(stateObj.itemList));
		formData.append('counter_entry', JSON.stringify(counterObj));
		formData.append('status', postObj.status);
		formData.append('total_tax1', postObj.totalTax1);
		formData.append('total_tax2', postObj.totalTax2);
		formData.append('total_tax3', postObj.totalTax3);
		formData.append('total_gross', postObj.totalGross);
		formData.append('total_discount', postObj.totalDiscount);
		formData.append('total_tax', postObj.totalTax);
		formData.append('total_bill', postObj.totalBill);
		formData.append('total_paid', postObj.totalAmount);

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
	
	updateInvoice(postObj,stateObj,counterObj) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/edit_receipt_transaction';
		const formData = new FormData();
		formData.append('id', postObj.id);
		formData.append('profile_id', postObj.profileID);
		formData.append('profile_name', stateObj.selectedProfile);
		formData.append('receipt_date', stateObj.receiptDate);
		formData.append('receipt_due_date', stateObj.receiptDueDate);
		formData.append('memo', stateObj.description);
		formData.append('type', postObj.type);
		formData.append('item_list', JSON.stringify(stateObj.itemList));
		formData.append('counter_entry', JSON.stringify(counterObj));
		formData.append('status', postObj.status);
		formData.append('total_tax1', postObj.totalTax1);
		formData.append('total_tax2', postObj.totalTax2);
		formData.append('total_tax3', postObj.totalTax3);
		formData.append('total_gross', postObj.totalGross);
		formData.append('total_discount', postObj.totalDiscount);
		formData.append('total_tax', postObj.totalTax);
		formData.append('total_bill', postObj.totalBill);
		formData.append('payment_method', postObj.paymentType);
		formData.append('total_paid', postObj.totalAmount);

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

	getItem(data) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/get_receipt_transaction/' + data.id;
		return instance().get(api)
			.then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
	
	getReceiptNO(receiptNO) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/search_receipt_transaction';
		const formData = new FormData();
		formData.append('receipt_no', receiptNO);

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
	
	getFilterList() {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/get_filter_list';
		return instance().get(api)
			.then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}

	getReceiptData(receiptID) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/get_accounting_receipt';
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
	
	saveReceiptPayment(postObj,$counter_entry) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/save_receipt_payment';
		const formData = new FormData();
		formData.append('type', postObj.type);
		formData.append('amount', postObj.amount);
		formData.append('receiptNo', postObj.receiptNO);
		formData.append('profileId', postObj.profileId);
		formData.append('method', JSON.stringify(postObj.method));
		formData.append('counter_entry', JSON.stringify($counter_entry));
		formData.append('id', postObj.id);
		formData.append('total_paid', postObj.totalAmount);
		formData.append('description', postObj.description);

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