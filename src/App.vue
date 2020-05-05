<template>
  <div id="app">
    <div id="nav">
      <dbNavbar v-if="isLoggedIn"></dbNavbar>
    </div>
    <div v-if="alert&&!isLoggedIn" class="alert alert-danger"><strong>Ausgeloggt: </strong>Du wurdest ausgeloggt, weil dein Sitzungstoken abgelaufen ist. Bitte neu einloggen.</div>
    <router-view v-bind:tables="tables" v-bind:queries="queries" v-bind:personQueries="personQueries" v-bind:seminars="seminars" v-bind:forms="forms"/>
  </div>
</template>

<script>
// @ is an alias to /src
import dbNavbar from '@/components/dbNavbar.vue'
import store from '@/store'
import { AUTH_LOGOUT } from '@/store/actions/auth'

export default {
  name: 'App',
  components: {
    dbNavbar
  },
  data () {
    return {
      tables: [],
      queries: [],
      personQueries: [],
      seminars: [],
      forms: [],
      alert: false
    }
  },
  computed: {
    isLoggedIn: function () {
      return store.getters.isAuthenticated
    }
  },
  created () {
    const app = this
    this.unsubscribe = store.subscribe((mutation, state) => {
      if (store.getters.getToken) {
        app.alert = false
        app.$http.defaults.headers.common.Authorization = 'Bearer ' + store.getters.getToken
        app.fetchData()
      }
    })
    this.$http.interceptors.response.use(undefined, function (err) {
      return new Promise(function (resolve, reject) {
        if (err.response.status === 401 && err.response.config && !err.response.config.__isRetryRequest) {
          app.alert = true
          app.logout()
        }
        throw err
      })
    })
  },
  mounted () {
    if (this.isLoggedIn) {
      this.fetchData()
    }
  },
  beforeDestroy () {
    this.unsubscribe()
  },
  methods: {
    logout: function () {
      console.log('Logging out.')
      this.$store.dispatch(AUTH_LOGOUT)
        .then(() => {
          this.$router.push('/login')
        })
    },
    fetchData: function () {
      this.getTables()
      this.getQueries()
      this.getPersonQueries()
      this.getSeminars()
      // this.getForms();
    },
    getTables: function () {
      console.log('getTables')
      const app = this
      this.$http.get('api/tables.php')
        .then(function (response) {
          console.log('getTables success')
          console.log(response.data)
          app.tables = response.data
        })
        .catch(function (error) {
          console.log('getTables error')
          console.log(error)
        })
    },
    getQueries: function () {
      const app = this
      this.$http.get('api/queries.php')
        .then(function (response) {
          console.log(response.data)
          app.queries = response.data
        })
        .catch(function (error) {
          console.log(error)
        })
    },
    getSeminars: function () {
      const app = this
      this.$http.get('api/tables.php?table=seminar')
        .then(function (response) {
          console.log(response.data)
          app.seminars = response.data.reverse()
        })
        .catch(function (error) {
          console.log(error)
        })
    },
    getPersonQueries: function () {
      const app = this
      this.$http.get('api/queries.php?person_query=list')
        .then(function (response) {
          console.log(response.data)
          app.personQueries = response.data
        })
        .catch(function (error) {
          console.log(error)
        })
    },
    getForms: function () {
      const app = this
      this.$http.get('api/forms.php')
        .then(function (response) {
          console.log(response.data)
          app.forms = response.data
        })
        .catch(function (error) {
          console.log(error)
        })
    }
  }
}
</script>

<style src="../public/css/bootstrap.min.css"></style>
