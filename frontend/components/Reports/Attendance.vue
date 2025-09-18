<template>
  <v-container fluid v-if="can(`attendance_report_access`)">
    <v-card
      class="background"
      elevation="0"
      v-if="can(`attendance_report_view`)"
    >
      <v-card-text>
         <v-row>
            <!-- Title -->
            <v-col
              cols="12"
              md="2"
              class="d-flex align-center"
              style="max-width: 250px"
            >
              <div style="font-size: 18px; font-weight: 600">
                Attendance Reports
              </div>
            </v-col>

            <!-- Members Filter -->
            <v-col>
              <v-autocomplete
                dense
                outlined
                v-model="payload.employee_id"
                :items="members"
                multiple
                item-value="system_user_id"
                item-text="full_name"
                placeholder="Employees"
                hide-details
                label="Members"
              >
                <!-- Select All -->
                <template v-if="members.length" #prepend-item>
                  <v-list-item @click="toggleEmployeesSelection">
                    <v-list-item-action>
                      <v-checkbox
                        @click.stop="toggleEmployeesSelection"
                        v-model="selectAllEmployees"
                        :indeterminate="isIndeterminateEmployee"
                        :true-value="true"
                        :false-value="false"
                      />
                    </v-list-item-action>
                    <v-list-item-content>
                      <v-list-item-title>
                        {{ selectAllEmployees ? "Unselect All" : "Select All" }}
                      </v-list-item-title>
                    </v-list-item-content>
                  </v-list-item>
                </template>

                <!-- Selection Display -->
                <template v-slot:selection="{ item, index }">
                  <span v-if="index === 0 && payload.employee_id.length === 1">
                    {{ item.name_with_user_id }}
                  </span>
                  <span
                    v-else-if="
                      index === 1 &&
                      payload.employee_id.length === members.length
                    "
                  >
                    All Selected
                  </span>
                  <span v-else-if="index === 1">
                    {{ payload.employee_id.length }} Employee(s)
                  </span>
                </template>
              </v-autocomplete>
            </v-col>

            <!-- Status Filter -->
            <v-col>
              <v-autocomplete
                dense
                outlined
                v-model="payload.status"
                :items="[
                  { id: '0', name: 'Expired' },
                  { id: '1', name: 'Active' },
                ]"
                multiple
                item-value="id"
                item-text="name"
                placeholder="Status"
                hide-details
                label="Status"
              />
            </v-col>

            <!-- Date Filter -->
            <v-col>
              <div style="width: 250px">
                <CustomFilter
                  @filter-attr="filterAttr"
                  :defaultFilterType="1"
                  height="50px"
                />
              </div>
            </v-col>

            <!-- Generate Button -->
            <v-col class="text-right">
              <v-btn
                color="primary"
                class="white--text"
                style="border-radius: 5px; min-width: 120px"
                @click="commonMethod"
              >
                Generate
              </v-btn>
            </v-col>
          </v-row>
      </v-card-text>
    </v-card>
    <v-card class="mt-5 background">
      <v-card-text>
        <ReportsTable
          :key="1"
          :shift_type_id="shift_type_id"
          title="General Reports"
          :headers="generalHeaders"
          :report_template="report_template"
          :payload1="payload11"
          render_endpoint="render_general_report"
        />
      </v-card-text>
    </v-card>
  </v-container>
  <NoAccess v-else />
</template>
<script>
import generalHeaders from "../../headers/general.json";

export default {
  props: ["title", "render_endpoint"],

  data: () => ({
    missingLogsDialog: false,
    key: 1,
    shift_type_id: 0,
    payload11: null,
    selectAllDepartment: false,
    selectAllEmployees: false,
    selectAllEmployeesForRegenerate: [],
    branches: [],
    tab: null,
    generalHeaders,
    date: null,
    menu: false,
    loading: false,
    Model: "Attendance Reports",
    endpoint: "report",
    search: "",
    dialog: false,
    from_date: null,
    from_menu: false,
    to_date: null,
    departments: [],
    members: [],

    report_template: "Template1",
    report_type: "Monthly",
    regenerateDialog: false,
    form: {
      company_id: "",
      employee_ids: "",
      from_date: "",
      to_date: "",
    },
    message: "",
    alertType: "success",
    payload: {
      from_date: null,
      to_date: null,
      employee_id: [],
      department_ids: [{ id: "-1", name: "" }],
      statuses: [],
      branch_id: null,
    },
    log_payload: {
      user_id: null,
      device_id: "",
      date: null,
      time: null,
    },
    data: [],
    statuses: [],
    isCompany: true,
    showTabs: { single: true, double: true, multi: true },
  }),

  computed: {
    isIndeterminateDepartment() {
      return (
        this.payload.department_ids.length > 0 &&
        this.payload.department_ids.length < this.departments.length
      );
    },
    isIndeterminateEmployee() {
      return (
        this.payload.employee_id.length > 0 &&
        this.payload.employee_id.length < this.members.length
      );
    },
    isIndeterminateEmployeeForRegenerate() {
      return (
        this.form.employee_ids.length > 0 &&
        this.form.employee_ids.length < this.members.length
      );
    },
  },

  watch: {
    dialog(val) {
      val || this.close();
      this.search = "";
    },
    selectAllDepartment(value) {
      if (value) {
        this.payload.department_ids = this.departments.map((e) => e.id);
      } else {
        this.payload.department_ids = [];
      }
    },
    selectAllEmployees(value) {
      if (value) {
        this.payload.employee_id = this.members.map((e) => e.system_user_id);
      } else {
        this.payload.employee_id = [];
      }
    },
    selectAllEmployeesForRegenerate(value) {
      if (value) {
        this.form.employee_ids = this.members.map((e) => e.system_user_id);
      } else {
        this.form.employee_ids = [];
      }
    },
  },
  async created() {
    this.payload.daily_date = new Date().toJSON().slice(0, 10);
    this.payload.department_ids = [];

    this.getScheduledEmployees();

    let dt = new Date();
    let y = dt.getFullYear();
    let m = dt.getMonth() + 1;
    let dd = new Date(dt.getFullYear(), m, 0);

    m = m < 10 ? "0" + m : m;

    this.payload.from_date = `${y}-${m}-01`;
    this.payload.from_date = `${y}-${m}-${dd.getDate()}`;
    this.payload.to_date = `${y}-${m}-${dd.getDate()}`;
    setTimeout(() => {
      this.tab = "tab-2";
    }, 1000);
    setTimeout(() => {
      this.tab = "tab-3";
    }, 1000);
    setTimeout(() => {
      this.tab = "tab-1";
    }, 1000);
    await this.getStatuses();
  },

  methods: {
    async getStatuses() {
      let { data } = await this.$axios.get(`attendance-statuses`);
      this.statuses = data;
    },
    openMissingPopup() {
      this.missingLogsDialog = true;
    },
    process_file_in_child_comp(val) {
      if (this.payload.employee_id && this.payload.employee_id.length == 0) {
        alert("Employee not selected");
        return;
      }

      let type = val.toLowerCase();

      let process_file_endpoint = "";

      if (this.shift_type_id == 2 || this.shift_type_id == 5) {
        process_file_endpoint = "multi_in_out_";
      }

      let path = this.$backendUrl + "/" + process_file_endpoint + type;

      let qs = ``;

      qs += `${path}`;
      qs += `?report_template=${this.report_template}`;
      qs += `&main_shift_type=${this.shift_type_id}`;

      if (parseInt(this.payload.branch_id) > 0)
        qs += `&branch_id=${this.payload.branch_id}`;

      qs += `&shift_type_id=${this.shift_type_id}`;
      qs += `&company_id=${this.$auth.user.company_id}`;
      // qs += `&status=${this.payload.status & this.payload.status || "-1"}`;
      if (
        this.payload.department_ids &&
        this.payload.department_ids.length > 0
      ) {
        qs += `&department_ids=${this.payload.department_ids.join(",")}`;
      }
      qs += `&employee_id=${this.payload.employee_id}`;
      qs += `&report_type=${this.report_type}`;

      qs += `&from_date=${this.from_date}&to_date=${this.to_date}`;

      // Convert showTabs object into a URL-friendly format
      if (this.payload.showTabs) {
        qs += `&showTabs=${encodeURIComponent(
          JSON.stringify(this.payload.showTabs)
        )}`;
      }
      console.log(qs);
      let report = document.createElement("a");
      report.setAttribute("href", qs);
      report.setAttribute("target", "_blank");
      report.click();
    },
    toggleDepartmentSelection() {
      this.selectAllDepartment = !this.selectAllDepartment;
    },
    toggleEmployeesSelection() {
      this.selectAllEmployees = !this.selectAllEmployees;
    },
    toggleEmployeesSelectionForRegenerate() {
      this.selectAllEmployeesForRegenerate =
        !this.selectAllEmployeesForRegenerate;
    },

    filterAttrForRegenerate(data) {
      this.form.from_date = data.from;
      this.form.to_date = data.to;
      this.form.company_id = this.$auth.user.company_id;
    },
    filterAttr(data) {
      this.from_date = data.from;
      this.to_date = data.to;
      this.filterType = "Monthly"; // data.type;
    },
    commonMethod(id = 0) {
      if (this.$auth.user.user_type == "department") {
        this.payload.department_ids = [this.$auth.user.department_id];
      }

      this.payload11 = {
        ...this.payload,
        report_type: "Monthly",
        tabselected: id, //this.tab
        from_date: this.from_date,
        to_date: this.to_date,
        filterType: this.filterType,
        showTabs: JSON.stringify(this.showTabs),
        key: this.key++,
      };

      this.getScheduledEmployees();
    },
    getScheduledEmployees() {
      let options = {
        params: {
          per_page: 1000,
        },
      };

      this.$axios.get(`/members-list`, options).then(({ data }) => {
        this.members = data;
        console.log("🚀 ~ this.$axios.get ~ this.members:", this.members);
      });
    },
    async getDepartments() {
      let config = {
        params: {
          branch_id: this.payload.branch_id,
          company_id: this.$auth.user.company_id,
        },
      };
      try {
        const { data } = await this.$axios.get(`department-list`, config);
        this.departments = data;
        this.toggleDepartmentSelection();
        setTimeout(() => {
          this.commonMethod();
        }, 3000);
      } catch (error) {
        console.error("Error fetching departments:", error);
      }
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    regnerateReport() {
      if (!this.payload.branch_id) {
        alert("Branch must be selected");
        return;
      }
      this.loading = true;
      let payload = {
        params: {
          dates: [this.form.from_date, this.form.to_date],
          company_ids: [this.$auth.user.company_id],
          user_id: this.$auth.user.id,
          updated_by: this.$auth.user.id,
          reason: "",
          employee_ids: this.form.employee_ids,
          shift_type_id: this.shift_type_id,
          company_id: this.$auth.user.company_id,
          showTabs: JSON.stringify(this.showTabs),
        },
      };
      // return;
      this.$axios
        .get("render_logs", payload)
        .then(({ data }) => {
          this.loading = false;

          let message = "";
          data.forEach((element) => {
            message = message + " \n " + element;
          });
          this.message = message;
          this.loading = false;

          this.$emit("update-data-table");
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
