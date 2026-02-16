<template>
  <div class="container">
    <div class="pageHeader">
      <p class="pageTitle">Menu Builder</p>
      <button class="btnPrimary" @click="openNew">
        New Menu Item
      </button>
    </div>

    <div v-if="err" class="err">{{ err }}</div>

    <div class="card">
      <div class="dtControls">
        <div class="dtLeft">
          <span>Show</span>
          <select class="dtSelect" v-model.number="pageSize" @change="page=1">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
          <span>entries</span>

          <label class="dtToggle">
            <input type="checkbox" v-model="showInactive" />
            Show Inactive
          </label>
        </div>

        <div class="dtRight">
          <label class="dtSearchLabel">Search:</label>
          <input class="dtSearchInput" v-model.trim="q" />
        </div>
      </div>

      <table class="table striped compact">
        <thead>
        <tr>
          <th>Menu Name</th>
          <th>Parent Name</th>
        </tr>
        </thead>

        <tbody>
        <tr v-for="r in paged" :key="r.id">
          <td>
            <a href="#" class="link" @click.prevent="openEdit(r)">
              {{ r.name }}
            </a>

            <span v-if="Number(r.is_active) === 0" class="pill">INACTIVE</span>
          </td>

          <td>
            {{ r.parent_id ? (r.parent_name || '—') : '—' }}
          </td>
        </tr>

        <tr v-if="paged.length === 0">
          <td colspan="2" class="empty">
            No menu items found.
          </td>
        </tr>
        </tbody>
      </table>

      <div class="dtFooter">
        <div class="dtInfo">
          Showing {{ startRow }} to {{ endRow }} of {{ filtered.length }}
        </div>

        <div class="dtPager">
          <button class="dtPagerBtn" :disabled="page <= 1" @click="page--">Previous</button>
          <button class="dtPagerBtn" :disabled="page >= totalPages" @click="page++">Next</button>
        </div>
      </div>
    </div>

    <MenuBuilderModal
        :open="modalOpen"
        :row="selected"
        :showInactive="showInactive"
        @close="modalOpen=false"
        @saved="onChanged"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import MenuBuilderModal from '../components/MenuBuilderModal.vue'
import { listAccountingMenus } from '../api/accounting_menus.js'

const rows = ref([])
const err = ref('')
const q = ref('')
const showInactive = ref(false)

const page = ref(1)
const pageSize = ref(25)

const modalOpen = ref(false)
const selected = ref(null)

onMounted(() => reload())
watch(q, () => page.value = 1)
watch(showInactive, () => { page.value = 1; reload() })

async function reload() {
  err.value = ''
  try {
    const data = await listAccountingMenus(showInactive.value)
    rows.value = Array.isArray(data) ? data : (data?.data ?? [])
  } catch (e) {
    err.value = e?.message || String(e)
  }
}

function openEdit(r) {
  selected.value = r
  modalOpen.value = true
}

function openNew() {
  selected.value = null
  modalOpen.value = true
}

const filtered = computed(() => {
  if (!q.value) return rows.value
  const s = q.value.toLowerCase()
  return rows.value.filter(r => JSON.stringify(r).toLowerCase().includes(s))
})

const totalPages = computed(() =>
    Math.max(1, Math.ceil(filtered.value.length / pageSize.value))
)

const paged = computed(() => {
  const start = (page.value - 1) * pageSize.value
  return filtered.value.slice(start, start + pageSize.value)
})

const startRow = computed(() =>
    filtered.value.length ? (page.value - 1) * pageSize.value + 1 : 0
)

const endRow = computed(() =>
    Math.min(page.value * pageSize.value, filtered.value.length)
)

async function onChanged() {
  modalOpen.value = false
  await reload()
}
</script>
