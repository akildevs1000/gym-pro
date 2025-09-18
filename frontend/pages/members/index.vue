<template>
  <div v-if="can('employee_access')">
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
            <v-list-item v-for="(feature, index) in selected_plan?.features" :key="index">
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
    <v-dialog v-model="DialogQrCode" width="300">
      <v-card class="background">
        <v-card-title dense class="popup_background">
          QR Code - Member Id -
          {{ currentItem && currentItem.system_user_id }}
          <v-spacer></v-spacer>
          <v-icon @click="DialogQrCode = false" outlined dark>
            mdi mdi-close-circle
          </v-icon>
        </v-card-title>
        <v-card-text class="text-center">
          <img :src="qr_codeImage" :key="key" style="width: 100%" />

          <v-btn dense class="ma-0" x-small small color="violet" @click="
              downloadImage(
                qr_codeImage,
                currentItem.system_user_id + ' QR Code'
              )
            ">
            <v-icon class="mx-1 ml-2">mdi mdi-download</v-icon>
            Download
          </v-btn>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog persistent v-model="dialogCropping" width="500">
      <v-card style="padding-top: 20px">
        <v-card-text>
          <VueCropper v-show="selectedFile" ref="cropper" :src="selectedFile" alt="Source Image" :aspectRatio="1"
            :autoCropArea="0.9" :viewMode="3"></VueCropper>
        </v-card-text>

        <v-card-actions>
          <div col="6" md="6" class="col-sm-12 col-md-6 col-12 pull-left">
            <v-btn class="danger btn btn-danger text-left" text @click="closePopup()" style="float: left">Cancel</v-btn>
          </div>
          <div col="6" md="6" class="col-sm-12 col-md-6 col-12 text-right">
            <v-btn class="violet btn btn-danger text-right"
              @click="saveCroppedImageStep2(), (dialog = false)">Crop</v-btn>
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog persistent v-model="memberDialog" width="800">
      <WidgetsClose left="790" @click="close" />
      <v-card class="background">
        <v-toolbar dense class="violet white--text" style="font-weight: 400">
          Add {{ Model }}
        </v-toolbar>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col md="6" sm="12" cols="12" class="mt-5" dense>
                <v-row>
                  <v-col md="12" cols="12" sm="12" dense>
                    <v-text-field label="Device Id" readonly dense outlined type="text"
                      v-model="employee.system_user_id" :hide-details="!errors.system_user_id"
                      :error="errors.system_user_id" :error-messages="
                        errors && errors.system_user_id
                          ? errors.system_user_id[0]
                          : ''
                      "></v-text-field>
                  </v-col>
                  <v-col md="6" sm="12" cols="12">
                    <!-- <label class="col-form-label"
                    >Title <span class="text-danger">*</span></label
                  > -->
                    <v-select label="Title" v-model="employee.title" :items="titleItems" :hide-details="!errors.title"
                      :error="errors.title" :error-messages="
                        errors && errors.title ? errors.title[0] : ''
                      " dense outlined></v-select>
                  </v-col>
                  <v-col md="6" sm="12" cols="12">
                    <!-- <label class="col-form-label"
                    >Joining Date <span class="text-danger">*</span></label
                  > -->
                    <div>
                      <v-menu v-model="dateofbirthMenuOpen" :close-on-content-click="false"
                        transition="scale-transition" offset-y max-width="290px" min-width="auto">
                        <template v-slot:activator="{ on, attrs }">
                          <v-text-field label="Date of Birth" :hide-details="!errors.date_of_birth" :error-messages="
                              errors && errors.date_of_birth
                                ? errors.date_of_birth[0]
                                : ''
                            " v-model="employee.date_of_birth" persistent-hint append-icon="mdi-calendar" readonly
                            outlined dense v-bind="attrs" v-on="on"></v-text-field>
                        </template>
                        <v-date-picker style="min-height: 320px" v-model="employee.date_of_birth" no-title
                          @input="dateofbirthMenuOpen = false"></v-date-picker>
                      </v-menu>
                    </div>
                  </v-col>
                  <v-col md="6" sm="12" cols="12" dense>
                    <!-- <label class="col-form-label"
                    >First Name <span class="text-danger">*</span></label
                  > -->
                    <v-text-field label="First Name" dense outlined :hide-details="!errors.first_name" type="text"
                      v-model="employee.first_name" :error="errors.first_name" :error-messages="
                        errors && errors.first_name ? errors.first_name[0] : ''
                      "></v-text-field>
                  </v-col>
                  <v-col md="6" sm="12" cols="12" dense>
                    <!-- <label class="col-form-label"
                    >Last Name <span class="text-danger">*</span></label
                  > -->
                    <v-text-field label="Last Name" dense outlined :hide-details="!errors.last_name" type="text"
                      v-model="employee.last_name" :error="errors.last_name" :error-messages="
                        errors && errors.last_name ? errors.last_name[0] : ''
                      "></v-text-field>
                  </v-col>
                  <v-col md="6" cols="6" sm="6" dense>
                    <!-- <label class="col-form-label"
                    >Mobile Number <span class="text-danger">*</span></label
                  > -->
                    <v-text-field label="Mobile Numbers" dense outlined type="number" v-model="employee.phone_number"
                      :hide-details="!errors.phone_number" :error="errors.phone_number" :error-messages="
                        errors && errors.phone_number
                          ? errors.phone_number[0]
                          : ''
                      "></v-text-field>
                  </v-col>
                  <v-col md="6" cols="6" sm="6" dense>
                    <!-- <label class="col-form-label"
                    >Whatsapp <span class="text-danger">*</span> ( ex:
                    971XXXX)</label
                  > -->
                    <v-text-field label="Whatsapp ( ex:
                    971XXXX)" dense outlined type="number" v-model="employee.whatsapp_number"
                      :hide-details="!errors.whatsapp_number" :error="errors.whatsapp_number" :error-messages="
                        errors && errors.whatsapp_number
                          ? errors.whatsapp_number[0]
                          : ''
                      "></v-text-field>
                  </v-col>

                  <v-col md="6" sm="12" cols="12">
                    <!-- <label class="col-form-label"
                    >Joining Date <span class="text-danger">*</span></label
                  > -->
                    <div>
                      <v-menu v-model="joiningDateMenuOpen" :close-on-content-click="false"
                        transition="scale-transition" offset-y max-width="290px" min-width="auto">
                        <template v-slot:activator="{ on, attrs }">
                          <v-text-field label="Plan Start Date" :hide-details="!errors.plan_start_date" :error-messages="
                              errors && errors.plan_start_date
                                ? errors.plan_start_date[0]
                                : ''
                            " v-model="employee.plan_start_date" persistent-hint append-icon="mdi-calendar" readonly
                            outlined dense v-bind="attrs" v-on="on"></v-text-field>
                        </template>
                        <v-date-picker style="min-height: 320px" v-model="employee.plan_start_date" no-title
                          @input="joiningDateMenuOpen = false"></v-date-picker>
                      </v-menu>
                    </div>
                  </v-col>

                  <v-col md="6" sm="12" cols="12">
                    <!-- <label class="col-form-label"
                    >Joining Date <span class="text-danger">*</span></label
                  > -->
                    <div>
                      <v-menu v-model="joiningDateMenuOpen2" :close-on-content-click="false"
                        transition="scale-transition" offset-y max-width="290px" min-width="auto">
                        <template v-slot:activator="{ on, attrs }">
                          <v-text-field label="Plan End Date" :hide-details="!errors.plan_end_date" :error-messages="
                              errors && errors.plan_end_date
                                ? errors.plan_end_date[0]
                                : ''
                            " v-model="employee.plan_end_date" persistent-hint append-icon="mdi-calendar" readonly
                            outlined dense v-bind="attrs" v-on="on"></v-text-field>
                        </template>
                        <v-date-picker style="min-height: 320px" v-model="employee.plan_end_date" no-title
                          @input="joiningDateMenuOpen2 = false"></v-date-picker>
                      </v-menu>
                    </div>
                  </v-col>

                  <v-col cols="12">
                    <div style="display: flex">
                      <v-autocomplete label="Plans" :items="plans" item-text="name" item-value="id" placeholder="Select"
                        v-model="employee.plan_id" :hide-details="!errors.plan_id" :error="errors.plan_id"
                        :error-messages="
                          errors && errors.plan_id ? errors.plan_id[0] : ''
                        " dense outlined @change="getPlanDetails(employee.plan_id)"></v-autocomplete>
                      <v-btn text small color="violet" @click="PlanDetailsDialog = true">
                        View Details
                      </v-btn>
                    </div>
                  </v-col>

                  <v-col md="6" sm="12" cols="12" dense>
                    <div style="display: flex">
                      <v-autocomplete label="Payment mode" :items="payment_modes" item-value="id" dense outlined
                        v-model="employee.payment_mode_id" item-text="name" placeholder="Select"
                        :hide-details="!errors['payment_mode_id']" :error="Boolean(errors['payment_mode_id'])"
                        :error-messages="
                          errors && errors['payment_mode_id']
                            ? errors['payment_mode_id'][0]
                            : ''
                        ">
                      </v-autocomplete>
                    </div>
                  </v-col>

                  <v-col md="6" sm="12" cols="12" dense>
                    <v-text-field label="Payment Mode Ref" dense outlined type="text"
                      v-model="employee.payment_mode_ref" :hide-details="!errors['payment_mode_ref']"
                      :error="Boolean(errors['payment_mode_ref'])" :error-messages="
                        errors && errors['payment_mode_ref']
                          ? errors['payment_mode_ref'][0]
                          : ''
                      "></v-text-field>
                  </v-col>

                  <v-col md="6" sm="12" cols="12" dense>
                    <v-text-field label="Discount" dense outlined type="text" v-model="employee.discount"
                      :hide-details="!errors['discount']" :error="Boolean(errors['discount'])" :error-messages="
                        errors && errors['discount']
                          ? errors['discount'][0]
                          : ''
                      " @input="setPaidAmount(employee.discount)"></v-text-field>
                  </v-col>

                  <v-col md="6" sm="12" cols="12" dense>
                    <v-text-field label="Amount" dense outlined type="text" v-model="employee.paid_amount"
                      :hide-details="!errors['paid_amount']" :error="Boolean(errors['paid_amount'])" :error-messages="
                        errors && errors['paid_amount']
                          ? errors['paid_amount'][0]
                          : ''
                      "></v-text-field>
                  </v-col>
                </v-row>
              </v-col>
              <v-col>
                <div class="pt-5" style="margin: 0 auto; width: 150px">
                  <v-img style="
                      width: 100%;
                      height: 150px;
                      border: 1px solid #6946dd;
                      border-radius: 50%;
                      margin: 0 auto;
                    " :src="previewImage || '/no-profile-image.jpg'"></v-img>
                  <br />

                  <input required type="file" @change="attachment" style="display: none" accept="image/*"
                    ref="attachment_input" />

                  <span v-if="errors && errors.profile_picture" class="text-danger mt-2">{{ errors.profile_picture[0]
                    }}</span>
                </div>
                <div class="text-center">
                  <v-btn small class="form-control violet" @click="onpick_attachment">{{ !upload.name ? "Upload" :
                    "Change" }} Profile Image
                    <v-icon right dark>mdi-cloud-upload</v-icon>
                  </v-btn>
                </div>
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

          <v-btn v-if="can('employee_create')" small :loading="loading" color="violet" @click="store_data">
            Submit
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <div v-if="!loading">
      <v-card class="pa-3 background">
        <v-row class="align-center justify-space-between">
          <!-- Left Side: Title + Reload Button -->
          <v-col cols="12" md="6" class="d-flex align-center">
            <div>Members</div>

            <v-btn color="text" dense small :ripple="false" icon title="Reload" @click="getDataFromApi">
              <v-icon>mdi-reload</v-icon>
            </v-btn>
          </v-col>

          <!-- Right Side: Search + New Button -->
          <v-col cols="12" md="6" class="d-flex justify-end align-center">
            <v-text-field v-model="search" placeholder="Search" @input="searchData" append-icon="mdi-magnify"
              hide-details dense outlined class="mr-2" style="max-width: 200px" />

            <v-btn class="violet" small @click="openNewPage" v-if="can('employee_create')">
              + New
            </v-btn>
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="12">
            <v-data-table class="background" elevation="0" dense v-model="selectedItems" :headers="headers_table"
              :items="data" model-value="data.id" :loading="loadinglinear" :options.sync="options" :footer-props="{
                itemsPerPageOptions: [100, 500, 1000],
              }" :server-items-length="totalRowsCount">
              <template v-slot:item.member="{ item, index }" style="width: 300px">
                <v-row no-gutters class="align-center pa-3">
                  <v-col cols="auto" style="
                      padding: 5px;
                      padding-left: 0px;
                      width: auto;
                      max-width: none;
                    " class="d-flex align-center">
                    <v-avatar size="40" class="me-2">
                      <v-img :src="
                          item.profile_picture
                            ? item.profile_picture
                            : '/no-profile-image.jpg'
                        "></v-img>
                    </v-avatar>
                    <span style="
                        font-size: 13px;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                      ">
                      {{ item.title }} {{ item.full_name }}
                      <br />
                      {{ item.system_user_id }}
                    </span>
                  </v-col>
                </v-row>
              </template>

              <template v-slot:item.contact="{ item, index }" style="width: 300px">
                {{ item.phone_number }} / {{ item.whatsapp_number }}
              </template>

              <template v-slot:item.status="{ item }">
                <v-chip :color="
                    item?.last_membership?.status == '1'
                      ? 'green lighten-4'
                      : 'red lighten-4'
                  " :text-color="
                    item?.last_membership?.status == '1'
                      ? 'green darken-2'
                      : 'red darken-2'
                  " small label rounded class="ma-1">
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
                  <v-list dense class="background">
                    <v-list-item v-if="can('member_profile_view')">
                      <v-list-item-title style="cursor: pointer">
                        <MemberProfile :memberId="item.id" @close-popup="closePopup2"
                          @eventFromChild="handleEventFromChild" :memberObject="item" />
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="can('member_profile_view')">
                      <v-list-item-title style="cursor: pointer">
                        <MemberViewPlan :memberId="item.id" @close-popup="closePopup2"
                          @eventFromChild="handleEventFromChild" :memberObject="item" />
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="can('member_profile_view')">
                      <v-list-item-title style="cursor: pointer">
                        <MemberInvoice :memberId="item.id" @close-popup="closePopup2"
                          @eventFromChild="handleEventFromChild" :memberObject="item" />
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="can('member_profile_view')">
                      <v-list-item-title style="cursor: pointer">
                        <MemberModifyPlan :memberId="item.id" @close-popup="closePopup2"
                          @eventFromChild="handleEventFromChild" :memberObject="item" />
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="
                        can('member_profile_view') &&
                        item?.last_membership?.status !== 1
                      ">
                      <v-list-item-title style="cursor: pointer">
                        <MemberRenew :memberId="item.id" @close-popup="closePopup2"
                          @eventFromChild="handleEventFromChild" :memberObject="item" />
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="can('member_profile_view')">
                      <v-list-item-title style="cursor: pointer">
                        <MemberPayments :memberId="item.id" @close-popup="closePopup2"
                          @eventFromChild="handleEventFromChild" :memberObject="item" />
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="can('member_profile_view')">
                      <v-list-item-title style="cursor: pointer">
                        <MemberMetrics :memberId="item.id" @close-popup="closePopup2"
                          @eventFromChild="handleEventFromChild" :memberObject="item" />
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="can('employee_delete')" @click="deleteItem(item)">
                      <v-list-item-title style="cursor: pointer">
                        <v-icon color="error" small> mdi-delete </v-icon>
                        Delete
                      </v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </template>
            </v-data-table>
          </v-col>
        </v-row>
      </v-card>
    </div>
    <Preloader v-else />
  </div>

  <NoAccess v-else />
</template>

<script>
import "cropperjs/dist/cropper.css";
import VueCropper from "vue-cropperjs";
import { getQrCode } from "@/utils/cardqrercode.js"; // Adjust the path as needed
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
    timezones: [],
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
    },
    defaultPayload: {
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
        text: "Phone / Whatsapp",
        align: "left",
        sortable: true,
        key: "contact",
        value: "contact",
        width: "15%",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Membership Start",
        align: "left",
        sortable: true,
        key: "last_membership.show_plan_start_date",
        value: "last_membership.show_plan_start_date", //template name should be match for sorting sub table should be the same
        width: "15%",
        filterable: true,
        filterSpecial: true,
      },
      {
        text: "Membership Expiry",
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
  computed: {
    // amountDisplay(){
    //   return Math.abs(this.employee.discount - this.employee.paid_amount);
    // },
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
      this.employee.paid_amount = Math.abs(
        this.employee.discount - this.selected_plan.price
      );
    },
    setPaidAmount(discount) {
      this.employee.paid_amount = Math.abs(discount - this.selected_plan.price);
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
    async handleEventFromChild() {
      this.refresh = true;
      await this.getDataFromApi();
    },
    getDefaultEmployee() {
      const today = new Date();
      const formatDate = (date) => date.toISOString().split("T")[0];

      return {
        title: "Mr",
        system_user_id: null,
        first_name: "",
        last_name: "",
        phone_number: "",
        whatsapp_number: "",
        date_of_birth: formatDate(today),
        plan_start_date: formatDate(today),
        plan_end_date: formatDate(
          new Date(today.getFullYear() + 1, today.getMonth(), today.getDate())
        ),
        plan_id: "",

        payment_mode_id: 0,
        payment_mode_ref: "",
        discount: 0,
        paid_amount: 0,
      };
    },
    async openNewPage() {
      this.employee = this.getDefaultEmployee(); // ✅ reset with default structure
      this.memberDialog = true;
      await this.getNextSystemUserId();
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
      member.append("plan_name", this.selected_plan?.name || 0);

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
