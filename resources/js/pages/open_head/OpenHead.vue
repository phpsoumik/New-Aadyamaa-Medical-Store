<template>
  <section>
    <div class="app-container">
      <Toolbar>
        <template #start>
          <Breadcrumb :home="home" :model="items" class="p-menuitem-text" />
        </template>

        <template #end>
          <div class="p-mx-2">
            <Button
              icon="pi pi-check-circle"
              class="p-button-success"
              label="Save"
              @click.prevent="saveItem(!v$.$invalid)"
              :disabled="getTheTotalDiff != 0"
            />
          </div>
        </template>
      </Toolbar>
      <div class="card p-my-1 p-p-2">
        <h5>Open Account Heads</h5>
        <div class="p-fluid p-formgrid p-grid">
          <div class="p-field p-col">
            <label
              for="description"
              :class="{ 'p-error': v$.description.$invalid && submitted }"
              >Description</label
            >
            <InputText
              id="description"
              v-model="v$.description.$model"
              :class="{ 'p-invalid': v$.description.$invalid && submitted }"
            />
            <span v-if="v$.description.$error && submitted">
              <span
                id="p-error"
                v-for="(error, index) of v$.description.$errors"
                :key="index"
              >
                <small class="p-error">{{ error.$message }}</small>
              </span>
            </span>
            <small
              v-else-if="
                (v$.description.$invalid && submitted) ||
                v$.description.$pending.$response
              "
              class="p-error"
              >{{
                v$.description.required.$message.replace("Value", "Description")
              }}</small
            >
          </div>
        </div>
      </div>
      <DataTable
        :value="item.itemList"
        :scrollable="true"
        class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
      >
        <template #empty>
          <div class="p-text-center p-p-3">No records found</div>
        </template>
        <Column field="accountHead" header="Account Name"></Column>
        <Column field="accountNature" header="Account Nature"></Column>
        <Column header="DEBIT" style="width: 20%">
          <template #body="slotProps">
            <InputNumber
              mode="decimal"
              :useGrouping="false"
              :maxFractionDigits="2"
              :minFractionDigits="2"
              v-model="slotProps.data.debitAmount"
              :disabled="slotProps.data.creditAmount > 0"
              class="p-p-1"
            />
          </template>
        </Column>
        <Column header="CREDIT" style="width: 20%">
          <template #body="slotProps">
            <InputNumber
              mode="decimal"
              :useGrouping="false"
              :maxFractionDigits="2"
              :minFractionDigits="2"
              v-model="slotProps.data.creditAmount"
              :disabled="slotProps.data.debitAmount > 0"
              class="p-p-1"
            />
          </template>
        </Column>
      </DataTable>
      <h4 class="p-text-center p-p-2 p-mt-3 total-balances">
        <b>
          TOTAL DEBIT :
          <span class="highlight-green">
           {{currency}} {{ fixDigits(totalDebitAmount) }}</span
          >
          TOTAL CREDIT :
          <span class="highlight-green">
           {{currency}} {{ fixDigits(totalCreditAmount) }}</span
          >
          DIFF :
          <span class="highlight"> {{currency}} {{ fixDigits(getTheTotalDiff) }} </span>
        </b>
      </h4>
    </div>
  </section>
</template>
<script lang="ts">
import { Options, Vue } from "vue-class-component";
import OpenHeadService from "../../service/OpenHeadService.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { required } from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";
import moment from "moment";
import { useStore, ActionTypes } from "../../store";

@Options({
  title: 'Open Head',
  components: {},
})
export default class OpenHead extends Vue {

  private store = useStore();
  private toast;
  private headService;
  private submitted = false;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Initialization", to: "initialization" },
    { label: "Open Account Heads", to: "open-head" },
  ];

  private item = {
    id: 0,
    itemList: [
      {
        accountID: 0,
        accountHead: "",
        accountNature: "",
        debitAmount: 0,
        creditAmount: 0,
      },
    ],
  };

  private state = reactive({
    description: "",
  });

  private validationRules = {
    description: {
      required,
    },
  };

  private v$ = useVuelidate(this.validationRules, this.state);

  //DEFAULT METHOD OF TYPE SCRIPT
  created() {
    this.headService = new OpenHeadService();
    this.toast = new Toaster();
  }

  //CALLNING AFTER CONSTRUCTOR GET CALLED
  mounted() {
    this.loadList();
  }

  //ADD OR UPDATE THE ITEM VIA HTTP
  saveItem(isFormValid) {
    this.submitted = true;
    if (isFormValid) {
      this.headService.save(this.item, this.state).then((res) => {
        //SHOW NOTIFICATION
        this.toast.handleResponse(res);
      });
    }
  }

  //FETCH THE DATA FROM SERVER
  loadList() {
    this.headService.getChartHeadList(this.item, this.state).then((data) => {
      const accounts = data.records;

      this.item.itemList = [];

      accounts.map((c) => {
        this.item.itemList.push({
          accountID: Number(c.id),
          accountHead: c.account_name,
          accountNature: c.account_nature,
          debitAmount: 0,
          creditAmount: 0,
        });
      });

      if (data.stored.length > 0) {
        const storedHead = data.stored[0].sub_transaction;
        this.state.description = data.stored[0].narration;

        this.item.itemList.map((c) => {
          storedHead.map((s) => {
            if (c.accountID == Number(s.account_id)) {
              if (s.type == "Debit") {
                c.debitAmount = Number(s.amount);
                c.creditAmount = 0;
              } else {
                c.debitAmount = 0;
                c.creditAmount = Number(s.amount);
              }
            }
          });
        });
      }
    });
  }

  fixDigits(amt) {
    return Number(amt).toFixed(2);
  }

  get totalDebitAmount() {
    let totalAmount = 0;
    this.item.itemList.forEach((v) => {
      totalAmount = totalAmount + Number(v.debitAmount);
    });
    return totalAmount;
  }

  get totalCreditAmount() {
    let totalAmount = 0;
    this.item.itemList.forEach((v) => {
      totalAmount = totalAmount + Number(v.creditAmount);
    });
    return totalAmount;
  }

  get getTheTotalDiff() {
    return this.totalDebitAmount - this.totalCreditAmount;
  }

  get currency() {
    return this.store.getters.getCurrency;
  }
}
</script>

<style scoped>
.total-balances {
  background-color: #eee;
  border: 1px solid #ccc;
}

.highlight-green {
  color: green;
}

.highlight {
  color: #c00;
}
</style>