import ExceptionHandling from './ExceptionHandling.js'
import { useStore, ActionTypes } from "../store";
import instance from './index';

export default class VoucherService {

	getItems(postObj,start) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/voucher_list';
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

	save(postObj,stateObj) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/add_voucher';
		const formData = new FormData();
		formData.append('voucher_no', postObj.voucherNo);
		formData.append('profile_id', postObj.profileID);
		formData.append('profile_name', stateObj.selectedProfile);
		formData.append('voucher_date', stateObj.voucherDate);
		formData.append('memo', stateObj.description);
		formData.append('total_amount', postObj.totalAmount);
		formData.append('type', postObj.type);
		formData.append('item_list', JSON.stringify(stateObj.itemList));
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

	update(postObj,stateObj) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/edit_voucher';
		const formData = new FormData();
		formData.append('id', postObj.id);
		formData.append('voucher_no', postObj.voucherNo);
		formData.append('profile_id', postObj.profileID);
		formData.append('profile_name', stateObj.selectedProfile);
		formData.append('voucher_date', stateObj.voucherDate);
		formData.append('memo', stateObj.description);
		formData.append('total_amount', postObj.totalAmount);
		formData.append('type', postObj.type);
		formData.append('item_list', JSON.stringify(stateObj.itemList));
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

	getItem(data) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/get_voucher/' + data.id;
		return instance().get(api)
			.then(res => res.data)
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
		const api = '/api/get_voucher_receipt';
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