import MarkdownIt from 'markdown-it'
import { isString } from 'lodash'

const md = new MarkdownIt({
    html: true,
    typographer: true,
})

export const useMarkdown = () => {
    const render = (content: string) => {
        if (isString(content)) {
            return md.render(content)
        }
        return ''
    }

    const removeHtml = (htmlString: string) => {
        const tempElement = document.createElement('div')
        tempElement.innerHTML = htmlString
        const textOutput =
            tempElement.textContent || tempElement.innerText || ''
        return textOutput.replace(/\s+/g, ' ').trim()
    }

    const removeMarkdown = (htmlString: string) => {
        const tempElement = document.createElement('div')
        tempElement.innerHTML = render(htmlString)
        const textOutput =
            tempElement.textContent || tempElement.innerText || ''
        return textOutput.replace(/\s+/g, ' ').trim()
    }

    const getPlainText = (text: string) => {
        return removeMarkdown(removeHtml(text))
    }

    return {
        render,
        removeHtml,
        getPlainText,
        removeMarkdown,
    }
}
