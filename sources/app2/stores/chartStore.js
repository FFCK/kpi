import { defineStore } from 'pinia'

export const useChartStore = defineStore('chartStore', {
  state: () => ({
    loading: false,
    error: null
  })
})
