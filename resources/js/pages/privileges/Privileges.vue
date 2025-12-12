<template>
    <section>
        <div class="app-container">
            <Toolbar>
                <template #start>
                    <Breadcrumb
                        :home="home"
                        :model="items"
                        class="p-menuitem-text"
                    />
                </template>

                <template #end>
                    <div class="p-mx-2">
                        <Dropdown
                            style="width: 15rem"
                            v-model="selectedRoleID"
                            :options="roles"
                            optionLabel="name"
                            optionValue="id"
                            @change="loadList(0)"
                        />
                    </div>
                    <div class="p-mx-2">
                        <Button
                            icon="pi pi-save"
                            class="p-button-success"
                            @click="savePermissions"
                            label="Save"
                            :disabled="selectedRoleID == 0"
                        />
                    </div>
                </template>
            </Toolbar>
            <h5 class="p-text-center top-bar">
                <i style="font-size:1.0rem" class="pi pi-check-circle"></i>
                Manage Permissions <br />
                <small>Please logout and Login to apply new Privileges</small>
            </h5>
            <div class="p-grid p-px-2">
                <div class="p-col-3 " v-for="p in permission" :key="p.id">
                    <div class="p-field-checkbox" :key="p.key">
                        <Checkbox
                            :id="p.id"
                            v-model="checkedPermission"
                            :value="p.id"
                        />
                        <label :for="p.id">{{ p.name }}</label>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script lang="ts">
import { Options, Vue } from "vue-class-component";
import PrivilegesService from "../../service/PrivilegesService.js";
import Toaster from "../../helpers/Toaster";

interface IRoles {
    id: number;
    name: string;
}

@Options({
    title: 'Privileges',
    components: {}
})
export default class Privileges extends Vue {
    private selectedRoleID = 0;
    private toast;
    private privilegesService;
    private home = { icon: "pi pi-home", to: "/" };
    private items = [
        { label: "Initialization", to: "initialization" },
        { label: "Privileges", to: "privileges" }
    ];

    private roles: IRoles[] = [];
    private permission: IRoles[] = [];
    private checkedPermission = [];

    //DEFAULT METHOD OF TYPE SCRIPT
    created() {
        this.privilegesService = new PrivilegesService();
        this.toast = new Toaster();
    }

    //CALLNING AFTER CONSTRUCTOR GET CALLED
    mounted() {
        this.loadList();
    }

    //FETCH THE DATA FROM SERVER
    loadList() {
        this.privilegesService.getItems(this.selectedRoleID).then(data => {
            this.roles = data.roles;
            this.permission = data.permission;
            this.checkedPermission = data.assignedPermission;
        });
    }

    savePermissions() {
        this.privilegesService
            .saveItem(this.selectedRoleID, this.checkedPermission)
            .then(res => {
                this.toast.handleResponse(res);
            });
    }
}
</script>

<style scoped>
.top-bar {
    background-color: #004c97;
    padding: 10px 0px;
    color: #fff;
}
</style>
