<template>
  <div class="container-fluid">
    Connectivité : <span id="offlineNotification" class="bi bi-reception-4 text-success"></span>

    <h1>Photos</h1>

    <div class="row">
      <button class="col-sm-3" @click="getPhotos">getPhotos</button>
      <button class="col-sm-3" @click="hydrate">hydrate</button>
      <button class="col-sm-3" @click="addPhoto">addPhoto</button>
      <button class="col-sm-3" @click="fetchIDB">fetchIDB</button>
      <button class="col-sm-3" @click="postTest">post</button>
    </div>

    <div class="row">
      <div
        v-for="photo in Photos"
        :key="photo.id"
        class="col-md-3 col-sm-4 col-xs-6 mb-2"
      >
        <div class="card">
          <img
            :src="photo.thumbnailUrl"
            class="card-img-top"
            :alt="photo.thumbnailUrl"
          />
          <div class="card-body">
            <h5 class="card-title">Album : {{ photo.albumId }}</h5>
            <p class="card-text">
              {{ photo.title }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Photo from '@/store/models/Photo'
import idbs from '@/services/idbStorage'
import { axiosInstance } from '@/services/axiosInstance'

export default {
  name: 'PhotoList',
  computed: {
    Photos () {
      return Photo.all()
    }
  },
  methods: {
    async getPhotosFromIdb () {
      const result = await idbs.dbFindAll('Photo')
      Photo.insertOrUpdate({
        data: result
      })
      console.log(
        'Photos récupérées depuis IndexedDB et insérées dans le store'
      )
    },
    async postTest () {
      await axiosInstance.post('/photos',
        {
          userId: 1,
          id: 1500,
          title: 'toto',
          body: 'toto post'
        }
      ).then(function (res) {
        console.log('Données transmises', res)
      }).catch(function (error) {
        console.log('Erreur lors de l\'envoi des données', error)
      })
    },
    async getPhotos () {
      try {
        const result = await Photo.api().get('/albums/1/photos')
        console.log("Photos récupérées depuis l'API et insérées dans le store")
        console.log(result)
        result.response.data.forEach(element => {
          idbs.dbPut('Photo', element)
          console.log("MAJ IndexedDB depuis les résultats de l'API")
        })
      } catch (error) {
        console.log('Erreur: ' + error)
      }
    },
    hydrate () {
      idbs.dbPut('Photo', {
        albumId: 1,
        id: 1555,
        title: 'toto',
        url: 'https://via.placeholder.com/600/68e0a8',
        thumbnailUrl: 'https://via.placeholder.com/150/68e0a8'
      })
      idbs.dbPut('Photo', {
        albumId: 2,
        id: 1888,
        title: 'tata',
        url: 'https://via.placeholder.com/600/68e0a8',
        thumbnailUrl: 'https://via.placeholder.com/150/68e0a8'
      })
      console.log('Insertions dans IndexedDB id=1555 & id=1888')
    },
    addPhoto () {
      Photo.insertOrUpdate({
        data: [
          {
            albumId: 1,
            id: 48,
            title: 'toto',
            url: 'https://via.placeholder.com/600/68e0a8',
            thumbnailUrl: 'https://via.placeholder.com/150/68e0a8'
          }
        ]
      })
      console.log('MAJ item 48 dans le store')
      idbs.dbPut('Photo', Photo.find(48))
      console.log('MAJ item 48 dans IndexedDB')
    },
    async fetchIDB () {
      console.log('Manupulations IndexedDB :')
      const result = await idbs.dbFindAll('Photo')
      console.log('FindAll : ', result)
      const result1 = await idbs.dbFind('Photo', 48)
      console.log('Find id=48 : ', result1)
      const result2 = await idbs.dbCount('Photo')
      console.log('Count : ' + result2)
      const result3 = await idbs.dbDelete('Photo', 47)
      console.log('Delete id=47 : ' + result3)
      const result4 = await idbs.dbCount('Photo')
      console.log('Count : ' + result4)
    }
  },
  created () {
    this.getPhotosFromIdb()

    navigator.serviceWorker.addEventListener('message', event => {
      switch (event.data) {
        case 'OFFLINE':
          document.querySelector('#offlineNotification').className = 'bi bi-reception-0 text-danger'
          break
        case 'ONLINE':
          document.querySelector('#offlineNotification').className = 'bi bi-reception-4 text-success'
          break
      }
    })
  }
}
</script>
