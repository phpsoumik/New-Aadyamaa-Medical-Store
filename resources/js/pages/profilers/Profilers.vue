<template>
  <section>
    <div class="app-container">
      <Toolbar>
        <template #start>
          <Breadcrumb :home="home" :model="items" class="p-menuitem-text" />
        </template>

        <template #end>
          <div class="p-mx-2">
            <div class="p-inputgroup">
              <InputText v-model.trim="keyword" placeholder="Contact No" />
              <Button
                icon="pi pi-search "
                class="p-button-primary p-mr-1"
                @click="loadSearchData"
              />
            </div>
          </div>
          <div class="p-mx-2">
            <Button
              icon="pi pi-plus"
              class="p-button-success"
              @click="openDialog"
            />
          </div>
        </template>
      </Toolbar>
      <div class="p-mt-2">
        <DataTable
          v-model:first.sync="goToFirstLink"
          :value="lists"
          :lazy="true"
          :paginator="checkPagination"
          :rows="limit"
          :totalRecords="totalRecords"
          :scrollable="true"
          @page="onPage($event)"
          class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
        >
          <template #empty>
            <div class="p-text-center p-p-3">No records found</div>
          </template>
          <Column field="account_title" header="Account Title"></Column>
          <Column field="email_address" header="Email Address"></Column>
          <Column field="contact_no" header="Contact No"></Column>
          <Column field="national_id" header="National ID"></Column>
          <Column field="address" header="Address"></Column>
          <Column field="account_type" header="Account Type"></Column>
          <Column field="description" header="Description"></Column>
          <Column :exportable="false" header="Action">
            <template #body="slotProps">
              <Button
                icon="pi pi-pencil"
                class="p-button-rounded p-button-primary p-mr-2"
                @click="editIem(slotProps.data)"
              />
              <Button
                icon="pi pi-trash"
                class="p-button-rounded p-button-danger"
                @click="confirmChangeStatus(slotProps.data)"
              />
            </template>
          </Column>
        </DataTable>
      </div>
      <Dialog
        v-model:visible="statusDialog"
        :style="{ width: '450px' }"
        header="Confirm"
      >
        <div class="confirmation-content">
          <i
            class="pi pi-exclamation-triangle p-mr-3"
            style="font-size: 2rem"
          />
          <span
            >Are you sure to delete <b>{{ item.accountTitle }}</b> ?</span
          >
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-success"
            @click="statusDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            class="p-button-danger"
            @click="changeStatus"
          />
        </template>
      </Dialog>
      <ProfilerDialog
        :profilerDetail="{
          status: this.profileStatus,
          profilerID: this.profilerID,
          statusType: this.statusType,
          dialogTitle: this.dialogTitle,
          currentUserID: this.currentUserID,
        }"
        v-on:updateProfilerStatus="updateProfilerStatus"
      />
    </div>
  </section>
</template>
<script lang="ts">
import { Options, Vue } from "vue-class-component";
import ProfilerService from "../../service/ProfilerService.js";
import Toaster from "../../helpers/Toaster";
import ProfilerDialog from "../../components/ProfilerDialog.vue";

@Options({
  title: 'Profilers',
  components: {
    ProfilerDialog,
  },
})
export default class Profilers extends Vue {
  private lists = [];
  private keyword = "";
  private dialogTitle = "";
  private statusType = "New";
  private toast;
  private profilerID = 0;
  private currentUserID = 0;
  private goToFirstLink = 0;
  private profilerService;
  private checkPagination = true;
  private profileStatus = false;
  private statusDialog = false;
  private totalRecords = 0;
  private limit = 0;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Initialization", to: "initialization" },
    { label: "Profilers", to: "profilers" },
  ];

  private item = {
    id: 0,
    accountTitle: "",
    status: "Active",
  };

  //CALLING WHEN PAGINATION BUTTON CLICKS
  onPage(event) {
    this.loadList(event.first);
  }

  //DEFAULT METHOD OF TYPE SCRIPT
  created() {
    this.profilerService = new ProfilerService();
    this.toast = new Toaster();
  }

  //CALLNING AFTER CONSTRUCTOR GET CALLED
  mounted() {
    this.loadList(0);
  }

  //OPEN DIALOG BOX FOR CONFIRMATION
  confirmChangeStatus(data) {
    this.item.id = data.id;
    this.item.accountTitle = data.account_title;
    this.statusDialog = true;
  }

  //CHANGE THE STATUS AND SEND HTTP TO SERVER
  changeStatus() {
    this.statusDialog = false;
    this.item.status = "Delete";
    this.profilerService.changeStatus(this.item).then((res) => {
      this.loadList(0);
      //SHOW NOTIFICATION
      this.toast.handleResponse(res);
    });
  }

  //FETCH THE DATA FROM SERVER
  loadList(page) {
    this.profilerService.getItems(this.keyword, page).then((data) => {
      this.lists = data.records;
      this.totalRecords = data.totalRecords;
      this.limit = data.limit;
      this.currentUserID = data.currentUserID;
    });
  }

  loadSearchData() {
    if (this.keyword) {
      this.goToFirstLink = 0;
      this.loadList(0);
    }
  }

  //OPEN DIALOG TO ADD NEW ITEM
  openDialog() {
    this.dialogTitle = "Add New Profile";
    this.profileStatus = true;
    this.statusType = "New";
  }

  editIem(data) {
    this.statusType = "Update";
    this.profilerID = data.id;
    this.dialogTitle = "Update Profile";
    this.profileStatus = true;
    this.statusType = "Update";
  }

  updateProfilerStatus(res) {
    this.profileStatus = false;

    if (res[0] == "load") {
      this.loadList(this.goToFirstLink);
    }
  }
}
</script>