import { PrintSchema, PrintParams } from './types'

export const printBracketSchema: PrintSchema[] = [
  {
    queryName: 'theme',
    type: 'string',
    default: 'light',
  },
  {
    queryName: 'position',
    type: 'string',
    default: 'top',
  },
  {
    queryName: 'inch_height',
    localName: 'inchHeight',
    type: 'number',
    default: 16,
  },
  {
    queryName: 'inch_width',
    localName: 'inchWidth',
    type: 'number',
    default: 11,
  },
  {
    queryName: 'title',
    type: 'string',
    default: 'Winner',
  },
  {
    queryName: 'date',
    type: 'string',
    default: '',
  },
  {
    queryName: 'picks',
    type: 'object',
    default: [],
  },
  {
    queryName: 'matches',
    type: 'object',
    default: [],
  },
  {
    queryName: 'num_teams',
    localName: 'numTeams',
    type: 'number',
    default: 0,
  },
]

export const parseParams = (urlParams: any): PrintParams => {
  const parsed = printBracketSchema.reduce((acc, param) => {
    const {
      queryName: paramName,
      type: paramType,
      default: paramDefault,
    } = param

    const paramValue = urlParams.get(paramName)
    const name = param.localName || paramName

    if (paramType === 'number') {
      acc[name] = Number(paramValue) || paramDefault
    } else if (paramType === 'object') {
      acc[name] = JSON.parse(decodeURIComponent(paramValue)) || []
    } else {
      acc[name] = paramValue || paramDefault
    }

    return acc
  }, {})
  return parsed as PrintParams
}
