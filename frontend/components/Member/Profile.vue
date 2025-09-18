<template>
  <v-dialog persistent v-model="viewDialog" width="800px" :key="memberId">
    <WidgetsClose left="790" @click="viewDialog = false" />
    <template v-slot:activator="{ on, attrs }">
      <span v-bind="attrs" v-on="on"> <v-icon color="text" small> mdi-eye </v-icon> View/Edit </span>
    </template>
    <v-card flat class="background" style="overflow: hidden">
      <v-row
        v-if="memberObject && memberObject.id"
        align="center"
        no-gutters
        class="pa-0 ma-0"
      >
        <v-col cols="12">
          <div dense flat dark class="white--text primary pa-2">
            Member Profile
          </div>
        </v-col>
        <v-col cols="12">
          <v-container>
            <v-card flat class="background">
              <v-card-text>
                <v-dialog v-model="dialogCropping" width="500">
                  <v-card class="background" style="padding-top: 20px">
                    <v-card-text>
                      <!-- <img :src="imageUrl" alt="Preview Image" /> -->
                      <!-- Cropping image step1 -->
                      <VueCropper
                        v-show="selectedFile"
                        ref="cropper"
                        :src="selectedFile"
                        alt="Source Image"
                        :aspectRatio="1"
                        :autoCropArea="0.9"
                        :viewMode="3"
                      ></VueCropper>

                      <!-- <div class="cropper-preview"></div> -->
                    </v-card-text>

                    <v-card-actions>
                      <div
                        col="6"
                        md="6"
                        class="col-sm-12 col-md-6 col-12 pull-left"
                      >
                        <v-btn
                          class="danger btn btn-danger text-left"
                          text
                          @click="dialogCropping = false"
                          style="float: left"
                          >Cancel</v-btn
                        >
                      </div>
                      <div
                        col="6"
                        md="6"
                        class="col-sm-12 col-md-6 col-12 text-right"
                      >
                        <v-btn
                          class="primary btn btn-danger text-right"
                          @click="saveCroppedImageStep2(), (dialog = false)"
                          >Crop</v-btn
                        >
                      </div>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
                <v-row
                  v-if="can('employee_profile_view')"
                  class="d-flex align-center"
                >
                  <v-col cols="4">
                    <v-card class="pa-4 background text-center" flat>
                      <v-avatar size="120" @click="onpick_attachment">
                        <img
                          :src="previewImage || '/no-profile-image.jpg'"
                          alt="Profile Image"
                        />
                        <input
                          required
                          type="file"
                          @change="attachment"
                          style="display: none"
                          accept="image/*"
                          ref="attachment_input"
                        />
                        <span
                          v-if="errors && errors.profile_picture"
                          class="text-danger mt-2"
                          >{{ errors.profile_picture[0] }}</span
                        >
                      </v-avatar>

                      <v-list dense class="mt-3 background">
                        <v-list-item>
                          <v-list-item-content>
                            <v-list-item-title class="font-weight-bold"
                              >{{ memberObject.title }}.
                              {{ memberObject.full_name }}</v-list-item-title
                            >
                            <v-list-item-subtitle
                              >Device Id:
                              {{
                                memberObject?.system_user_id ?? "---"
                              }}</v-list-item-subtitle
                            >
                          </v-list-item-content>
                        </v-list-item> </v-list
                      >
                    </v-card>
                  </v-col>

                  <v-col>
                    <div
                      class="text-right"
                      v-if="
                        can(
                          !editForm
                            ? 'employee_profile_edit'
                            : 'employee_profile_view'
                        )
                      "
                    >
                      <v-icon
                        small
                        color="primary"
                        @click="editForm = !editForm"
                        >mdi-{{ editForm ? "eye" : "pencil" }}</v-icon
                      >
                    </div>
                    <v-simple-table
                      v-if="can('employee_profile_view')"
                      dense
                      flat
                      class="my-simple-table background"
                    >
                      <tbody>
                        <tr>
                          <td style="width: 200px">Device Id</td>
                          <td>
                            <span v-if="!editForm">{{
                              employee.system_user_id
                            }}</span>
                            <v-text-field
                              v-else
                              :readonly="!editForm"
                              style="border-bottom: 1px solid #eaeaea"
                              class="small-input-font"
                              dense
                              v-model="employee.system_user_id"
                              color="primary"
                              :hide-details="!errors.system_user_id"
                              :error-messages="
                                errors && errors.system_user_id
                                  ? errors.system_user_id[0]
                                  : ''
                              "
                            />
                          </td>
                        </tr>

                        <tr v-if="editForm">
                          <td style="width: 200px">Title</td>
                          <td>
                            <v-autocomplete
                              :append-icon="!editForm ? '' : 'mdi-menu-down'"
                              :items="titleItems"
                              class="small-input-font"
                              style="border-bottom: 1px solid #eaeaea"
                              dense
                              v-model="employee.title"
                              color="primary"
                              :hide-details="!errors.title"
                              :error-messages="
                                errors && errors.title ? errors.title[0] : ''
                              "
                            />
                          </td>
                        </tr>

                        <tr>
                          <td style="width: 200px">First Name</td>
                          <td>
                            <span v-if="!editForm">{{
                              employee.first_name
                            }}</span>
                            <v-text-field
                              v-else
                              :readonly="!editForm"
                              style="border-bottom: 1px solid #eaeaea"
                              class="small-input-font"
                              dense
                              v-model="employee.first_name"
                              color="primary"
                              :hide-details="!errors.first_name"
                              :error-messages="
                                errors && errors.first_name
                                  ? errors.first_name[0]
                                  : ''
                              "
                            />
                          </td>
                        </tr>
                        <tr>
                          <td style="width: 200px">Last Name</td>
                          <td>
                            <span v-if="!editForm">{{
                              employee.last_name
                            }}</span>
                            <v-text-field
                              v-else
                              :readonly="!editForm"
                              style="border-bottom: 1px solid #eaeaea"
                              class="small-input-font"
                              dense
                              v-model="employee.last_name"
                              color="primary"
                              :hide-details="!errors.last_name"
                              :error-messages="
                                errors && errors.last_name
                                  ? errors.last_name[0]
                                  : ''
                              "
                            />
                          </td>
                        </tr>

                        <tr>
                          <td style="width: 200px">Phone</td>
                          <td>
                            <span v-if="!editForm">{{
                              employee.phone_number
                            }}</span>
                            <v-text-field
                              v-else
                              :readonly="!editForm"
                              style="border-bottom: 1px solid #eaeaea"
                              class="small-input-font"
                              dense
                              v-model="employee.phone_number"
                              color="primary"
                              :hide-details="!errors.phone_number"
                              :error-messages="
                                errors && errors.phone_number
                                  ? errors.phone_number[0]
                                  : ''
                              "
                            />
                          </td>
                        </tr>

                        <tr>
                          <td style="width: 200px">Whatsapp</td>
                          <td>
                            <span v-if="!editForm">{{
                              employee.whatsapp_number
                            }}</span>
                            <v-text-field
                              v-else
                              :readonly="!editForm"
                              style="border-bottom: 1px solid #eaeaea"
                              class="small-input-font"
                              dense
                              v-model="employee.whatsapp_number"
                              color="primary"
                              :hide-details="!errors.whatsapp_number"
                              :error-messages="
                                errors && errors.whatsapp_number
                                  ? errors.whatsapp_number[0]
                                  : ''
                              "
                            />
                          </td>
                        </tr>

                        <tr>
                          <td style="width: 200px">Plan Start Date</td>
                          <td>
                            <span>{{
                              employee?.last_membership?.show_plan_start_date
                            }}</span>
                          </td>
                        </tr>

                        <tr>
                          <td style="width: 200px">Plan End Date</td>
                          <td>
                            <span>{{
                              employee?.last_membership?.show_plan_end_date
                            }}</span>
                          </td>
                        </tr>
                      </tbody>
                    </v-simple-table>
                  </v-col>
                </v-row>
              </v-card-text>
              <v-card-actions class="mt-auto">
                <v-spacer></v-spacer>
                <v-btn
                  :disabled="!editForm"
                  x-small
                  class="grey white--text"
                  @click="close"
                  >Cancel</v-btn
                >
                <v-btn
                  :disabled="!editForm"
                  x-small
                  class="primary"
                  :loading="loading"
                  @click="store_data"
                  >Save</v-btn
                >
              </v-card-actions>
            </v-card>
          </v-container>
        </v-col>
      </v-row>
    </v-card>
  </v-dialog>
</template>
<script>
import "cropperjs/dist/cropper.css";
import VueCropper from "vue-cropperjs";
export default {
  components: {
    VueCropper,
  },
  props: ["memberId", "memberObject"],
  data: () => ({
    viewDialog: false,
    menu: false,
    menu2: false,
    editForm: false,
    image: "",
    leave_managers: [],
    mime_type: "",
    cropedImage: "",
    cropper: "",
    autoCrop: false,
    dialogCropping: false,
    selectedFile: "",
    upload_edit: {
      name: "",
    },

    attrs: [],
    dialog: false,
    editDialog: false,
    tab: null,
    m: false,
    expand: false,
    expand2: false,
    boilerplate: false,
    right: true,
    rightDrawer: false,
    drawer: true,
    tab: null,
    selectedItem: 1,

    on: "",
    color: "background",
    files: "",
    Model: "Member",
    endpoint: "member",
    loading: false,
    response: "",
    snackbar: false,
    employee: {
      title: "",
      display_name: "",
      employee_id: "",
      system_user_id: "",
      profile_picture: "",
      //employee_role_id: "",
      leave_group_id: "",
      reporting_manager_id: "",
    },
    upload: {
      name: "",
    },
    previewImage: null,
    snackbar: false,
    ids: [],
    loading: false,
    titleItems: ["Mr", "Mrs", "Miss", "Ms", "Dr"],
    response: "",
    data: [],
    errors: [],
    plans: [],
  }),

  watch: {
    dialog(val) {
      val || this.close();
    },
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  async created() {
    this.employee = { ...this.memberObject };
    console.log("🚀 ~ created ~ this.employee:", this.employee.last_membership)

    await this.getPlans();
  },
  methods: {
    async getPlans() {
      try {
        let { data } = await this.$axios.get(`/plans-list`);
        this.plans = data;
      } catch (error) {}
    },

    saveCroppedImageStep2() {
      this.cropedImage = this.$refs.cropper.getCroppedCanvas().toDataURL();

      this.image_name = this.cropedImage;
      this.previewImage = this.cropedImage;

      this.dialogCropping = false;
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    close() {
      this.dialog = false;
      this.$emit("close-popup");
    },
    onpick_attachment() {
      this.$refs.attachment_input.click();
    },

    attachment(e) {
      this.upload.name = e.target.files[0] || "";

      let input = this.$refs.attachment_input;
      let file = input.files;

      if (file[0].size > 1024 * 1024) {
        e.preventDefault();
        this.errors["profile_picture"] = [
          "File too big (> 1MB). Upload less than 1MB",
        ];
        return;
      }

      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
          //croppedimage step6
          // this.previewImage = e.target.result;

          this.selectedFile = event.target.result;

          this.$refs.cropper.replace(this.selectedFile);
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);

        this.dialogCropping = true;
      }
    },

    mapper(obj) {
      let employee = new FormData();

      for (let x in obj) {
        if (obj[x]) {
          employee.append(x, obj[x]);
        }
      }

      return employee;
    },

    store_data() {
      let final = Object.assign(this.employee);
      let employee = this.mapper(final);

      //croppedimageStep3
      if (this.$refs.attachment_input.files[0]) {
        this.cropedImage = this.$refs.cropper.getCroppedCanvas().toDataURL();

        this.$refs.cropper.getCroppedCanvas().toBlob((blob) => {
          // Create a FormData object and append the Blob as a file
          //const formData = new FormData();
          employee.append("profile_picture", blob, "cropped_image.jpg");
          employee.append("attachment_input", blob, "cropped_image.jpg");

          //croppedimagesptep4 //push to API in blob method only
          this.saveToAPI(employee);
        }, "image/jpeg");
      } else {
        employee.delete("profile_picture");
        this.saveToAPI(employee);
      }
    },
    saveToAPI(employee) {
      this.$axios
        .post(`/members-update/${this.memberId}`, employee)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.errors = [];
            this.snackbar = true;
            this.response = "Member Updated successfully";
            this.$emit("eventFromchild");
            setTimeout(() => {
              this.$emit("close-popup");
            }, 1000);

            //this.employeeDialog = false;
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
