<template>
  <footer class="footer">
    <div class="p-grid p-p-0 p-mx-2">
      <div class="p-col-6 text-right">{{ copyRight }}</div>
      <div class="p-col-6 text-right">{{ userName }} | {{ branchName }}</div>
    </div>
  </footer>
</template>

<script lang="ts">
import { Vue } from "vue-class-component";
import footerService from "../service/footerService";

export default class Footer extends Vue {
  private userName = "";
  private branchName = "";
  private copyRight = "";
  private footerService;

  created() {
    this.footerService = new footerService();
  }

  mounted() {
    this.loadList();
  }

  loadList() {
    this.footerService.branchDetail().then((data) => {
      this.userName = data.userName;
      this.branchName = data.branchName;
      this.copyRight = data.copyright;
    });
  }
}
</script>

<style scoped>
.footer {
  width: 100%;
  line-height: 15px;
  background-color: #004c97;
  color: #fff;
}
</style>
