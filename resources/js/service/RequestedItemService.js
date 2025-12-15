import ExceptionHandling from './ExceptionHandling.js'
import { useStore, ActionTypes } from "../store";
import instance from './index';

export default class RequestedItemService {

	getItems(storeID,keyword,start) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/requested_items_list';
		const formData = new FormData();
		formData.append('keyword', keyword);
		formData.append('start', start);
		formData.append('storeID', storeID);
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

	saveItem(postObj) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/add_requested_items';
		const formData = new FormData();
		formData.append('customer_id', postObj.customer_id || '');
		formData.append('medicine_name', postObj.medicine_name);
		formData.append('quantity', postObj.quantity);
		formData.append('order_date', postObj.order_date);
		formData.append('advance_payment', postObj.advance_payment || 0);
		formData.append('has_advance', postObj.has_advance ? '1' : '0');
		formData.append('status', postObj.status);
		formData.append('branch_id', postObj.branch_id);
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

	updateItem(postObj) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/edit_requested_items';
		const formData = new FormData();
		formData.append('id', postObj.id);
		formData.append('customer_id', postObj.customer_id || '');
		formData.append('medicine_name', postObj.medicine_name);
		formData.append('quantity', postObj.quantity);
		formData.append('order_date', postObj.order_date);
		formData.append('advance_payment', postObj.advance_payment || 0);
		formData.append('has_advance', postObj.has_advance ? '1' : '0');

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
		const api = '/api/get_requested_items/' + data.id;
		return instance().get(api)
			.then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}

	changeStatus(postObj) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/delete_requested_items';
		const formData = new FormData();
		formData.append('id', postObj.id);
		formData.append('status', postObj.status);

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

	// Search medicine
	searchMedicine(query) {
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/search_medicine';
		const formData = new FormData();
		formData.append('query', query);
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

	// Get customer phone
	getCustomerPhone(customerId) {
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/get_customer_phone';
		const formData = new FormData();
		formData.append('customer_id', customerId);
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

	// Store new customer
	storeCustomer(customerData) {
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/store_customer';
		const formData = new FormData();
		formData.append('name', customerData.name);
		formData.append('phone', customerData.phone);
		formData.append('email', customerData.email || '');
		formData.append('address', customerData.address || '');
		formData.append('status', customerData.status || 'Active');
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