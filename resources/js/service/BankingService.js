import ExceptionHandling from './ExceptionHandling.js'
import { useStore, ActionTypes } from "../store";
import instance from './index';

export default class BankingService {

	getItems(postObj,start) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/bank_transaction_list';
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

	saveItem(postObj,stateObj) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/add_bank_transaction';
		const formData = new FormData();
		formData.append('bank_id', stateObj.bankID);
		formData.append('amount', stateObj.amount);
		formData.append('receipt_date', stateObj.receiptDate);
		formData.append('description', stateObj.description);
		formData.append('receipt_no', stateObj.receiptNo);
		formData.append('type', stateObj.type.value);
		formData.append('profile_id',postObj.profileID);
		formData.append('account_head',stateObj.accountHead);
		formData.append('account_id', postObj.accountID);
		formData.append('status', postObj.status);
		formData.append('item_list', JSON.stringify(stateObj.itemList));
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
		const api = '/api/edit_bank_transaction';
		const formData = new FormData();
		formData.append('id', postObj.id);
		formData.append('bank_id', stateObj.bankID);
		formData.append('amount', stateObj.amount);
		formData.append('receipt_date', stateObj.receiptDate);
		formData.append('description', stateObj.description);
		formData.append('receipt_no', stateObj.receiptNo);
		formData.append('type', stateObj.type.value);
		formData.append('profile_id',postObj.profileID);
		formData.append('account_head',stateObj.accountHead);
		formData.append('account_id', postObj.accountID);
		formData.append('status', postObj.status);
		formData.append('item_list', JSON.stringify(stateObj.itemList));

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
		const api = '/api/get_bank_transaction/' + data.id;
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
		const api = '/api/get_bank_transaction_receipt';
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
			});
	}

	updateBankStatus(postObj) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/edit_bank_activity';
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