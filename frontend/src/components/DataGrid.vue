<script setup>
import { computed, ref, watch } from 'vue'
import * as XLSX from 'xlsx'
import { jsPDF } from 'jspdf'
import { autoTable } from 'jspdf-autotable'

const props = defineProps({
  rows: {
    type: Array,
    default: () => []
  },
  columns: {
    type: Array,
    required: true
  },
  title: {
    type: String,
    default: 'Datos'
  },
  fileName: {
    type: String,
    default: 'datos'
  }
})

const search = ref("")
const pageSize = ref(10)
const currentPage = ref(1)

function displayValue(row, column) {
  const value = row[column.key]
  return column.formatter ? column.formatter(value, row) : value
}

const searchableColumns = computed(() => 
  props.columns.filter(column => column.searchable !== false && column.key !== 'actions')
)

const filteredRows = computed(() => {
  const text = search.value.trim().toLowerCase()
  if (!text) {
    return props.rows
  }
  return props.rows.filter(row => 
    searchableColumns.value.some(column => 
      String(displayValue(row, column) ?? '').toLowerCase().includes(text)
    )
  )
})

const totalPages = computed(() => 
  Math.max(1, Math.ceil(filteredRows.value.length / pageSize.value)) 
)

const paginatedRows = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return filteredRows.value.slice(start, start + pageSize.value)
})

// Reiniciar a la página 1 si se busca algo nuevo o se cambia el tamaño de página
watch([search, pageSize], () => {
  currentPage.value = 1
})

// Si se eliminan registros y la página actual queda fuera de rango, retroceder
watch(totalPages, (value) => {
  if (currentPage.value > value) {
    currentPage.value = value
  }
})

const exportColumns = computed(() => 
  props.columns.filter(column => column.export !== false && column.key !== 'actions')
)

function exportRows() {
  return filteredRows.value.map(row => {
    const result = {}
    exportColumns.value.forEach(column => {
      result[column.label] = displayValue(row, column)
    })
    return result
  })
}

function exportExcel() {
  const worksheet = XLSX.utils.json_to_sheet(exportRows())
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Datos')
  XLSX.writeFile(workbook, `${props.fileName}.xlsx`)
}

function exportPDF() {
  const doc = new jsPDF({ 
    orientation: exportColumns.value.length > 5 ? 'landscape' : 'portrait' 
  })
  doc.setFontSize(16)
  doc.text(props.title, 14, 15)
  
  autoTable(doc, {
    startY: 22,
    head: [ exportColumns.value.map(column => column.label) ],
    body: filteredRows.value.map(row => 
      exportColumns.value.map(column => displayValue(row, column))
    ),
    styles: { fontSize: 8 },
    theme: 'striped'
  })
  
  doc.save(`${props.fileName}.pdf`)
}
</script>

<template>
  <section class="data-grid">
    <div class="grid-toolbar">
      <div>
        <label>
          Buscar
          <input v-model="search" type="search" placeholder="Filtrar registros...">
        </label>
      </div>
      <div class="grid-actions">
        <button type="button" class="btn-export excel" @click="exportExcel">Excel</button>
        <button type="button" class="btn-export pdf" @click="exportPDF">PDF</button>
      </div>
    </div>

    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th v-for="column in columns" :key="column.key">{{ column.label }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in paginatedRows" :key="row.id">
            <td v-for="column in columns" :key="column.key">
              <slot v-if="column.key === 'actions'" name="actions" :row="row" />
              <span v-else>{{ displayValue(row, column) }}</span>
            </td>
          </tr>
          <tr v-if="paginatedRows.length === 0">
            <td :colspan="columns.length" class="empty-state">No existen registros.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <footer class="grid-footer">
      <div>
        Registros: <strong>{{ filteredRows.length }}</strong>
      </div>
      <div>
        <select v-model.number="pageSize">
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
        </select> por página
      </div>
      <div class="pagination">
        <button :disabled="currentPage === 1" @click="currentPage--">Anterior</button>
        <span>Página {{ currentPage }} de {{ totalPages }}</span>
        <button :disabled="currentPage === totalPages" @click="currentPage++">Siguiente</button>
      </div>
    </footer>
  </section>
</template>