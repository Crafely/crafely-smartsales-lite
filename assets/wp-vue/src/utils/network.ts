export const getAxiosConfig = (url, token) => {
    return {
        baseURL: url,
        headers: {
            Authorization: `Bearer ${token}`,
        },
    }
}
