<script setup lang="ts">
    import Quill, { Delta } from 'quill'
    import { onMounted } from 'vue'

    onMounted(() => {
        const quill = new Quill('#editor', {
            modules: { toolbar: true },
            theme: 'snow',
        })
    })
</script>

<template>
    <div id="editor">
        <p>Hello World!</p>
        <p>Some initial <strong>bold</strong> text</p>
        <p><br /></p>
    </div>
</template>
