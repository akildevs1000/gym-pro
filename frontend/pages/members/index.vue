<template>
  <div v-if="can('member_access')">
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

          <v-btn
            dense
            class="ma-0"
            x-small
            small
            color="violet"
            @click="
              downloadImage(
                qr_codeImage,
                currentItem.system_user_id + ' QR Code'
              )
            "
          >
            <v-icon class="mx-1 ml-2">mdi mdi-download</v-icon>
            Download
          </v-btn>
        </v-card-text>
      </v-card>
    </v-dialog>
    <div v-if="!loading">
      <v-card class="pa-3 background">
        <v-row class="align-center justify-space-between">
          <!-- Left Side: Title + Reload Button -->
          <v-col cols="12" md="6" class="d-flex align-center">
            <div>Members</div>

            <v-btn
              color="text"
              dense
              small
              :ripple="false"
              icon
              title="Reload"
              @click="getDataFromApi"
            >
              <v-icon>mdi-reload</v-icon>
            </v-btn>
          </v-col>

          <!-- Right Side: Search + New Button -->
          <v-col cols="12" md="6" class="d-flex justify-end align-center">
            <v-text-field
              v-model="search"
              placeholder="Search"
              @input="searchData"
              append-icon="mdi-magnify"
              hide-details
              dense
              outlined
              class="mr-2"
              style="max-width: 200px"
            />
            <MemberCreate :key="createKey" v-if="can('member_create')" @response="() => {
              createKey += 1;
              getDataFromApi();
            }" />
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="12">
            <v-data-table
              class="background"
              elevation="0"
              dense
              :headers="headers_table"
              :items="data"
              model-value="data.id"
              :loading="loadinglinear"
              :options.sync="options"
              :footer-props="{
                itemsPerPageOptions: [100, 500, 1000],
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
                  <v-list dense class="background">
                    <v-list-item v-if="can('member_profile_view')">
                      <v-list-item-title style="cursor: pointer">
                        <MemberProfile
                          :memberId="item.id"
                          @close-popup="closePopup2"
                          @eventFromChild="handleEventFromChild"
                          :memberObject="item"
                        />
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="can('member_profile_view')">
                      <v-list-item-title style="cursor: pointer">
                        <MemberViewPlan
                          :memberId="item.id"
                          @close-popup="closePopup2"
                          @eventFromChild="handleEventFromChild"
                          :memberObject="item"
                        />
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="can('member_profile_view')">
                      <v-list-item-title style="cursor: pointer">
                        <MemberInvoice
                          :memberId="item.id"
                          @close-popup="closePopup2"
                          @eventFromChild="handleEventFromChild"
                          :memberObject="item"
                        />
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="can('member_profile_view')">
                      <v-list-item-title style="cursor: pointer">
                        <MemberModifyPlan
                          :memberId="item.id"
                          @close-popup="closePopup2"
                          @eventFromChild="handleEventFromChild"
                          :memberObject="item"
                        />
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      v-if="
                        can('member_profile_view') &&
                        item?.last_membership?.status !== 1
                      "
                    >
                      <v-list-item-title style="cursor: pointer">
                        <MemberRenew
                          :memberId="item.id"
                          @close-popup="closePopup2"
                          @eventFromChild="handleEventFromChild"
                          :memberObject="item"
                        />
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="can('member_profile_view')">
                      <v-list-item-title style="cursor: pointer">
                        <MemberPayments
                          :memberId="item.id"
                          @close-popup="closePopup2"
                          @eventFromChild="handleEventFromChild"
                          :memberObject="item"
                        />
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="can('member_profile_view')">
                      <v-list-item-title style="cursor: pointer">
                        <MemberMetrics
                          :memberId="item.id"
                          @close-popup="closePopup2"
                          @eventFromChild="handleEventFromChild"
                          :memberObject="item"
                        />
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item
                      v-if="can('member_delete')"
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
          </v-col>
        </v-row>
      </v-card>
    </div>
    <Preloader v-else />
  </div>

  <NoAccess v-else />
</template>

<script>
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
  data: () => ({
    createKey:1,
    totalRowsCount: 0,
    filters: {},
    DialogQrCode: false,
    loadinglinear: false,
    image: "",
    currentItem: {},
    viewDialog: false,
    right: true,
    rightDrawer: false,
    drawer: true,
    tab: null,
    selectedItem: 1,
    on: "",
    files: "",
    loading: false,
    snackbar: false,
    qr_codeImage: null,
    options: {},
    Model: "Member",
    endpoint: "members",
    snackbar: false,
    ids: [],
    loading: false,
    response: "",
    data: [],
    errors: [],
    key: 1,
    refresh: false,
    search: null,
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
  }),

  async created() {
    this.loading = false;
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
    searchData() {
      //if (this.search.length == 0 || this.search.length >= 1) {
      this.getDataFromApi();
      //}
    },
    closePopup2() {
      this.viewDialog = false;
      this.getDataFromApi();
    },
    async handleEventFromChild() {
      console.log("calling refresh");
      
      this.refresh = true;
      await this.getDataFromApi();
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
  },
};
</script>
