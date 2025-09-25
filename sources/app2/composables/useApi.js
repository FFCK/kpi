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

    const postApi = (url, data, options = {}) => {
        return apiFetch(url, {
            ...options,
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers,
            },
            body: JSON.stringify(data)
        });
    }

    const putApi = (url, data, options = {}) => {
        return apiFetch(url, {
            ...options,
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers,
            },
            body: JSON.stringify(data)
        });
    }

    return {
        getApi: (url, options) => apiFetch(url, { ...options, method: 'GET' }),
        postApi: postApi,
        putApi: putApi,
        deleteApi: (url, options) => apiFetch(url, { ...options, method: 'DELETE' }),
    }
}