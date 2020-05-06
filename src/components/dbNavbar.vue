<template>
  <div
    class="container"
    style="text-align:center; margin-right: 0px; margin-bottom: 40px; padding: 20px;"
  >
    <nav class="navbar navbar-expand-lg navbar-light bg-light justify-content-center fixed-top">
      <a class="navbar-brand" href="https://carea-menschenrechte.de">
        <img
          src="../assets/navbar_logo.png"
          alt="CAREA e.V."
          style="filter: invert(100%); height: 1.4eM;"
        />
      </a>
      <ul class="nav nav-pills" style="text-color: white;">
        <li v-for="nav in nav_items" v-bind:key="nav.name" class="nav-item">
          <router-link class="nav-link" v-bind:to="nav.to">{{ nav.name }}</router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link" to="register">Benutzer registrieren</router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link disabled" to="Passwort_ändern">Passwort ändern</router-link>
        </li>
        <li class="nav-item">
          <a class="nav-link btn-danger" href="" v-on:click="logout()" >Logout</a>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
import { AUTH_LOGOUT } from '../store/actions/auth'
export default {
  name: 'dbNavbar',
  data () {
    return {
      nav_items: [{ name: 'Übersicht', to: '/' },
        { name: 'Seminare', to: '/events' },
        { name: 'Abfragen', to: '/queries' },
        { name: 'Eingabeformulare', to: '/forms' },
        { name: 'Daten löschen', to: '/delete-data' }]
    }
  },
  methods: {
    logout: function () {
      console.log('Logging out.')
      this.$store.dispatch(AUTH_LOGOUT)
        .then(() => {
          this.$router.push('/login')
        })
    }
  }
}
</script>
