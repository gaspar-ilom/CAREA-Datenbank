<template>
  <div class="home" style="margin: 15px;">

    <div v-if="deleteButtonUnclicked" class="alert alert-info">Bitte wähle eine Abfrage aus, um die Daten anzusehen.</div>

    <template v-for="query in queries">
      <button style="margin: 2px;" class="btn btn-secondary" type="button" v-bind:key="query"
        v-on:click="getQuery(query)">{{ query }}</button>
    </template>
    <br>
    <selectableTable v-bind:resetSelection="resetSelection" v-bind:table="displayTable" v-bind:tableName="displayTableName"/>
  </div>
</template>

<script>
import selectableTable from '@/components/selectableTable.vue'

export default {
  name: 'Queries',
  components: {
    selectableTable
  },
  data () {
    return {
      displayTableName: '',
      displayTable: [],
      resetSelection: false,
      deleteButtonUnclicked: true
    }
  },
  props: {
    tables: Array,
    queries: Array,
    forms: Array
  },
  methods: {
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
    deleteButtonSelection: function () {
      this.deleteButtonUnclicked = false
    }
  }
}
</script>
