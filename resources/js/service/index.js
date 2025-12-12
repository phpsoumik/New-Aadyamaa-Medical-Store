import axios from 'axios';
import router from "../router";
import Toaster from '../helpers/Toaster'

export default () => {

    const token = localStorage.getItem("store_token");
    if (token) {
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    }

   // axios.defaults.baseURL = 'https://medixv4.stitchit.cloud/server/';

    // axios.defaults.withCredentials = true;
    const options = {};
   // alert(laravel.baseURL);
    //options.baseURL = config.SERVER_URL;
    //options.baseURL = 'http://localhost/pos?_t=' + new Date().getTime();
    options.timeout = 60000;
    options.params = {}; // do not remove this, its added to add params later in the config
    
    //console.log(options.baseURL);

    const instance = axios.create(options);

    instance.interceptors.request.use(
        config => {
            return config;
        },
        error => {
            const toaster = new Toaster()
            toaster.showError('Request Failed! Please try to restart the api server.');
            return Promise.reject(error.request);
        }
    );


    //on successful response
    instance.interceptors.response.use(
        response => {
            if (response.status === 200 || response.status === 201) {
                return Promise.resolve(response);
            } else {
                return Promise.reject(response);
            }
        },
        error => {
            if (error.response.status) {
                const toaster = new Toaster();

                switch (error.response.status) {
                    case 400:
                        toaster.showError('Bad Request! Server is rejected the request.');
                        break;
                    case 401:
                        toaster.showError('Unauthorized! Session expired Please try to login again.');
                        break;
                    case 403:
                        toaster.showError('Forbidden! Invalid access.');
                        router.replace({ path: "/login", params: {} });
                        break;
                    case 404:
                        toaster.showError('Not Found! Page not found on server');
                        break;
                    case 502:
                        toaster.showError('Bad gateway! Got an invalid response');
                        router.replace({ path: "/", params: {} });
                }
                return Promise.reject(error.response);
            }
            else {
                const toaster = new Toaster();
                toaster.showError('Got an invalid response from server');
            }
        }
    );

    return instance;
}