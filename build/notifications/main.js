import { createApp } from 'vue'
import App from './App.vue'

const app = (props) => {
  const vueApp = createApp(App, props.props ?? {})
  vueApp.provide('references', props.references)
  vueApp.provide('wa_url', window.wa_url || '/')
  vueApp.provide('ui2', props.ui === '2.0')
  return vueApp
}

export default app
