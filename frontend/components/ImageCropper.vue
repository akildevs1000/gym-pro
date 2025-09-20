<template>
  <v-dialog persistent v-model="internalDialog" width="500">
    <v-card style="padding-top: 20px">
      <v-card-text>
        <VueCropper
          v-if="selectedFile"
          ref="cropper"
          :src="selectedFile"
          alt="Source Image"
          :aspectRatio="16 / 9"
          :autoCropArea="0.8"
          :viewMode="3"
        ></VueCropper>
      </v-card-text>

      <v-card-actions>
        <div class="d-flex justify-space-between w-100">
          <v-btn
            class="danger btn btn-danger text-left"
            text
            @click="closeCroppingPopup()"
          >
            Cancel
          </v-btn>
          <v-btn
            class="violet btn btn-danger text-right"
            @click="saveCroppedImageStep2()"
          >
            Crop
          </v-btn>
        </div>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import VueCropper from "vue-cropperjs";
import "cropperjs/dist/cropper.css";

export default {
  name: "CroppingDialog",
  components: {
    VueCropper,
  },
  props: {
    selectedFile: {
      type: String,
      default: null,
    },
  },
  data: () => ({
    internalDialog: false,
  }),
  methods: {
    openDialog() {
      // Method for the parent to call
      this.internalDialog = true;
    },
    closeDialog() {
      // Method for the parent to call
      this.internalDialog = false;
    },
    saveCroppedImageStep2() {
      // Get the cropped image as a blob
      this.$refs.cropper.getCroppedCanvas().toBlob((blob) => {
        // Emit the cropped blob to the parent
        this.$emit("cropped", blob);

        this.closeDialog();
      }, "image/jpeg");
    },
    closeCroppingPopup() {
      this.$emit("close");
    },
  },
  watch: {
    // Watch for changes to the selectedFile prop
    selectedFile(newValue, oldValue) {
      if (newValue && this.$refs.cropper) {
        // Use the replace method to load the new image
        this.$refs.cropper.replace(newValue);
      }
    },
  },
};
</script>
