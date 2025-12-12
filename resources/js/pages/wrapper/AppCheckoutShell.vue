<template>
  <div id="app">
    <Header />
    <div style="margin-top:7.5vh">
      <router-view></router-view>
    </div>
  </div>
</template>
<script lang="ts">
import { Options, Vue } from "vue-class-component";
import Header from "../../components/Header.vue";
import { useStore } from "../../store";

@Options({
  components: {
    Header,
  },
})
export default class AppCheckoutShell extends Vue {
  private store = useStore();
  get progressBar() {
    return this.store.getters.getProgressBar;
  }
}
</script>
