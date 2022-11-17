import {createApp} from "vue";
import App from './App'

const app = props=>{
    const app = createApp(App, props.props??{});
    app.provide('references', props.references);
    app.provide('wa_url', wa_url || '/');
    return app;
}

export default app;
