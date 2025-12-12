import ExceptionHandling from './ExceptionHandling.js'
import { useStore, ActionTypes } from "../store";
import instance from './index';

export default class BalanceService {

	getItems(postObj,start) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/balances_list';
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
		const api = '/api/add_balance';
		const formData = new FormData();
		formData.append('profile_id', postObj.profileID);
		formData.append('profile_name', stateObj.selectedProfile);
		formData.append('balance_date', stateObj.balanceDate);
		formData.append('memo', stateObj.description);
		formData.append('total_amount', stateObj.amount);
		formData.append('account_type', stateObj.account);
		formData.append('type', postObj.type);
		formData.append('item_list', JSON.stringify(postObj.itemList));
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
		const api = '/api/edit_balance';
		const formData = new FormData();
		formData.append('id', postObj.id);
		formData.append('profile_id', postObj.profileID);
		formData.append('profile_name', stateObj.selectedProfile);
		formData.append('balance_date', stateObj.balanceDate);
		formData.append('memo', stateObj.description);
		formData.append('total_amount', stateObj.amount);
		formData.append('account_type', stateObj.account);
		formData.append('type', postObj.type);
		formData.append('item_list', JSON.stringify(postObj.itemList));

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
		const api = '/api/get_balance/' + data.id;
		return instance().get(api)
			.then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
}