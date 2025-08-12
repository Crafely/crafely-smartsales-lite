import { useAxios } from './useAxios'
import { getAxiosConfig } from '@/utils'
import { appConfig } from '@/config/app'
export const useAxiosNode = (token?: string | null) => {
    const baseURL = appConfig.aiBaseURL
    return useAxios(getAxiosConfig(baseURL, token))
}
