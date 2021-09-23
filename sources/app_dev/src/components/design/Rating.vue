<template>
  <div class="rating">
    <div>
      <span class="list">
        <span
          @click="rate(star)"
          v-for="(star, index) in maxStars"
          :class="{ active: star <= stars }"
          :key="index"
          class="star"
        >
          <i :class="star <= stars ? 'bi bi-star-fill' : 'bi bi-star'"></i>
        </span>
      </span>
      <span v-if="hasCounter" class="info counter">
        <span class="score-rating">{{ stars }}</span>
        <span class="divider">/</span>
        <span class="score-max">{{ maxStars }}</span>
      </span>
    </div>
    <div v-if="thanks" class="info">
      <span class="score-rating">{{ $t("Rating.Thanks") }}</span>
    </div>
  </div>
</template>
<script>
export default {
  name: 'Rating',
  props: {
    grade: {
      type: Number,
      default: 0
    },
    maxStars: {
      type: Number,
      default: 5
    },
    hasCounter: {
      type: Boolean,
      default: true
    },
    thanks: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      stars: this.grade
    }
  },
  methods: {
    rate (star) {
      if (!this.thanks && typeof star === 'number' && star <= this.maxStars && star >= 0) {
        this.stars = star
        this.$emit('rated', this.stars)
      }
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
.rating {
  align-items: center;
  padding: 5px;
  color: #b7b7b7;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 6px 33px rgba(19, 18, 18, 0.09);
  margin: 5px;

  .list {
    padding: 0;
    margin: 0 5px 0 0;
    &:hover {
      .star {
        color: #ffe100;
      }
    }
    .star {
      display: inline-block;
      font-size: 35px;
      transition: all 0.3s ease-in-out;
      cursor: pointer;
      &:hover {
        ~ .star:not(.active) {
          color: inherit;
        }
      }
      &:first-child {
        margin-left: 0;
      }
      &.active {
        color: #ffe100;
      }
    }
  }
  .info {
    // margin-top: 15px;
    font-size: 30px;
    text-align: center;
    display: inline-block;
    .divider {
      margin: 0 5px;
      font-size: 25px;
    }
    .score-max {
      font-size: 25px;
      vertical-align: sub;
    }
  }
  .counter {
    margin-left: 5px;
  }
}
</style>
