import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import ServiceCard from './ServiceCard.vue'

describe('ServiceCard', () => {
  it('muestra el nombre del servicio', () => {
    const servicio = {
      id: 1,
      nombre: 'Desarrollo Web',
      descripcion: 'Aplicaciones web',
      precio: 8500
    }
    const wrapper = mount(ServiceCard, { props: { servicio } })
    expect(wrapper.text()).toContain('Desarrollo Web')
  })

  it('muestra el precio del servicio', () => {
    const servicio = {
      id: 1,
      nombre: 'Desarrollo Web',
      descripcion: 'Aplicaciones web',
      precio: 8500
    }
    const wrapper = mount(ServiceCard, { props: { servicio } })
    expect(wrapper.text()).toContain('8500')
  })

  it('emite el evento seleccionar al hacer clic', async () => {
    const servicio = {
      id: 1,
      nombre: 'Desarrollo Web',
      descripcion: 'Aplicaciones web',
      precio: 8500
    }
    const wrapper = mount(ServiceCard, { props: { servicio } })
    await wrapper.find('button').trigger('click')
    expect(wrapper.emitted('seleccionar')).toBeTruthy()
    expect(wrapper.emitted('seleccionar')[0][0]).toEqual(servicio)
  })
})