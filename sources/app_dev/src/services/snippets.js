/**
 *
 * @param {Array} arr (array to distribute)
 * @param {String} property (field)
 * @returns {Array}
 */
const groupBy = (arr, property) => {
  return arr.reduce((memo, x) => {
    if (!memo[x[property]]) { memo[x[property]] = [] }
    memo[x[property]].push(x)
    return memo
  }, {})
}
const globalArray = []
const distributedArray = groupBy(globalArray, 'field')
console.log(distributedArray)
