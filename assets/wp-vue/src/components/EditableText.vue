<script setup>
    import { onMounted, watch, ref } from 'vue'

    const props = defineProps({
        modelValue: String,
        class: String,
        isNumber: {
            type: Boolean,
            default: false,
        },
    })
    const emit = defineEmits(['update:modelValue'])
    const editable = ref(null)

    onMounted(() => {
        editable.value.innerText = props.modelValue ?? ''
    })

    // Optional: Update if parent changes modelValue later (but avoid overwriting while typing)
    watch(
        () => props.modelValue,
        (newVal) => {
            if (editable.value && editable.value.innerText !== newVal) {
                editable.value.innerText = newVal
            }
        }
    )

    function onInput(e) {
        emit('update:modelValue', e.target.innerText)
    }

    function filterKeyPress(e) {
        // Allow only digits and control keys
        if (props.isNumber) {
            if (
                !/[0-9]/.test(e.key) &&
                !['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight'].includes(
                    e.key
                )
            ) {
                e.preventDefault()
            }
        }
    }
</script>

<template>
    <p
        ref="editable"
        contenteditable="true"
        class="editable p-0.5 border border-transparent cursor-text rounded-sm hover:bg-accent hover:border-accent-foreground focus:outline-none focus:border-accent-foreground"
        :class="props?.class"
        @input="onInput"
        @keypress="filterKeyPress"
    ></p>
</template>
