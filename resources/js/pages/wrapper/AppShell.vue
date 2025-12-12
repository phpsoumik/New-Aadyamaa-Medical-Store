<template>
  <div id="app">
    <Header />
    <div style="margin-top:48px"> 
      <router-view></router-view>
    </div>
    <Footer />
  </div>
</template>
<script lang="ts">
import { Options, Vue } from "vue-class-component";
import Footer from "../../components/Footer.vue";
import Header from "../../components/Header.vue";

@Options({
  components: {
    Header,
    Footer,
  },
})
export default class AppShell extends Vue {
 
}
</script>
