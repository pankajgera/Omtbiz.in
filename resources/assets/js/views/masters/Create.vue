<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ isEdit ? $t('masters.edit_master') : $t('masters.new_master') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/masters">{{ $tc('masters.account_master',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ isEdit ? $t('masters.edit_master') : $t('masters.new_master') }}</a></li>
      </ol>
    </div>
    <div class="row">
      <div class="col-sm-6">
        <div class="card">
          <form action="" @submit.prevent="submitMaster">
            <div class="card-body">
              <div class="form-group">
                <label class="control-label">{{ $t('masters.name') }}</label><span class="text-danger"> *</span>
                <base-input
                  v-model.trim="formData.name"
                  :invalid="$v.formData.name.$error"
                  focus
                  type="text"
                  name="name"
                  @input="$v.formData.name.$touch()"
                />
                <div v-if="$v.formData.name.$error">
                  <span v-if="!$v.formData.name.required" class="text-danger">{{ $t('validation.required') }} </span>
                  <span v-if="!$v.formData.name.minLength" class="text-danger">
                    {{ $tc('validation.name_min_length', $v.formData.name.$params.minLength.min, { count: $v.formData.name.$params.minLength.min }) }}
                  </span>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('masters.groups') }}</label><span class="text-danger"> *</span>
                <group-select
                  ref="selectedGroup"
                  :invalid="$v.formData.groups.$error"
                  :group-options="groupOptions"
                  :selected-group="groupOptions.find(each => each.name === this.formData.groups)"
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
                <label class="control-label">{{ $t('masters.country') }}</label><span class="text-danger"> *</span>
                      <country-select
                        v-model="formData.country"
                        :country="formData.country"
                        :className="'base-input select-input'"
                        topCountry="IN" />
                <div v-if="$v.formData.country.$error">
                  <span v-if="!$v.formData.country.maxLength" class="text-danger">{{ $t('validation.required') }}</span>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('masters.state') }}</label><span class="text-danger"> *</span>
                   <region-select
                    v-model="formData.state"
                    :country="formData.country"
                    :defaultRegion="'IN'"
                    :className="'base-input select-input'"
                    :region="formData.state" />
                <div v-if="$v.formData.state.$error">
                  <span v-if="!$v.formData.state.maxLength" class="text-danger">{{ $t('validation.required') }}</span>
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
                <label class="control-label">{{ $t('masters.balance') }}</label><span class="text-danger"> *</span>
                <base-input
                  v-model.trim="formData.balance"
                  :invalid="$v.formData.balance.$error"
                  focus
                  type="number"
                  name="balance"
                  @input="$v.formData.balance.$touch()"
                />
                <div v-if="$v.formData.balance.$error">
                    {{ $tc('validation.balance_min_length') }}
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
import { mapActions, mapGetters } from 'vuex'
import GroupSelect from './GroupSelect'
const { required, minLength, numeric, minValue, maxLength } = require('vuelidate/lib/validators')

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
        country: '',
        state: '',
        balance: 0,
      },
      groupOptions: [],
      selectedGroup: '',
    }
  },
  computed: {
    isEdit () {
      if (this.$route.name === 'masters.edit') {
        return true
      }
      return false
    }
  },
  created () {
    this.loadGroups()
    if (this.isEdit) {
      this.loadEditData()
    }
    window.hub.$on('newGroup', (val) => {
      if (!this.formData.group && this.modalActive && this.isSelected) {
        this.onSelectGroup(val)
      }
    })
  },
  validations: {
    formData: {
      name: {
        required,
        minLength: minLength(3)
      },
      groups: {
      },
      address: {
        maxLength: maxLength(255)
      },
      country: {
      },
      state: {
      },
      balance: {
      },
    }
  },
  methods: {
    ...mapActions('master', [
      'addMaster',
      'fetchMaster',
      'updateMaster'
    ]),
    ...mapActions('group', [
      'fetchGroups'
    ]),
    async loadGroups () {
      let groupResponse = await this.fetchGroups()
      this.groupOptions = groupResponse.data.groups
    },
    async loadEditData () {
      let response = await this.fetchMaster(this.$route.params.id)
      this.formData = response.data.master
    },
    async submitMaster () {
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
        return false
      }
      if (this.isEdit) {
        this.isLoading = true
        let response = await this.updateMaster(this.formData)
        if (response.data) {
          this.isLoading = false
          window.toastr['success'](this.$tc('masters.updated_message'))
          this.$router.push('/masters')
          return true
        }
        window.toastr['error'](response.data.error)
      } else {
        this.isLoading = true
        let response = await this.addMaster(this.formData)

        if (response.data) {
          window.toastr['success'](this.$tc('masters.created_message'))
          this.$router.push('/masters')
          this.isLoading = false
          return true
        }
        window.toastr['success'](response.data.success)
      }
    },
    searchVal (val) {
      this.formData.groups = val
    },
    onSelectGroup (obj) {
      this.formData.groups = obj.name
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
