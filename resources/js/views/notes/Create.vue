<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ pageTitle }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/notes">{{ $tc('notes.notes',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ pageTitle }}</a></li>
      </ol>
    </div>
    <div class="row">
      <div class="col col-12 col-md-12 col-lg-6">
        <div class="card">
          <form action="" @submit.prevent="submitNote">
            <fieldset :disabled="isView" class="card-body" id="to_print">
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
                <div id="note-upload" class="fileUpload btn btn-default">
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
              <div v-if="!isView" class="form-group">
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
            </fieldset>
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
import { required, minLength, numeric, minValue, maxLength } from '@vuelidate/validators';
export default {
  mixins: [validationMixin],
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
    isView () {
      return this.$route.name === 'notes.view'
    },
    isEdit () {
      return this.$route.name === 'notes.edit' || this.isView
    },
    pageTitle () {
      if (this.isView) return this.$tc('notes.notes', 1)
      return this.isEdit ? this.$t('notes.edit_note') : this.$t('notes.new_note')
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
      if (this.isView) return false
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
          ignoreElements: ['submit-note', 'note-upload'],
          scanStyles: false,
          style: `
            #to_print { border: 0; margin: 0; padding: 0; min-width: 0; font-family: Arial, sans-serif; }
            .form-group { margin: 0 0 14px; }
            label, .control-label { display: block; width: auto; margin: 0 0 4px; font-size: 13px; font-weight: 600; white-space: normal; }
            .text-danger { display: none; }
            input[type="text"], textarea { display: block; box-sizing: border-box; width: 100%; padding: 8px 12px; border: 1px solid #000; border-radius: 4px; font-size: 14px; line-height: 21px; background: #fff; color: #000; resize: none; }
            textarea { min-height: 110px; }
            img { max-width: 200px; height: auto; }
          `
        })
    }
  }
}
</script>
