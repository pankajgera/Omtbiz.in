<template>
  <div class="item-selector">
    <div v-if="groupOptions.id" class="selected-item">
      {{ groupOptions.name }}
      <span class="deselect-icon" @click="deselectGroup">
        <font-awesome-icon icon="times-circle" />
      </span>
    </div>
    <base-select
      v-else
      ref="baseSelect"
      v-model="groupSelect"
      :options="groupOptions"
      :show-labels="false"
      :preserve-search="true"
      :initial-search="groupOptions.name"
      :invalid="invalid"
      :placeholder="$t('groups.select_an_group')"
      label="name"
      class="multi-select-item"
      @value="onTextChange"
      @select="(val) => $emit('select', val)"
    >
      <div slot="afterList">
        <button type="button" class="list-add-button" @click="openGroupModal">
          <font-awesome-icon class="icon" icon="cart-plus" />
          <label>{{ $t('groups.add_new_group') }}</label>
        </button>
      </div>
    </base-select>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
  props: {
    groupOptions: {
      type: Array,
      required: true
    },
    invalid: {
      type: Boolean,
      required: false,
      default: false
    },
    invalidDescription: {
      type: Boolean,
      required: false,
      default: false
    }
  },
  data () {
    return {
      groupSelect: null,
      loading: false
    }
  },
  watch: {
    invalidDescription (newValue) {
      console.log(newValue)
    }
  },
  methods: {
    ...mapActions('modal', [
      'openModal'
    ]),
    ...mapActions('group', [
      'fetchGroups'
    ]),
    async searchGroups (search) {
      let data = {
        filter: {
          name: search,
        },
        orderByField: '',
        orderBy: '',
        page: 1
      }

      this.loading = true
      await this.fetchGroups(data)
      this.loading = false
    },
    onTextChange (val) {
      this.searchGroups(val)
      this.$emit('search', val)
    },
    openGroupModal () {
      this.$emit('onSelectGroup')
      this.openModal({
        'title': 'Add Group',
        'componentName': 'GroupModal'
      })
    },
    deselectGroup () {
      this.groupSelect = null
      this.$emit('deselect')
    }
  }
}
</script>
