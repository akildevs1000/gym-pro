<template>
  <span>
    <style scoped>
      .rounded-lg {
        border-radius: 16px;
      }

      .dropzone {
        border: 2px dashed rgba(255, 255, 255, 0.18);
        border-radius: 12px;
        min-height: 200px;
        cursor: pointer;
        transition: border-color 0.2s, background-color 0.2s;
      }

      .dropzone--active {
        border-color: rgba(98, 0, 238, 0.7); /* primary tint */
        background-color: rgba(255, 255, 255, 0.04);
      }

      .underline {
        text-decoration: underline;
      }
    </style>
    <v-card color="input_bg" dark class="rounded-lg">
      <!-- Selected file preview / message -->

      <div
        class="dropzone d-flex flex-column align-center justify-center text-center"
        :class="{ 'dropzone--active': isDragging }"
        @click="openPicker"
        @dragover.prevent="onDragOver"
        @dragleave.prevent="onDragLeave"
        @drop.prevent="onDrop"
        role="button"
        tabindex="0"
        @keydown.enter.space="openPicker"
      >
        <div class="d-flex flex-column align-center">
          <ImageCropper
            v-show="file"
            ref="croppingDialog"
            :selectedFile="filePreview"
            @cropped="handleCroppedResult"
            @close="closeCroppingDialog"
          />
          <img
            v-if="filePreview"
            :src="filePreview"
            alt="Preview"
            style="max-width: 100%; max-height: 150px; border-radius: 8px"
          />
        </div>

        <!-- Upload section only visible if NO file and NO preview -->
        <div v-if="!file && !filePreview">
          <div class="pt-2" style="margin: 0 auto; width: 150px">
            <v-img
              style="
                width: 100%;
                height: 150px;
                border: 1px solid #6946dd;
                border-radius: 50%;
                margin: 0 auto;
              "
              :src="previewImage || '/no-profile-image.jpg'"
            ></v-img>
            <br />

            <input
              required
              type="file"
              @change="attachment"
              style="display: none"
              accept="image/*"
              ref="attachment_input"
            />
          </div>
          <div class="mb-1">
            <span
              class="primary--text font-weight-medium underline"
              @click.stop="openPicker"
            >
              Upload a file
            </span>
            <span> or drag and drop</span>
          </div>

          <!-- <div class="caption grey--text text--lighten-1">
            PNG, JPG, GIF up to 10MB
          </div> -->
        </div>
      </div>

      <!-- hidden file input -->
      <input
        ref="input"
        type="file"
        style="display: none !important"
        :accept="accept"
        @change="onPick"
      />
    </v-card>
  </span>
</template>

<script>
import VueCropper from "vue-cropperjs";

export default {
  components: {
    VueCropper,
  },

  props: ["side"],
  name: "DropzoneUpload",
  data: () => ({
    filePreview: null, // Holds the URL of the selected file
    isDragging: false,
    file: null,
    error: null,
    maxSizeBytes: 10 * 1024 * 1024, // 10MB
    accept: "image/png,image/jpeg,image/gif",
  }),
  methods: {
    handleCroppedResult(blob) {
      console.log("🚀 ~ handleCroppedResult ~ blob:", blob);

      // Convert blob to an object URL for preview
      const croppedUrl = URL.createObjectURL(blob);

      // Update preview to show cropped result
      this.filePreview = croppedUrl;
      this.file = blob;

      // Emit upwards if parent needs it
      this.$emit("selected", { side: this.side, file: blob });
    },
    openPicker() {
      this.$refs.input && this.$refs.input.click();
    },
    onDragOver() {
      this.isDragging = true;
    },
    onDragLeave() {
      this.isDragging = false;
    },
    onDrop(e) {
      this.isDragging = false;
      const f = e.dataTransfer.files && e.dataTransfer.files[0];
      if (f) this.handleFile(f);
    },
    onPick(e) {
      const f = e.target.files && e.target.files[0];
      if (f) this.handleFile(f);
      e.target.value = ""; // reset so same file can be re-picked
    },
    handleFile(f) {
      // Cannot set properties of undefined (setting 'dialogCropping')
      this.$refs.croppingDialog.openDialog();

      this.error = null;

      // Type check
      const allowed = ["image/png", "image/jpeg", "image/gif"];
      if (!allowed.includes(f.type)) {
        this.error = "Unsupported file type. Please upload PNG, JPG, or GIF.";
        this.file = null;
        return;
      }

      // Size check
      if (f.size > this.maxSizeBytes) {
        this.error = "File is too large. Maximum size is 10MB.";
        this.file = null;
        return;
      }

      this.file = f;
      this.filePreview = URL.createObjectURL(f);
    },
    // This method closes the dialog and resets the state
    closeCroppingDialog() {
      if (this.filePreview) {
        URL.revokeObjectURL(this.filePreview);
      }
      this.filePreview = URL.createObjectURL(blob);
    },
    prettySize(bytes) {
      if (bytes < 1024) return `${bytes} B`;
      if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
      return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    },
  },
};
</script>
