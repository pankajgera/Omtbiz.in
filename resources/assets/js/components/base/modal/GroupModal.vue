<template>
  <div class="group-modal">
    <form action="" @submit.prevent="submitGroupData">
      <div class="card-body">
        <div class="form-group row">
          <label class="col-sm-4 col-form-label input-label">
            {{ $t('groups.name') }}<span class="text-danger required">*</span>
          </label>
          <div class="col-sm-7">
            <base-input
              ref="name"
              :invalid="$v.formData.name.$error"
              v-model.trim="formData.name"
              type="text"
              @input="$v.formData.name.$touch()"
            />
            <div v-if="$v.formData.name.$error">
              <span v-if="!$v.formData.name.required" class="text-danger">{{ $tc('validation.required') }}</span>
              <span v-if="!$v.formData.name.minLength" class="text-danger">
                {{ $tc('validation.name_min_length', $v.formData.name.$params.minLength.min,
                  {
                    count: $v.formData.name.$params.minLength.min
                  })
                }}
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <base-button
          :outline="true"
          class="mr-3 display-inline"
          color="theme"
          type="button"
          @click="closeGroupModal"
        >
          {{ $t('general.cancel') }}
        </base-button>
        <base-button
          :loading="isLoading"
          icon="save"
          color="theme"
          type="submit"
          class="display-initial"
        >
          {{ $t('general.save') }}
        </base-button>
      </div>
    </form>
  </div>
</template>
<style scoped>
.display-initial{
  display: initial;
}
.display-inline{
  display: inline;
}
</style>
<script>
import { mapActions, mapGetters } from 'vuex'
import { validationMixin } from 'vuelidate'
const { required, minLength, numeric, maxLength, minValue } = require('vuelidate/lib/validators')
export default {
  mixins: [validationMixin],
  data () {
    return {
      isEdit: false,
      isLoading: false,
      tempData: null,
      formData: {
        name: null,
      }
    }
  },
  validations: {
    formData: {
      name: {
        required,
        minLength: minLength(3)
      },
    }
  },
  computed: {
    ...mapGetters('modal', [
      'modalDataID'
    ]),
    ...mapGetters('group', [
      'getGroupById'
    ])
  },
  watch: {
    modalDataID () {
      this.isEdit = true
      this.fetchEditData()
    }
  },
  created () {
    if (this.modalDataID) {
      this.isEdit = true
      this.fetchEditData()
    }
  },
  mounted () {
    this.$refs.name.focus = true
  },
  methods: {
    ...mapActions('modal', [
      'closeModal',
      'resetModalData'
    ]),
    ...mapActions('group', [
      'addGroup',
      'updateGroup'
    ]),
    resetFormData () {
      this.formData = {
        name: null,
        id: null
      }
      this.$v.$reset()
    },
    fetchEditData () {
      this.tempData = this.getGroupById(this.modalDataID)
      if (this.tempData) {
        this.formData.name = this.tempData.name
        this.formData.id = this.tempData.id
      }
    },
    async submitGroupData () {
      this.$v.formData.$touch()

      if (this.$v.$invalid) {
        return true
      }
      if (this.formData.unit) {
        this.formData.unit = this.formData.unit.name
      }
      this.isLoading = true
      let response
      if (this.isEdit) {
        response = await this.updateGroup(this.formData)
      } else {
        response = await this.addGroup(this.formData)
      }

      if (response.data) {
        window.toastr['success'](this.$tc('groups.created_message'))
        //this.setGroup(response.data.group)
        this.isLoading = false
        this.closeGroupModal()
        window.hub.$emit('newGroup', response.data.group)
      } else {
        window.toastr['error'](response.data.error)
      }
    },
    closeGroupModal () {
      this.resetFormData()
      this.closeModal()
      this.resetModalData()
    }
  }
}
</script>
