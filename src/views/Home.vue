<template>
  <div class="home" style="margin: 15px;">

    <div style="text-align:center;">
      <h1>Willkommen in der CAREA-Datenbank</h1>
    </div>

    <div class="container-fluid">
      <div class="row" style="margin-bottom: 30px;">
        <ul class="list-group offset-1"/>

        <tableList v-on:show-table="getTable($event)" v-bind:tables="tables" v-bind:activeTable="displayTableName" tableName="Tabellen"/>
        <tableList v-on:show-table="getQuery($event)" v-bind:tables="queries" v-bind:activeTable="displayTableName" tableName="Abfragen"/>
        <tableList v-bind:tables="forms" v-bind:activeTable="displayTableName" tableName="Eingabeformulare"/>

      </div>
    </div>

    <selectableTable v-bind:table="displayTable" v-bind:tableName="displayTableName"/>
  </div>
</template>

<script>
import selectableTable from '@/components/selectableTable.vue'
import tableList from '@/components/tableList.vue'

export default {
  name: 'Home',
  components: {
    selectableTable, tableList
  },
  data () {
    return {
      displayTableName: '',
      displayTable: []
    }
  },
  props: {
    tables: Array,
    queries: Array,
    forms: Array,
    seminars: Array
  },
  methods: {
    getTable: function (table) {
      this.displayTableName = table
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
      this.displayTableName = table
      const app = this
      this.$http.get('api/queries.php?query=' + table)
        .then(function (response) {
          console.log(response.data)
          app.displayTable = response.data
        })
        .catch(function (error) {
          console.log(error)
        })
    }
  }
}
</script>
