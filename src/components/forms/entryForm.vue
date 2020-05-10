<template>
  <div style="margin: 15px;">
    <div class="container">
      <h1 style="text-align: center;">{{ formName }} eintragen</h1>
      <div class="border" style="margin: 20px; padding: 20px;" v-for="(curForm, index) in form" :key="tableName(index)">
        <div v-if="init()" style="margin: 10px;">
          <h2>{{ tableName(index) }}</h2>

          <form @change="$forceUpdate()" @submit.prevent="submit()">

            <div class="form-group" v-for="field in curForm[tableName(index)]" :key="field.Field">
              <template v-if="field.Type=='bool'">
                <label style="margin-right: 0.5em;">{{ field.Field }}</label>
                <input type="checkbox" value="1" v-bind:checked="field.Default==1" />
              </template>
              <template v-else-if="field.Type=='date'">
                <label>{{ field.Field }}</label>
                <input class="form-control col-2" v-bind:required="!!field.Required" v-model="formInput[tableName(index)][field.Field]" v-bind:type="field.Type"/>
              </template>
              <template v-else-if="field.Type=='string'">
                <template v-if="field.Field.includes('mail')">
                  <label>{{ field.Field }}</label>
                  <input class="form-control" v-bind:required="!!field.Required" v-model="formInput[tableName(index)][field.Field]" type="email" v-bind:placeholder="field.Field" />
                </template>
                <template v-else-if="field.Field=='Geschlecht'">
                  <label>{{ field.Field }}</label><br>
                  <label style="margin-right: 0.5em;">Männlich</label>
                  <input style="margin-right: 2em;" type="radio" v-model="formInput[tableName(index)][field.Field]" value="m">
                  <label style="margin-right: 0.5em;">Weiblich</label>
                  <input style="margin-right: 2em;" type="radio" v-model="formInput[tableName(index)][field.Field]" value="w">
                  <label style="margin-right: 0.5em;">Anderes</label>
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
            <div v-if="alertEmph==='Fehler: '" class="alert alert-danger"><strong>{{ alertEmph }}</strong>{{ alertMessage }}</div>
          </form>

        </div>
      </div>
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
      personId: '',
      seminarId: '',
      ausreiseId: '',
      formInput: {}
    }
  },
  props: {
    formName: String,
    form: Array
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
          this.formInput[this.tableName(i)][this.form[i][this.tableName(i)][j].Field] = this.form[i][this.tableName(i)][j].Default
        }
      }
      this.initiated = true
      return this.initiated
    },
    submit: function () {
      console.log(JSON.stringify(this.formInput))
      // TODO
    }

  }
}
</script>
