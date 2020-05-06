<template>
  <div style="margin: 15px;">
    <h1 style="text-align: center;">Benutzerkonto erstellen</h1>
    <div class="wrapper" style="margin: auto; margin-top: 0px;">
      <form @submit.prevent="register()">
        <div v-if="alertEmph==='Erfolg: '" class="alert alert-success"><strong>{{ alertEmph }}</strong>{{ alertMessage }}</div>
        <div v-if="alertEmph==='Fehler: '" class="alert alert-danger"><strong>{{ alertEmph }}</strong>{{ alertMessage }}</div>
        <div class="form-group">
          <label>Benutzername</label>
          <input class="form-control" required v-model="username" type="text" placeholder="Benutzername" />
        </div>
        <div class="form-group">
          <label>Passwort</label>
          <input
            required
            v-model="password"
            type="password"
            placeholder="Passwort"
            class="form-control"
          />
        </div>
        <div class="form-group">
          <label>Passwort bestätigen </label>
          <input
            required
            v-model="passwordConfirm"
            type="password"
            placeholder="Passwort wiederholen"
            class="form-control"
          />
        </div>
        <button class="btn btn-primary" type="submit">Registrieren</button>
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

export default {
  name: 'Register',
  data () {
    return {
      username: '',
      password: '',
      passwordConfirm: '',
      alertEmph: '',
      alertMessage: ''
    }
  },
  methods: {
    register: function () {
      const app = this
      if (this.password !== this.passwordConfirm) {
        app.alertEmph = 'Fehler: '
        app.alertMessage = 'Passwörter stimmen nicht überein.'
        return false
      }
      const formData = new FormData()
      formData.append('username', this.username)
      formData.append('password', this.password)
      formData.append('confirm_password', this.passwordConfirm)
      this.$http.post('api/register.php', formData)
        .then(resp => {
          console.log(resp.data)
          app.username = ''
          app.password = ''
          app.passwordConfirm = ''
          app.alertEmph = 'Erfolg: '
          app.alertMessage = 'Ein neuer Benutzer wurde angelegt.'
          // app.$router.push('/')
          return true
        })
        .catch(err => {
          console.log(err)
          app.alertEmph = 'Fehler: '
          app.alertMessage = 'Benutzer wurde nicht angelegt.'
          Object.keys(err.response.data).forEach(element => {
            if (err.response.data[element]) {
              app.alertMessage += '\n' + err.response.data[element]
            }
          })
          app.username = ''
          app.password = ''
          app.passwordConfirm = ''
          return false
        })
    }
  }
}
</script>
