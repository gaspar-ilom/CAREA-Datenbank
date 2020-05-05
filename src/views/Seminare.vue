<template>
  <div class="home" style="margin: 15px;">

    <div v-if="deleteButtonUnclicked" class="alert alert-info">Bitte wähle ein Seminar aus, um die Daten anzusehen.
    </div>

    <template v-for="seminar in seminars">
      <button style="margin: 2px;" class="btn btn-secondary" type="button" v-bind:key="seminar.id"
        v-on:click="selectSeminar(seminar)">{{ seminar.Bezeichnung }}</button>
    </template>
    <br>
    <button v-if="displayTableName" style="margin-left: 2px;" class="btn btn-success float-right" type="button" v-on:click="toggleIntern()">{{ isIntern }}</button>
    <selectableTable v-bind:resetSelection="resetSelection" v-bind:table="displayTable" v-bind:tableName="displayTableName"/>
  </div>
</template>

<script>
import selectableTable from '@/components/selectableTable.vue'

export default {
  name: 'Seminare',
  components: {
    selectableTable
  },
  data () {
    return {
      displayTableName: '',
      displayTable: [],
      intern: 'TN Liste Intern',
      resetSelection: false,
      deleteButtonUnclicked: true
    }
  },
  computed: {
    isIntern: function () {
      if (this.intern === 'TN Liste Intern') {
        return 'Zeige TN Liste Extern'
      }
      return 'Zeige TN Liste Intern'
    }
  },
  props: {
    tables: Array,
    queries: Array,
    forms: Array,
    seminars: Array
  },
  methods: {
    getSeminarQuery: function (table) {
      this.displayTableName = this.intern + ': ' + table.Bezeichnung
      const app = this
      this.$http.get('api/queries.php?seminar_query=' + this.intern + '&seminar_id=' + table.id)
        .then(function (response) {
          console.log(response.data)
          app.displayTable = response.data
        })
        .catch(function (error) {
          console.log(error)
        })
    },
    setIntern: function (intern) {
      if (intern) {
        this.intern = 'TN Liste Intern'
      } else {
        this.intern = 'TN Liste Extern'
      }
    },
    toggleIntern: function () {
      if (this.intern === 'TN Liste Intern') {
        this.intern = 'TN Liste Extern'
      } else {
        this.intern = 'TN Liste Intern'
      }
      const curBez = this.displayTableName.split(': ')[1]
      this.getSeminarQuery(this.seminars.filter(s => {
        return s.Bezeichnung === curBez
      })[0])
      this.resetSelection = !this.resetSelection
    },
    selectSeminar: function (table) {
      this.resetSelection = !this.resetSelection
      this.setIntern(true)
      this.getSeminarQuery(table)
      this.deleteButtonSelection()
    },
    deleteButtonSelection: function () {
      this.deleteButtonUnclicked = false
    }
  }
}
</script>
