import ExceptionHandling from './ExceptionHandling.js'
import { useStore, ActionTypes } from "../store";
import instance from './index';

export default class BranchService {

	getItems(keyword,start) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/store_list';
		const formData = new FormData();
		formData.append('keyword', keyword);
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

	saveItem(postObj,StoreName,code,address) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/add_store';
		const formData = new FormData();
		formData.append('name', StoreName);
		formData.append('code', code);
		formData.append('address', address);
		formData.append('description', postObj.description);
		formData.append('licenseNo', postObj.licenseNo);
		formData.append('email', postObj.email);
		formData.append('contact', postObj.contact);
		formData.append('status', postObj.status);
		formData.append('show_1', postObj.show1);
		formData.append('tax_name_1', postObj.taxName1);
		formData.append('tax_value_1', postObj.taxValue1);
		formData.append('required_optional_1', postObj.requiredOptional1);
		formData.append('link1', postObj.link1);
		formData.append('show_2', postObj.show2);
		formData.append('tax_name_2', postObj.taxName2);
		formData.append('tax_value_2', postObj.taxValue2);
		formData.append('required_optional_2', postObj.requiredOptional2);
		formData.append('link2', postObj.link2);
		formData.append('show_3', postObj.show3);
		formData.append('tax_name_3', postObj.taxName3);
		formData.append('tax_value_3', postObj.taxValue3);
		formData.append('required_optional_3', postObj.requiredOptional3);
		formData.append('link3', postObj.link3);
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

	updateItem(postObj,StoreName,code,address) {
		//SHOW LOADING
		const store = useStore();
		store.dispatch(ActionTypes.PROGRESS_BAR, true);
		const api = '/api/edit_store';
		const formData = new FormData();
		formData.append('id', postObj.id);
		formData.append('name', StoreName);
		formData.append('code', code);
		formData.append('address', address);
		formData.append('description', postObj.description);
		formData.append('license_no', postObj.licenseNo);
		formData.append('email', postObj.email);
		formData.append('contact', postObj.contact);
		formData.append('show_1', postObj.show1);
		formData.append('tax_name_1', postObj.taxName1);
		formData.append('tax_value_1', postObj.taxValue1);
		formData.append('required_optional_1', postObj.requiredOptional1);
		formData.append('link1', postObj.link1);
		formData.append('show_2', postObj.show2);
		formData.append('tax_name_2', postObj.taxName2);
		formData.append('tax_value_2', postObj.taxValue2);
		formData.append('required_optional_2', postObj.requiredOptional2);
		formData.append('link2', postObj.link2);
		formData.append('show_3', postObj.show3);
		formData.append('tax_name_3', postObj.taxName3);
		formData.append('tax_value_3', postObj.taxValue3);
		formData.append('required_optional_3', postObj.requiredOptional3);
		formData.append('link3', postObj.link3);

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
		const api = '/api/get_store/' + data.id;
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
		const api = '/api/delete_store';
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