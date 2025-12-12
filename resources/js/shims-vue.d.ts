/* eslint-disable */

declare module '*.vue' {
  import { defineComponent } from 'vue'
  import VueToast from 'vue-toast-notification';
  const component: ReturnType<typeof defineComponent>
  export default component
}
