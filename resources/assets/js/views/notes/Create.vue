<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ isEdit ? $t('notes.edit_note') : $t('notes.new_note') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/notes">{{ $tc('notes.notes',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ isEdit ? $t('notes.edit_note') : $t('notes.new_note') }}</a></li>
      </ol>
    </div>
    <div class="row">
      <div class="col col-12 col-md-12 col-lg-6">
        <div class="card">
          <form action="" @submit.prevent="submitNote">
            <div class="card-body" id="to_print">
              <div class="form-group">
                <label class="control-label">{{ $t('notes.name') }}</label><span class="text-danger"> *</span>
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
                <label class="control-label">{{ $t('notes.design_no') }}</label>
                <base-input
                  v-model.trim="formData.design_no"
                  focus
                  type="text"
                  name="design_no"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('notes.rate') }}</label>
                <base-input
                  v-model.trim="formData.rate"
                  focus
                  type="text"
                  name="rate"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('notes.average') }}</label>
                <base-input
                  v-model.trim="formData.average"
                  focus
                  type="text"
                  name="average"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('notes.per_price') }}</label>
                <base-input
                  v-model.trim="formData.per_price"
                  focus
                  format-second-last-decimal
                  type="text"
                  name="per_price"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('notes.note') }}</label>
                <base-text-area
                  v-model.trim="formData.note"
                  focus
                  type="text"
                  name="note"
                />
              </div>
                <div class="form-group">
                <div class="fileUpload btn btn-default">
                  <label class="upload mb-0">
                    <input type="file" accept="image/*" name="image" @change="uploadImage">
                      Upload Photo
                  </label>
                </div>
                  <div  v-if="previewImage" style="width:200px; height:auto; margin-top:20px">
        <img :src="previewImage" class="uploading-image" style="width: 100%; height: 100%">
      </div>
                <div v-if="formData.image">
                  <a style="font-size: 12px" :href="formData.image" target="_blank"></a>
                </div>
              </div>
              <div class="form-group">
                <base-button
                  id="submit-note"
                  :loading="isLoading"
                  :disabled="isLoading"
                  icon="save"
                  color="theme"
                  type="submit"
                  class="collapse-button"
                >
                  {{ isEdit ? $t('notes.update_note') : $t('notes.save_note') }}
                </base-button>
              </div>
            </div>
          </form>
          <button type="button" class="btn btn-lg btn-default print_no mt-2" @click="printNote">Print</button>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
input[type="file"]
{
  display: none;
}
.fileUpload input.upload
{
    display: inline-block;
}
.base-text-area.text-area-field {
    width: 100%;
    padding: 8px 13px;
    text-align: left;
    background: #FFFFFF;
    border: 1px solid #EBF1FA;
    box-sizing: border-box;
    border-radius: 5px;
    font-style: normal;
    font-weight: 500;
    font-size: 14px;
    line-height: 21px;
}
</style>
<script>
import { validationMixin } from 'vuelidate'
import { mapActions, mapGetters } from 'vuex'
const { required, minLength, numeric, minValue, maxLength } = require('vuelidate/lib/validators')

export default {
  mixins: {
    validationMixin
  },
  data () {
    return {
      isLoading: false,
      title: 'Add Note',
      previewImage: '',
      formData: {
        name: '',
        image: '',
        design_no: '',
        rate: '',
        average: '',
        per_price: '',
        note: ''
      },
    }
  },
  computed: {
    isEdit () {
      if (this.$route.name === 'notes.edit') {
        return true
      }
      return false
    }
  },
  created () {
    if (this.isEdit) {
      this.loadEditData()
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
  methods: {
    ...mapActions('notes', [
      'addNote',
      'fetchNote',
      'updateNote'
    ]),
    async loadEditData () {
      let response = await this.fetchNote(this.$route.params.id)
      this.formData = response.data.note
       this.previewImage = response.data.image
      this.formData.image = response.data.image
    },
    async submitNote () {
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
        window.toastr['error']("Error! missing required field or value is invalid.!")
        return false
      }
      this.isLoading = true
      if (this.isEdit) {
        let response = await this.updateNote(this.formData)
        if (response.data) {
          this.isLoading = false
          window.toastr['success'](this.$tc('notes.updated_message'))
          this.$router.push('/notes')
          return true
        }
        window.toastr['error'](response.data.error)
      } else {
        let response = await this.addNote(this.formData)

        if (response.data) {
          window.toastr['success'](this.$tc('notes.created_message'))
          this.$router.push('/notes')
          this.isLoading = false
          return true
        }
        window.toastr['success'](response.data.success)
      }
    },
    uploadImage (e) {
      const image = e.target.files[0]
      const reader = new FileReader()
      reader.readAsDataURL(image)
      reader.onload = e => {
        this.previewImage = e.target.result
        this.formData.image = this.previewImage
      }
    },
    printNote() {
        printJS({
          printable: 'to_print',
          type: 'html',
          ignoreElements: ['submit-note'],
          scanStyles: true,
          targetStyles: ['*'],
          style: '.base-text-area.text-area-field, .base-input .input-field { margin-bottom: 10px; width: 100%; padding: 8px 13px;text-align: left;background: #FFFFFF;border: 1px solid #000;box-sizing: border-box;border-radius: 5px;font-style: normal;font-weight: 500;font-size: 14px;line-height: 21px;}'
        })
    }
  }
}
</script>
