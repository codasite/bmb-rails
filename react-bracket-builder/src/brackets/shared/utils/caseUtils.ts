// Utility function to convert snake_case to camelCase
function toCamelCase(str: string): string {
  return str.replace(/([-_][a-z])/g, (group) =>
    group.toUpperCase().replace('-', '').replace('_', '')
  )
}
// Recursive function to convert object keys to camelCase
export function camelCaseKeys(obj: any): any {
  if (Array.isArray(obj)) {
    return obj.map((value) => camelCaseKeys(value))
  } else if (typeof obj === 'object' && obj !== null) {
    return Object.entries(obj).reduce((accumulator: any, [key, value]) => {
      accumulator[toCamelCase(key)] = camelCaseKeys(value)
      return accumulator
    }, {})
  }
  return obj
}
function camelCaseToSnakeCase(str: string): string {
  return str.replace(/[A-Z]/g, (match) => `_${match.toLowerCase()}`)
}
// Recursive function to convert object keys to snake_case
export function snakeCaseKeys(obj: any): any {
  if (Array.isArray(obj)) {
    return obj.map((value) => snakeCaseKeys(value))
  } else if (typeof obj === 'object' && obj !== null) {
    return Object.entries(obj).reduce((accumulator: any, [key, value]) => {
      accumulator[camelCaseToSnakeCase(key)] = snakeCaseKeys(value)
      return accumulator
    }, {})
  }
  return obj
}
