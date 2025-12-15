import { useToast } from 'vue-toastification'

export default class Toaster  {

    handleResponse(res) {
        if (res.alert == 'info' || res.alert == 'success') {
            this.showSuccess(res.msg)
        }
        else {
            this.showError(res.msg);
        }
    }

    showSuccess(payload) {
        const toast = useToast();
        toast.success(payload);
    }

    showError(payload) {
        const toast = useToast();
        toast.error(payload);
    }

    showWarning(payload) {
        const toast = useToast();
        toast.warning(payload);
    }

    showInfo(payload) {
        const toast = useToast();
        toast.info(payload);
    }
}