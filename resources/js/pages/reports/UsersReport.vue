<template>
    <section>
        <div class="app-container">
            <Toolbar>
                <template #start>
                    <Breadcrumb
                        :home="home"
                        :model="items"
                        class="p-menuitem-text p-p-1"
                    />
                </template>

                <template #end>
                    <div class="p-mx-2">
                        <Button
                            icon="pi pi-search"
                            class="p-button-primary px-4"
                            @click="openDialog"
                        />
                    </div>
                    <div class="">
                        <Button
                            icon="pi pi-file-excel"
                            class="p-button-success px-4"
                            @click="dt.exportCSV()"
                        />
                    </div>
                </template>
            </Toolbar>
            <div class="m-2 mt-4 mb-4 p-text-center">
                <h5>User Report</h5>
                <p>{{ resultTitle }}</p>
            </div>
            <div class="p-mt-2">
                <DataTable
                    :value="rList"
                    :lazy="true"
                    class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
                    :resizableColumns="true"
                    :loading="loading"
                    :autoLayout="true"
                    scrollHeight="70vh"
                    responsiveLayout="scroll"
                    :scrollable="true"
                    ref="dt"
                >
                    <template #empty>
                        <div class="p-text-center p-p-3">No records found</div>
                    </template>
                    <Column field="name" header="Name"></Column>
                    <Column field="roleName" header="Role"></Column>
                    <Column field="email" header="Email Address"></Column>
                    <Column field="description" header="Description"></Column>
                    <Column field="contact" header="Contact"></Column>
                    <Column field="address" header="Address"></Column>
                    <Column field="branchName" header="Branch"></Column>
                </DataTable>
            </div>

            <Dialog
                v-model:visible="productDialog"
                :style="{ width: '50vw' }"
                :maximizable="true"
                position="top"
                class="p-fluid"
            >
                <template #header>
                    <h5 class="p-dialog-titlebar p-dialog-titlebar-icon">
                        <i class="pi pi-search" style="font-size: 1.2rem"></i>
                        {{ dialogTitle }}
                    </h5>
                </template>
                <div class="p-grid">
                    <div class="p-col">
                        <div class="p-field">
                            <label for="filterStore">Branch</label>
                            <Dropdown
                                v-model="searchFilters.storeID"
                                :options="filterBranch"
                                :filter="true"
                                optionLabel="name"
                                optionValue="id"
                            />
                        </div>
                    </div>
                </div>
                <template #footer>
                    <Button
                        type="submit"
                        label="Search"
                        icon="pi pi-search"
                        class="p-button-primary"
                        @click="loadList"
                    />
                </template>
            </Dialog>
        </div>
    </section>
</template>
<script lang="ts">
import { Options, mixins } from "vue-class-component";
import { ref } from "vue";
import StoreReports from "../../service/StoreReports";
import UtilityOptions from "../../mixins/UtilityOptions";

interface IBranch {
    name: string;
}

interface IReport {
    firstName: string;
    patientNo: string;
    patientType: string;
    gender: string;
    dob: string;
    phoneNo: string;
    branchDetails: IBranch;
}

@Options({
    title: 'User Report',
    components: {},
})
export default class UserReport extends mixins(UtilityOptions) {
    private dt = ref();
    private lists: IReport[] = [];
    private reportService;
    private resultTitle = "";
    private productDialog = false;
    private loading = false;
    private home = { icon: "pi pi-home", to: "/" };
    private items = [
        { label: "Reports", to: "reports" },
        { label: "User Report", to: "user-report"  },
    ];

    private searchFilters = {
        id: "",
        storeID: 0,
    };
    private dialogTitle;
    private submitted = false;
    private filterBranch = [];

    //CALLING WHENEVER COMPONENT LOADS
    created() {
        this.reportService = new StoreReports();
    }

    //CALLNING AFTER CONSTRUCTOR GET CALLED
    mounted() {
        this.storeList();
        this.loadList();
    }

    //OPEN DIALOG TO ADD NEW ITEM
    openDialog() {
        this.submitted = false;
        this.dialogTitle = "Filter Report";
        this.productDialog = true;
    }

    storeList() {
        this.reportService.getFilterList().then((res) => {
            this.filterBranch = res.stores;
        });
    }

    // USED TO GET SEARCHED ASSOCIATE
    loadList() {
        this.loading = true;
        this.reportService.userReport(this.searchFilters).then((res) => {
            const data = this.camelizeKeys(res);
            this.resultTitle = data.resultTitle;
            this.lists = data.record;
            this.loading = false;
        });
        this.productDialog = false;
    }

    get rList() {
        const l: IReport[] = [];

        this.lists.forEach((e) => {
            e.dob = this.formatDate(e.dob);
            l.push(e);
        });

        return l;
    }
}
</script>
