<template>
  <div style="margin: 15px;">
    <h1 style="text-align: center;">CAREA Datenbank</h1>
    <div class="wrapper" style="margin: auto; margin-top: 20px;">
      <form @submit.prevent="login">
        <h1>Login</h1>
        <p>Please fill in your credentials to login.</p>
        <div v-if="alertMessage" class="alert alert-danger"><strong>{{ alertEmph }}</strong>{{ alertMessage }}</div>
        <div class="form-group">
          <label>Username</label>
          <input class="form-control" required v-model="username" type="text" placeholder="Username" />
        </div>
        <div class="form-group">
          <label>Password</label>
          <input
            required
            v-model="password"
            type="password"
            placeholder="Password"
            class="form-control"
          />
        </div>
        <button class="btn btn-primary" type="submit">Login</button>
      </form>
    </div>
  </div>
</template>

<style>
  .wrapper {
    width: 350px;
    padding: 20px;
  }
</style>

<script>
import { AUTH_REQUEST } from '../store/actions/auth'

export default {
  name: 'Login',
  data () {
    return {
      username: '',
      password: '',
      alertEmph: '',
      alertMessage: ''
    }
  },
  methods: {
    login: function () {
      const { username, password } = this
      this.$store.dispatch(AUTH_REQUEST, { username, password }).then(() => {
        this.$router.push('/')
      })
        .catch(a => {
          this.username = ''
          this.password = ''
          this.alertEmph = 'Fehler: '
          this.alertMessage = 'Benutzername und/oder Passwort sind nicht korrekt.'
        })
    }
  }
}
</script>
