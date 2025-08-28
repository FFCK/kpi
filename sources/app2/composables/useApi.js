export default function useApi() {
    const apiFetch = async( url, options = {} ) => {
        // const userStore = useUserStore();
        // const { gestionErrorFetch }  =  await useGestionError() 

        return fetch( url, {
            credentials: 'include',
            ...options,
            // headers: {
            //     ...options.headers,
            //     Authorization: `Bearer ${userStore.getToken}`
            // },

            onResponse({ response }) {
                // Handle successful response      
                if (response.status == 200) {
                    
                } else {
                    console.log("1")
                    // gestionErrorFetch(response)
                }
            },

            onRequestError({ response }) {
                // Handle the request errors
                console.log("2")
                // gestionErrorFetch(response)
            },

            onResponseError({ response }) {
                console.log("3")
            //    gestionErrorFetch(response)
            }
        })
    }

    return {
        getApi: (url, options) => apiFetch(url, { ...options, method: 'GET' }),
        postApi: (url, options) => apiFetch(url, { ...options, method: 'POST' }),
        putApi: (url, options) => apiFetch(url, { ...options, method: 'PUT' }),
        deleteApi: (url, options) => apiFetch(url, { ...options, method: 'DELETE' }),
    }
}