<template>
  <div class="home" style="margin: 15px;">

    <div v-if="deleteButtonUnclicked" class="alert alert-info">Bitte wähle ein Eingabeformular aus, um Daten einzugeben.</div>

    <template v-for="form in forms">
      <button style="margin: 2px;" class="btn btn-secondary" type="button" v-bind:key="form" v-on:click="getForm(form)">
        {{ form }}
      </button>
    </template>

    <template v-if="requirePersonId&&personTable">
      <selectableTable v-on:selection-changed="setPersonId($event)" v-bind:table="personTable" tableName="Wähle aus, für wen du Daten eintragen möchtest"/>
    </template>

    <template v-if="requireSeminarId&&seminarTable">
      <selectableTable v-on:selection-changed="setSeminarId($event)" v-bind:table="seminarTable" tableName="Wähle das Seminar aus, zu dem du Daten eintragen möchtest?"/>
    </template>

    <entryForm v-on:required-prop="fetchProp($event)" v-bind:formName="displayFormName" v-bind:form="displayForm" v-bind:personId="personId" v-bind:seminarId="seminarId"></entryForm>

  </div>
</template>

<script>
import selectableTable from '@/components/selectableTable.vue'
import entryForm from '@/components/forms/entryForm.vue'

export default {
  name: 'Forms',
  components: {
    selectableTable, entryForm
  },
  data () {
    return {
      displayFormName: '',
      personTable: [],
      seminarTable: [],
      displayForm: [],
      resetSelection: false,
      deleteButtonUnclicked: true,
      requirePersonId: false,
      requireSeminarId: false,
      personId: '',
      seminarId: ''
    }
  },
  props: {
    tables: Array,
    queries: Array,
    forms: Array
  },
  methods: {
    getForm: function (form) {
      this.resetSelection = !this.resetSelection
      this.displayFormName = form
      this.deleteButtonSelection()
      this.displayForm = []
      const app = this
      this.$http.get('api/forms.php?form=' + form)
        .then(function (response) {
          console.log(response.data)
          const keys = Object.keys(response.data)
          for (var i = 0; i < keys.length; i++) {
            const key = keys[i]
            const obj = { } // new Object()
            obj[key] = response.data[key]
            app.displayForm.push(obj)
          }
        })
        .catch(function (error) {
          console.log(error)
        })
    },
    deleteButtonSelection: function () {
      this.deleteButtonUnclicked = false
      this.requirePersonId = false
      this.requireSeminarId = false
      this.personId = ''
      this.seminarId = ''
    },
    fetchProp: function (property) {
      const app = this
      let table = ''
      switch (property) {
        case 'Person_id':
          this.requirePersonId = true
          table = 'person'
          break

        case 'Seminar_id':
          this.requireSeminarId = true
          table = 'seminar'
          break

        default:
          return
      }
      console.log(property)
      this.$http.get('api/tables.php?table=' + table)
        .then(function (response) {
          console.log(response.data)
          switch (table) {
            case 'person':
              app.personTable = response.data
              break

            case 'seminar':
              app.seminarTable = response.data
              break
          }
        })
        .catch(function (error) {
          console.log(error)
        })
    },
    setPersonId: function (id) {
      this.personId = id
    },
    setSeminarId: function (id) {
      this.seminarId = id
    }
  }
}

</script>
