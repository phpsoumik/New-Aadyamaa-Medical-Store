<template>
    <Dialog
        v-model:visible="statusDialog"
        :style="{ width: '450px' }"
        header="Confirm"
        @hide="closeDialog"
        :closable="true"
    >
        <div class="confirmation-content">
            <i
                class="pi pi-exclamation-triangle p-mr-3"
                style="font-size: 2rem"
            />
            <span>{{ dialogTitle }}?</span>
        </div>
        <template #footer>
            <Button
                label="No"
                icon="pi pi-times"
                class="p-button-success"
                @click.stop="sendOption(false)"
            />
            <Button
                label="Yes"
                icon="pi pi-check"
                class="p-button-danger"
                @click.stop="sendOption(true)"
            />
        </template>
    </Dialog>
</template>
<script lang="ts">
import { Options, Vue } from "vue-class-component";
import { useStore } from "../store";

@Options({
    emits: ["updateConfirmationStatus"],
    components: {}
})
export default class Confirmation extends Vue {

    private statusDialog = true;
    private result = false;
    private vuexStore = useStore();

    //CLOSE THE ITEM DAILOG BOX
    closeDialog() {
        this.$emit("updateConfirmationStatus", {
            result: this.result
        });
    }

    get dialogTitle()
    {
        return this.vuexStore.getters.getReceiptTitle;
    }

    sendOption(r) {
        this.result = r;
        this.closeDialog();
    }
}
</script>
