import { PrintParams } from './types'

export const validateParams = (params: PrintParams): string[] => {
  const {
    theme,
    position,
    inchHeight,
    inchWidth,
    title,
    date,
    picks,
    matches,
    numTeams,
  } = params

  const errors: string[] = []

  if (!theme || !['light', 'dark'].includes(theme)) {
    errors.push('theme must be light or dark')
  }

  if (!position || !['top', 'center', 'bottom'].includes(position)) {
    errors.push('position must be top, center, or bottom')
  }

  if (!inchHeight || inchHeight < 1 || inchHeight > 100) {
    errors.push('inchHeight must be between 1 and 100')
  }

  if (!inchWidth || inchWidth < 1 || inchWidth > 100) {
    errors.push('inchWidth must be between 1 and 100')
  }

  if (!title || title.length > 100) {
    errors.push('title is required and must be less than 100 characters')
  }

  if (date && date.length > 100) {
    errors.push('date must be less than 100 characters')
  }

  if (!picks || picks.length < 1) {
    errors.push('picks is required')
  }

  if (!matches || matches.length < 1) {
    errors.push('matches is required')
  }

  if (!numTeams || numTeams < 1) {
    errors.push('numTeams is required')
  }

  return errors
}
