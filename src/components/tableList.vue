<template>
  <ul class="list-group list-hover offset-1 col-2">
    <h2 style="text-align:center;">{{ tableName }}</h2>
    <a class="list-group-item list-group-item-action list-group-item-secondary" :class="{ 'active': activeTable === table }"
    v-for='table in tables' v-bind:key="table" v-on:click="$emit('show-table', table)" href="#">
      {{ table }}
    </a>
  </ul>
</template>

<script>
export default {
  name: 'tableList',
  props: ['tables', 'tableName', 'activeTable']
}
</script>
