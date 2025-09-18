<template>
  <v-dialog persistent v-model="viewDialog" max-width="800px" :key="memberId">
    <WidgetsClose left="790" @click="viewDialog = false" />
    <template v-slot:activator="{ on, attrs }">
      <span v-bind="attrs" v-on="on">
        <v-icon color="text" small>mdi-repeat</v-icon>
        Change Plan
      </span>
    </template>
    <v-card flat class="background">
      <v-alert dense class="primary white--text">
        Change Plan for Member #{{ memberId }}
      </v-alert>

      <v-card-text>
        <v-container>
          <v-row>
            <v-col cols="12">
              <v-container>
                <v-row>
                  <v-col cols="12">
                    <v-autocomplete class="background"
                      v-model="plan_id"
                      :items="plans"
                      item-text="name"
                      item-value="id"
                      label="Select New Plan"
                      dense
                      outlined
                      hide-details
                      @change="handlePlanChange"
                    />
                  </v-col>
                  <!-- Current Plan -->
                  <v-col cols="12" md="5" class="d-flex">
                    <v-card
                      outlined
                      class="pa-3 d-flex flex-column fill-height background"
                      style="width: 100%; border-radius: 10px"
                    >
                      <v-card-subtitle
                        class="text-uppercase font-weight-bold pa-0"
                      >
                        Current Plan
                      </v-card-subtitle>
                      <v-divider class="mb-3" />
                      <v-list dense class="background">
                        <v-list-item>
                          <v-list-item-content>
                            <v-list-item-title>Name</v-list-item-title>
                            <v-list-item-subtitle>{{
                              existing_plan?.name
                            }}</v-list-item-subtitle>
                          </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                          <v-list-item-content>
                            <v-list-item-title>Price</v-list-item-title>
                            <v-list-item-subtitle>{{
                              existing_plan?.price
                            }}</v-list-item-subtitle>
                          </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                          <v-list-item-content>
                            <v-list-item-title>Description</v-list-item-title>
                            <v-list-item-subtitle>{{
                              existing_plan?.description
                            }}</v-list-item-subtitle>
                          </v-list-item-content>
                        </v-list-item>
                        <v-list-item>
                          <v-list-item-content>
                            <v-list-item-title>Features</v-list-item-title>
                            <v-list-item-subtitle>
                              <ul>
                                <li
                                  v-for="(
                                    feature, i
                                  ) in existing_plan?.features"
                                  :key="i"
                                >
                                  {{ feature }}
                                </li>
                              </ul>
                            </v-list-item-subtitle>
                          </v-list-item-content>
                        </v-list-item>
                      </v-list>
                    </v-card>
                  </v-col>
                  <v-col
                    cols="12"
                    md="2"
                    class="d-flex align-center justify-center"
                  >
                    <!-- Your centered content here -->
                    <v-btn fab color="primary" small>
                      <v-icon>mdi-arrow-right</v-icon></v-btn
                    >
                  </v-col>
                  <!-- New Plan -->
                  <v-col cols="12" md="5" class="d-flex">
                    <v-card
                      outlined
                      class="pa-3 d-flex flex-column fill-height background"
                      style="width: 100%; border-radius: 10px"
                    >
                      <v-card-subtitle
                        class="text-uppercase font-weight-bold pa-0"
                      >
                        New Plan
                      </v-card-subtitle>
                      <v-divider class="mb-3" />
                      <div v-if="planPreview?.id" class="flex-grow-1">
                        <v-list dense class="background">
                          <v-list-item>
                            <v-list-item-content>
                              <v-list-item-title>Name</v-list-item-title>
                              <v-list-item-subtitle>{{
                                planPreview?.name
                              }}</v-list-item-subtitle>
                            </v-list-item-content>
                          </v-list-item>
                          <v-list-item>
                            <v-list-item-content>
                              <v-list-item-title>Price</v-list-item-title>
                              <v-list-item-subtitle>{{
                                planPreview?.price
                              }}</v-list-item-subtitle>
                            </v-list-item-content>
                          </v-list-item>
                          <v-list-item>
                            <v-list-item-content>
                              <v-list-item-title>Description</v-list-item-title>
                              <v-list-item-subtitle>{{
                                planPreview?.description
                              }}</v-list-item-subtitle>
                            </v-list-item-content>
                          </v-list-item>
                          <v-list-item>
                            <v-list-item-content>
                              <v-list-item-title>Features</v-list-item-title>
                              <v-list-item-subtitle>
                                <ul>
                                  <li
                                    v-for="(
                                      feature, i
                                    ) in planPreview?.features"
                                    :key="i"
                                  >
                                    {{ feature }}
                                  </li>
                                </ul>
                              </v-list-item-subtitle>
                            </v-list-item-content>
                          </v-list-item>
                        </v-list>
                      </div>
                      <div
                        v-else
                        class="grey--text text--darken-1 text-center my-auto"
                      >
                        No new plan selected
                      </div>
                    </v-card>
                  </v-col>

                  <v-col
                    cols="12"
                    v-if="
                      existing_plan?.price !== undefined &&
                      planPreview?.price !== undefined
                    "
                  >
                    <table style="width: 100%">
                      <tr>
                        <td colspan="5"><b>Other Info</b></td>
                      </tr>
                      <tr>
                        <td class="text-center"><b>Start</b></td>
                        <td class="text-center"><b>End</b></td>
                        <td class="text-center"><b>Day Exempted</b></td>
                        <td class="text-center"><b>Price Exempted</b></td>
                        <td class="text-center"><b>Price Difference</b></td>
                      </tr>
                      <tr>
                        <td class="text-center">
                          <div>
                            {{
                              memberObject?.last_membership
                                ?.show_plan_start_date
                            }}
                          </div>
                        </td>
                        <td class="text-center">
                          <div>
                            {{
                              memberObject?.last_membership?.show_plan_end_date
                            }}
                          </div>
                        </td>
                        <td class="text-center">
                          <div>{{ exemptedDays }} / {{ totalDays }}</div>
                        </td>
                         <td class="text-center">
                          <div>{{ usedPrice }} </div>
                        </td>
                        <td class="text-center">
                          <div v-if="planPreview.id !== existing_plan.id">
                            {{
                              `(${planPreview.price} x ${totalMonths}) - ${usedPrice} = ${calculatedPrice}`
                            }}
                          </div>
                          <div class="" v-else>No Price Change</div>
                        </td>
                      </tr>
                    </table>
                  </v-col>
                </v-row>
              </v-container>
            </v-col>

            <v-col class="text-right" cols="12">
              <v-btn
                @click="update"
                small
                color="primary"
                :disabled="plan_id == existing_plan.id"
                >Submit</v-btn
              >
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
<script>
export default {
  props: ["memberId", "memberObject"],
  data: () => ({
    viewDialog: false,
    editForm: false,
    image: "",
    previewImage: null,
    cropedImage: "",
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
    rightDrawer: false,
    drawer: true,
    selectedItem: 1,
    on: "",
    color: "background",
    files: "",
    Model: "Member",
    endpoint: "member",
    loading: false,
    response: "",
    snackbar: false,
    errors: [],
    titleItems: ["Mr", "Mrs", "Miss", "Ms", "Dr"],

    plans: [],
    existing_plan: {
      id: null,
      name: null,
      price: null,
      description: null,
      features: [],
    },
    plan_id: null,
    planPreview: null,
  }),

  watch: {
    dialog(val) {
      val || this.close();
    },
  },

  async created() {
    await this.getPlans();
    if (this.memberObject?.last_membership) {
      this.planPreview = this.existing_plan =
        this.memberObject?.last_membership?.plan;
      this.plan_id = this.memberObject?.last_membership?.plan_id;
    }
  },
  computed: {
    exemptedDays() {
      let { plan_start_date } = this.memberObject?.last_membership;

      const planStartDate = new Date(plan_start_date);
      const today = new Date(); // current date

      planStartDate.setHours(0, 0, 0, 0);
      today.setHours(0, 0, 0, 0);

      const diffInMs = today - planStartDate;

      const MS_PER_DAY = 1000 * 60 * 60 * 24;

      return Math.floor(diffInMs / MS_PER_DAY);
    },

    totalDays() {
      let { plan_start_date, plan_end_date } =
        this.memberObject?.last_membership;

      const planStartDate = new Date(plan_start_date);
      const planEndDate = new Date(plan_end_date);

      // Normalize time to midnight
      planStartDate.setHours(0, 0, 0, 0);
      planEndDate.setHours(0, 0, 0, 0);

      const MS_PER_DAY = 1000 * 60 * 60 * 24;

      const diffInMs = planEndDate - planStartDate;

      return Math.floor(diffInMs / MS_PER_DAY) + 1;
    },
    totalMonths() {
      let { plan_start_date, plan_end_date } =
        this.memberObject?.last_membership;

      const start = new Date(plan_start_date);
      const end = new Date(plan_end_date);

      const years = end.getFullYear() - start.getFullYear();
      const months = end.getMonth() - start.getMonth();
      const total = years * 12 + months;

      // Optional: if end date's day >= start date's day, count the last month
      if (end.getDate() >= start.getDate()) {
        return total + 1;
      } else {
        return total;
      }
    },

    perDayPrice() {
      const planPrice = this.memberObject?.last_membership?.plan?.price;
      return (planPrice * this.totalMonths) / this.totalDays;
    },

    usedPrice() {
      return (this.perDayPrice * this.exemptedDays).toFixed(2);
    },
    calculatedPrice() {
      return (
        this.planPreview.price * this.totalMonths -
        this.usedPrice
      ).toFixed(2);
    },
  },
  methods: {
    async getPlans() {
      try {
        const { data } = await this.$axios.get(`/plans-list`);
        this.plans = data;
      } catch (error) {
        console.error("Failed to fetch plans", error);
      }
    },

    getPlanDetails(plan_id) {
      const found = this.plans.find((e) => e.id == plan_id);
      if (found) {
        this.existing_plan = { ...found };
      }
    },

    handlePlanChange(planId) {
      const found = this.plans.find((p) => p.id === planId);
      this.planPreview = found ? { ...found } : null;
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
      this.planPreview = null;
      this.plan = { id: null };
      this.$emit("close-popup");
    },

    onpick_attachment() {
      this.$refs.attachment_input.click();
    },

    async update() {
      if (!this.planPreview?.id) return;

      this.loading = true;
      try {
        const { data } = await this.$axios.post(
          `/membership-update/${this.memberObject?.last_membership.id}`,
          {
            plan_id: this.planPreview.id,
            total: this.planPreview.price,
          }
        );

        if (!data.status) {
          this.errors = data.errors || {};
        } else {
          this.errors = {};
          this.snackbar = true;
          this.response = "Member Plan Updated successfully";
          this.$emit("eventFromchild");
          this.viewDialog = false
        }
      } catch (e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
 <style scoped>
      table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
      }

      td,
      th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
      }
    </style>