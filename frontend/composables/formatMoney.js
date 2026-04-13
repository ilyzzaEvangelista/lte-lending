/**
 * Philippine Peso (PHP) display for loan amounts and payments.
 * @param {number|string|null|undefined} value
 * @returns {string}
 */
export function formatMoney(value) {
  if (value == null || value === '') return '—'
  const n = Number(value)
  if (Number.isNaN(n)) return '—'
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(n)
}
