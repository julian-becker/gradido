<template>
  <b-card
    id="formusercoinanimation"
    class="bg-transparent"
    style="background-color: #ebebeba3 !important; border-radius: 0px"
  >
    <div>
      <b-row class="mb-3">
        <b-col class="mb-2 col-12">
          <small>
            <b>{{ $t('settings.coinanimation.coinanimation') }}</b>
          </small>
        </b-col>
        <b-col class="col-12">
          <b-form-checkbox
            class="Test-BFormCheckbox"
            v-model="CoinAnimationStatus"
            name="check-button"
            switch
            @change="onSubmit"
          >
            {{
              CoinAnimationStatus
                ? $t('settings.coinanimation.True')
                : $t('settings.coinanimation.False')
            }}
          </b-form-checkbox>
        </b-col>
      </b-row>
    </div>
  </b-card>
</template>
<script>
import { updateUserInfos } from '../../../graphql/mutations'
export default {
  name: 'FormUserCoinAnimation',
  data() {
    return {
      CoinAnimationStatus: this.$store.state.coinanimation,
    }
  },
  methods: {
    async onSubmit() {
      this.$apollo
        .mutate({
          mutation: updateUserInfos,
          variables: {
            coinanimation: this.CoinAnimationStatus,
          },
        })
        .then(() => {
          this.$store.commit('coinanimation', this.CoinAnimationStatus)
          this.$toasted.success(
            this.CoinAnimationStatus
              ? this.$t('settings.coinanimation.True')
              : this.$t('settings.coinanimation.False'),
          )
        })
        .catch((error) => {
          this.CoinAnimationStatus = this.$store.state.coinanimation
          this.$toasted.global.error(error.message)
        })
    },
  },
}
</script>
