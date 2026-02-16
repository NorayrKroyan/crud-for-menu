<template>
  <div v-if="open" class="modalOverlay" @click.self="emit('close')">
    <div class="modalCard">
      <div class="modalHeader">
        <div class="modalTitle">
          Menu Item Form
          <span v-if="Number(form.is_active) === 0" class="pillDeleted">INACTIVE</span>
        </div>
        <button class="iconBtn" @click="emit('close')" aria-label="Close">✕</button>
      </div>

      <div class="modalBody">
        <div class="formColWrap">

          <div class="row">
            <div class="lbl">Name:</div>
            <div class="ctl">
              <input class="input inputMed" v-model.trim="form.name" type="text" />
            </div>
          </div>

          <div class="row">
            <div class="lbl">Parent:</div>
            <div class="ctl">
              <select class="select inputMed" v-model="parentSel">
                <option value="">— None (Top Level) —</option>
                <option v-for="p in parents" :key="p.id" :value="String(p.id)">
                  {{ p.name }}
                </option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="lbl">URL:</div>
            <div class="ctl">
              <input class="input" v-model.trim="form.url" type="text" />
            </div>
          </div>

          <div class="row">
            <div class="lbl">Component:</div>
            <div class="ctl">
              <input class="input" v-model.trim="form.component" type="text" />
            </div>
          </div>

          <div class="row">
            <div class="lbl">Sort Order:</div>
            <div class="ctl">
              <input class="input inputMed" v-model.number="form.sort_order" type="number" />
            </div>
          </div>

          <div class="row">
            <div class="lbl">Active:</div>
            <div class="ctl">
              <select class="select inputMed" v-model.number="form.is_active">
                <option :value="1">Yes</option>
                <option :value="0">No</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="lbl">Icon:</div>
            <div class="ctl">
              <input class="input" v-model.trim="form.icon" type="text" />
            </div>
          </div>

          <div class="row">
            <div class="lbl">Permission:</div>
            <div class="ctl">
              <input class="input" v-model.trim="form.permission" type="text" />
            </div>
          </div>

        </div>

        <div v-if="error" class="err" style="margin-top:12px;">
          {{ error }}
        </div>
      </div>

      <div class="modalFooter">
        <div class="actions">
          <button
              v-if="!isNew && Number(form.is_active) === 1"
              class="btnDanger"
              :disabled="saving"
              @click="onDeactivate"
          >
            Deactivate
          </button>
        </div>

        <div class="actions">
          <button
              class="btnPrimary"
              :disabled="saving || !canSave"
              @click="onSave"
          >
            {{ saving ? 'Saving…' : 'Save and return' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import {
  createAccountingMenu,
  updateAccountingMenu,
  deactivateAccountingMenu,
  listAccountingMenuParents,
} from '../api/accounting_menus.js'

const props = defineProps({
  open: { type: Boolean, default: false },
  row: { type: Object, default: null },
  showInactive: { type: Boolean, default: false }, // to match list checkbox
})

const emit = defineEmits(['close', 'saved'])

const saving = ref(false)
const error = ref('')

const parents = ref([])      // dropdown options
const parentSel = ref('')    // selected value as string or ''

const form = reactive({
  id: null,
  name: '',
  parent_id: null,
  url: null,
  component: null,
  sort_order: 0,
  is_active: 1,
  icon: null,
  permission: null,
})

const isNew = computed(() => !form.id)
const canSave = computed(() => String(form.name || '').trim().length > 0)

watch(
    () => props.open,
    async (v) => {
      if (!v) return
      error.value = ''

      // load parents list each time modal opens (so it stays updated)
      try {
        const data = await listAccountingMenuParents(props.showInactive)
        parents.value = Array.isArray(data) ? data : (data?.data ?? [])
      } catch (e) {
        parents.value = []
      }

      const r = props.row
      if (!r) {
        Object.assign(form, {
          id: null,
          name: '',
          parent_id: null,
          url: null,
          component: null,
          sort_order: 0,
          is_active: 1,
          icon: null,
          permission: null,
        })
        parentSel.value = ''
        return
      }

      Object.assign(form, {
        id: r.id ?? null,
        name: r.name ?? '',
        parent_id: r.parent_id ?? null,
        url: r.url ?? null,
        component: r.component ?? null,
        sort_order: Number(r.sort_order ?? 0),
        is_active: Number(r.is_active ?? 1),
        icon: r.icon ?? null,
        permission: r.permission ?? null,
      })

      parentSel.value = form.parent_id ? String(form.parent_id) : ''
    },
    { immediate: true }
)

function payload() {
  // map dropdown back into parent_id
  const pid = parentSel.value ? Number(parentSel.value) : null

  return {
    name: String(form.name || '').trim(),
    parent_id: pid,
    url: String(form.url || '').trim() || null,
    component: String(form.component || '').trim() || null,
    sort_order: Number(form.sort_order ?? 0),
    is_active: Number(form.is_active ?? 1),
    icon: String(form.icon || '').trim() || null,
    permission: String(form.permission || '').trim() || null,
  }
}

async function onSave() {
  saving.value = true
  error.value = ''
  try {
    const res = isNew.value
        ? await createAccountingMenu(payload())
        : await updateAccountingMenu(form.id, payload())

    emit('saved', res)
    emit('close')
  } catch (e) {
    error.value = e?.message || String(e)
  } finally {
    saving.value = false
  }
}

async function onDeactivate() {
  saving.value = true
  error.value = ''
  try {
    await deactivateAccountingMenu(form.id)
    emit('saved')
    emit('close')
  } catch (e) {
    error.value = e?.message || String(e)
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.formColWrap {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.row {
  display: grid;
  grid-template-columns: 180px 1fr;
  align-items: center;
  gap: 12px;
}

.lbl {
  text-align: right;
  font-weight: 900;
  font-size: 13px;
  color: #111827;
  white-space: nowrap;
}

.ctl { width: 100%; }
.inputMed { max-width: 360px; }
</style>
