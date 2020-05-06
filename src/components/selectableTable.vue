<template>
  <div class="filteredTable">
    <h2 style="text-align:center; margin-bottom: 15px;">{{ tableName }}</h2>

    <div v-if="table.length" class="input-group mb-3">
      <input
        type="text"
        class="form-control"
        v-model="filterKey"
        placeholder="Eingabe um Daten zu filtern. Dann auf eine Zeile klicken."
      />
      <div class="input-group-append">
        <button class="btn btn-primary" type="button" v-on:click="deselect">Auswahl aufheben</button>
      </div>
    </div>

    <table v-if="table.length" class="table table-hover table-striped table-bordered">
      <thead>
        <tr>
          <th v-if="getHeader.length" class="table-dark">#</th>
          <th class="table-dark" v-for="col in getHeader" v-bind:key="col">{{ col }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, index) in filteredTable" v-bind:key="index" v-on:click="setSelector(row)">
          <!-- <td v-for="col in getHeader" v-bind:key="col">{{ row[col] }}</td> -->
          <template v-if="row.hasOwnProperty('Absage')&&row.Absage==1" >
            <td class="table-danger" v-if="getHeader.length">{{ index }}</td>
            <td class="table-danger" v-for='col in getHeader' v-bind:key="col">{{ row[col] }}</td>
          </template>
          <template v-else>
            <td v-if="getHeader.length">{{ index }}</td>
            <td v-for='col in getHeader' v-bind:key="col">{{ row[col] }}</td>
          </template>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'selectableTable',
  data () {
    return {
      filterKey: '',
      filterRow: '',
      selector: ''
    }
  },
  props: ['table', 'tableName', 'resetSelection'],
  watch: {
    getSelected: function (val) {
      this.$emit('selection-changed', val)
    },
    resetSelection: function (newval) {
      this.deselect()
    }
  },
  computed: {
    getHeader: function () {
      if (this.table.length) {
        return Object.keys(this.table[0])
      }
      return []
    },
    filterKeys: function () {
      return this.filterKey.split(' ')
    },
    filteredTable: function () {
      if (!this.table.length) {
        return []
      }
      return this.table.filter((p, i) => {
        if (this.selector && p.id) {
          // eslint-disable-next-line eqeqeq
          return p.id == this.selector
        }
        if (this.filterRow !== '') {
          return JSON.stringify(this.filterRow) === JSON.stringify(p)
        }
        return this.filterKeys.reduce((acc, k) => {
          var result = false
          Object.values(p).forEach(v => {
            if (v && typeof v === 'string' && v.includes(k)) {
              result = true
              return true
            }
          })
          return acc && result
        }, true)
      })
    },
    getSelected: function () {
      if (this.selector) {
        return this.selector
      }
      if (this.filteredTable.length === 1 && Object.prototype.hasOwnProperty.call(this.filteredTable[0], 'id')) {
        return this.filteredTable[0].id
      }
      if (this.filterRow) {
        return this.filterRow
      }
      return ''
    }
  },
  methods: {
    setSelector: function (row) {
      this.deselect()
      if (!row) {
        return
      }
      if (Object.prototype.hasOwnProperty.call(row, 'id') && row.id) {
        this.selector = row.id
      } else {
        this.filterRow = row
      }
    },
    deselect: function () {
      this.selector = ''
      this.filterRow = ''
      this.filterKey = ''
    }
  }
}
</script>
