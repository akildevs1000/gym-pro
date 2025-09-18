<template>
    <div v-if="can(`attendance_report_access`)">
        <div class="text-center">
            <v-snackbar v-model="snackbar" multi-line top="top" color="secondary" elevation="24">
                <span v-html="response"></span>
            </v-snackbar>
            <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
                {{ snackText }}

                <template v-slot:action="{ attrs }">
                    <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
                </template>
            </v-snackbar>
        </div>

        <v-card class="mb-5 background" elevation="0" v-if="can(`attendance_report_view`)">
            <v-data-table class="background" dense :headers="headers" :items="data" :loading="loading"
                :options.sync="options" :footer-props="{
          itemsPerPageOptions: [10, 30, 50, 100, 500, 1000],
          page: true,
        }" model-value="data.id" :server-items-length="totalRowsCount" :height="tableHeight"
                no-data-text="No Data available. Click 'Generate' button to see the results">
                <template v-slot:item.member="{ item, index }" style="width: 300px">
                    <v-row no-gutters>
                        <v-col style="
                padding: 5px;
                padding-left: 0px;
                width: 50px;
                max-width: 50px;
              ">
                            <v-img style="
                  border-radius: 50%;
                  height: auto;
                  width: 45px;
                  max-width: 45px;
                " :src="
                  item?.member?.profile_picture
                    ? item?.member?.profile_picture
                    : '/no-profile-image.jpg'
                ">
                            </v-img>
                        </v-col>
                        <v-col style="padding: 10px">
                            <div style="font-size: 13px">
                                {{ item?.member?.full_name || "---" }}
                            </div>
                            <small style="font-size: 12px; color: #6c7184">
                                {{ item?.member?.system_user_id || "---" }}
                            </small>
                        </v-col>
                    </v-row>
                </template>

                <template v-slot:item.date="{ item }">
                    <div>{{ item.date }}</div>
                    <div class="secondary-value">
                        {{ item.day }}
                    </div>
                </template>
                <template v-slot:item.in="{ item }">
                    <div :class="`${item?.device_in?.name == 'Manual' ? 'red' : ''}--text`">
                        <div>{{ item.in }}</div>
                        <div class="secondary-value">
                            <div v-if="
                  item.device_in &&
                  item.device_in.name &&
                  item.device_in.name != '---'
                ">
                                {{ item.device_in.name }}
                            </div>
                            <div v-else-if="item.device_id_in != '---'">
                                {{ item.device_id_in }}
                            </div>
                            <div v-else>---</div>
                        </div>
                    </div>
                </template>
                <template v-slot:item.out="{ item }">
                    <div :class="`${item?.device_out?.name == 'Manual' ? 'red' : ''}--text`">
                        <div>{{ item.out }}</div>
                        <div class="secondary-value">
                            <div v-if="
                  item.device_out &&
                  item.device_out.name &&
                  item.device_out.name != '---'
                ">
                                {{ item.device_out.name }}
                            </div>
                            <div v-else-if="item.device_id_out != '---'">
                                {{ item.device_id_out }}
                            </div>
                            <div v-else>---</div>

                            <!-- {{ item.device_id_out == "Manual" ? "Manual" : "---" }} -->
                        </div>
                    </div>
                </template>

                <template v-slot:item.device_in="{ item }">
                    <v-tooltip v-if="item && item.device_in" top color="primary">
                        <template v-slot:activator="{ on, attrs }">
                            <div class="primary--text" v-bind="attrs" v-on="on">
                                {{ (item.device_in && item.device_in.short_name) || "---" }}
                            </div>
                        </template>
                        <div v-for="(iterable, index) in item.device_in" :key="index">
                            <span v-if="index !== 'id'">
                                {{ caps(index) }}: {{ iterable || "---" }}</span>
                        </div>
                    </v-tooltip>
                    <span v-else>---</span>
                </template>

                <template v-slot:item.device_out="{ item }">
                    <v-tooltip v-if="item && item.device_out" top color="primary">
                        <template v-slot:activator="{ on, attrs }">
                            <div class="primary--text" v-bind="attrs" v-on="on">
                                {{ (item.device_out && item.device_out.short_name) || "---" }}
                            </div>
                        </template>
                        <div v-for="(iterable, index) in item.device_out" :key="index">
                            <span v-if="index !== 'id'">
                                {{ caps(index) }}: {{ iterable || "---" }}</span>
                        </div>
                    </v-tooltip>
                    <span v-else>---</span>
                </template>

                <template v-slot:item.status="{ item }">
                    <v-chip :color="
              item?.member?.status == '1' ? 'green lighten-4' : 'red lighten-4'
            " :text-color="
              item?.member?.status == '1' ? 'green darken-2' : 'red darken-2'
            " small label rounded class="ma-1">
                        {{
                        item?.member?.last_membership?.status == "1"
                        ? "Active"
                        : "Expired"
                        }}
                    </v-chip>
                </template>

                <template v-slot:item.actions="{ item }">
                    <v-icon @click="viewItem(item)" small color="primary" class="mr-2"
                        v-if="can('attendance_report_view')">
                        mdi-eye
                    </v-icon>
                </template>
            </v-data-table>
        </v-card>

        <v-row justify="center">
            <v-dialog persistent v-model="log_details" max-width="800px">
                <WidgetsClose left="790" @click="log_details = false" />
                <v-card class="background">
                    <v-alert dense flat class="primary">
                        <span class="white--text">Log Details </span>
                    </v-alert>

                    <v-card-text>
                        <v-alert flat dense class="background">
                            Member Id: <b>{{ log_list[0]?.UserID }}</b>
                            <v-spacer></v-spacer>
                            Total logs
                            <b class="text--text">({{ log_list.length }})</b>
                        </v-alert>
                        <!-- <hr /> -->
                        <v-simple-table class="background" dense>
                            <tbody>
                                <tr>
                                    <td>LogTime</td>
                                    <td>Device</td>
                                    <td>Log Type</td>
                                </tr>
                                <tr v-for="(log, index) in log_list" :key="index">
                                    <td :class="`${log?.device?.name == 'Manual' ? 'red' : ''}--text`">
                                        {{ log.LogTime }}
                                    </td>
                                    <td>{{ log?.device?.name }}</td>
                                    <td>
                                        <b>
                                            <div v-if="log.log_type">
                                                {{ log.log_type }}
                                            </div>
                                            <div v-else>Device</div>
                                        </b>
                                    </td>
                                </tr>
                            </tbody>
                        </v-simple-table>
                    </v-card-text>
                </v-card>
            </v-dialog>
        </v-row>
    </div>
    <NoAccess v-else />
</template>
<script>
export default {
  props: ["title", "shift_type_id", "headers", "payload1", "system_user_id"],

  data: () => ({
    key: 1,
    tableHeight: 750,
    status: "",
    department_ids: "",
    employee_id: "",
    daily_date: "",
    from_date: "",
    to_date: "",
    report_type: "Monthly",
    filters: {},
    totalRowsCount: 0,
    datatable_search_textbox: "",
    snack: false,
    snackColor: "",
    snackText: "",
    date: null,
    menu: false,
    log_details: false,
    overtime: false,
    options: { page: 1 },
    loading: false,
    time_menu: false,
    Model: "Attendance Reports",
    search: "",
    snackbar: false,
    dialog: false,
    from_menu: false,
    to_menu: false,
    ids: [],
    departments: [],
    scheduled_employees: [],
    DateRange: true,
    devices: [],
    valid: true,
    nameRules: [(v) => !!v || "reason is required"],
    timeRules: [(v) => !!v || "time is required"],
    dailyDate: false,
    editItems: {
      shift_type_id: 0,
      attendance_logs_id: "",
      UserID: "",
      device_id: "",
      user_id: "",
      reason: "",
      date: "",
      time: null,
      manual_entry: false,
    },
    loading: false,
    total: 0,

    payload: {
      from_date: null,
      to_date: null,
      daily_date: null,
      employee_id: "",
      department_ids: [],
      status: "-1",
    },
    log_payload: {
      user_id: null,
      device_id: "",
      date: null,
      time: null,
    },
    log_list: [],
    snackbar: false,
    editedIndex: -1,
    editedItem: { name: "" },
    defaultItem: { name: "" },
    response: "",
    data: [],
    shifts: [],
    errors: [],
    custom_options: {},
    max_date: null,
    originalTableHeaders: [],
    clearPagenumber: false,
    baseURL: null,
  }),

  watch: {
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    },
    payload1(value) {
      this.payload = value;
      this.report_type = value.report_type;
      this.department_ids = value.department_ids;
      this.employee_id = value.employee_id;
      this.statuses = value.statuses;

      if (this.payload.from_date == null) {
        this.payload.from_date = this.payload.daily_date;
        this.payload.to_date = this.payload.daily_date;
      }
      this.originalTableHeaders = this.headers;
      this.clearPagenumber = true;
      this.getDataFromApi();
    },

    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  mounted() {
    this.tableHeight = window.innerHeight - 370;
    window.addEventListener("resize", () => {
      this.tableHeight = window.innerHeight - 370;
    });
  },

  created() {
    this.payload = {
      ...this.payload,
      ...this.payload1,
    };
    this.baseURL = `http://${window.location.hostname ?? "localhost"}:8000/api`;
  },

  methods: {
    checkHalfday(item) {
      let currentDay = new Date().toLocaleString("en-US", {
        weekday: "long",
      });

      return item.shift && currentDay === item.shift.halfday;
    },
    update() {
      let log_payload = {
        UserID: this.editItems.UserID,
        LogTime: this.editItems.date + " " + this.editItems.time,
        DeviceID: "Manual",
        company_id: this.$auth.user.company_id,
        log_type: "auto",
      };
      this.loading = true;

      this.$axios
        .post(`/generate_log`, log_payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            // this.render_report(
            //   this.editItems.date,
            //   this.editItems.shift_type_id
            // );
            this.regenerateAttendance(this.editItems);
            this.$emit("close-popup");
            this.snackbar = true;
            this.response = data.message;
            this.getDataFromApi();
            this.dialog = false;
          }
        })
        .catch(({ message }) => {
          this.snackbar = true;
          this.response = message;
        });
    },
    regenerateAttendance({ date, shift_type_id, UserID }) {
      let payload = {
        params: {
          date,
          UserID,
          shift_type_id,
          reason: this.reason,
          company_id: this.$auth.user.company_id,
          user_id: this.$auth.user.id,
          updated_by: this.$auth.user.id,
        },
      };
      this.$axios
        .get("regenerate-attendance", payload)
        .then(({ data }) => {
          this.snackbar = true;
          this.response = "Reprot has been regerated";
          this.loading = false;
          this.$emit("update-data-table");
        })
        .catch((e) => console.log(e));
    },
    caps(str) {
      return str.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
    },

    can(per) {
      return this.$pagePermission.can(per, this);
    },
    getDataFromApi() {
      if (!this.payload.from_date) return false;

      let { page, itemsPerPage } = this.options;

      this.loading = true;

      let payload = {
        page: page,
        per_page: itemsPerPage,
        company_id: this.$auth.user.company_id,
        report_type: this.report_type,
        shift_type_id: this.shift_type_id,
        overtime: this.overtime ? 1 : 0,
        ...this.filters,
        ...this.payload,
      };

      this.$axios.post(`attendance-report-new`, payload).then(({ data }) => {
        if (data.data.length == 0) {
          this.snack = true;
          this.snackColor = "error";
          this.snackText = "No Results Found";
          this.data = [];
          this.total = 0;
          this.loading = false;
          this.totalRowsCount = 0;
          return false;
        }

        this.data = data.data;
        this.total = data.total;
        this.loading = false;
        this.totalRowsCount = data.total;

        if (this.clearPagenumber) {
          this.options.page = 1;
          this.clearPagenumber = false;
        }
      });
    },

    editItem(item) {
      this.dialog = true;
      this.editItems = item;
      this.editItems.UserID = item.employee_id;
      this.editItems.date = item.edit_date;
    },

    viewItem(item) {
      this.log_list = item.attendance_logs;
      console.log(
        "🚀 ~ viewItem ~ item.attendance_logs:",
        item.attendance_logs
      );
      this.log_details = true;
    },

    close() {
      this.dialog = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },
    getShortShiftDetails(item) {
      if (item.shift) {
        let shiftWorkingHours = item.shift.working_hours;
        let employeeHours = item.total_hrs;

        if (
          shiftWorkingHours != "" &&
          employeeHours != "" &&
          shiftWorkingHours != "---" &&
          employeeHours != "---"
        ) {
          let [hours, minutes] = shiftWorkingHours.split(":").map(Number);
          shiftWorkingHours = hours * 60 + minutes;

          [hours, minutes] = employeeHours.split(":").map(Number);
          employeeHours = hours * 60 + minutes;

          if (
            employeeHours < shiftWorkingHours &&
            !this.checkHalfday(item || `---`)
          ) {
            return "Short Shift";
          }
        }
      }
    },
    setStatusLabel(status) {
      const statuses = {
        A: "Absent",
        P: "Present",
        M: "Incomplete",
        LC: "Late In",
        EG: "Early Out",
        O: "Week Off",
        L: "Leave",
        H: "Holiday",
        V: "Vaccation",
      };
      return statuses[status];
    },
  },
};
</script>
