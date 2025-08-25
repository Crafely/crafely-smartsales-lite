<script setup>
    import { ref } from 'vue'

    const printRef = ref(null)

    const printHandler = () => {
        const originalContent = document.body.innerHTML
        const originalHead = document.head.innerHTML

        const clone = printRef.value.cloneNode(true)

        // Copy input/textarea values
        const inputs = printRef.value.querySelectorAll('input, textarea')
        const clonedInputs = clone.querySelectorAll('input, textarea')

        inputs.forEach((input, i) => {
            const cloned = clonedInputs[i]
            if (!cloned) return
            if (cloned.tagName === 'TEXTAREA') {
                cloned.innerHTML = input.value
            } else if (cloned.tagName === 'INPUT') {
                cloned.defaultValue = input.value
            }
        })

        // Wrapper with padding
        const printWrapper = document.createElement('div')
        printWrapper.className = 'p-0'
        printWrapper.appendChild(clone)

        // Inject local styles for print (no external CDN)
        document.head.innerHTML = `
            ${originalHead}
            <style>
                @media print {
                body{
                    background: white !important;
                    color: black !important;
                }
                input, textarea {
                    font-size: 1rem !important;
                }
                }
            </style>
        `

        // Replace body and print
        document.body.innerHTML = printWrapper.outerHTML
        window.print()

        // Restore
        document.body.innerHTML = originalContent
        document.head.innerHTML = originalHead
        location.reload()
    }

    defineExpose({
        printHandler,
    })
</script>
<template>
    <div>
        <!-- <button @click="printHandler">Print</button> -->
        <div ref="printRef">
            <slot></slot>
        </div>
    </div>
</template>
