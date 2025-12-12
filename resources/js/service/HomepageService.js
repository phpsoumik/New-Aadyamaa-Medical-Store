import ExceptionHandling from './ExceptionHandling.js'
import { useStore, ActionTypes } from "../store";
import instance from './index';

export default class HomepageService {

	getBaseURL() {
		return '';
	}

	getHompageDetails(branchID) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/dashboard_information';
		const formData = new FormData();
		formData.append('storeID',branchID);
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
	
	getCurrency(branchID) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/get_currency';
		return instance()(
			{
				method: 'get',
				url: api
			}
		).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			})
	}
}