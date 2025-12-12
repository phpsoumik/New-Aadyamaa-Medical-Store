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
                <h5>User Activity Report</h5>
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
                    <Column field="createdAt" header="Date/Time"></Column>
                    <Column
                        field="userDetails.name"
                        header="User Name"
                    ></Column>
                    <Column field="userRole" header="User Role"></Column>
                    <Column field="activityAction" header="Action"></Column>
                    <Column field="branchDetails.name" header="Branch"></Column>
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
                <div class="p-field">
                    <label for="filterStore">Filter Date</label>
                    <Dropdown
                        v-model="searchFilters.filterType"
                        :options="filterDates"
                        :filter="true"
                        optionLabel="key"
                        optionValue="value"
                    />
                </div>
                <h5>OR</h5>
                <div class="p-grid">
                    <div class="p-col">
                        <div class="p-field">
                            <label for="from">Date From</label>
                            <input
                                type="date"
                                id="from"
                                v-model="searchFilters.date1"
                                class="form-control"
                            />
                        </div>
                    </div>
                    <div class="p-col">
                        <div class="p-field">
                            <label for="to">Date To</label>
                            <input
                                type="date"
                                id="to"
                                v-model="searchFilters.date2"
                                class="form-control"
                            />
                        </div>
                    </div>
                </div>
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

interface IUser {
    name: string;
}

interface IReport {
    activityAction: string;
    userRole: string;
    createdAt: string;
    userDetails: IUser;
    branchDetails: IBranch;
}

@Options({
    title: 'Activity Report',
    components: {},
})
export default class ActivityReport extends mixins(UtilityOptions) {
    private dt = ref();
    private lists: IReport[] = [];
    private reportService;
    private resultTitle = "";
    private productDialog = false;
    private loading = false;
    private home = { icon: "pi pi-home", to: "/" };
    private items = [
        { label: "Reports", to: "reports" },
        { label: "Activity Report", to: "activity-report" },
    ];

    private searchFilters = {
        id: "",
        date1: "",
        date2: "",
        filterType: "None",
        storeID: 0,
    };

    private filterDates = [];
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
            this.filterDates = res.datesList;
        });
    }

    // USED TO GET SEARCHED ASSOCIATE
    loadList() {
        this.loading = true;
        this.reportService
            .userActivityReport(this.searchFilters)
            .then((res) => {
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
            e.createdAt = this.formatDateTime(e.createdAt);
            l.push(e);
        });

        return l;
    }
}
</script>
