<template>
  <div
    style="width: 100%"
    v-if="can('dashboard_access') && can('dashboard_view')"
  >
    <SubscriptionExpiry />

    <v-container fluid>
      <DashboardCards />
    </v-container>

    <v-container fluid>
      <v-row>
        <v-col cols="6">
          <v-row class="align-stretch">
            <!-- Peak Hours Card -->
            <v-col cols="12" class="fill-height">
              <v-card elevation="3" class="d-flex flex-column fill-height background">
                <v-card-title>Peak Hours</v-card-title>
                <v-card-text class="pa-0 flex-grow-1">
                  <div class="fill-height">
                    <DashboardPeakHoursChart />
                  </div>
                </v-card-text>
              </v-card>
            </v-col>

            <!-- Live Logs Card -->
            <v-col cols="12" class="fill-height">
              <v-card elevation="3" class="d-flex flex-column fill-height background">
                <v-card-title class="d-flex align-center justify-space-between">
                  <span>Live Logs</span>
                </v-card-title>
                <v-card-text class="pa-0 flex-grow-1">
                  <div class="fill-height">
                    <DashboardLogs />
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-col>
        <v-col cols="6">
          <v-row class="align-stretch">
            <!-- Peak Hours Card -->

            <!-- Members Card -->
            <v-col cols="12" class="fill-height">
              <v-card elevation="3" class="d-flex flex-column fill-height background">
                <v-card-title class="d-flex align-center justify-space-between">
                  <span>Members</span>
                </v-card-title>
                <v-card-text class="pa-0 flex-grow-1">
                  <div class="fill-height">
                    <DashboardMembers />
                  </div>
                </v-card-text>
              </v-card>
            </v-col>

            <!-- Plans Card -->
            <v-col cols="12" class="fill-height">
              <v-card elevation="3" class="d-flex flex-column fill-height background">
                <v-card-title class="d-flex align-center justify-space-between">
                  <span>Plans</span>
                </v-card-title>
                <v-card-text class="pa-0 flex-grow-1">
                  <div class="fill-height">
                    <DashboardPlans v-if="can('plan_access')" />
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </v-container>
  </div>

  <NoAccess v-else />
</template>

<script>
export default {
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
  },
};
</script>
