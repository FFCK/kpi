// fetch.js
import { ref, watchEffect, toValue } from "vue";

export async function useFetchApi() {
  const data = ref(null);

  const dataTotalInitiale = ref(null);
  const dataTotal = ref(null);
  const pageLength = ref(null);
  const error = ref(null);

  const isLoading = ref(false);

  const traitementDonne = async (response) => {
    const json = await response._data;
    data.value = await json["hydra:member"];
    dataTotal.value = await json["hydra:totalItems"];
    dataTotalInitiale.value = dataTotal.value;

    const hydraLast = await json["hydra:view"]["hydra:last"];
    if (hydraLast) {
      const params = new URLSearchParams(hydraLast.split("?")[1]);
      pageLength.value = params.get("page") || null;
    } else {
      pageLength.value = dataTotal.value > 0 ? 1 : 0;
    }
    return {
      data,
      dataTotal,
      dataTotalInitiale,
      pageLength,
      traitementDonne,
    };
  };

  const { errorNuxt, errorFetch, gestionErrorNuxt, gestionErrorFetch } =
    await useGestionError();

  const fetchData = async (url) => {
    isLoading.value = true;
    error.value = null;
    const userStore = useUserStore();
    const options = {
      bearerValue: userStore.getToken,
      queryValue: { q: url.search },
      token: userStore.getToken,
    };

    try {
      const { data: response, error } = await useFetch(url.value, {
        querry: options.queryValue,
        transform: (users) => {
          return users.map((user) => ({
            ...user,
            fullName: `${user.firstName} ${user.lastName}`,
          }));
        },
        headers: {
          Authorization: `Bearer ${options.token}`,
        },

        onResponse({ response }) {
          // Handle successful response
          if (response.status == 200) {
            const json = response._data;
            data.value = json["hydra:member"];
            dataTotal.value = json["hydra:totalItems"];
            dataTotalInitiale.value = dataTotal.value;

            const hydraLast = json["hydra:view"]["hydra:last"];
            if (hydraLast) {
              const params = new URLSearchParams(hydraLast.split("?")[1]);
              pageLength.value = params.get("page") || null;
            } else {
              pageLength.value = dataTotal.value > 0 ? 1 : 0;
            }

            console.log(hydraLast);
          } else {
            console.log("1 ");
            gestionErrorFetch(response);
          }
        },

        onRequestError({ response, options, error }) {
          // Handle the request errors
          console.log("2");
          gestionErrorFetch(response);
        },
        // Handle the response errors
        onResponseError({ response, error }) {
          console.log("3");
          gestionErrorFetch(response);
        },
      });
    } catch (err) {
      error.value = err;
      gestionErrorNuxt(error);
    } finally {
      isLoading.value = false;
    }
  };

  return {
    data,
    error,
    errorNuxt,
    errorFetch,
    dataTotal,
    dataTotalInitiale,
    pageLength,
    fetchData,
  };
}
