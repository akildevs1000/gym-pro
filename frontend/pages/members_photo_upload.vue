<template>
  <div style="width: 100% !important" v-if="can(`employee_upload_access`)">
    <div class="text-center ma-2">
      <v-snackbar
        :color="snackbar.color"
        v-model="snackbar.show"
        small
        top="top"
        :timeout="3000"
      >
        {{ response }}
      </v-snackbar>
    </div>

    <v-card class="mb-5 background">
      <v-container fluid>
        <v-row>
          <v-col>
            <div
              style="
                display: flex;
                gap: 5px;
                align-items: center;
                justify-content: space-between;
              "
            >
              <div>
                <b class="" style="font-size: 18px; font-weight: 600"
                  >Members upload to Devices</b
                >
              </div>
              <div class="ml-5">
                <v-text-field
                  v-model="searchInput"
                  dense
                  outlined
                  hide-details
                  label="Search...."
                  append-icon="mdi-magnify"
                  @input="handleSearch"
                ></v-text-field>
              </div>
            </div>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="5">
            <v-card
              class="photo-displaylist mx-1 background darken-1"
              style="height: 300px"
              outlined
            >
              <div class="pa-2">Members</div>
              <v-divider />
              <div
                style="max-height: 250px; overflow-y: auto; overflow-x: hidden"
              >
                <v-card-text>
                  <v-row
                    class="timezone-displaylistview1"
                    v-for="(user, index) in leftMembers"
                    :id="user.id"
                    v-model="leftMembers"
                    :key="user.id"
                    style="border-bottom: 1px solid #ddd"
                  >
                    <v-col md="1" style="padding: 0px; margin-top: -7px">
                      <v-checkbox
                        dense
                        small
                        hideDetails
                        v-model="leftSelectedEmp"
                        :value="user.id"
                        primary
                        hide-details
                      ></v-checkbox>
                    </v-col>

                    <v-col md="1" style="padding: 0px">
                      <v-img
                        :title="user.first_name + ' ' + user.last_name"
                        style="
                          float: left;
                          border-radius: 50%;
                          height: auto;
                          padding: 0px;
                          position: relative;
                          top: 0;
                          transition: top ease 1s;

                          margin-left: -3px;
                          width: 25px;
                          border: 1px solid #ddd;
                        "
                        :src="
                          user.profile_picture
                            ? user.profile_picture
                            : '/no-profile-image.jpg'
                        "
                      >
                      </v-img>
                    </v-col>
                    <v-col md="3" style="padding: 0px; padding-top: 5px">
                      {{ user.first_name }}
                      {{ user.last_name }}
                    </v-col>
                    <v-col md="3" style="padding: 0px; padding-top: 5px">
                      {{ user.system_user_id }}
                    </v-col>
                  </v-row>
                </v-card-text>
              </div>
            </v-card>
          </v-col>

          <v-col cols="2">
            <div style="text-align: -webkit-center">
              <v-btn type="button" class="primary mt-8 mb-2" block>
                Transfer Members
              </v-btn>

              <button
                @click="moveToRightEmpOption2"
                type="button"
                id="undo_redo_rightSelected"
                class="btn btn-default btn-block"
              >
                <i
                  aria-hidden="true"
                  class="v-icon notranslate mdi mdi-chevron-right theme--red"
                ></i>
              </button>

              <button
                @click="allmoveToRightEmp"
                type="button"
                id="undo_redo_rightAll"
                class="btn btn-default btn-block"
              >
                <i
                  aria-hidden="true"
                  class="v-icon notranslate mdi mdi-chevron-double-right theme--red"
                ></i>
              </button>
              <button
                @click="moveToLeftempOption2"
                type="button"
                id="undo_redo_leftSelected"
                class="btn btn-default btn-block"
              >
                <i
                  aria-hidden="true"
                  class="v-icon notranslate mdi mdi-chevron-left theme--red"
                ></i>
              </button>
              <button
                @click="allmoveToLeftemp"
                type="button"
                id="undo_redo_leftAll"
                class="btn btn-default btn-block"
              >
                <i
                  aria-hidden="true"
                  class="v-icon notranslate mdi mdi-chevron-double-left theme--red"
                ></i>
              </button>
            </div>
          </v-col>

          <v-col cols="5">
            <v-card
              class="photo-displaylist mx-1 background darken-1"
              outlined
              style="height: 300px"
            >
              <div class="pa-2">Selected Members</div>
              <v-divider />
              <div
                style="max-height: 250px; overflow-y: auto; overflow-x: hidden"
              >
                <v-card-text>
                  <v-row
                    class="timezone-displaylistview1"
                    v-for="(user, index) in rightMembers"
                    :id="user.id"
                    v-model="rightSelectedEmp"
                    :key="user.id"
                    style="border-bottom: 1px solid #ddd"
                  >
                    <v-col md="1" style="padding: 0px">
                      <v-checkbox
                        dense
                        small
                        hideDetails
                        v-model="rightSelectedEmp"
                        :value="user.id"
                        primary
                        hide-details
                      ></v-checkbox>
                    </v-col>

                    <v-col md="1" style="padding: 0px">
                      <v-img
                        :title="user.first_name + ' ' + user.last_name"
                        style="
                          float: left;
                          border-radius: 50%;
                          height: auto;
                          padding: 0px;
                          position: relative;
                          top: 0;
                          transition: top ease 1s;
                          margin-left: -3px;
                          width: 25px;
                          border: 1px solid #ddd;
                        "
                        :src="
                          user.profile_picture
                            ? user.profile_picture
                            : '/no-profile-image.jpg'
                        "
                      >
                      </v-img>
                    </v-col>
                    <v-col md="3" style="padding: 0px; padding-top: 5px">
                      {{ user.first_name }}
                      {{ user.last_name }}
                    </v-col>
                    <v-col md="3" style="padding: 0px; padding-top: 5px">
                      {{ user.system_user_id }}
                    </v-col>
                  </v-row>
                </v-card-text>
              </div>
            </v-card>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="5">
            <v-card
              class="photo-displaylist mx-1 background darken-1"
              outlined
              style="height: 300px"
            >
              <div class="pa-2">Devices</div>
              <v-divider />
              <div
                style="max-height: 250px; overflow-y: auto; overflow-x: hidden"
              >
                <v-card-text>
                  <v-row
                    class="timezone-displaylistview1"
                    v-for="(user, index) in leftDevices"
                    :id="user.id"
                    v-model="leftSelectedDevices"
                    :key="user.id"
                    style="border-bottom: 1px solid #ddd"
                  >
                    <v-col md="1" style="padding: 0px;margin-top-3">
                      <v-checkbox
                        v-if="user.status.name == 'active'"
                        dense
                        small
                        hideDetails
                        v-model="leftSelectedDevices"
                        :value="user.id"
                        primary
                        hide-details
                      ></v-checkbox>
                      <v-checkbox
                        style="padding: 0px;margin-top-3"
                        v-else
                        indeterminate
                        value
                        disabled
                        dense
                        small
                        hideDetails
                        v-model="leftSelectedDevices"
                        :value="user.id"
                        primary
                        hide-details
                      ></v-checkbox>
                    </v-col>
                    <v-col md="3" style="padding: 0px; padding-top: 5px">
                      {{ user.name }}
                    </v-col>
                    <v-col md="3" style="padding: 0px; padding-top: 5px">
                      {{ user.model_number }}
                    </v-col>
                    <v-col md="3" style="padding: 0px">
                      <img
                        title="Online"
                        v-if="user.status.name == 'active'"
                        src="/icons/device_status_open.png"
                        style="width: 30px"
                      />
                      <img
                        title="Offline"
                        v-else
                        src="/icons/device_status_close.png"
                        style="width: 30px"
                      />
                    </v-col>
                  </v-row>
                </v-card-text>
              </div>
            </v-card>
          </v-col>

          <v-col cols="2">
            <div style="text-align: -webkit-center">
              <v-btn type="button" class="primary mt-8 mb-2" block>
                Transfer Devices
              </v-btn>
              <button
                @click="moveToRightDevicesOption2"
                type="button"
                id="undo_redo_rightSelected"
                class="btn btn-default btn-block"
              >
                <i
                  aria-hidden="true"
                  class="v-icon notranslate mdi mdi-chevron-right theme--red"
                ></i>
              </button>

              <button
                @click="allmoveToRightDevices"
                type="button"
                id="undo_redo_rightAll"
                class="btn btn-default btn-block"
              >
                <i
                  aria-hidden="true"
                  class="v-icon notranslate mdi mdi-chevron-double-right theme--red"
                ></i>
              </button>
              <button
                @click="moveToLeftDevicesOption2"
                type="button"
                id="undo_redo_leftSelected"
                class="btn btn-default btn-block"
              >
                <i
                  aria-hidden="true"
                  class="v-icon notranslate mdi mdi-chevron-left theme--red"
                ></i>
              </button>
              <button
                @click="allmoveToLeftDevices"
                type="button"
                id="undo_redo_leftAll"
                class="btn btn-default btn-block"
              >
                <i
                  aria-hidden="true"
                  class="v-icon notranslate mdi mdi-chevron-double-left theme--red"
                ></i>
              </button>
            </div>
          </v-col>

          <v-col cols="5">
            <v-card
              class="photo-displaylist mx-1 background darken-1"
              outlined
              style="height: 300px"
            >
              <div class="pa-2">Selected Devices</div>
              <v-divider />
              <div
                style="max-height: 250px; overflow-y: auto; overflow-x: hidden"
              >
                <v-card-text>
                  <v-row
                    class="timezone-displaylistview1"
                    v-for="(user, index) in rightDevices"
                    :id="user.id"
                    v-model="rightSelectedDevices"
                    :key="user.id"
                    style="border-bottom: 1px solid #ddd"
                  >
                    <v-col md="1" style="padding: 0px;margin-top-3">
                      <v-checkbox
                        v-if="user.status.name == 'active'"
                        dense
                        small
                        hideDetails
                        v-model="rightSelectedDevices"
                        :value="user.id"
                        primary
                        hide-details
                      ></v-checkbox>
                      <v-checkbox
                        style="padding: 0px;margin-top-3"
                        v-else
                        indeterminate
                        value
                        disabled
                        dense
                        small
                        hideDetails
                        v-model="leftSelectedDevices"
                        :value="user.id"
                        primary
                        hide-details
                      ></v-checkbox>
                    </v-col>
                    <v-col md="3" style="padding: 0px; padding-top: 5px">
                      {{ user.name }}
                    </v-col>
                    <v-col md="3" style="padding: 0px; padding-top: 5px">
                      {{ user.model_number }}
                    </v-col>
                    <v-col md="3" style="padding: 0px">
                      <span
                        v-if="user.sdkDeviceResponse == 'Success'"
                        style="color: green"
                        >{{ user.sdkDeviceResponse }}</span
                      >
                      <span v-else style="color: red">{{
                        user.sdkDeviceResponse
                      }}</span>
                    </v-col>
                  </v-row>
                </v-card-text>
              </div>
            </v-card>
          </v-col>
        </v-row>
        <v-row class="mx-1">
          <v-progress-linear
            v-if="progressloading"
            :active="loading"
            :indeterminate="loading"
            absolute
            color="primary"
          ></v-progress-linear>
          <v-col cols="12" class="text-center">
            <span v-if="errors && errors.message" class="text-danger mt-2">{{
              errors.message
            }}</span>
            <!-- <v-btn class="grey" @click="goback" small dark> Back </v-btn> -->
            <v-btn
              v-if="can('employee_upload_create')"
              small
              class="primary"
              :disabled="!displaybutton"
              :loading="loading"
              @click="onSubmit"
            >
              Submit
            </v-btn>

            <UploadPersonResponse
              ref="UploadPersonRef"
              :deviceResponses="deviceResponses"
              :cameraResponses="cameraResponses"
              :cameraResponses2="cameraResponses2"
            />
          </v-col>
        </v-row>
      </v-container>
    </v-card>
  </div>
  <NoAccess v-else />
</template>

<script>
// import Back from "../components/Snippets/Back.vue";

export default {
  components: {},
  data() {
    return {
      deviceResponses: [],
      cameraResponses: [],
      cameraResponses2: [],
      uploadPersonResponseDialog: false,
      isCompany: true,
      branchesList: [],
      loading: false,
      counter: 0,
      devices_dialog: [],
      displaybutton: false,
      progressloading: false,
      searchInput: null,
      debounceTimer: null,
      snackbar: {
        message: "",
        color: "black",
        show: false,
      },
      errors: [],
      response: "",
      color: "primary",
      leftSelectedEmp: [],
      leftMembers: [],
      rightSelectedEmp: [],
      rightMembers: [],
      leftSelectedDevices: [],
      leftDevices: [],
      rightSelectedDevices: [],
      rightDevices: [],
      options: {
        params: {
          company_id: this.$auth.user.company_id,
          cols: ["id", "name"],
        },
      },
    };
  },
  mounted: function () {
    // this.snackbar.show = false;
    // this.snackbar.message = "Data loading...Please wait ";
    // this.response = "Data loading...Please wait ";

    this.$nextTick(function () {
      setTimeout(() => {
        this.loading = false;
        //this.snackbar = false;
      }, 2000);
    });

    setTimeout(() => {
      this.loading = false;
      //this.snackbar = false;
    }, 2000);
  },
  async created() {
    this.progressloading = true;
    await this.getDevisesDataFromApi();
    await this.getMembersDataFromApi();
  },
  beforeDestroy() {
    // Clear the timer on page change / component unload
    clearTimeout(this.debounceTimer);
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    resetErrorMessages() {
      this.errors = [];
      this.response = "";

      // $.extend(this.rightMembers, {
      //   sdkEmpResponse: "",
      // });
      // $.extend(this.rightDevices, {
      //   sdkDeviceResponse: "",
      // });
      this.leftMembers.forEach((element) => {
        element["sdkEmpResponse"] = "";
      });
      this.leftDevices.forEach((element) => {
        element["sdkDeviceResponse"] = "";
      });
    },

    goback() {
      this.$router.push("/timezonemapping/list");
    },
    async getDevisesDataFromApi() {
      //this.loading = true;
      // let page = this.pagination.current;
      let options = {
        params: {
          page: 1, //this.pagination.per_page,
          per_page: 1000, //this.pagination.per_page,
          company_id: this.$auth.user.company_id,
          sortBy: "status_id",
        },
      };
      this.$axios.get(`device`, options).then(({ data }) => {
        this.leftDevices = data.data;
      });
    },
    async getMembersDataFromApi() {
      let options = {
        params: {
          per_page: 1000,
          page: 1,
          company_id: this.$auth.user.company_id,
          cols: [
            "id",
            "system_user_id",
            "display_name",
            "first_name",
            "last_name",
          ],
          searchInput: this.searchInput,
        },
      };
      this.$axios
        .get(`get_employeeswith_timezonename`, options)
        .then(({ data }) => {
          this.leftMembers = data.data;
        }, 1000);
    },
    handleSearch() {
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => {
        console.log("calling api with:", this.searchInput);
        this.getMembersDataFromApi();
      }, 500);
    },
    sortObject: (o) =>
      o.sort(function compareByName(a, b) {
        if (a.first_name && b.first_name) {
          let nameA = a.first_name.toUpperCase(); // Convert names to uppercase for case-insensitive sorting
          let nameB = b.first_name.toUpperCase();

          if (nameA < nameB) {
            return -1;
          } else if (nameA > nameB) {
            return 1;
          } else {
            return 0;
          }
        } else {
        }
      }),
    sortObjectD: (o) =>
      o.sort(function compareByName(a, b) {
        if (a.device_id && b.device_id) {
          let nameA = a.device_id.toUpperCase(); // Convert names to uppercase for case-insensitive sorting
          let nameB = b.device_id.toUpperCase();

          if (nameA < nameB) {
            return -1;
          } else if (nameA > nameB) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
      }),
    sortObjectC: (o) =>
      o.sort(function compareByName(a, b) {
        if (a.name && b.name) {
          let nameA = a.name.toUpperCase(); // Convert names to uppercase for case-insensitive sorting
          let nameB = b.name.toUpperCase();

          if (nameA < nameB) {
            return -1;
          } else if (nameA > nameB) {
            return 1;
          } else {
            return 0;
          }
        }
      }),
    verifySubmitButton() {
      if (this.rightMembers.length > 0 && this.rightDevices.length > 0) {
        this.displaybutton = true;
      } else {
        this.displaybutton = false;
      }
    },
    allmoveToLeftemp() {
      this.resetErrorMessages();
      this.leftMembers = this.leftMembers.concat(this.rightMembers);
      this.rightMembers = [];
      this.leftMembers = this.sortObject(this.leftMembers);

      this.verifySubmitButton();
    },
    allmoveToRightEmp() {
      this.resetErrorMessages();
      // this.rightMembers = this.rightMembers.concat(this.leftMembers);
      // this.leftMembers = [];

      this.rightMembers = this.rightMembers.concat(
        this.leftMembers.filter((el) => el.profile_picture != null)
      );

      this.leftMembers = this.leftMembers.filter(
        (el) => el.profile_picture == null
      );

      this.rightMembers = this.sortObject(this.rightMembers);
      this.verifySubmitButton();
    },
    moveToLeftempOption2() {
      this.resetErrorMessages();

      if (!this.rightSelectedEmp.length) return;
      //for (let i = this.leftSelectedEmp.length; i > 0; i--) {
      let _rightSelectedEmp_length = this.rightSelectedEmp.length;
      for (let i = 0; i < _rightSelectedEmp_length; i++) {
        if (this.rightSelectedEmp) {
          let selectedindex = this.rightMembers.findIndex(
            (e) => e.id == this.rightSelectedEmp[i]
          );

          let selectedobject = this.rightMembers.find(
            (e) => e.id == this.rightSelectedEmp[i]
          );

          selectedobject.sdkEmpResponse = "";
          this.leftMembers.push(selectedobject);

          this.rightMembers.splice(selectedindex, 1);
        }
      }
      this.leftMembers = this.sortObject(this.leftMembers);
      for (let i = 0; i < _rightSelectedEmp_length; i++) {
        this.rightSelectedEmp.pop(this.rightSelectedEmp[i]);
      }

      this.verifySubmitButton();
    },
    moveToLeftemp(id) {
      this.resetErrorMessages();
      this.rightSelectedEmp.push(id);
      if (!this.rightSelectedEmp.length) return;

      //for (let i = this.leftSelectedEmp.length; i > 0; i--) {
      let _rightSelectedEmp_length = this.rightSelectedEmp.length;
      for (let i = 0; i < _rightSelectedEmp_length; i++) {
        if (this.rightSelectedEmp) {
          let selectedindex = this.rightMembers.findIndex(
            (e) => e.id == this.rightSelectedEmp[i]
          );

          let selectedobject = this.rightMembers.find(
            (e) => e.id == this.rightSelectedEmp[i]
          );

          selectedobject.sdkEmpResponse = "";
          this.leftMembers.push(selectedobject);

          this.rightMembers.splice(selectedindex, 1);
        }
      }
      this.leftMembers = this.sortObject(this.leftMembers);

      this.rightSelectedEmp.pop(id);
      this.verifySubmitButton();
    },
    check: function (id, e) {},
    selectLeftEmployee(id) {
      this.leftSelectedEmp.push(id);
    },

    moveToRightEmpOption2() {
      this.resetErrorMessages();
      this.resetErrorMessages();
      if (!this.leftSelectedEmp.length) return;

      let _leftSelectedEmp_length = this.leftSelectedEmp.length;
      for (let i = 0; i < _leftSelectedEmp_length; i++) {
        if (this.leftSelectedEmp) {
          let selectedindex = this.leftMembers.findIndex(
            (e) => e.id == this.leftSelectedEmp[i]
          );

          let selectedobject = this.leftMembers.find(
            (e) => e.id == this.leftSelectedEmp[i]
          );

          this.rightMembers.push(selectedobject);

          this.leftMembers.splice(selectedindex, 1);
        }
      }
      this.rightMembers = this.sortObject(this.rightMembers);

      for (let i = 0; i < _leftSelectedEmp_length; i++) {
        this.leftSelectedEmp.pop(this.leftSelectedEmp[i]);
      }
      this.verifySubmitButton();
    },

    /* Devices---------------------------------------- */
    allmoveToLeftDevices() {
      this.resetErrorMessages();
      this.leftDevices = this.leftDevices.concat(this.rightDevices);
      this.rightDevices = [];

      this.leftDevices = this.sortObjectD(this.leftDevices);
      this.verifySubmitButton();
    },
    allmoveToRightDevices() {
      this.resetErrorMessages();
      ///this.rightDevices = this.rightDevices.concat(this.leftDevices);
      //this.leftDevices = [];

      this.rightDevices = this.rightDevices.concat(
        this.leftDevices.filter((el) => el.status.name == "active")
      );

      this.leftDevices = this.leftDevices.filter(
        (el) => el.status.name == "inactive"
      );

      this.rightDevices = this.sortObjectD(this.rightDevices);
      this.verifySubmitButton();
    },
    moveToLeftDevicesOption2() {
      this.resetErrorMessages();

      if (!this.rightSelectedDevices.length) return;

      //for (let i = this.leftSelectedDevices.length; i > 0; i--) {
      let _rightSelectedDevices_length = this.rightSelectedDevices.length;
      for (let i = 0; i < _rightSelectedDevices_length; i++) {
        if (this.rightSelectedDevices) {
          let selectedindex = this.rightDevices.findIndex(
            (e) => e.id == this.rightSelectedDevices[i]
          );

          let selectedobject = this.rightDevices.find(
            (e) => e.id == this.rightSelectedDevices[i]
          );
          selectedobject["sdkEmpResponse"] = "";
          this.leftDevices.push(selectedobject);

          this.rightDevices.splice(selectedindex, 1);
        }
      }

      this.leftDevices = this.sortObjectD(this.leftDevices);

      for (let i = 0; i < _rightSelectedDevices_length; i++) {
        this.rightSelectedDevices.pop(this.rightSelectedDevices[i]);
      }
      this.verifySubmitButton();
    },
    moveToLeftDevices(id) {
      this.resetErrorMessages();
      this.rightSelectedDevices.push(id);

      if (!this.rightSelectedDevices.length) return;

      //for (let i = this.leftSelectedDevices.length; i > 0; i--) {
      let _rightSelectedDevices_length = this.rightSelectedDevices.length;
      for (let i = 0; i < _rightSelectedDevices_length; i++) {
        if (this.rightSelectedDevices) {
          let selectedindex = this.rightDevices.findIndex(
            (e) => e.id == this.rightSelectedDevices[i]
          );

          let selectedobject = this.rightDevices.find(
            (e) => e.id == this.rightSelectedDevices[i]
          );

          this.leftDevices.push(selectedobject);

          this.rightDevices.splice(selectedindex, 1);
        }
      }

      this.leftDevices = this.sortObjectD(this.leftDevices);

      this.rightSelectedDevices.pop(id);
      this.verifySubmitButton();
    },
    moveToRightDevicesOption2() {
      this.resetErrorMessages();

      if (!this.leftSelectedDevices.length) return;

      let _leftSelectedDevices_length = this.leftSelectedDevices.length;
      for (let i = 0; i < _leftSelectedDevices_length; i++) {
        if (this.leftSelectedDevices) {
          let selectedindex = this.leftDevices.findIndex(
            (e) => e.id == this.leftSelectedDevices[i]
          );

          let selectedobject = this.leftDevices.find(
            (e) => e.id == this.leftSelectedDevices[i]
          );
          selectedobject["sdkDeviceResponse"] = "";
          this.rightDevices.push(selectedobject);

          this.leftDevices.splice(selectedindex, 1);
        }
      }

      this.rightDevices = this.sortObjectD(this.rightDevices);

      for (let i = 0; i < _leftSelectedDevices_length; i++) {
        this.leftSelectedDevices.pop(this.leftSelectedDevices[i]);
      }
      this.verifySubmitButton();
    },
    moveToRightDevices(id) {
      this.resetErrorMessages();
      this.leftSelectedDevices.push(id);

      if (!this.leftSelectedDevices.length) return;

      let _leftSelectedDevices_length = this.leftSelectedDevices.length;
      for (let i = 0; i < _leftSelectedDevices_length; i++) {
        if (this.leftSelectedDevices) {
          let selectedindex = this.leftDevices.findIndex(
            (e) => e.id == this.leftSelectedDevices[i]
          );

          let selectedobject = this.leftDevices.find(
            (e) => e.id == this.leftSelectedDevices[i]
          );

          selectedobject["sdkDeviceResponse"] = "";
          this.rightDevices.push(selectedobject);

          this.leftDevices.splice(selectedindex, 1);
        }
      }

      this.rightDevices = this.sortObjectD(this.rightDevices);

      this.leftSelectedDevices.pop(id);
      this.verifySubmitButton();
    },
    async onSubmit() {
      

      this.$refs["UploadPersonRef"]["uploadPersonResponseDialog"] = true;

      this.deviceResponses = [];
      this.cameraResponses = [];
      this.cameraResponses2 = [];

      this.displaybutton = false;
      this.loading = true;
      if (this.rightMembers.length == 0) {
        this.response = this.response + " Atleast select one Employee Details";
      } else if (this.rightDevices.length == 0) {
        this.response = this.response + " Atleast select one Device Details";
      }

      this.errors = [];

      for (const item of this.rightMembers) {
        let person = {
          name: `${item.first_name} ${item.last_name}`,
          userCode: parseInt(item.system_user_id),
          profile_picture_raw: item.profile_picture_raw,
          faceImage: item.profile_picture,
          expiry: item?.last_membership?.plan_end_date + " 00:00:00",
        };
        
        console.log("🚀 ~ onSubmit ~ person:", person)

        if (item.rfid_card_number) {
          person.cardData = item.rfid_card_number;
        }

        if (item.rfid_card_password) {
          person.password = item.rfid_card_password;
        }

        if (
          Array.isArray(item.finger_prints) &&
          item.finger_prints.length > 0
        ) {
          person.fp = item.finger_prints.map((e) => e.fp);
        }

        if (Array.isArray(item.palms) && item.palms.length > 0) {
          person.palm = item.palms.map((e) => e.palm);
        }

        let personListArray = [person];

        let payload = {
          personList: personListArray,
          snList: this.rightDevices.map((e) => e.device_id),
          branch_id: this.branch_id,
        };

        try {
          let { data } = await this.$axios.post(`/SDK/AddPerson`, payload);
          if (data.deviceResponse[0]) {
            this.deviceResponses.push(data.deviceResponse[0]);
          }
          if (data.cameraResponse[0]) {
            this.cameraResponses.push(data.cameraResponse[0]);
          }
          if (data.cameraResponse2[0]) {
            this.cameraResponses2.push(data.cameraResponse2[0]);
          }
        } catch (error) {
          console.log(`Error for ${person.name}:`, error);
        }
      }

      this.loading = false;

      this.displaybutton = true;
    },
  },
};
</script>
