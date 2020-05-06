<template>
  <div style="margin: 15px;">
    <h1 style="text-align: center;">Benutzerkonto erstellen</h1>
    <div class="wrapper" style="margin: auto; margin-top: 0px;">
      <form @submit.prevent="resetPassword()">
        <div v-if="alertEmph==='Erfolg: '" class="alert alert-success"><strong>{{ alertEmph }}</strong>{{ alertMessage }}</div>
        <div v-if="alertEmph==='Fehler: '" class="alert alert-danger"><strong>{{ alertEmph }}</strong>{{ alertMessage }}</div>
        <div class="form-group">
          <label>Altes Passwort</label>
          <input
            required
            v-model="oldPassword"
            type="password"
            placeholder="Altes Passwort"
            class="form-control"
          />
        </div>
        <div class="form-group">
          <label>Neues Passwort</label>
          <input
            required
            v-model="password"
            type="password"
            placeholder="Neues Passwort"
            class="form-control"
          />
        </div>
        <div class="form-group">
          <label>Neues Passwort bestätigen </label>
          <input
            required
            v-model="passwordConfirm"
            type="password"
            placeholder="Neues Passwort wiederholen"
            class="form-control"
          />
        </div>
        <button class="btn btn-primary" type="submit">Passwort ändern</button>
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
  name: 'ResetPassword',
  data () {
    return {
      oldPassword: '',
      password: '',
      passwordConfirm: '',
      alertEmph: '',
      alertMessage: ''
    }
  },
  methods: {
    resetPassword: function () {
      const app = this
      if (this.password !== this.passwordConfirm) {
        app.alertEmph = 'Fehler: '
        app.alertMessage = 'Passwörter stimmen nicht überein.'
        return false
      }
      const formData = new FormData()
      formData.append('old_password', this.oldPassword)
      formData.append('new_password', this.password)
      formData.append('confirm_password', this.passwordConfirm)
      this.$http.post('api/reset_password.php', formData)
        .then(resp => {
          console.log(resp.data)
          app.oldPassword = ''
          app.password = ''
          app.passwordConfirm = ''
          app.alertEmph = 'Erfolg: '
          app.alertMessage = 'Das Passwort wurde geändert.'
          // app.logout()?
          return true
        })
        .catch(err => {
          console.log(err)
          app.alertEmph = 'Fehler: '
          app.alertMessage = 'Passwort wurde nicht geändert.'
          Object.keys(err.response.data).forEach(element => {
            if (err.response.data[element]) {
              app.alertMessage += '\n' + err.response.data[element]
            }
          })
          app.oldPassword = ''
          app.password = ''
          app.passwordConfirm = ''
          return false
        })
    }
  }
}
</script>
