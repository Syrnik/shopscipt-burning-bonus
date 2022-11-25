import {h, nextTick, onBeforeUnmount, onMounted, watch} from 'vue';

export default {
    props: {
        width: {type: String, default: '100%'},
        height: {type: String, default: '100%'},
        theme: {type: String, default: 'eclipse'},
        lang: {default: 'html'},
        options: Object,
        content: String,
        editor: Object,
        fontSize: {default:14},
        basePath: String
    },

    setup(props, {emit}) {
        const componentId = `wa-ace-editor-${Math.random().toString().substring(2)}`;
        let _editor;

        function setMode(mode) {
            if (typeof mode === 'string') {
                _editor.session.setMode(mode.startsWith('ace/mode/') ? mode : `ace/mode/${mode}`);
                return;
            }
            if (Array.isArray(mode)) mode.forEach(m => setMode(m));
        }

        onMounted(() => {
            _editor = ace.edit(componentId);
            if(!!props.basePath) ace.config.set('basePath', props.basePath);
            _editor.$blockScrolling = Infinity;
            _editor.setTheme(`ace/theme/${props.theme}`);
            setMode(props.lang);
            if(props.options) _editor.setOptions(props.options);
            _editor.setFontSize(props.fontSize);
            _editor.setShowPrintMargin(false);
            _editor.insert(!!props.content ? props.content : '');
            emit('update:editor', _editor);
            _editor.on('change', ()=> emit('update:content', _editor.getValue())
            );
        });

        onBeforeUnmount(()=>{
            _editor.destroy();
            emit('update:editor', null);
            _editor.container.remove();
        });

        watch(()=>props.content, ()=>{
            if(props.content !== _editor.getValue()) {
                _editor.setValue(props.content);
                _editor.resize();
                // noinspection JSIgnoredPromiseFromCall
                nextTick(()=>_editor.navigateFileEnd());
            }
        })

        return ()=>h('div', {id: componentId, style: {width: props.width, height: props.height}});
    }
}

