<template>
  <v-app class="background">
    <!-- <style scoped>
      .animated-border {
        padding: 3px;
        border-radius: 5px;
        background: linear-gradient(270deg, #42a5f5, #7e57c2, #ef5350, #42a5f5);
        background-size: 600% 600%;
        animation: borderMove 8s ease infinite;
        transition: box-shadow 0.3s ease-in-out;
      }

      .animated-border:hover {
        box-shadow: 0 0 20px rgba(66, 165, 245, 0.5);
      }

      .animated-border .v-card {
        border-radius: 5px; /* Slightly smaller to show border */
        background-color: #1e1e2f !important;
      }

      @keyframes borderMove {
        0% {
          background-position: 0% 50%;
        }
        50% {
          background-position: 100% 50%;
        }
        100% {
          background-position: 0% 50%;
        }
      }
    </style> -->
    <v-container fluid fill-height>
      <v-row align="center" justify="center">
        <v-col cols="12" sm="8" md="3" style="max-width: 330px">
          <div class="animated-border">
            <v-card class="pa-10 background darken-1" elevation="10">
              <v-card-title class="text-h5 justify-center">
                <v-img src="/logo22.png" max-height="60" contain></v-img>
              </v-card-title>
              <v-form ref="form" method="post" v-model="valid">
                <v-text-field
                  label="Email"
                  v-model="email"
                  hide-details
                  required
                  dense
                  outlined
                  class="mb-4"
                ></v-text-field>
                <v-text-field
                  hide-details
                  dense
                  outlined
                  :rules="passwordRules"
                  :append-icon="show_password ? 'mdi-eye' : 'mdi-eye-off'"
                  :type="show_password ? 'text' : 'password'"
                  v-model="password"
                  @click:append="show_password = !show_password"
                  class="mb-2"
                ></v-text-field>

                <v-checkbox
                  v-model="remember"
                  label="Remember Me"
                  dense
                  hide-details
                  class="mt-0 mb-2"
                />

                <span
                  v-if="msg"
                  class="error--text"
                  style="
                    font-size: 12px;
                    display: inline-block;
                    margin-bottom: 10px;
                  "
                >
                  {{ msg }}
                </span>
                <v-btn
                  block
                  small
                  :loading="loading"
                  @click="login"
                  class="mt-2"
                  color="primary"
                >
                  Log in
                </v-btn>
              </v-form>
            </v-card>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </v-app>
</template>

<script>
export default {
  layout: "login",
  data: () => ({
    remember: false,
    logo: "/logo1.png",
    valid: true,
    loading: false,
    snackbar: false,
    email: "admin",
    password: "admin",
    show_password: false,
    msg: "",
    emailRules: [
      (v) => !!v || "E-mail is required",
      (v) => /.+@.+\..+/.test(v) || "E-mail must be valid",
    ],

    passwordRules: [(v) => !!v || "Password is required"],
  }),
  methods: {
    login() {
      if (this.$refs.form.validate()) {
        this.msg = "";
        this.loading = true;
        let credentials = {
          email: this.email,
          password: this.password,
        };
        this.$auth
          .loginWith(
            "local",
            { data: credentials },
            {
              "Access-Control-Allow-Origin": "*",
            }
          )
          .then(({ data }) => {
            this.$auth.user_verified_mobileotp = true;
            const updatedUser = Object.assign({}, this.$auth.user, {
              is_verified: 1,
            });
            this.$auth.setUser(updatedUser);
            if (this.remember) {
              localStorage.setItem("remember_email", this.email);
              localStorage.setItem("remember_password", this.password);
            } else {
              localStorage.removeItem("remember_email");
              localStorage.removeItem("remember_password");
            }

            this.$router.push(`/`);
            // return;
          })
          .catch(({ response }) => {
            if (!response) {
              return false;
            }
            let { status, data, statusText } = response;
            this.msg = status == 422 ? data.message : statusText;
            setTimeout(() => (this.loading = false), 2000);
          });
      }
    },
  },
};
</script>
