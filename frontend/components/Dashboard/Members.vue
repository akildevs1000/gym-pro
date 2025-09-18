<template>
  <div v-if="can('employee_access')">
    <style scoped>
      .custom-input {
        padding: 6px 10px;
        height: 30px;
        position: relative;
        border-radius: 5px;
        border: 1px solid grey;
        font-size: 16px;
        transition: border-color 0.3s ease-in-out;
        outline: none;
      }

      .custom-input:focus {
        border-color: purple;
      }
    </style>

    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" small top="top" :color="color">
        {{ response }}
      </v-snackbar>
      <v-snackbar v-model="snack" :color="snackColor">
        {{ snackText }}

        <template v-slot:action="{ attrs }">
          <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
        </template>
      </v-snackbar>
    </div>
    <div v-if="!loading">
      <v-dialog persistent v-model="viewDialog" width="800px" :key="memberId">
        <WidgetsClose left="790" @click="viewDialog = false" />

        <v-card flat style="overflow: hidden" class="background">
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
              <v-tabs v-model="tab" right>
                <v-tab dense v-for="(item, index) in viewTabMenu" :key="index">
                  <v-icon size="20">{{ item.icon }}</v-icon>
                </v-tab>
              </v-tabs>
              <v-tabs-items v-model="tab">
                <v-tab-item v-for="(item, index) in viewTabMenu" :key="index">
                  <component
                    :is="getViewComponents(item.value)"
                    :memberId="memberId"
                    @close-popup="closePopup2"
                    @eventFromChild="handleEventFromChild"
                    v-if="tab == item.value"
                    :memberObject="memberObject"
                  />
                </v-tab-item>
              </v-tabs-items>
            </v-col>
          </v-row>
        </v-card>
      </v-dialog>

      <v-data-table class="background"
        elevation="0"
        dense
        v-model="selectedItems"
        :headers="headers_table"
        :items="data"
        model-value="data.id"
        :loading="loadinglinear"
        :options.sync="options"
        :footer-props="{
          itemsPerPageOptions: [15, 100, 500, 1000],
        }"
        :server-items-length="totalRowsCount"
      >
        <template
                v-slot:item.member="{ item, index }"
                style="width: 300px"
              >
                <v-row no-gutters class="align-center pa-3">
                  <v-col
                    cols="auto"
                    style="
                      padding: 5px;
                      padding-left: 0px;
                      width: auto;
                      max-width: none;
                    "
                    class="d-flex align-center"
                  >
                    <v-avatar size="40" class="me-2">
                      <v-img
                        :src="
                          item.profile_picture
                            ? item.profile_picture
                            : '/no-profile-image.jpg'
                        "
                      ></v-img>
                    </v-avatar>
                    <span
                      style="
                        font-size: 13px;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                      "
                    >
                      {{ item.title }} {{ item.full_name }}
                      <br />
                      {{ item.system_user_id }}
                    </span>
                  </v-col>
                </v-row>
              </template>

              <template
                v-slot:item.contact="{ item, index }"
                style="width: 300px"
              >
                {{ item.phone_number }} / {{ item.whatsapp_number }}
              </template>

              <template v-slot:item.status="{ item }">
                <v-chip
                  :color="
                    item?.last_membership?.status == '1'
                      ? 'green lighten-4'
                      : 'red lighten-4'
                  "
                  :text-color="
                    item?.last_membership?.status == '1'
                      ? 'green darken-2'
                      : 'red darken-2'
                  "
                  small
                  label
                  rounded
                  class="ma-1"
                >
                  {{
                    item?.last_membership?.status == "1" ? "Active" : "Expired"
                  }}
                </v-chip>
              </template>

        <template v-slot:item.options="{ item }">
          <v-menu bottom left>
            <template v-slot:activator="{ on, attrs }">
              <v-btn dark-2 icon v-bind="attrs" v-on="on">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <v-list dense>
              <v-list-item
                v-if="can('employee_profile_view')"
                @click="viewItem(item)"
              >
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="secondary" small> mdi-eye </v-icon>
                  View/Edit
                </v-list-item-title>
              </v-list-item>
              <!-- <v-list-item v-if="can('employee_edit')">
                      <v-list-item-title style="cursor: pointer">
                        <WidgetsEmployeeDowloadDialogSingle
                          :key="item.id"
                          :id="item.id"
                          :system_user_id="item.system_user_id"
                          @response="getDataFromApi"
                        />
                      </v-list-item-title>
                    </v-list-item> -->

              <!-- <v-list-item v-if="can('employee_edit')">
                      <v-list-item-title style="cursor: pointer">
                        <DeviceUser
                          iconColor="secondary"
                          label="Members"
                          :key="generateRandomId()"
                          :system_user_id="item.system_user_id"
                        />
                      </v-list-item-title>
                    </v-list-item> -->
              <v-list-item
                v-if="can('employee_delete')"
                @click="deleteItem(item)"
              >
                <v-list-item-title style="cursor: pointer">
                  <v-icon color="error" small> mdi-delete </v-icon>
                  Delete
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>
      </v-data-table>
    </div>
    <Preloader v-else />
  </div>

  <NoAccess v-else />
</template>

<script>
import "cropperjs/dist/cropper.css";
import VueCropper from "vue-cropperjs";
import { getQrCode } from "@/utils/cardqrercode.js"; // Adjust the path as needed

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
    timezones: [],
    joiningDate: null,
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

    viewTabMenu: [
      {
        text: "Profile",
        icon: "mdi-account-box",
        value: 0,
      },
      {
        text: "Plan",
        icon: "mdi-package-variant-closed",
        value: 1,
      },
      {
        text: "Payments",
        icon: "mdi-wallet",
        value: 2,
      },
    ],
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
    color: "background",
    response: "",
    snackbar: false,
    btnLoader: false,
    max_employee: 0,
    selected_plan: null,
    qr_codeImage: null,
    employee: {
      title: "Mr",
      system_user_id: "12345",
      first_name: "franics",
      last_name: "gill",
      phone_number: "971554501483",
      whatsapp_number: "971554501483",
      plan_start_date: "2025-07-15",
      plan_end_date: "2026-07-15",
      plan_id: "",

      payment_mode_id: 0,
      payment_mode_ref: "",
      paid_amount: 299.0,
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
    headers_table: [
      {
        text: "Member",
        align: "left",
        sortable: true,
        key: "member",
        value: "member",
        width: "15%",
        filterable: true,
        filterSpecial: false,
      },
       {
        text: "Plan Start",
        align: "left",
        sortable: true,
        key: "last_membership.show_plan_start_date",
        value: "last_membership.show_plan_start_date", //template name should be match for sorting sub table should be the same
        width: "15%",
        filterable: true,
        filterSpecial: true,
      },
      {
        text: "Plan End",
        align: "left",
        sortable: true,
        key: "last_membership.show_plan_end_date",
        value: "last_membership.show_plan_end_date", //template name should be match for sorting sub table should be the same
        width: "15%",
        filterable: true,
        filterSpecial: true,
      },

      {
        text: "Plan",
        align: "left",
        sortable: true,
        key: "last_membership.plan.name",
        value: "last_membership.plan.name", //template name should be match for sorting sub table should be the same
        width: "15%",
        filterable: true,
        filterSpecial: true,
      },
      {
        text: "Fees",
        align: "left",
        sortable: true,
        key: "last_membership.plan.price",
        value: "last_membership.plan.price", //template price should be match for sorting sub table should be the same
        width: "15%",
        filterable: true,
        filterSpecial: true,
      },
      {
        text: "Status",
        align: "left",
        sortable: true,
        key: "status",
        value: "status", //template price should be match for sorting sub table should be the same
        width: "15%",
        filterable: true,
        filterSpecial: true,
      },
      {
        text: "Options",
        align: "left",
        sortable: false,
        key: "options",
        value: "options",
      },
    ],
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
    await this.getDataFromApi();
  },
  mounted() {
    this.handleChangeEvent();

    this.getTimezone(null);
  },
  watch: {
    options: {
      async handler() {
        await this.getDataFromApi();
      },
      deep: true,
    },
  },
  methods: {
    getPlanDetails(plan_id) {
      this.selected_plan = this.plans.find((e) => e.id == plan_id);
    },
    getRelatedIcons({
      profile_picture,
      rfid_card_number,
      rfid_card_password,
      finger_prints,
      palms,
    }) {
      let icons = [];

      let iconPath = "/icons/employee-access/";

      let colorMode = this.$vuetify.theme.dark ? "w" : "b"; // b = black, w = white

      if (profile_picture) {
        icons.push({
          type: "image",
          name: `${iconPath}1-${colorMode}.png`,
        });
      }

      if (
        rfid_card_password != "" &&
        rfid_card_password != "FFFFFFFF" &&
        rfid_card_password != null
      ) {
        icons.push({
          type: "image",
          name: `${iconPath}2-${colorMode}.png`,
        });
      }
      if (
        rfid_card_number != "" &&
        rfid_card_number != "0" &&
        rfid_card_number != null
      ) {
        icons.push({
          type: "image",
          name: `${iconPath}3-${colorMode}.png`,
        });

        icons.push({
          name: "mdi-qrcode-scan",
        });
      }
      if (finger_prints.length) {
        icons.push({
          type: "image",
          name: `${iconPath}4-${colorMode}.png`,
        });
      }
      if (palms.length) {
        icons.push({
          type: "image",
          name: `${iconPath}5-${colorMode}.png`,
        });
      }
      return icons;
    },

    downloadImage(faceImage, userId) {
      let options = {
        params: {
          company_id: this.$auth.user.company_id,
          face_image: faceImage,
          system_user_id: userId,
        },
      };
      this.$axios
        .post(`/download-profilepic-sdk`, options.params)
        .then(({ data }) => {
          this.downloadProfileLink =
            this.$backendUrl +
            "/download-profilepic-disk?image=" +
            data +
            "&name=" +
            userId;

          //this.$refs.goTo.click;

          let path = this.downloadProfileLink;
          let pdf = document.createElement("a");
          pdf.setAttribute("href", path);
          pdf.setAttribute("target", "_blank");
          pdf.click();
        })
        .catch((e) => console.log(e));
    },
    async viewDialogQrCode(item) {
      this.currentItem = item;
      this.key++;
      let year = new Date().getFullYear() + 1;
      const date = new Date(year + "-12-31 23:00:00");
      const cardNum = item.rfid_card_number;
      const cardType = 1; // Use 1 for numeric card numbers, 2 for Chinese User IDs

      let qrCodeResult = (
        await getQrCode(date, cardNum, cardType)
      ).toUpperCase();

      this.qr_codeImage = await this.$qrcode.generate(qrCodeResult, {
        width: 200,
        margin: 2,
        color: {
          dark: "#000000", // Black dots
          light: "#FFFFFF", // White background
        },
      });

      this.DialogQrCode = true;
    },
    generateRandomId() {
      const length = 8; // Adjust the length of the ID as needed
      const randomNumber = Math.floor(Math.random() * Math.pow(10, length)); // Generate a random number
      return randomNumber.toString().padStart(length, "0"); // Convert to string and pad with leading zeros if necessary
    },
    searchData() {
      //if (this.search.length == 0 || this.search.length >= 1) {
      this.getDataFromApi();
      //}
    },
    closePopup2() {
      this.editDialog = false;
      this.viewDialog = false;
      this.getDataFromApi();
    },
    async handleChangeEvent() {
      this.branchList = await this.$store.dispatch("fetchDropDowns", {
        key: "branchList",
        endpoint: "branch-list",
      });
    },

    getViewComponents(value) {
      const componentsList = {
        0: "MemberProfile",
        1: "MemberPlan",
        2: "MemberPayments",
      };
      return componentsList[value] || "div"; // default to a div if no component found
    },
    async handleEventFromChild() {
      this.refresh = true;
      await this.getDataFromApi();
    },
    async openNewPage() {
      this.memberDialog = true;

      await this.getPlans();
      await this.getPaymentModes();
      await this.handleChangeEvent();
    },
    async getPlans() {
      try {
        let { data } = await this.$axios.get(`/plans-list`);
        this.plans = data;
      } catch (error) {}
    },
    async getPaymentModes() {
      try {
        let { data } = await this.$axios.get(`/payment-modes-list`);
        this.payment_modes = data;
      } catch (error) {}
    },
    async getTimezone(filterBranchId) {
      let options = {
        endpoint: "timezone-list",
        isFilter: this.isFilter,
        params: {
          company_id: this.$auth.user.company_id,
          branch_id: filterBranchId,
        },
      };
      this.timezones = await this.$store.dispatch("timezone_list", options);
    },
    closeViewDialog() {
      this.viewDialog = false;
    },
    caps(str) {
      if (str == "" || str == null) {
        return "---";
      } else {
        let res = str.toString();
        return res.replace(/\b\w/g, (c) => c.toUpperCase());
      }
    },

    closePopup() {
      //croppingimagestep5
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
    json_to_csv(json) {
      let data = json.map((e) => ({
        title: e.title,
        system_user_id: e.system_user_id,
        first_name: e.first_name,
        last_name: e.last_name,
        phone_number: e.phone_number,
        whatsapp_number: e.whatsapp_number,
        plan_start_date: e.show_plan_start_date,
        plan_end_date: e.show_plan_end_date,
        plan: e.plan.name,
      }));
      let header = Object.keys(data[0]).join(",") + "\n";
      let rows = "";
      data.forEach((e) => {
        rows += Object.values(e).join(",").trim() + "\n";
      });
      return header + rows;
    },
    export_submit() {
      if (this.data.length == 0) {
        this.snackbar = true;
        this.response = "No record to download";
        return;
      }

      let csvData = this.json_to_csv(this.data);
      let element = document.createElement("a");
      element.setAttribute(
        "href",
        "data:text/csv;charset=utf-8, " + encodeURIComponent(csvData)
      );
      element.setAttribute("download", "download.csv");
      document.body.appendChild(element);
      element.click();
      document.body.removeChild(element);
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    async getDataFromApi() {
      this.loadinglinear = true;

      this.filters.search = this.search;

      const data = await this.$store.dispatch("fetchData", {
        key: "members",
        options: this.options,
        refresh: this.refresh,
        endpoint: "members",
        filters: this.filters,
      });

      this.data = data.data;
      this.totalRowsCount = data.total;
      this.loadinglinear = false;
    },
    viewItem(item) {
      this.memberId = item.id;
      this.system_user_id = item.system_user_id;

      this.memberObject = item;
      this.viewDialog = true;
    },
    deleteItem(item) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete(`${this.endpoint}/${item.id}`)
          .then(async ({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.refresh = true;
              await this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
            }
          })
          .catch((err) => console.log(err));
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
      let member = new FormData();

      for (let x in obj) {
        member.append(x, obj[x]);
      }
      member.append("profile_picture", this.upload.name);
      member.append("plan_price", this.selected_plan?.price || 0);

      return member;
    },
    store_data() {
      let final = Object.assign(this.employee);
      let member = this.mapper(final);

      //croppedimageStep3
      if (this.$refs.attachment_input.files[0]) {
        this.cropedImage = this.$refs.cropper.getCroppedCanvas().toDataURL();

        this.$refs.cropper.getCroppedCanvas().toBlob((blob) => {
          // Create a FormData object and append the Blob as a file
          //const formData = new FormData();
          member.append("profile_picture", blob, "cropped_image.jpg");
          member.append("attachment_input", blob, "cropped_image.jpg");

          //croppedimagesptep4 //push to API in blob method only
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
            this.errors = [];
            this.snackbar = true;
            this.response = "Members inserted successfully";
            this.refresh = true;
            await this.getDataFromApi();
            this.memberDialog = false;
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
