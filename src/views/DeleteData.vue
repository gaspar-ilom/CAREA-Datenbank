<template>
  <div class="home" style="margin: 15px;">

    <div v-if="deleteButtonUnclicked" class="alert alert-info">Bitte wähle aus, was du löschen möchtest.</div>

    <button class="btn btn-secondary" style="margin: 2px;" type="button" v-on:click="getQuery('Löschbare Notfallkontakte')">
      Löschbare Notfallkontakte löschen
    </button>
    <button class="btn btn-secondary" style="margin: 2px;" type="button" v-on:click="getTable('person')">
      Daten zu einer Person löschen
    </button>
    <button class="btn btn-secondary" style="margin: 2px;" type="button" v-on:click="getQuery('Reader ist bezahlt')">
      Abgeschlossene Readerbestellungen löschen
    </button>

    <div v-if="success" class="alert alert-success" style="margin: 20px;"><strong>Erfolgreich ausgeführt: </strong>{{ success }}</div>

    <selectableTable v-on:selection-changed="setSelected($event)" v-bind:resetSelection="resetSelection" v-bind:table="displayTable" v-bind:tableName="displayTableName"/>

    <div v-if="listPersonData">

      <div v-for='table in getPTables' :key="table">
        <div v-if="tableNotEmpty(table)">
          <h2 style="text-align:center; margin-bottom: 15px;">{{ table }}</h2>

          <table class="table table-hover table-striped table-bordered">
            <thead>
              <tr>
                <th class="table-dark" v-for='col in getPersonHeader(table)' :key="col">{{ col }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for='(row, index) in getPersonBody(table)' :key="index">
                <td v-for='col in getPersonHeader(table)' :key="col">{{ row[col] }}</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

      <button v-if="displayTableHasEntries" class="btn btn-danger" type="button" v-on:click="deletePerson(selected)">
        Jetzt alle Daten zu {{ getFullName }} unwiderruflich löschen
      </button>

    </div>

    <div v-if="aboutToDeleteContacts">
      <button v-if="displayTableHasEntries" class="btn btn-danger" type="button" v-on:click="deleteOldContacts()">
        Jetzt unwiderruflich Notfallkontakte löschen
      </button>
      <div v-else class="alert alert-info">
        <strong>Keine Notfallkontakte gefunden: </strong>
        Überprüfe, ob alle Rückkehrer*innen schon in der Datenbank eingetragen worden sind.
      </div>
    </div>

    <div v-if="aboutToDeleteReaderOrders">
      <button v-if="displayTableHasEntries" class="btn btn-danger" type="button" v-on:click="deletePaidReaderOrders()">
        Jetzt unwiderruflich Bestelldaten löschen
      </button>
      <div v-else class="alert alert-info">
        <strong>Keine abgeschlossenen Readerbestellungen gefunden: </strong>
        Überprüfe, ob alle Bezahlungen und Bestellungen eingetragen worden sind.
      </div>
    </div>

  </div>
</template>

<script>
import selectableTable from '@/components/selectableTable.vue'

export default {
  name: 'DeleteData',
  components: {
    selectableTable
  },
  data () {
    return {
      displayTableName: '',
      displayTable: [],
      personData: [],
      resetSelection: false,
      deleteButtonUnclicked: true,
      success: '',
      selected: ''
    }
  },
  props: {
    tables: Array,
    queries: Array,
    forms: Array,
    personQueries: Array
  },
  computed: {
    aboutToDeleteContacts () {
      return this.displayTableName === 'Löschbare Notfallkontakte'
    },
    aboutToDeleteReaderOrders () {
      return this.displayTableName === 'Reader ist bezahlt'
    },
    listPersonData: function () {
      return this.selected && this.displayTableName === 'person'
    },
    displayTableHasEntries: function () {
      return this.displayTable.length
    },
    tableNotEmpty: function () {
      const app = this
      return function (table) {
        return app.getPersonHeader(table).length > 0
      }
    },
    getPTables: function () {
      return this.personData.reduce((acc, e) => {
        acc.push(Object.keys(e)[0])
        return acc
      }, [])
        .filter(el => {
          return !['seminar', 'person', 'users', 'person_id'].includes(el)
        })
    },
    getPersonHeader: function () {
      // return Object.keys(this.getPersonBody()[0])
      const app = this
      return function (table) {
        for (var i = 0; i < app.personData.length; i++) {
          if (Object.prototype.hasOwnProperty.call(app.personData[i], table) && app.personData[i][table].length > 0) {
            return Object.keys(app.personData[i][table][0])
          }
        }
        return []
      }
    },
    getPersonBody: function () {
      const app = this
      return function (table) {
        for (var i = 0; i < app.personData.length; i++) {
          if (Object.prototype.hasOwnProperty.call(app.personData[i], table) && app.personData[i][table].length > 0) {
            return app.personData[i][table]
          }
        }
        return []
      }
    },
    getFullName: function () {
      if (!this.selected) {
        return ''
      }
      const app = this
      const personRow = this.displayTable.filter(row => {
        return row.id === app.selected
      })
      return personRow[0].Vorname + ' ' + personRow[0].Name
    }
  },
  methods: {
    getTable: function (table) {
      this.resetSelection = !this.resetSelection
      this.displayTableName = table
      this.deleteButtonSelection()
      const app = this
      this.$http.get('api/tables.php?table=' + table)
        .then(function (response) {
          console.log(response.data)
          app.displayTable = response.data
        })
        .catch(function (error) {
          console.log(error)
        })
    },
    getQuery: function (table) {
      this.resetSelection = !this.resetSelection
      this.displayTableName = table
      this.deleteButtonSelection()
      const app = this
      this.$http.get('api/queries.php?query=' + table)
        .then(function (response) {
          console.log(response.data)
          app.displayTable = response.data
        })
        .catch(function (error) {
          console.log(error)
        })
    },
    getPersonData: async function (personId) {
      const app = this
      console.log('Getting person data')
      if (!personId || !this.listPersonData) {
        this.personData = []
        return
      }
      this.personData = []
      function storePersonData (key, response) {
        console.log('PD: ' + response.data + ' ' + key)
        const obj = { } // new Object()
        obj[key] = response.data
        app.personData.push(obj)
      }
      for (var i = 0; i < this.personQueries.length; i++) {
        this.$http.get('api/queries.php?person_query=' + this.personQueries[i] + '&person_id=' + personId)
          .then(storePersonData.bind(null, app.personQueries[i]))
          .catch(function (error) {
            console.log(error)
          })
      }
    },
    deleteButtonSelection: function () {
      this.deleteButtonUnclicked = false
    },
    resetSuccess: async function (message) {
      function sleep (ms) {
        return new Promise(resolve => {
          setTimeout(() => {
            resolve('resolved')
          }, ms)
        })
      }
      this.success = message
      this.deleteButtonUnclicked = true
      await sleep(5000)
      this.success = ''
    },
    deleteOldContacts: function () {
      const app = this
      const formData = new FormData()
      formData.append('deletion', 'Löschbare Notfallkontakte')
      this.$http.post('api/queries.php', formData)
        .then(function (response) {
          console.log('DELETION ' + response.data)
          app.resetSuccess('Alte Notfallkontakte gelöscht.')
          app.getQuery('Löschbare Notfallkontakte')
        })
        .catch(function (error) {
          console.log(error)
        })
    },
    deletePaidReaderOrders: function () {
      const app = this
      const formData = new FormData()
      formData.append('deletion', 'Bezahlte Readerbestellungen löschen')
      this.$http.post('api/queries.php', formData)
        .then(function (response) {
          console.log('DELETION ' + response.data)
          app.resetSuccess('Bezahlte und abgeschlossene Readerbestellungen gelöscht.')
          app.getQuery('Reader ist bezahlt')
        })
        .catch(function (error) {
          console.log(error)
        })
    },
    deletePerson: function (personId) {
      const app = this
      const formData = new FormData()
      formData.append('deletion', 'Person löschen')
      formData.append('person_id', personId)
      this.$http.post('api/queries.php', formData)
        .then(function (response) {
          console.log('DELETION ' + response.data)
          app.resetSuccess('Alle Daten zu ' + app.getFullName + ' wurden gelöscht.')
          app.getTable('person')
          app.getPersonData(personId)
        })
        .catch(function (error) {
          console.log(error)
        })
    },
    setSelected: function (selected) {
      console.log('selection event handled.')
      this.selected = selected
      this.getPersonData(selected)
    }
  }
}
</script>
