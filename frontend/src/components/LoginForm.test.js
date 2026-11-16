import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import LoginForm from './LoginForm.vue'

describe('LoginForm', () => {
  it('emite las credenciales capturadas', async () => {
    const wrapper = mount(LoginForm)
    
    // Buscar los inputs por su ID y escribir
    await wrapper.find('#usuario').setValue('admin')
    await wrapper.find('#contrasena').setValue('123456')
    
    // Disparar el submit del formulario
    await wrapper.find('form').trigger('submit')
    
    // Verificar que se emitió el evento
    expect(wrapper.emitted('submit')).toBeTruthy()
    expect(wrapper.emitted('submit')[0][0]).toEqual({
      usuario: 'admin',
      contrasena: '123456'
    })
  })
})