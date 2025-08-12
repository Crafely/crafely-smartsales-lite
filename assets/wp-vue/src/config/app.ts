const ai_url =
    import.meta.env.VITE_APP_API_SERVER_URL || 'https://api.crafely.space'
export const appConfig = {
    aiBaseURL: `${ai_url}/api/v1`,
}
