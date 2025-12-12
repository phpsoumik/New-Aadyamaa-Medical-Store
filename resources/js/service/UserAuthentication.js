import instance from './index';
import ExceptionHandling from './ExceptionHandling.js'
import { useStore, ActionTypes } from "../store";
import Toaster from '../helpers/Toaster';

export default class UserAuthentication {

	loginUser(email, password, deviceName) {

		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/login';
		const formData = new FormData();
		formData.append('email', email);
		formData.append('password', password);
		formData.append('deviceName', deviceName);
		return instance()(
			{
				method: 'post',
				url: api,
				data: formData,
			}
		).then(res => res.data)
			.catch((e) => {
				const t = new Toaster();
				t.showError(e.data.errors.email[0]);
				ExceptionHandling.HandleErrors(e);
			})
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			});

	}
	
	installApp(obj) {

		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/install_app';
		const formData = new FormData();
		
		formData.append('storeName', obj.storeName);
		formData.append('storeCode', obj.storeCode);
		formData.append('address', obj.address);
		formData.append('userName', obj.userName);
		formData.append('userEmail', obj.userEmail);
		formData.append('password', obj.password);
		formData.append('purchaseCode', obj.purchaseCode);
		
		return instance()(
			{
				method: 'post',
				url: api,
				data: formData,
			}
		).then(res => res.data)
		.catch((e) => {
			ExceptionHandling.HandleErrors(e);
		})
		.finally(() => {
			store.dispatch(ActionTypes.PROGRESS_BAR, false);
		});
	}
	
	checkNewInstallation() {

		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/check_new_installation';
		
		return instance()(
			{
				method: 'get',
				url: api
			}
		).then(res => res.data)
			.catch((e) => {
				ExceptionHandling.HandleErrors(e);
			})
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			});

	}

	logoutUser() {
		const api = 'login/sign_out';
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		//sign_out
		return instance().get(api).then(res => res.data)
			.catch((e) => ExceptionHandling.HandleErrors(e))
			.finally(() => {
				store.dispatch(ActionTypes.PROGRESS_BAR, false);
			});
	}
}