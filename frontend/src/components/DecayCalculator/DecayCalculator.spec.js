import { mount } from '@vue/test-utils'
import DecayCalculator from './DecayCalculator.vue'

const localVue = global.localVue

describe('Status', () => {
  let wrapper

  const mocks = {
    $n: jest.fn((n) => n),
    $t: jest.fn((t) => t),
  }

  const Wrapper = () => {
    return mount(DecayCalculator, { localVue, mocks })
  }

  describe('mount', () => {
    beforeEach(() => {
      wrapper = Wrapper()
    })

    describe('I can call the div', () => {
      it('I can call the id', () => {
        expect(wrapper.find('div#decay_calculator').exists()).toBeTruthy()
      })
    })
  })
})
