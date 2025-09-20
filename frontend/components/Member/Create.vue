<template>
  <div>
    <v-dialog v-model="PlanDetailsDialog" max-width="500px">
      <WidgetsClose left="490" @click="PlanDetailsDialog = false" />

      <v-card class="background">
        <v-card-title>
          {{ selected_plan?.name }}
        </v-card-title>
        <v-card-subtitle class="mt-1">{{
          selected_plan?.description
        }}</v-card-subtitle>
        <v-card-text>
          <div><strong>Price:</strong> {{ selected_plan?.price }}</div>
          <!-- <div>
            <strong>Duration:</strong>
            {{ selected_plan?.duration_in_days }} days
          </div> -->
          <div class="mt-2"><strong>Features:</strong></div>
          <v-list dense class="background">
            <v-list-item
              v-for="(feature, index) in selected_plan?.features"
              :key="index"
            >
              <v-list-item-icon>
                <v-icon color="green">mdi-check-circle</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>{{ feature }}</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog persistent v-model="dialogCropping" width="500">
      <v-card style="padding-top: 20px">
        <v-card-text>
          <VueCropper
            v-show="selectedFile"
            ref="cropper"
            :src="selectedFile"
            alt="Source Image"
            :aspectRatio="1"
            :autoCropArea="0.9"
            :viewMode="3"
          ></VueCropper>
        </v-card-text>

        <v-card-actions>
          <div col="6" md="6" class="col-sm-12 col-md-6 col-12 pull-left">
            <v-btn
              class="danger btn btn-danger text-left"
              text
              @click="closeCroppingPopup()"
              style="float: left"
              >Cancel</v-btn
            >
          </div>
          <div col="6" md="6" class="col-sm-12 col-md-6 col-12 text-right">
            <v-btn
              class="violet btn btn-danger text-right"
              @click="saveCroppedImageStep2(), (dialog = false)"
              >Crop</v-btn
            >
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog persistent v-model="memberDialog" width="1100">
      <WidgetsClose left="1090" @click="close" />

      <template v-slot:activator="{ on, attrs }">
        <v-btn
          v-bind="attrs"
          v-on="on"
          class="violet"
          small
          @click="openNewPage"
        >
          + New
        </v-btn>
      </template>

      <v-card color="dialog_bg" dense flat>
        <v-toolbar
          color="dialog_bg"
          dense
          flat
          class="white--text"
          style="font-weight: 400"
        >
          Add {{ Model }}
        </v-toolbar>
        <v-divider></v-divider>
        <v-card-text>
          <v-container>
            <v-row no-gutters>
              <v-col md="8" sm="12" cols="12">
                <div class="pt-2 pb-2 white--text">
                  <h3>Personal Details</h3>
                </div>
                <v-divider></v-divider>
                <v-container>
                  <v-row dense>
                    <v-col md="12" cols="12" sm="12" dense>
                      <div>Device Id</div>
                      <v-text-field
                        background-color="input_bg"
                        color="input_bg"
                        readonly
                        dense
                        solo
                        type="text"
                        v-model="employee.system_user_id"
                        :hide-details="!errors.system_user_id"
                      ></v-text-field>
                    </v-col>
                    <v-col class="mt-2" md="6" sm="12" cols="12">
                      <div>Title</div>
                      <v-select
                        background-color="input_bg"
                        color="input_bg"
                        v-model="employee.title"
                        :items="titleItems"
                        hide-details
                        dense
                        solo
                      ></v-select>
                    </v-col>
                    <v-col class="mt-2" md="6" sm="12" cols="12">
                      <div>Date of Birth</div>
                      <div>
                        <v-menu
                          v-model="dateofbirthMenuOpen"
                          :close-on-content-click="false"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="auto"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              background-color="input_bg"
                              color="input_bg"
                              hide-details
                              v-model="employee.date_of_birth"
                              persistent-hint
                              append-icon="mdi-calendar"
                              readonly
                              solo
                              dense
                              v-bind="attrs"
                              v-on="on"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            style="min-height: 320px"
                            v-model="employee.date_of_birth"
                            no-title
                            @input="dateofbirthMenuOpen = false"
                          ></v-date-picker>
                        </v-menu>
                      </div>
                    </v-col>
                    <v-col class="mt-2" md="6" sm="12" cols="12" dense>
                      <div>First Name</div>
                      <v-text-field
                        background-color="input_bg"
                        color="input_bg"
                        dense
                        solo
                        type="text"
                        v-model="employee.first_name"
                        hide-details
                      ></v-text-field>
                    </v-col>
                    <v-col class="mt-2" md="6" sm="12" cols="12" dense>
                      <div>Last Name</div>
                      <v-text-field
                        background-color="input_bg"
                        color="input_bg"
                        dense
                        solo
                        type="text"
                        v-model="employee.last_name"
                        hide-details
                      ></v-text-field>
                    </v-col>
                    <v-col class="mt-2" md="6" cols="6" sm="6" dense>
                      <div>Mobile Numbers</div>
                      <v-text-field
                        background-color="input_bg"
                        color="input_bg"
                        dense
                        solo
                        type="number"
                        v-model="employee.phone_number"
                        hide-details
                      ></v-text-field>
                    </v-col>
                    <v-col class="mt-2" md="6" cols="6" sm="6" dense>
                      <div>Whatsapp ( ex:971XXXX)</div>
                      <v-text-field
                        background-color="input_bg"
                        color="input_bg"
                        dense
                        solo
                        type="number"
                        v-model="employee.whatsapp_number"
                        hide-details
                      ></v-text-field>
                    </v-col>
                  </v-row>
                </v-container>

                <div class="pt-5 pb-2 white--text">
                  <h3>Membership & Payment</h3>
                </div>
                <v-divider></v-divider>
                <v-container>
                  <v-row dense>
                    <v-col md="6" sm="12" cols="12">
                      <div>Plan Start Date</div>
                      <div>
                        <v-menu
                          v-model="joiningDateMenuOpen"
                          :close-on-content-click="false"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="auto"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              background-color="input_bg"
                              color="input_bg"
                              label=""
                              hide-details
                              v-model="employee.plan_start_date"
                              persistent-hint
                              append-icon="mdi-calendar"
                              readonly
                              solo
                              dense
                              v-bind="attrs"
                              v-on="on"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            style="min-height: 320px"
                            v-model="employee.plan_start_date"
                            no-title
                            @input="joiningDateMenuOpen = false"
                          ></v-date-picker>
                        </v-menu>
                      </div>
                    </v-col>

                    <v-col md="6" sm="12" cols="12">
                      <div>Plan End Date</div>
                      <div>
                        <v-menu
                          v-model="joiningDateMenuOpen2"
                          :close-on-content-click="false"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="auto"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              background-color="input_bg"
                              color="input_bg"
                              hide-details
                              v-model="employee.plan_end_date"
                              persistent-hint
                              append-icon="mdi-calendar"
                              readonly
                              solo
                              dense
                              v-bind="attrs"
                              v-on="on"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            style="min-height: 320px"
                            v-model="employee.plan_end_date"
                            no-title
                            @input="joiningDateMenuOpen2 = false"
                          ></v-date-picker>
                        </v-menu>
                      </div>
                    </v-col>

                    <v-col cols="12">
                      <div>Plans</div>
                      <div style="display: flex">
                        <v-autocomplete
                          :items="plans"
                          item-text="name"
                          item-value="id"
                          placeholder="Select"
                          v-model="employee.plan_id"
                          hide-details
                          dense
                          solo
                          background-color="input_bg"
                          color="input_bg"
                          @change="getPlanDetails(employee.plan_id)"
                        ></v-autocomplete>
                        <v-btn
                          text
                          small
                          color="violet"
                          @click="PlanDetailsDialog = true"
                        >
                          View Details
                        </v-btn>
                      </div>
                    </v-col>

                    <v-col md="6" sm="12" cols="12" dense>
                      <div>Payment mode</div>
                      <div style="display: flex">
                        <v-autocomplete
                          background-color="input_bg"
                          color="input_bg"
                          :items="payment_modes"
                          item-value="id"
                          dense
                          solo
                          v-model="employee.payment_mode_id"
                          item-text="name"
                          placeholder="Select"
                          hide-details
                        >
                        </v-autocomplete>
                      </div>
                    </v-col>

                    <v-col md="6" sm="12" cols="12" dense>
                      <div>Payment Mode Ref</div>
                      <v-text-field
                        background-color="input_bg"
                        color="input_bg"
                        dense
                        solo
                        type="text"
                        v-model="employee.payment_mode_ref"
                        hide-details
                      ></v-text-field>
                    </v-col>

                    <v-col md="6" sm="12" cols="12" dense>
                      <div>Discount</div>
                      <v-text-field
                        background-color="input_bg"
                        color="input_bg"
                        dense
                        solo
                        type="text"
                        v-model="employee.discount"
                        hide-details
                        @input="setPaidAmount(employee.discount)"
                      ></v-text-field>
                    </v-col>

                    <v-col md="6" sm="12" cols="12" dense>
                      <div>Amount</div>
                      <v-text-field
                        background-color="input_bg"
                        color="input_bg"
                        dense
                        solo
                        type="text"
                        v-model="employee.paid_amount"
                        hide-details
                      ></v-text-field>
                    </v-col>
                  </v-row>
                </v-container>
              </v-col>
              <v-col cols="4">
                <v-row class="text-center">
                  <v-col cols="12">
                    <v-container>
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
                      <div class="text-center mb-5">
                        <v-btn class="violet" @click="onpick_attachment">
                          <v-icon left dark size="20">mdi-cloud-upload</v-icon>
                          {{ !upload.name ? "Upload" : "Change" }} Image
                        </v-btn>
                      </div>
                      <v-divider></v-divider>

                      <v-container>
                        <div class="mt-2">
                          <!-- <div>ID Front</div> -->
                          <DropZoneUpload
                            side="front"
                            @selected="handleDropZoneUpload"
                          />
                        </div>
                        <div class="mt-2">
                          <!-- <div>ID Back</div> -->
                          <DropZoneUpload
                            side="back"
                            @selected="handleDropZoneUpload"
                          />
                        </div>
                      </v-container>
                    </v-container>
                  </v-col>
                </v-row>
              </v-col>
              <v-col cols="12">
                <!-- Error -->
                <v-alert
                  v-if="errors.length"
                  type="error"
                  dense
                  text
                  class="mt-4"
                >
                  {{ errors[0] }}
                </v-alert>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions>
          <!-- <pre>{{ employee }}</pre> -->
          <v-spacer></v-spacer>
          <!-- <v-btn small color="grey white--text" @click="memberDialog = false">
              Close
            </v-btn> -->

          <v-btn small :loading="loading" @click="close"> Cancel </v-btn>
          <v-btn small :loading="loading" color="violet" @click="store_data">
            Submit
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import "cropperjs/dist/cropper.css";
import VueCropper from "vue-cropperjs";
const today = new Date();

// Format function to get YYYY-MM-DD
const formatDate = (date) => {
  return date.toISOString().split("T")[0];
};

// Today's date
const planStartDate = formatDate(today);

// One year from today
const planEndDate = formatDate(
  new Date(today.setFullYear(today.getFullYear() + 1))
);

export default {
  head() {
    return {
      link: [
        {
          rel: "stylesheet",
          href: "~/assets/source-sans-pro.css", // Adjust the path if needed
        },
      ],
    };
  },
  components: {
    VueCropper,
  },

  data: () => ({
    PlanDetailsDialog: false,
    totalRowsCount: 0,
    refresh: true,
    id: "",
    employee_id: "",
    system_user_id: "",
    shifts: [],
    joiningDate: null,
    dateofbirthMenuOpen: false,
    joiningDateMenuOpen: false,
    joiningDateMenuOpen2: false,
    showFilters: false,
    filters: {},
    isFilter: false,
    sortBy: "employee_id",
    sortDesc: false,
    server_datatable_totalItems: 1000,
    snack: false,
    snackColor: "",
    DialogQrCode: false,
    snackText: "",
    selectedItems: [],
    datatable_search_textbox: "",
    datatable_searchById: "",
    loadinglinear: true,
    displayErrormsg: false,
    image: "",
    mime_type: "",
    cropedImage: "",
    cropper: "",
    autoCrop: false,
    dialogCropping: false,
    tab: 0,
    memberId: 0,
    currentItem: {},
    employee_id: 0,
    memberObject: {},
    attrs: [],
    dialog: false,
    editDialog: false,
    viewDialog: false,
    selectedFile: "",
    memberDialog: false,
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
    files: "",
    loading: false,
    //total: 0,
    per_page: 1000,
    color: "background",
    response: "",
    snackbar: false,
    btnLoader: false,
    max_employee: 0,
    selected_plan: null,
    qr_codeImage: null,
    employee: {
      title: "Mr",
      system_user_id: null,
      first_name: "",
      last_name: "",
      phone_number: "",
      whatsapp_number: "",
      date_of_birth: "",
      plan_start_date: planStartDate, // dynamically generated
      plan_end_date: planEndDate, // dynamically generated
      plan_id: "",

      payment_mode_id: 0,
      payment_mode_ref: "",
      discount: 0,
      paid_amount: 0,

      front: null,
      back: null,
    },
    upload: {
      name: "",
    },
    previewImage: null,
    payload: {},
    personalItem: {},
    contactItem: {},
    emirateItems: {},
    setting: {},
    options: {},
    Model: "Member",
    endpoint: "members",
    snackbar: false,
    ids: [],
    loading: false,
    //total: 0,
    titleItems: ["Mr", "Mrs", "Miss", "Ms", "Dr"],
    editedIndex: -1,
    editedItem: { name: "" },
    defaultItem: { name: "" },
    response: "",
    data: [],
    errors: [],
    plans: [],
    payment_modes: [],
    key: 1,
    branchList: [],
    branch_id: null,
    isCompany: true,
    import_branch_id: "",

    refresh: false,
    search: null,
  }),

  async created() {
    this.loading = false;
    this.boilerplate = true;

    await this.getPlans();
    await this.getPaymentModes();
  },
  methods: {
    handleDropZoneUpload(item) {
      console.log("🚀 ~ handleDropZoneUpload ~ item:", item);
      if (item.side == "front") {
        this.employee.front = item.file;
      }
      if (item.side == "back") {
        this.employee.back = item.file;
      }
    },
    getPlanDetails(plan_id) {
      this.selected_plan = this.plans.find((e) => e.id == plan_id);
      this.employee.paid_amount = Math.abs(
        this.employee.discount - this.selected_plan.price
      );
    },
    setPaidAmount(discount) {
      this.employee.paid_amount = Math.abs(discount - this.selected_plan.price);
    },
    getDefaultEmployee() {
      const today = new Date();
      const formatDate = (date) => date.toISOString().split("T")[0];

      return {
        title: "Mr",
        system_user_id: null,
        first_name: "Francis",
        last_name: "Gill",
        phone_number: "971554501483",
        whatsapp_number: "971554501483",
        date_of_birth: formatDate(today),
        plan_start_date: formatDate(today),
        plan_end_date: formatDate(
          new Date(today.getFullYear() + 1, today.getMonth(), today.getDate())
        ),
        plan_id: 1,

        payment_mode_id: 0,
        payment_mode_ref: "",
        discount: 0,
        paid_amount: 0,
      };
    },
    async openNewPage() {
      this.employee = this.getDefaultEmployee();
      this.memberDialog = true;
      await this.getNextSystemUserId();
      await this.getPlans();
      await this.getPaymentModes();
    },
    async getPlans() {
      try {
        let { data } = await this.$axios.get(`/plans-list`);
        this.plans = data;
      } catch (error) {}
    },
    async getNextSystemUserId() {
      try {
        let { data } = await this.$axios.get(`/get-next-system-user-id`);
        this.employee.system_user_id = data;
      } catch (error) {}
    },
    async getPaymentModes() {
      try {
        let { data } = await this.$axios.get(`/payment-modes-list`);
        this.payment_modes = data;
      } catch (error) {}
    },
    closeCroppingPopup() {
      this.$refs.attachment_input.value = null;
      this.dialogCropping = false;
    },
    saveCroppedImageStep2() {
      this.cropedImage = this.$refs.cropper.getCroppedCanvas().toDataURL();

      this.image_name = this.cropedImage;
      this.previewImage = this.cropedImage;

      this.dialogCropping = false;
    },

    close() {
      this.employee = {};
      this.memberDialog = false;
      this.errors = [];
    },
    onpick_attachment() {
      this.$refs.attachment_input.click();
    },
    attachment(e) {
      this.upload.name = e.target.files[0] || "";
      console.log("🚀 ~ attachment ~ this.upload.name:", this.upload.name);

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
      let member = new FormData();

      for (let x in obj) {
        member.append(x, obj[x]);
      }

      member.append("front", this.employee.front);
      member.append("back", this.employee.back);
      member.append("plan_price", this.selected_plan?.price || 0);
      member.append("plan_name", this.selected_plan?.name || 0);

      return member;
    },

    store_data() {
      let final = Object.assign(this.employee);
      let member = this.mapper(final);

      if (this.$refs.attachment_input.files[0]) {
        this.$refs.cropper.getCroppedCanvas().toBlob((blob) => {
          if (blob) {
            member.append("profile_picture", blob, "cropped_image.jpg");
            member.append("attachment_input", blob, "cropped_image.jpg");
          }
          this.saveToAPI(member);
        }, "image/jpeg");
      } else {
        this.saveToAPI(member);
      }
    },
    saveToAPI(member) {
      this.$axios
        .post("/members", member)
        .then(async ({ data }) => {
          //this.loading = false;

          if (!data.status) {
            this.errors = [];
            if (data.errors) this.errors = data.errors;
            else {
              this.snackbar = true;
              this.response = data.message;
            }
          } else {
            this.$emit("response");
            this.errors = [];
            this.memberDialog = false;
          }
        })
        .catch((e) => {
          this.errors = e;
        });
    },
  },
};
</script>
