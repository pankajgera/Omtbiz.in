import { createI18n } from 'vue-i18n'
import en from './en.json'

const i18n = createI18n({
  legacy: true,
  locale: 'en',
  messages: {
    en,
  }
})

export default i18n
