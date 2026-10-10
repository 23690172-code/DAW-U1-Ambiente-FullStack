import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import LoginForm from './LoginForm.vue'

describe('LoginForm', () => {
  it('emite las credenciales capturadas', async () => {
    const wrapper = mount(LoginForm)

    await wrapper.get('#usuario').setValue('admin')
    await wrapper.get('#contrasena').setValue('123456')
    await wrapper.get('form').trigger('submit')

    expect(wrapper.emitted('submit')).toBeTruthy()
    expect(wrapper.emitted('submit')[0][0]).toEqual({
      usuario: 'admin',
      contrasena: '123456'
    })
  })
})