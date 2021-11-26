import { mount, RouterLinkStub } from '@vue/test-utils'
import ResetPassword from './ResetPassword'
import flushPromises from 'flush-promises'

// validation is tested in src/views/Pages/UserProfile/UserCard_FormUserPasswort.spec.js

const localVue = global.localVue

const apolloQueryMock = jest.fn().mockRejectedValue({ message: 'error' })
const apolloMutationMock = jest.fn()

const toasterMock = jest.fn()
const routerPushMock = jest.fn()

describe('ResetPassword', () => {
  let wrapper

  const mocks = {
    $i18n: {
      locale: 'en',
    },
    $t: jest.fn((t) => t),
    $route: {
      params: {
        optin: '123',
      },
    },
    $toasted: {
      error: toasterMock,
    },
    $router: {
      push: routerPushMock,
    },
    $loading: {
      show: jest.fn(() => {
        return { hide: jest.fn() }
      }),
    },
    $apollo: {
      mutate: apolloMutationMock,
      query: apolloQueryMock,
    },
  }

  const stubs = {
    RouterLink: RouterLinkStub,
  }

  const Wrapper = () => {
    return mount(ResetPassword, { localVue, mocks, stubs })
  }

  describe('mount', () => {
    beforeEach(() => {
      wrapper = Wrapper()
    })

    it.skip('calls the email verification when created', async () => {
      expect(apolloQueryMock).toBeCalledWith(
        expect.objectContaining({ variables: { optin: '123' } }),
      )
    })

    describe('No valid optin', () => {
      it.skip('does not render the Reset Password form when not authenticated', () => {
        expect(wrapper.find('form').exists()).toBeFalsy()
      })

      it.skip('toasts an error when no valid optin is given', () => {
        expect(toasterMock).toHaveBeenCalledWith('error')
      })

      it.skip('has a message suggesting to contact the support', () => {
        expect(wrapper.find('div.header').text()).toContain('settings.password.reset')
        expect(wrapper.find('div.header').text()).toContain('settings.password.not-authenticated')
      })
    })

    describe('is authenticated', () => {
      beforeEach(() => {
        apolloQueryMock.mockResolvedValue({
          data: {
            loginViaEmailVerificationCode: {
              sessionId: 1,
              email: 'user@example.org',
            },
          },
        })
      })

      it.skip('Has sessionId from API call', async () => {
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.sessionId).toBe(1)
      })

      it.skip('renders the Reset Password form when authenticated', () => {
        expect(wrapper.find('div.resetpwd-form').exists()).toBeTruthy()
      })

      describe('Register header', () => {
        it.skip('has a welcome message', async () => {
          expect(wrapper.find('div.header').text()).toContain('settings.password.reset')
          expect(wrapper.find('div.header').text()).toContain(
            'settings.password.reset-password.text',
          )
        })
      })

      describe('links', () => {
        it.skip('has a link "Back"', async () => {
          expect(wrapper.findAllComponents(RouterLinkStub).at(0).text()).toEqual('back')
        })

        it.skip('links to /login when clicking "Back"', async () => {
          expect(wrapper.findAllComponents(RouterLinkStub).at(0).props().to).toBe('/Login')
        })
      })

      describe('reset password form', () => {
        it.skip('has a register form', async () => {
          expect(wrapper.find('form').exists()).toBeTruthy()
        })

        it.skip('has 2 password input fields', async () => {
          expect(wrapper.findAll('input[type="password"]').length).toBe(2)
        })

        it.skip('toggles the first input field to text when eye icon is clicked', async () => {
          wrapper.findAll('button').at(0).trigger('click')
          await wrapper.vm.$nextTick()
          expect(wrapper.findAll('input').at(0).attributes('type')).toBe('text')
        })

        it.skip('toggles the second input field to text when eye icon is clicked', async () => {
          wrapper.findAll('button').at(1).trigger('click')
          await wrapper.vm.$nextTick()
          expect(wrapper.findAll('input').at(1).attributes('type')).toBe('text')
        })
      })

      describe('submit form', () => {
        beforeEach(async () => {
          await wrapper.setData({ authenticated: true, sessionId: 1 })
          await wrapper.vm.$nextTick()
          await wrapper.findAll('input').at(0).setValue('Aa123456_')
          await wrapper.findAll('input').at(1).setValue('Aa123456_')
          await flushPromises()
          await wrapper.find('form').trigger('submit')
        })

        describe('server response with error', () => {
          beforeEach(() => {
            apolloMutationMock.mockRejectedValue({ message: 'error' })
          })
          it.skip('toasts an error message', () => {
            expect(toasterMock).toHaveBeenCalledWith('error')
          })
        })

        describe('server response with success', () => {
          beforeEach(() => {
            apolloMutationMock.mockResolvedValue({
              data: {
                resetPassword: 'success',
              },
            })
          })
          it.skip('calls the API', () => {
            expect(apolloMutationMock).toBeCalledWith(
              expect.objectContaining({
                variables: {
                  sessionId: 1,
                  email: 'user@example.org',
                  password: 'Aa123456_',
                },
              }),
            )
          })

          it.skip('redirects to "/thx/reset"', () => {
            expect(routerPushMock).toHaveBeenCalledWith('/thx/reset')
          })
        })
      })
    })
  })
})
