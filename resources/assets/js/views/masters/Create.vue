<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ isEdit ? $t('masters.edit_master') : $t('masters.new_master') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/masters">{{ $tc('masters.account_master',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ isEdit ? $t('masters.edit_master') : $t('masters.new_master') }}</a></li>
      </ol>
    </div>
    <div class="row">
      <div class="col col-12 col-md-12 col-lg-6">
        <div class="card">
          <form action="" @submit.prevent="submitMaster" autocomplete="off">
            <div class="card-body">
              <div class="form-group">
                <label class="control-label">{{ $t('masters.name') }}</label><span class="text-danger"> *</span>
                <base-input
                  v-model.trim="formData.name"
                  :invalid="$v.formData.name.$error"
                  focus
                  type="text"
                  name="name"
                  @input="onSearch"
                />
                <div v-if="$v.formData.name.$error || duplicateName">
                  <span v-if="!$v.formData.name.required" class="text-danger">{{ $t('validation.required') }} </span>
                  <span v-if="!$v.formData.name.minLength" class="text-danger">
                    {{ $tc('validation.name_min_length', $v.formData.name.$params.minLength.min, { count: $v.formData.name.$params.minLength.min }) }}
                  </span>
                  <span v-if="duplicateName" class="text-danger">Name already exists.</span>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('masters.groups') }}</label><span class="text-danger"> *</span>
                <group-select
                  :key="groupOptions.length"
                  ref="selectedGroup"
                  :invalid="$v.formData.groups.$error"
                  :group-options="groupOptions"
                  :selected-group="groupOptions.length ? groupOptions.find(each => each && each.name === this.formData.groups) : {}"
                  @search="searchVal"
                  @select="onSelectGroup"
                  @deselect="deselectGroup"
                  @onSelectGroup="isSelected = true"
                />
                <div v-if="$v.formData.groups.$error">
                  <span v-if="!$v.formData.groups.maxLength" class="text-danger">{{ $t('validation.required') }}</span>
                </div>
              </div>
              <div class="form-group">
                <label for="address">{{ $t('masters.address') }}</label>
                <base-text-area
                  v-model="formData.address"
                  rows="2"
                  name="address"
                  @input="$v.formData.address.$touch()"
                />
                <div v-if="$v.formData.address.$error">
                  <span v-if="!$v.formData.address.maxLength" class="text-danger">{{ $t('validation.address_maxlength') }}</span>
                </div>
              </div>
              <div class="form-group">
                <label class="input-label">{{ $tc('masters.state') }}</label><span class="text-danger"> * </span>
                <base-select
                    v-model="stateBind"
                    :invalid="$v.formData.state.$error"
                    :options="stateOptions"
                    :searchable="true"
                    :show-labels="false"
                    :allow-empty="false"
                    :placeholder="$tc('masters.select-state')"
                    autocomplete="off" aria-invalid="false" aria-haspopup="false" spellcheck="false"
                    track-by="code"
                    label="name"
                  />
                <div v-if="$v.formData.state.$error">
                  <span v-if="!$v.formData.state.required" class="text-danger">{{ $tc('validation.required') }}</span>
                </div>
              </div>
              <div class="form-group">
                <div class="col-md-12">
                  <div class="row">
                    <div class="col-md-4 p-0">
                      <label class="control-label">Type</label>
                      <select class="select-class" style="width: 95%" v-model="formData.type">
                        <option value="Cr">Cr</option>
                        <option value="Dr">Dr</option>
                      </select>
                    </div>
                    <div class="col-md-8 p-0">
                      <label class="control-label">{{ $t('masters.opening_balance') }}</label>
                      <base-input
                        v-model.trim="formData.opening_balance"
                        focus
                        type="number"
                        name="opening_balance"
                        @input="formData.opening_balance"
                      />
                    </div>
                  </div>
                </div>
              </div>
               <div class="form-group">
                <base-button
                  :loading="isLoading"
                  :disabled="isLoading"
                  icon="save"
                  color="theme"
                  type="submit"
                  class="collapse-button"
                >
                  {{ isEdit ? $t('masters.update_master') : $t('masters.save_master') }}
                </base-button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.select-class {
  min-height: 40px;
  display: block;
  padding: 8px 40px 0 8px;
  border-radius: 5px;
  border: 1px solid #EBF1FA;
  background: #fff;
  font-size: 14px;
}
.base-input.select-input{
    width: 100%;
    height: 40px;
    padding: 8px 13px;
    text-align: left;
    background: #FFFFFF;
    border: 1px solid #EBF1FA;
    box-sizing: border-box;
    border-radius: 5px;
    font-style: normal;
    font-weight: 400;
    font-size: 14px;
    line-height: 21px;
}
</style>
<script>
import { validationMixin } from 'vuelidate'
import { mapActions } from 'vuex'
import GroupSelect from './GroupSelect'
const { required, minLength, maxLength } = require('vuelidate/lib/validators')

export default {
  components: {
    GroupSelect
  },
  mixins: {
    validationMixin
  },
  data () {
    return {
      isLoading: false,
      title: 'Add Master',
      formData: {
        name: '',
        groups: '',
        address: '',
        country: 'IN',
        state: '',
        opening_balance: 0,
        type: 'Cr'
      },
      groupOptions: [],
      selectedGroup: '',
      stateOptions: [],
      duplicateName: false,
    }
  },
  computed: {
    isEdit () {
      if (this.$route.name === 'masters.edit') {
        return true
      }
      return false
    },
    stateBind: {
      get: function () {
        return this.formData.state
      },
      set: function (val) {
        this.formData.state = val
      }
    },
  },
  created () {
    this.loadGroups()
    if (this.isEdit) {
      this.loadEditData()
    }
    window.hub.$on('newGroup', (val) => {
      if (!this.formData.group && this.isSelected) {
        this.onSelectGroup(val)
      }
    })
    this.loadStates()
    this.onSearch = _.debounce(this.checkName, 1000)
  },
  validations: {
    formData: {
      name: {
        required,
        minLength: minLength(3)
      },
      groups: {
        required
      },
      state: {
        required
      },
      address: {
        maxLength: maxLength(255)
      },
    }
  },
  methods: {
    ...mapActions('master', [
      'addMaster',
      'fetchMaster',
      'updateMaster',
      'checkMasterName'
    ]),
    ...mapActions('group', [
      'fetchGroups'
    ]),
    ...mapActions('states', [
      'fetchStates'
    ]),
    async loadGroups () {
      let groupResponse = await this.fetchGroups()
      this.groupOptions = groupResponse.data.groups
    },
    async loadStates () {
      let stateResponse = await this.fetchStates()
      this.stateOptions = stateResponse.data.states
    },
    async loadEditData () {
      let response = await this.fetchMaster(this.$route.params.id)
      this.formData = response.data.master
      this.selectedGroup = this.formData.groups
    },
    async checkName() {
      let response = await this.checkMasterName(this.formData)
      if (response.data.name_exists) {
          this.duplicateName = true
          window.toastr['error']('Name already exists')
      }
    },
    async submitMaster () {
      this.$v.formData.$touch()
      if (this.$v.$invalid || this.duplicateName) {
        return false
      }
      this.formData.state = this.formData.state.name
      this.isLoading = true
      if (this.isEdit) {
        try {
          let response = await this.updateMaster(this.formData)
          if (response.data) {
            this.isLoading = false
            window.toastr['success'](this.$tc('masters.updated_message'))
            this.$router.push('/masters')
            return true
          }
        } catch (err) {
          if (err) {
            this.isLoading = false
            window.toastr['error'](err)
          }
        }
      } else {
        try {
          let response = await this.addMaster(this.formData)
          if (response.data) {
            window.toastr['success'](this.$tc('masters.created_message'))
            this.$router.push('/masters')
            this.isLoading = false
            return true
          }
        } catch (err) {
          if (err) {
            this.isLoading = false
            window.toastr['error'](err)
          }
        }
      }
    },
    searchVal (val) {
      this.formData.groups = val
    },
    onSelectGroup (obj) {
      this.loadGroups().then(() => {
        this.formData.groups = obj.name
      })
    },
    deselectGroup () {
      this.formData.groups = ''
      this.$nextTick(() => {
        this.$refs.selectedGroup.$refs.baseSelect.$refs.search.focus()
      })
    },
  }
}
</script>
