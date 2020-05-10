<template>
  <div style="margin: 15px;">
    <!-- init() makes sure component is re-rendered if parent changes the form
      also initializes formInput correctly for the form -->
    <div v-if="init()" class="container">
      <h1 style="text-align: center;">{{ formName }}</h1>
      <div class="border" style="margin: 20px; padding: 20px;" v-for="(curForm, index) in form" :key="tableName(index)">
        <div style="margin: 10px;">
          <h2>{{ tableName(index) }}</h2>

          <form @change="$forceUpdate()" @submit.prevent="submit(tableName(index))">

            <div class="form-group" v-for="field in curForm[tableName(index)]" :key="field.Field">
              <template v-if="field.Type=='number'&&(field.Field==='Person_id'||field.Field==='Seminar_id')">
                <!-- <label>{{ field.Field }}</label>
                <input class="form-control" v-bind:required="!!field.Required" v-model="formInput[tableName(index)][field.Field]" v-bind:type="field.Type" v-bind:placeholder="field.Field" /> -->
              </template>
              <template v-else-if="field.Type=='bool'">
                <label style="margin-right: 0.5em;">{{ field.Field }}</label>
                <input type="checkbox" value="1" v-bind:checked="field.Default==1" />
              </template>
              <template v-else-if="field.Type=='date'">
                <label>{{ field.Field }}</label>
                <input class="form-control col-2" v-bind:required="!!field.Required" v-model="formInput[tableName(index)][field.Field]" type="date"/>
              </template>
              <template v-else-if="field.Type=='string'">
                <template v-if="field.Field.includes('mail')">
                  <label>{{ field.Field }}</label>
                  <input class="form-control" v-bind:required="!!field.Required" v-model="formInput[tableName(index)][field.Field]" type="email" v-bind:placeholder="field.Field" />
                </template>
                <template v-else-if="field.Field=='Geschlecht'">
                  <label>{{ field.Field }} (für automatische Zeugniserstellung etc.)</label><br>
                  <label style="margin-right: 0.5em;">Männlich</label>
                  <input style="margin-right: 2em;" type="radio" v-model="formInput[tableName(index)][field.Field]" value="m">
                  <label style="margin-right: 0.5em;">Weiblich</label>
                  <input style="margin-right: 2em;" type="radio" v-model="formInput[tableName(index)][field.Field]" value="w">
                  <label style="margin-right: 0.5em;">Divers</label>
                  <input style="margin-right: 2em;" type="radio" v-model="formInput[tableName(index)][field.Field]" value="x">
                </template>
                <template v-else-if="field.Field=='Region'">
                  <label>{{ field.Field }}</label><br>
                  <label style="margin-right: 0.5em;">Chiapas</label>
                  <input style="margin-right: 2em;" type="radio" v-model="formInput[tableName(index)][field.Field]" value="Chiapas">
                  <label style="margin-right: 0.5em;">Guatemala</label>
                  <input style="margin-right: 2em;" type="radio" v-model="formInput[tableName(index)][field.Field]" value="Guatemala">
                  <label style="margin-right: 0.5em;">Sonstige</label>
                  <input style="margin-right: 2em;" type="radio" v-model="formInput[tableName(index)][field.Field]" value="x">
                </template>
                <template v-else>
                  <label>{{ field.Field }}</label>
                  <input class="form-control" v-bind:required="!!field.Required" v-model="formInput[tableName(index)][field.Field]" type="text" v-bind:placeholder="field.Field" />
                </template>
              </template>
              <template v-else-if="field.Type=='text'">
                <label>{{ field.Field }}</label>
                <textarea class="form-control" v-bind:required="!!field.Required" v-model="formInput[tableName(index)][field.Field]" v-bind:placeholder="field.Field" />
              </template>
              <template v-else>
                <label>{{ field.Field }}</label>
                <input class="form-control" v-bind:required="!!field.Required" v-model="formInput[tableName(index)][field.Field]" v-bind:type="field.Type" v-bind:placeholder="field.Field" />
              </template>
            </div>

            <button class="btn btn-primary" type="submit">Daten eintragen</button>

            <div v-if="alertEmph==='Erfolg: '" class="alert alert-success"><strong>{{ alertEmph }}</strong>{{ alertMessage }}</div>
            <div v-else-if="alertEmph==='Fehler: '" class="alert alert-danger"><strong>{{ alertEmph }}</strong>{{ alertMessage }}</div>

          </form>

        </div>
      </div>
      <button v-if="form.length>1" class="btn btn-primary" style="margin-left: 20px;" v-on:click="submitAllForms()">Alle Daten eintragen</button>
    </div>
  </div>
</template>

<script>

export default {
  name: 'entryForm',
  data () {
    return {
      initiated: false,
      alertEmph: '',
      alertMessage: '',
      ausreiseId: '',
      formInput: {}
    }
  },
  props: {
    formName: String,
    form: Array,
    personId: String,
    seminarId: String
  },
  computed: {
    tableName: function () {
      const app = this
      return function (index) {
        return Object.keys(app.form[index])[0]
      }
    }
  },
  watch: {
    form: function (val) {
      this.formInput = {}
      this.initiated = false
    }
  },
  methods: {
    init: function () {
      if (this.initiated) {
        return true
      }
      for (var i = 0; i < this.form.length; i++) {
        this.formInput[this.tableName(i)] = {}
        for (var j = 0; j < this.form[i][this.tableName(i)].length; j++) {
          if (this.personId && this.form[i][this.tableName(i)][j].Field === 'Person_id') {
            this.formInput[this.tableName(i)].Person_id = this.personId
          } else if (this.seminarId && this.form[i][this.tableName(i)][j].Field === 'Seminar_id') {
            this.formInput[this.tableName(i)].Seminar_id = this.seminarId
          } else {
            this.formInput[this.tableName(i)][this.form[i][this.tableName(i)][j].Field] = this.form[i][this.tableName(i)][j].Default
          }
        }
      }
      this.initiated = true
      return this.initiated
    },
    postFormData: function (formData) {
      // const app = this
      this.$http.post('api/tables.php', formData)
        .then(function (response) {
          console.log(response.data)
          // app.displayTable = response.data
        })
        .catch(function (error) {
          console.log(error)
        })
    },
    submitAllForms: function () {
      for (var i = 0; i < this.form.length; i++) {
        this.submit(this.tableName(i))
      }
    },
    submit: function (table) {
      console.log('FormInput: ' + JSON.stringify(this.formInput[table]))
      // TODO
      const inputData = this.formInput[table]
      const formData = new FormData()
      const fields = Object.keys(inputData)
      formData.append('table', table)
      for (var i = 0; i < fields.length; i++) {
        formData.append(fields[i], inputData[fields[i]])
      }
      switch (table) {
        case 'person':
          // TODO
          break

        case 'notfallkontakt':
          // TODO
          if (!this.ausreiseId) {
            // TODO handle error
          }
          break

        case 'seminar':
          // TODO make sure correct field entries
          break

        default:
          // TODO
          break
      }
      this.postFormData(formData)
    }
  }
}
</script>
