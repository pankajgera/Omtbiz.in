import Vue from 'vue'
import VueI18n from 'vue-i18n'
import en from './en.json'
import fr from './fr.json'
import es from './es.json'
<<<<<<< HEAD
import ar from './ar.json'
=======
>>>>>>> b7cd4d4c92eb822c2c1930072dceeafcc38c7c9d

Vue.use(VueI18n)

const i18n = new VueI18n({
  locale: 'en',
  messages: {
    en,
    fr,
<<<<<<< HEAD
    es,
    ar
=======
    es
>>>>>>> b7cd4d4c92eb822c2c1930072dceeafcc38c7c9d
  }
})

export default i18n
