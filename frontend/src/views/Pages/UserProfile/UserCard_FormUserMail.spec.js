import { mount } from '@vue/test-utils'
import flushPromises from 'flush-promises'
import UserCardFormUserMail from './UserCard_FormUserMail'

const localVue = global.localVue
jest.spyOn(window, 'alert').mockImplementation(() => {})

const mockAPIcall = jest.fn()

describe('UserCard_FormUserMail', () => {
  let wrapper

  const mocks = {
    $t: jest.fn((t) => t),
    $store: {
      state: {
        sessionId: 1,
        email: 'user@example.org',
        firstName: 'Peter',
        lastName: 'Lustig',
        description: '',
      },
    },
    $apollo: {
      query: mockAPIcall,
    },
  }

  const Wrapper = () => {
    return mount(UserCardFormUserMail, { localVue, mocks })
  }

  describe('mount', () => {
    beforeEach(() => {
      wrapper = Wrapper()
    })

    it('renders the component', () => {
      expect(wrapper.find('div#formusermail').exists()).toBeTruthy()
    })

    it('renders the edit link', () => {
      expect(wrapper.find('a[href="#formusermail"]').exists()).toBeTruthy()
    })

    it('renders the E-Mail form.change', () => {
      expect(wrapper.findAll('div.col').at(0).text()).toBe('E-Mail form.change')
    })

    it('renders the E-Mail', () => {
      expect(wrapper.findAll('div.col').at(1).text()).toBe('E-Mail')
    })

    it('renders the E-Mail Adress', () => {
      expect(wrapper.findAll('div.col').at(2).text()).toBe('user@example.org')
    })

    describe('edit user data', () => {
      beforeEach(async () => {
        await wrapper.find('a[href="#formusermail"]').trigger('click')
        await flushPromises()
        await wrapper.findAll('input').at(0).setValue('test@example.org')
        await flushPromises()
      })

      it('enter email in input field', () => {
        expect(wrapper.find('input').element.value).toBe('test@example.org')
      })

      describe('error API send', () => {
        beforeEach(async () => {
          mockAPIcall.mockRejectedValue({
            message: 'Ouch!',
          })
          await wrapper.find('a[href="#formusermail"]').trigger('click')
          await flushPromises()
        })

        it('send request with filled variables to the API', async () => {
          expect(mockAPIcall).toHaveBeenCalledWith(
            expect.objectContaining({
              variables: {
                sessionId: 1,
                email: 'user@example.org',
                newEmail: 'test@example.org',
              },
            }),
          )
        })

        it('error message is send to the window.alert', async () => {
          expect(window.alert).toBeCalledWith('Ouch!')
        })
      })

      describe('successful API send', () => {
        beforeEach(async () => {
          mockAPIcall.mockResolvedValue({
            data: {
              updateUserInfos: {
                validValues: 1,
              },
            },
          })
          await wrapper.find('a[href="#formusermail"]').trigger('click')
          await flushPromises()
        })

        it('send request with filled variables to the API', async () => {
          expect(mockAPIcall).toHaveBeenCalledWith(
            expect.objectContaining({
              variables: {
                sessionId: 1,
                email: 'user@example.org',
                newEmail: 'test@example.org',
              },
            }),
          )
        })

        it('successful message is send to the window.alert', async () => {
          expect(window.alert).toBeCalledWith('changePassword success')
        })
      })
    })
  })
})
