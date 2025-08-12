<template>
    <div
        class="prose"
        :class="
            twMerge(
                'break-words !text-[16px] font-light leading-[30px]',
                wrapperClass
            )
        "
        v-if="isString(localContent)"
        v-html="localContent"
    ></div>
</template>

<script setup lang="ts">
    import MarkdownIt from 'markdown-it'
    import { isString } from 'lodash'
    import { computed } from 'vue'
    import { twMerge } from 'tailwind-merge'

    const isHTML = (text: string) => {
        // Match HTML tags but exclude <figure> tag
        return /<(?!chart\b)([a-z][\w-]*)(\s[^>]*)?>[\s\S]*<\/\1>/i.test(text)
    }
    const md = new MarkdownIt({
        typographer: true,
    })
    defineOptions({
        name: 'Markdown',
    })
    const props = defineProps<{
        content?: string
        htmlContent?: string
        wrapperClass?: string
        removeMargin?: boolean
    }>()

    const trimMarkdown = (text?: string) => {
        return String(text || '')
            .trim()
            .replace(/^```(\w+)?/, '') // Removes the starting ``` and any language label
            .trim()
            .replace(/```$/, '')
            .trim() // Removes the ending ```
    }
    const replaceFigureWithPlaceholder = (text?: string) => {
        let isInsideFigure = false
        return (text || '')
            .split(/(<chart\b[^>]*>|<\/chart>)/gi) // Split by <figure> and </figure> tags
            .map((segment) => {
                if (segment.toLowerCase().startsWith('<chart')) {
                    isInsideFigure = true // Start of <figure>
                    return 'Chart generating...'
                } else if (segment.toLowerCase() === '</chart>') {
                    isInsideFigure = false // End of <figure>
                    return '' // Remove the closing tag
                } else if (isInsideFigure) {
                    return '' // Skip content inside <figure> until closed
                }
                return segment // Append content outside <figure>
            })
            .join('')
    }

    const localContent = computed(() => {
        return trimMarkdown(props.htmlContent) || isHTML(props.content || '')
            ? trimMarkdown(props.content)
            : md.render(
                  trimMarkdown(replaceFigureWithPlaceholder(props.content))
              )
    })
</script>
