<template>
  <div>
    <b-list-group>
      <b-list-group-item v-if="count > 5">
        <router-link
          to="/transactions"
          v-html="$t('transaction.show_all', { count: count })"
        ></router-link>
      </b-list-group-item>
    </b-list-group>
  </div>
</template>

<script>
export default {
  name: 'GddTransactionListFooter',
  props: {
    count: { count: Number },
  },
}
</script>
