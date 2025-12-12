import ExceptionHandling from './ExceptionHandling.js'
import { useStore, ActionTypes } from "../store";
import instance from './index';

export default class BankService {

	getItems(storeID,start) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/bank_list';
		const formData = new FormData();
		formData.append('storeID', storeID);
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

	saveItem(postObj,stateObj, currentStoreID) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/add_bank';
		const formData = new FormData();
		formData.append('bank', stateObj.bankName);
		formData.append('branch', stateObj.branchName);
		formData.append('code', stateObj.code);
		formData.append('title', stateObj.title);
		formData.append('number', stateObj.accountNumber);
		formData.append('type', stateObj.type.key);
		formData.append('ending_date', postObj.endingDate);
		formData.append('balance', postObj.balance);
		formData.append('branch_id', currentStoreID);
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

	updateItem(postObj,stateObj) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/edit_bank';
		const formData = new FormData();
		formData.append('id', postObj.id);
		formData.append('bank', stateObj.bankName);
		formData.append('branch', stateObj.branchName);
		formData.append('code', stateObj.code);
		formData.append('title', stateObj.title);
		formData.append('number', stateObj.accountNumber);
		formData.append('type', stateObj.type.key);
		formData.append('ending_date', postObj.endingDate);
		formData.append('balance', postObj.balance);

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
		const api = '/api/get_bank/' + data.id;
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
		const api = '/api/delete_bank/';
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

}