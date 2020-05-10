<template>
  <div class="home" style="margin: 15px;">

    <div v-if="deleteButtonUnclicked" class="alert alert-info">Bitte wähle ein Eingabeformular aus, um Daten einzugeben.</div>

    <template v-for="form in forms">
      <button style="margin: 2px;" class="btn btn-secondary" type="button" v-bind:key="form" v-on:click="getForm(form)">
        {{ form }}
      </button>
    </template>

    <entryForm v-bind:formName="displayFormName" v-bind:form="displayForm"></entryForm>

  </div>
</template>

<script>
import entryForm from '@/components/forms/entryForm.vue'

export default {
  name: 'Forms',
  components: {
    entryForm
  },
  data () {
    return {
      displayFormName: '',
      displayTable: [],
      displayForm: [],
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
    }
  }
}
</script>
