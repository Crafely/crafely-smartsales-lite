import OpenAi from 'openai'
export const useOpenAi = () => {
    const API_KEY = JSON.parse(
        localStorage.getItem('chatConfig') || '{}'
    ).api_key

    if (!API_KEY) {
        console.error('Please add your API key in the settings page.')
        return {}
    }

    const openai = new OpenAi({
        apiKey: API_KEY,
        dangerouslyAllowBrowser: true,
    })

    return {
        openai,
    }
}
