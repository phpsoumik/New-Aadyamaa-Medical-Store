
import Toaster from '../helpers/Toaster'

export default class ExceptionHandling {

	static HandleErrors(error) {

		const toaster = new Toaster();

		toaster.showError(error.data.message +' status code '+ error.status);
	}
}