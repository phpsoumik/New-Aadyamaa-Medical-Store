import { createApp } from 'vue';
import  App  from './App.vue';
import 'primevue/resources/themes/bootstrap4-light-blue/theme.css';
import 'primevue/resources/primevue.min.css';
import 'primeflex/primeflex.css';
import 'primeicons/primeicons.css';
import 'bootstrap'
import 'bootstrap/dist/css/bootstrap.min.css'
import '@/assets/css/style.css';
import PrimeVue from 'primevue/config';
import router from './router'
import Toolbar from "primevue/toolbar";
import DataTable from 'primevue/datatable';
import Column from "primevue/column";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Breadcrumb from "primevue/breadcrumb";
import InputNumber from "primevue/inputnumber";
import InputText from "primevue/inputtext";
import FileUpload from 'primevue/fileupload';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';
import RadioButton from 'primevue/radiobutton';
import Toast, { PluginOptions } from "vue-toastification";
import "vue-toastification/dist/index.css";
import ProgressBar from 'primevue/progressbar';
import instance from './service/index';
import InputMask from 'primevue/inputmask';
import Password from 'primevue/password';
import Chart from 'primevue/chart';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Listbox from 'primevue/listbox';
import InputSwitch from 'primevue/inputswitch';
import Checkbox from 'primevue/checkbox';
import Splitter from 'primevue/splitter';
import SplitterPanel from 'primevue/splitterpanel';
import Textarea from 'primevue/textarea';
import Menubar from 'primevue/menubar';
import titleMixin from './helpers/titleMixin'
import Calendar from 'primevue/calendar';
import VueFullscreen from 'vue-fullscreen';
import Sidebar from 'primevue/sidebar';
import Divider from 'primevue/divider';
import Panel from 'primevue/panel';
import Card from 'primevue/card';
import VueBarcode from '@chenfengyuan/vue-barcode';
import Vuelidate from 'vuelidate';




globalThis.__VUE_OPTIONS_API__ = true;
globalThis.__VUE_PROD_DEVTOOLS__ = false;


const app = createApp(App);
app.use(router);
app.use(VueFullscreen);
app.mixin(titleMixin)
app.use(instance);
const options: PluginOptions = {
    timeout: 3000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: "button",
    icon: true,
    rtl: true
};

app.use(Toast, options);
app.use(Vuelidate);
app.use(PrimeVue,{ripple: true});
app.component('Toast',Toast)
app.component('Panel',Panel)
app.component('Card',Card)
app.component('VueBarcode',VueBarcode);
app.component('Menubar',Menubar)
app.component('Textarea',Textarea)
app.component('Toolbar',Toolbar)
app.component('DataTable',DataTable)
app.component('InputSwitch',InputSwitch)
app.component('Column',Column)
app.component('Dialog',Dialog)
app.component('InputNumber',InputNumber)
app.component('Listbox',Listbox)
app.component('InputText',InputText)
app.component('Button',Button)
app.component('Sidebar',Sidebar)
app.component('Breadcrumb',Breadcrumb)
app.component('FileUpload',FileUpload)
app.component('Dropdown',Dropdown)
app.component('MultiSelect',MultiSelect)
app.component('RadioButton',RadioButton)
app.component('ProgressBar',ProgressBar)
app.component('InputMask',InputMask)
app.component('Password',Password)
app.component('TabView',TabView)
app.component('TabPanel',TabPanel)
app.component('Chart',Chart)
app.component('Checkbox',Checkbox)
app.component('Calendar',Calendar)
app.component('SplitterPanel',SplitterPanel)
app.component('Splitter',Splitter)
app.component('Divider',Divider)
app.mount('#app')
