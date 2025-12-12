<template>
  <Dialog
    v-model:visible="productDialog"
    :style="{ width: '50vw' }"
    :maximizable="false"
    position="middle"
    class="p-fluid"
    :modal="true"
    :closable="true"
    @hide="closeDialogBox"
  >
    <template #header>
      <h5 class="p-dialog-titlebar p-dialog-titlebar-icon">
        <i class="pi pi-image" style="fontsize: 1.5rem"></i> {{ dialogTitle }}
      </h5>
    </template>
    <div class="p-grid">
      <div class="p-col">
        <div class="p-field">
            <FileUpload name="image[]"  :customUpload="true" @uploader="myUploader" :previewWidth="150" :multiple="false" :accept="imageType" :maxFileSize="2000000">
                <template #empty>
                    <p>Drag and drop files to here to upload.</p>
                </template>
            </FileUpload>
        </div>
      </div>
    </div>
  </Dialog>
</template>

<script lang="ts">
import { Options, Vue } from "vue-class-component";
import Toaster from "../helpers/Toaster";
import FileUpload from 'primevue/fileupload';

@Options({
  components: {
    FileUpload,
  },
  props: {
    uploaderDetail: Object,
  },
  watch: {
    uploaderDetail(obj) {
      if (obj.status == true) {
        this.dialogTitle = obj.dialogTitle;  
        this.imageType = obj.imageType;  
        this.openDialog();
      }
    },
  },
  emits: ["updateUploaderStatus"],
})
export default class FileUploader extends Vue {
  private toast;
  private submitted = false;
  private productDialog = false;
  private dialogTitle = "";
  private imageType = "";

  created() {
    this.toast = new Toaster();
  }

  mounted() {
    
  }

  openDialog() {
    this.submitted = false;
    this.productDialog = true;
  }

  closeDialogBox() {
    this.submitted = false;
    this.productDialog = false;
    this.$emit("updateUploaderStatus",[]);
  }


  myUploader(e)
  {
    this.$emit("updateUploaderStatus",e.files);
    this.submitted = false;
    this.productDialog = false;
  }
}
</script>
